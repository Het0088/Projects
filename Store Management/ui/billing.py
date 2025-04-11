from PyQt5.QtWidgets import (QWidget, QVBoxLayout, QHBoxLayout, QLabel, QPushButton, QTableWidget, 
                            QTableWidgetItem, QLineEdit, QComboBox, QSpinBox, QDoubleSpinBox, QFormLayout, 
                            QDialog, QMessageBox, QFrame, QSplitter, QApplication, QCompleter, QScrollArea,
                            QDialogButtonBox, QDateEdit, QTextEdit, QGridLayout)
from PyQt5.QtCore import Qt, QTimer, QDateTime, QDate
from PyQt5.QtGui import QFont, QIcon, QPixmap, QColor

from database.db import get_session
from database.models import Product, Inventory, Location, Activity

import datetime
import random
import string

class BillingWidget(QWidget):
    def __init__(self, user):
        super().__init__()
        self.user = user
        self.cart_items = []  # List to store items in current cart
        self.total_amount = 0.0
        self.discount_amount = 0.0
        self.tax_amount = 0.0
        self.net_amount = 0.0
        self.init_ui()
        
    def init_ui(self):
        # Main layout
        main_layout = QVBoxLayout(self)
        main_layout.setContentsMargins(15, 15, 15, 15)
        main_layout.setSpacing(15)
        
        # Header
        header = QLabel("Point of Sale / Billing")
        header.setObjectName("page-header")
        main_layout.addWidget(header)
        
        # Create horizontal splitter (left: products, right: cart)
        splitter = QSplitter(Qt.Horizontal)
        
        # Left side - Product search and selection
        left_widget = QWidget()
        left_layout = QVBoxLayout(left_widget)
        left_layout.setContentsMargins(0, 0, 10, 0)  # Add right padding for separation
        left_layout.setSpacing(15)  # Increase spacing between elements
        
        # Product search box with title
        search_title = QLabel("Search Products")
        search_title.setFont(QFont("Segoe UI", 12, QFont.Bold))
        search_title.setStyleSheet("color: #1e293b; margin-bottom: 5px;")
        left_layout.addWidget(search_title)
        
        search_container = QFrame()
        search_container.setFrameShape(QFrame.StyledPanel)
        search_container.setStyleSheet("""
            QFrame {
                background-color: white;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 10px;
            }
        """)
        search_layout = QHBoxLayout(search_container)
        search_layout.setContentsMargins(10, 10, 10, 10)
        
        search_label = QLabel("Search:")
        self.search_input = QLineEdit()
        self.search_input.setPlaceholderText("Search by name, SKU or scan barcode...")
        self.search_input.textChanged.connect(self.search_products)
        self.search_input.setStyleSheet("""
            QLineEdit {
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 8px;
                background-color: white;
                color: #1e293b;
                font-size: 13px;
            }
            QLineEdit:focus {
                border: 1px solid #3b82f6;
            }
        """)
        
        scan_btn = QPushButton("")
        scan_btn.setIcon(QIcon.fromTheme("camera"))
        scan_btn.setToolTip("Scan Barcode")
        scan_btn.clicked.connect(self.scan_barcode)
        scan_btn.setStyleSheet("""
            QPushButton {
                background-color: #4361ee;
                color: white;
                border-radius: 4px;
                padding: 8px;
                min-width: 40px;
                min-height: 40px;
            }
        """)
        
        search_layout.addWidget(search_label)
        search_layout.addWidget(self.search_input)
        search_layout.addWidget(scan_btn)
        
        left_layout.addWidget(search_container)
        
        # Products section title
        products_title = QLabel("Available Products")
        products_title.setFont(QFont("Segoe UI", 12, QFont.Bold))
        products_title.setStyleSheet("color: #1e293b; margin-top: 10px; margin-bottom: 5px;")
        left_layout.addWidget(products_title)
        
        # Products table
        self.products_table = QTableWidget()
        self.products_table.setColumnCount(5)
        self.products_table.setHorizontalHeaderLabels(["ID", "Product", "Price", "Stock", ""])
        self.products_table.setColumnHidden(0, True)  # Hide ID column
        self.products_table.setEditTriggers(QTableWidget.NoEditTriggers)
        self.products_table.setSelectionBehavior(QTableWidget.SelectRows)
        self.products_table.setMinimumHeight(400)  # Increased height
        self.products_table.doubleClicked.connect(self.add_product_to_cart)
        self.products_table.setShowGrid(True)
        self.products_table.setAlternatingRowColors(True)
        self.products_table.verticalHeader().setVisible(False)
        self.products_table.horizontalHeader().setDefaultSectionSize(150)
        self.products_table.horizontalHeader().setMinimumSectionSize(100)
        self.products_table.horizontalHeader().setFont(QFont("Segoe UI", 12, QFont.Bold))
        self.products_table.horizontalHeader().setFixedHeight(40)
        
        # Set style for products table 
        self.products_table.setStyleSheet("""
            QTableWidget {
                background-color: white;
                color: #1e293b;
                border: 1px solid #e2e8f0;
                gridline-color: #e2e8f0;
            }
            QTableWidget::item {
                padding: 8px;
                border-bottom: 1px solid #e2e8f0;
                color: #1e293b;
                font-size: 13px;
            }
            QHeaderView::section {
                background-color: #334155;
                color: white;
                padding: 8px;
                border: none;
                font-weight: bold;
                font-size: 14px;
            }
        """)
        
        left_layout.addWidget(self.products_table)
        
        # Category filter in a separate container
        filter_container = QFrame()
        filter_container.setFrameShape(QFrame.StyledPanel)
        filter_container.setStyleSheet("""
            QFrame {
                background-color: white;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 10px;
            }
        """)
        
        category_layout = QHBoxLayout(filter_container)
        category_layout.setContentsMargins(10, 10, 10, 10)
        
        category_label = QLabel("Filter by Category:")
        category_label.setFont(QFont("Segoe UI", 11))
        
        self.category_combo = QComboBox()
        self.category_combo.addItem("All Categories")
        # Will be populated with categories from database
        self.category_combo.currentTextChanged.connect(self.filter_by_category)
        self.category_combo.setStyleSheet("""
            QComboBox {
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 8px;
                background-color: white;
                color: #1e293b;
                min-height: 30px;
                font-size: 13px;
            }
            QComboBox::drop-down {
                border: none;
                background-color: #e2e8f0;
            }
        """)
        
        category_layout.addWidget(category_label)
        category_layout.addWidget(self.category_combo)
        category_layout.addStretch()
        
        left_layout.addWidget(filter_container)
        
        # Right side - Shopping cart and checkout
        right_widget = QWidget()
        right_layout = QVBoxLayout(right_widget)
        right_layout.setContentsMargins(10, 0, 0, 0)  # Add left padding for separation
        right_layout.setSpacing(20)  # Increased spacing between sections
        
        # Cart title in a styled container
        bill_header = QFrame()
        bill_header.setStyleSheet("""
            QFrame {
                background-color: #4361ee;
                border-radius: 6px;
                padding: 10px;
            }
        """)
        bill_header_layout = QVBoxLayout(bill_header)
        
        cart_title = QLabel("Current Bill")
        cart_title.setObjectName("section-header")
        cart_title.setFont(QFont("Segoe UI", 16, QFont.Bold))
        cart_title.setStyleSheet("color: white;")
        bill_header_layout.addWidget(cart_title)
        
        right_layout.addWidget(bill_header)
        
        # Customer section in a styled frame
        customer_frame = QFrame()
        customer_frame.setFrameShape(QFrame.StyledPanel)
        customer_frame.setStyleSheet("""
            QFrame {
                background-color: white;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 15px;
            }
            QLabel {
                color: #1e293b;
                font-weight: bold;
            }
            QLineEdit, QDateEdit {
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 8px;
                color: #1e293b;
                background-color: white;
            }
            QLineEdit:focus, QDateEdit:focus {
                border: 1px solid #3b82f6;
            }
        """)
        
        customer_layout = QFormLayout(customer_frame)
        customer_layout.setContentsMargins(15, 15, 15, 15)
        customer_layout.setSpacing(15)  # Increased spacing between form rows
        customer_layout.setLabelAlignment(Qt.AlignLeft)
        customer_layout.setFieldGrowthPolicy(QFormLayout.AllNonFixedFieldsGrow)
        
        # Section header
        customer_section_title = QLabel("Customer Information")
        customer_section_title.setFont(QFont("Segoe UI", 12, QFont.Bold))
        customer_section_title.setStyleSheet("color: #1e293b; margin-bottom: 10px;")
        customer_layout.addRow(customer_section_title)
        
        # Add a separator line
        separator = QFrame()
        separator.setFrameShape(QFrame.HLine)
        separator.setFrameShadow(QFrame.Sunken)
        separator.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 5px 0;")
        customer_layout.addRow(separator)
        
        # Customer name
        customer_name_label = QLabel("Customer:")
        customer_name_label.setFont(QFont("Segoe UI", 11))
        self.customer_name = QLineEdit()
        self.customer_name.setPlaceholderText("Enter customer name")
        self.customer_name.setMinimumHeight(35)
        customer_layout.addRow(customer_name_label, self.customer_name)
        
        # Customer phone
        customer_phone_label = QLabel("Phone:")
        customer_phone_label.setFont(QFont("Segoe UI", 11))
        self.customer_phone = QLineEdit()
        self.customer_phone.setPlaceholderText("Enter phone number")
        self.customer_phone.setMinimumHeight(35)
        customer_layout.addRow(customer_phone_label, self.customer_phone)
        
        # Invoice date
        date_label = QLabel("Date:")
        date_label.setFont(QFont("Segoe UI", 11))
        self.invoice_date = QDateEdit()
        self.invoice_date.setCalendarPopup(True)
        self.invoice_date.setDate(QDate.currentDate())
        self.invoice_date.setMinimumHeight(35)
        customer_layout.addRow(date_label, self.invoice_date)
        
        right_layout.addWidget(customer_frame)
        
        # Cart items section
        cart_section = QFrame()
        cart_section.setFrameShape(QFrame.StyledPanel)
        cart_section.setStyleSheet("""
            QFrame {
                background-color: white;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 15px;
            }
        """)
        cart_layout = QVBoxLayout(cart_section)
        cart_layout.setContentsMargins(15, 15, 15, 15)
        cart_layout.setSpacing(15)
        
        # Cart items header
        cart_label = QLabel("Items in Cart")
        cart_label.setFont(QFont("Segoe UI", 12, QFont.Bold))
        cart_label.setStyleSheet("color: #1e293b;")
        cart_layout.addWidget(cart_label)
        
        # Add a separator line
        cart_separator = QFrame()
        cart_separator.setFrameShape(QFrame.HLine)
        cart_separator.setFrameShadow(QFrame.Sunken)
        cart_separator.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 5px 0 10px 0;")
        cart_layout.addWidget(cart_separator)
        
        # Create a scroll area for the cart items
        self.cart_list = QWidget()
        self.cart_items_layout = QVBoxLayout(self.cart_list)
        self.cart_items_layout.setContentsMargins(0, 0, 0, 0)
        self.cart_items_layout.setSpacing(10)
        
        cart_scroll = QScrollArea()
        cart_scroll.setWidgetResizable(True)
        cart_scroll.setWidget(self.cart_list)
        cart_scroll.setMinimumHeight(250)
        cart_scroll.setVerticalScrollBarPolicy(Qt.ScrollBarAsNeeded)
        cart_scroll.setStyleSheet("""
            QScrollArea {
                background-color: #ffffff;
                border: 2px solid #334155;
                border-radius: 4px;
            }
            QScrollBar:vertical {
                border: none;
                background-color: #f1f5f9;
                width: 14px;
                margin: 0px;
            }
            QScrollBar::handle:vertical {
                background-color: #94a3b8;
                min-height: 30px;
                border-radius: 7px;
            }
            QScrollBar::handle:vertical:hover {
                background-color: #64748b;
            }
        """)
        
        cart_layout.addWidget(cart_scroll, 1)  # Give it a stretch factor of 1
        
        # Add a "No items in cart" message label
        self.empty_cart_label = QLabel("No items in cart. Add products from the left panel.")
        self.empty_cart_label.setAlignment(Qt.AlignCenter)
        self.empty_cart_label.setStyleSheet("color: #64748b; font-size: 14px; margin: 20px;")
        self.cart_items_layout.addWidget(self.empty_cart_label)
        
        right_layout.addWidget(cart_section, 1)  # Give it a stretch factor of 1 in the right layout
        
        # Cart summary section
        summary_frame = QFrame()
        summary_frame.setFrameShape(QFrame.StyledPanel)
        summary_frame.setStyleSheet("""
            QFrame {
                background-color: white;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 15px;
            }
            QLabel {
                color: #1e293b;
            }
        """)
        
        # Use GridLayout for the summary section
        summary_layout = QGridLayout(summary_frame)
        summary_layout.setContentsMargins(15, 15, 15, 15)
        summary_layout.setVerticalSpacing(20)  # Increased vertical spacing
        summary_layout.setHorizontalSpacing(15)
        
        # Summary header
        summary_title = QLabel("Order Summary")
        summary_title.setFont(QFont("Segoe UI", 12, QFont.Bold))
        summary_title.setStyleSheet("color: #1e293b; margin-bottom: 10px;")
        summary_layout.addWidget(summary_title, 0, 0, 1, 2)
        
        # Separator after the title
        summary_separator = QFrame()
        summary_separator.setFrameShape(QFrame.HLine)
        summary_separator.setFrameShadow(QFrame.Sunken)
        summary_separator.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 5px 0 15px 0;")
        summary_layout.addWidget(summary_separator, 1, 0, 1, 2)
        
        # Row 2: Subtotal
        subtotal_label = QLabel("Subtotal:")
        subtotal_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
        subtotal_label.setStyleSheet("color: #1e293b; background-color: white;")
        summary_layout.addWidget(subtotal_label, 2, 0)
        
        self.subtotal_value = QLabel("$0.00")
        self.subtotal_value.setFont(QFont("Segoe UI", 11))
        self.subtotal_value.setStyleSheet("color: #1e293b; background-color: white;")
        self.subtotal_value.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
        summary_layout.addWidget(self.subtotal_value, 2, 1)
        
        # Row 3: Discount
        discount_label = QLabel("Discount:")
        discount_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
        discount_label.setStyleSheet("color: #1e293b; background-color: white;")
        summary_layout.addWidget(discount_label, 3, 0)
        
        discount_widget = QWidget()
        discount_widget.setStyleSheet("background-color: white;")
        discount_layout = QHBoxLayout(discount_widget)
        discount_layout.setContentsMargins(0, 0, 0, 0)
        discount_layout.setSpacing(8)
        
        self.discount_type = QComboBox()
        self.discount_type.addItems(["Percentage", "Fixed Amount"])
        self.discount_type.setMinimumHeight(35)
        self.discount_type.setStyleSheet("""
            QComboBox {
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 8px;
                background-color: white;
                color: #1e293b;
                font-size: 13px;
            }
            QComboBox::drop-down {
                border: none;
                background-color: #e2e8f0;
            }
        """)
        
        self.discount_value = QDoubleSpinBox()
        self.discount_value.setRange(0, 999999)
        self.discount_value.setPrefix("$ " if self.discount_type.currentText() == "Fixed Amount" else "")
        self.discount_value.setSuffix("%" if self.discount_type.currentText() == "Percentage" else "")
        self.discount_value.valueChanged.connect(self.calculate_totals)
        self.discount_value.setMinimumHeight(35)
        self.discount_value.setMinimumWidth(120)
        self.discount_value.setStyleSheet("""
            QDoubleSpinBox {
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 8px;
                background-color: white;
                color: #1e293b;
                font-size: 13px;
            }
            QDoubleSpinBox::up-button, QDoubleSpinBox::down-button {
                background-color: #e2e8f0;
                border: 1px solid #cbd5e1;
                width: 20px;
            }
        """)
        self.discount_type.currentTextChanged.connect(self.update_discount_format)
        
        discount_layout.addWidget(self.discount_type)
        discount_layout.addWidget(self.discount_value)
        
        summary_layout.addWidget(discount_widget, 3, 1)
        
        # Row 4: Discount amount
        discount_amount_label = QLabel("Discount Amount:")
        discount_amount_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
        discount_amount_label.setStyleSheet("color: #1e293b; background-color: white;")
        summary_layout.addWidget(discount_amount_label, 4, 0)
        
        self.discount_amount_value = QLabel("$0.00")
        self.discount_amount_value.setFont(QFont("Segoe UI", 11))
        self.discount_amount_value.setStyleSheet("color: #ef4444; background-color: white;")
        self.discount_amount_value.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
        summary_layout.addWidget(self.discount_amount_value, 4, 1)
        
        # Row 5: Tax
        tax_label = QLabel("Tax (8%):")
        tax_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
        tax_label.setStyleSheet("color: #1e293b; background-color: white;")
        summary_layout.addWidget(tax_label, 5, 0)
        
        self.tax_value = QLabel("$0.00")
        self.tax_value.setFont(QFont("Segoe UI", 11))
        self.tax_value.setStyleSheet("color: #1e293b; background-color: white;")
        self.tax_value.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
        summary_layout.addWidget(self.tax_value, 5, 1)
        
        # Row 6: Separator
        separator = QFrame()
        separator.setFrameShape(QFrame.HLine)
        separator.setFrameShadow(QFrame.Sunken)
        separator.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 10px 0;")
        summary_layout.addWidget(separator, 6, 0, 1, 2)
        
        # Row 7: Total
        total_label = QLabel("Total:")
        total_label.setFont(QFont("Segoe UI", 13, QFont.Bold))
        total_label.setStyleSheet("color: #1e293b; background-color: white;")
        summary_layout.addWidget(total_label, 7, 0)
        
        self.total_value = QLabel("$0.00")
        self.total_value.setFont(QFont("Segoe UI", 13, QFont.Bold))
        self.total_value.setStyleSheet("color: #22c55e; background-color: white;")
        self.total_value.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
        summary_layout.addWidget(self.total_value, 7, 1)
        
        # Row 8: Separator
        separator2 = QFrame()
        separator2.setFrameShape(QFrame.HLine)
        separator2.setFrameShadow(QFrame.Sunken)
        separator2.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 10px 0;")
        summary_layout.addWidget(separator2, 8, 0, 1, 2)
        
        # Payment section header
        payment_title = QLabel("Payment Details")
        payment_title.setFont(QFont("Segoe UI", 12, QFont.Bold))
        payment_title.setStyleSheet("color: #1e293b; margin-top: 10px;")
        summary_layout.addWidget(payment_title, 9, 0, 1, 2)
        
        # Row 10: Payment method
        payment_method_label = QLabel("Payment Method:")
        payment_method_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
        payment_method_label.setStyleSheet("color: #1e293b; background-color: white;")
        summary_layout.addWidget(payment_method_label, 10, 0)
        
        self.payment_method = QComboBox()
        self.payment_method.addItems(["Cash", "Credit Card", "Debit Card", "Online Transfer"])
        self.payment_method.setMinimumHeight(35)
        self.payment_method.setStyleSheet("""
            QComboBox {
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 8px;
                background-color: white;
                color: #1e293b;
                font-size: 13px;
            }
            QComboBox::drop-down {
                border: none;
                background-color: #e2e8f0;
            }
        """)
        summary_layout.addWidget(self.payment_method, 10, 1)
        
        # Row 11: Payment amount
        payment_amount_label = QLabel("Amount Received:")
        payment_amount_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
        payment_amount_label.setStyleSheet("color: #1e293b; background-color: white;")
        summary_layout.addWidget(payment_amount_label, 11, 0)
        
        self.payment_amount = QDoubleSpinBox()
        self.payment_amount.setRange(0, 999999)
        self.payment_amount.setPrefix("$ ")
        self.payment_amount.valueChanged.connect(self.calculate_change)
        self.payment_amount.setMinimumHeight(35)
        self.payment_amount.setStyleSheet("""
            QDoubleSpinBox {
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                padding: 8px;
                background-color: white;
                color: #1e293b;
                font-size: 13px;
            }
            QDoubleSpinBox::up-button, QDoubleSpinBox::down-button {
                background-color: #e2e8f0;
                border: 1px solid #cbd5e1;
                width: 20px;
            }
        """)
        summary_layout.addWidget(self.payment_amount, 11, 1)
        
        # Row 12: Change
        change_label = QLabel("Change:")
        change_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
        change_label.setStyleSheet("color: #1e293b; background-color: white;")
        summary_layout.addWidget(change_label, 12, 0)
        
        self.change_value = QLabel("$0.00")
        self.change_value.setFont(QFont("Segoe UI", 11))
        self.change_value.setStyleSheet("color: #1e293b; background-color: white;")
        self.change_value.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
        summary_layout.addWidget(self.change_value, 12, 1)
        
        # Make the second column (values) stretch more
        summary_layout.setColumnStretch(1, 1)
        
        right_layout.addWidget(summary_frame)
        
        # Action buttons in a footer container
        button_container = QFrame()
        button_container.setStyleSheet("""
            QFrame {
                background-color: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 15px;
            }
        """)
        
        button_layout = QHBoxLayout(button_container)
        button_layout.setContentsMargins(15, 15, 15, 15)
        button_layout.setSpacing(15)
        
        clear_btn = QPushButton("Clear Cart")
        clear_btn.clicked.connect(self.clear_cart)
        clear_btn.setMinimumHeight(50)
        clear_btn.setStyleSheet("""
            QPushButton {
                background-color: #6b7280;
                color: white;
                font-weight: bold;
                font-size: 14px;
                padding: 12px;
                border-radius: 6px;
                min-width: 150px;
            }
            QPushButton:hover {
                background-color: #4b5563;
            }
        """)
        
        print_btn = QPushButton("Print Receipt")
        print_btn.clicked.connect(self.print_receipt)
        print_btn.setMinimumHeight(50)
        print_btn.setStyleSheet("""
            QPushButton {
                background-color: #3b82f6;
                color: white;
                font-weight: bold;
                font-size: 14px;
                padding: 12px;
                border-radius: 6px;
                min-width: 150px;
            }
            QPushButton:hover {
                background-color: #2563eb;
            }
        """)
        
        checkout_btn = QPushButton("Checkout")
        checkout_btn.setMinimumHeight(50)
        checkout_btn.setStyleSheet("""
            QPushButton {
                background-color: #22c55e;
                color: white;
                font-weight: bold;
                font-size: 16px;
                padding: 12px;
                border-radius: 6px;
                min-width: 150px;
            }
            QPushButton:hover {
                background-color: #16a34a;
            }
        """)
        checkout_btn.clicked.connect(self.checkout)
        
        button_layout.addWidget(clear_btn)
        button_layout.addWidget(print_btn)
        button_layout.addWidget(checkout_btn)
        
        right_layout.addWidget(button_container)
        
        # Add widgets to splitter
        splitter.addWidget(left_widget)
        splitter.addWidget(right_widget)
        splitter.setSizes([400, 600])  # Set initial sizes
        
        # Add splitter to main layout
        main_layout.addWidget(splitter)
        
        # Load initial data
        self.load_products()
        self.load_categories()
        
    def load_products(self):
        """Load products from database"""
        try:
            with get_session() as session:
                # Get products with inventory information
                products = session.query(
                    Product, Inventory
                ).join(
                    Inventory, Product.id == Inventory.product_id
                ).filter(
                    Product.is_active == True,
                    Inventory.quantity > 0
                ).all()
                
                self.products_table.setRowCount(len(products))
                
                for i, (product, inventory) in enumerate(products):
                    # ID (hidden)
                    id_item = QTableWidgetItem(str(product.id))
                    self.products_table.setItem(i, 0, id_item)
                    
                    # Create widget for product name and add button
                    product_widget = QWidget()
                    product_widget.setStyleSheet("background-color: white;")
                    product_layout = QVBoxLayout(product_widget)
                    product_layout.setContentsMargins(10, 10, 10, 10)
                    product_layout.setSpacing(8)
                    
                    # Product name
                    name_label = QLabel(product.name)
                    name_label.setWordWrap(True)
                    name_label.setStyleSheet("font-weight: bold; font-size: 14px; color: #1e293b;")
                    product_layout.addWidget(name_label)
                    
                    # Add category under name if available
                    if product.category:
                        category_label = QLabel(f"Category: {product.category}")
                        category_label.setStyleSheet("font-size: 12px; color: #64748b;")
                        product_layout.addWidget(category_label)
                    
                    # Add button below the name
                    add_btn = QPushButton("Add to Cart")
                    add_btn.setProperty("product_id", product.id)
                    add_btn.setProperty("product_name", product.name)
                    add_btn.setProperty("product_price", product.price)
                    add_btn.setProperty("product_stock", inventory.quantity)
                    add_btn.clicked.connect(self.add_item_to_cart)
                    add_btn.setStyleSheet("""
                        QPushButton {
                            background-color: #4361ee;
                            color: white;
                            border-radius: 4px;
                            padding: 8px;
                            min-height: 30px;
                            min-width: 120px;
                            font-size: 13px;
                            font-weight: bold;
                        }
                        QPushButton:hover {
                            background-color: #3a56d4;
                        }
                    """)
                    product_layout.addWidget(add_btn)
                    
                    self.products_table.setCellWidget(i, 1, product_widget)
                    
                    # Price
                    price_item = QTableWidgetItem(f"${product.price:.2f}")
                    price_item.setTextAlignment(Qt.AlignCenter)
                    price_item.setFont(QFont("Segoe UI", 12))
                    self.products_table.setItem(i, 2, price_item)
                    
                    # Stock
                    stock_item = QTableWidgetItem(str(inventory.quantity))
                    stock_item.setTextAlignment(Qt.AlignCenter)
                    stock_item.setFont(QFont("Segoe UI", 12))
                    self.products_table.setItem(i, 3, stock_item)
                    
                    # Set row height for better visibility
                    self.products_table.setRowHeight(i, 90)
                    
                # Adjust column widths
                self.products_table.setColumnWidth(1, 300) # Wider column for name + button
                self.products_table.setColumnWidth(2, 100)  # Price column
                self.products_table.setColumnWidth(3, 100)  # Stock column
                self.products_table.setColumnWidth(4, 0)   # Hide action column
                
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error loading products: {str(e)}")
            
    def load_categories(self):
        """Load product categories from database"""
        try:
            with get_session() as session:
                # Get distinct categories
                categories = session.query(Product.category).distinct().all()
                
                # Add categories to combo box
                for category in categories:
                    if category[0]:  # Check if category is not None or empty
                        self.category_combo.addItem(category[0])
                        
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error loading categories: {str(e)}")
            
    def search_products(self):
        """Filter products based on search input"""
        search_text = self.search_input.text().lower()
        
        for row in range(self.products_table.rowCount()):
            item_name = self.products_table.item(row, 1).text().lower()
            
            if search_text and search_text not in item_name:
                self.products_table.setRowHidden(row, True)
            else:
                current_category = self.category_combo.currentText()
                
                if current_category != "All Categories":
                    # If there's also a category filter, check if the row matches
                    # This would need the category to be in the table, which we might need to add
                    if True:  # For now, we skip category filtering in search
                        self.products_table.setRowHidden(row, False)
                else:
                    self.products_table.setRowHidden(row, False)
    
    def filter_by_category(self):
        """Filter products based on selected category"""
        category = self.category_combo.currentText()
        
        # If "All Categories" is selected, load all products
        if category == "All Categories":
            self.load_products()
            return
            
        try:
            with get_session() as session:
                # Get products with inventory information filtered by category
                products = session.query(
                    Product, Inventory
                ).join(
                    Inventory, Product.id == Inventory.product_id
                ).filter(
                    Product.is_active == True,
                    Inventory.quantity > 0,
                    Product.category == category
                ).all()
                
                self.products_table.setRowCount(len(products))
                
                for i, (product, inventory) in enumerate(products):
                    # ID (hidden)
                    id_item = QTableWidgetItem(str(product.id))
                    self.products_table.setItem(i, 0, id_item)
                    
                    # Create widget for product name and add button
                    product_widget = QWidget()
                    product_widget.setStyleSheet("background-color: white;")
                    product_layout = QVBoxLayout(product_widget)
                    product_layout.setContentsMargins(10, 10, 10, 10)
                    product_layout.setSpacing(8)
                    
                    # Product name
                    name_label = QLabel(product.name)
                    name_label.setWordWrap(True)
                    name_label.setStyleSheet("font-weight: bold; font-size: 14px; color: #1e293b;")
                    product_layout.addWidget(name_label)
                    
                    # Add category under name if available
                    if product.category:
                        category_label = QLabel(f"Category: {product.category}")
                        category_label.setStyleSheet("font-size: 12px; color: #64748b;")
                        product_layout.addWidget(category_label)
                    
                    # Add button below the name
                    add_btn = QPushButton("Add to Cart")
                    add_btn.setProperty("product_id", product.id)
                    add_btn.setProperty("product_name", product.name)
                    add_btn.setProperty("product_price", product.price)
                    add_btn.setProperty("product_stock", inventory.quantity)
                    add_btn.clicked.connect(self.add_item_to_cart)
                    add_btn.setStyleSheet("""
                        QPushButton {
                            background-color: #4361ee;
                            color: white;
                            border-radius: 4px;
                            padding: 8px;
                            min-height: 30px;
                            min-width: 120px;
                            font-size: 13px;
                            font-weight: bold;
                        }
                        QPushButton:hover {
                            background-color: #3a56d4;
                        }
                    """)
                    product_layout.addWidget(add_btn)
                    
                    self.products_table.setCellWidget(i, 1, product_widget)
                    
                    # Price
                    price_item = QTableWidgetItem(f"${product.price:.2f}")
                    price_item.setTextAlignment(Qt.AlignCenter)
                    price_item.setFont(QFont("Segoe UI", 12))
                    self.products_table.setItem(i, 2, price_item)
                    
                    # Stock
                    stock_item = QTableWidgetItem(str(inventory.quantity))
                    stock_item.setTextAlignment(Qt.AlignCenter)
                    stock_item.setFont(QFont("Segoe UI", 12))
                    self.products_table.setItem(i, 3, stock_item)
                    
                    # Set row height for better visibility
                    self.products_table.setRowHeight(i, 90)
                    
                # Adjust column widths
                self.products_table.setColumnWidth(1, 300) # Wider column for name + button
                self.products_table.setColumnWidth(2, 100)  # Price column
                self.products_table.setColumnWidth(3, 100)  # Stock column
                self.products_table.setColumnWidth(4, 0)   # Hide action column
                
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error filtering products: {str(e)}")
    
    def scan_barcode(self):
        """Simulate barcode scanning to add product"""
        scan_dialog = QDialog(self)
        scan_dialog.setWindowTitle("Barcode Scanner")
        scan_dialog.setFixedSize(300, 200)
        
        layout = QVBoxLayout(scan_dialog)
        
        # Scanning message
        scanning_label = QLabel("Scanning barcode...")
        scanning_label.setAlignment(Qt.AlignCenter)
        scanning_label.setStyleSheet("font-size: 16px; color: #333;")
        layout.addWidget(scanning_label)
        
        # Loading indicator
        loading_label = QLabel("Please wait...")
        loading_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(loading_label)
        
        # Show the dialog
        scan_dialog.show()
        
        # Simulate scanning delay
        def complete_scan():
            scan_dialog.accept()
            
            # In a real app, this would scan a real barcode
            # Here, we'll just add a random product from the list
            rows = self.products_table.rowCount()
            if rows > 0:
                random_row = random.randint(0, rows - 1)
                add_btn = self.products_table.cellWidget(random_row, 4)
                if add_btn:
                    self.add_item_to_cart(add_btn)
                    product_name = add_btn.property("product_name")
                    QMessageBox.information(self, "Product Scanned", f"Added {product_name} to cart")
            else:
                QMessageBox.warning(self, "No Products", "No products available to add")
        
        # Delay for 1.5 seconds
        QTimer.singleShot(1500, complete_scan)
        
        # Process events to show the dialog
        QApplication.processEvents()
    
    def add_product_to_cart(self):
        """Add product to cart when double-clicked"""
        selected_rows = self.products_table.selectedItems()
        if not selected_rows:
            return
        
        row = selected_rows[0].row()
        
        # Get the product ID from the hidden column
        product_id = int(self.products_table.item(row, 0).text())
        
        # Find the product in our database
        try:
            with get_session() as session:
                product = session.query(Product).filter(Product.id == product_id).first()
                inventory = session.query(Inventory).filter(Inventory.product_id == product_id).first()
                
                if product and inventory:
                    # Create a dummy button to reuse the existing add_item_to_cart function
                    dummy_btn = QPushButton()
                    dummy_btn.setProperty("product_id", product.id)
                    dummy_btn.setProperty("product_name", product.name)
                    dummy_btn.setProperty("product_price", product.price)
                    dummy_btn.setProperty("product_stock", inventory.quantity)
                    
                    self.add_item_to_cart(dummy_btn)
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Could not add product to cart: {str(e)}")
    
    def add_item_to_cart(self, button=None):
        """Add item to cart from button click"""
        # Get product data from sender button
        sender = self.sender() if not button else button
        product_id = sender.property("product_id")
        product_name = sender.property("product_name")
        product_price = float(sender.property("product_price"))
        available_stock = int(sender.property("product_stock"))
        
        # Check if product already in cart
        for i, item in enumerate(self.cart_items):
            if item['id'] == product_id:
                # Increment quantity if already in cart
                if item['quantity'] < available_stock:
                    item['quantity'] += 1
                    item['total'] = item['quantity'] * item['price']
                    item['tax'] = item['total'] * 0.08  # 8% tax
                    
                    # Update UI
                    self.update_cart_table()
                    self.calculate_totals()
                else:
                    QMessageBox.warning(self, "Stock Limit", f"Maximum available stock ({available_stock}) reached for this product")
                return
        
        # Add new item to cart
        cart_item = {
            'id': product_id,
            'name': product_name,
            'price': product_price,
            'quantity': 1,
            'total': product_price,
            'tax': product_price * 0.08,  # 8% tax
            'available_stock': available_stock
        }
        
        self.cart_items.append(cart_item)
        
        # Update UI
        self.update_cart_table()
        self.calculate_totals()
    
    def update_cart_table(self):
        """Update the cart items display"""
        # Clear all existing items
        while self.cart_items_layout.count():
            item = self.cart_items_layout.takeAt(0)
            if item.widget():
                item.widget().deleteLater()
        
        # Show/hide empty cart message
        if not self.cart_items:
            self.empty_cart_label = QLabel("No items in cart. Add products from the left panel.")
            self.empty_cart_label.setAlignment(Qt.AlignCenter)
            self.empty_cart_label.setStyleSheet("color: #64748b; font-size: 14px; margin: 20px;")
            self.cart_items_layout.addWidget(self.empty_cart_label)
            return
        
        # Add each cart item as a card
        for i, item in enumerate(self.cart_items):
            # Create card widget
            card = QFrame()
            card.setFrameShape(QFrame.StyledPanel)
            card.setStyleSheet("""
                QFrame {
                    background-color: white;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    margin: 5px 0;
                }
            """)
            
            card_layout = QGridLayout(card)
            card_layout.setContentsMargins(10, 10, 10, 10)
            card_layout.setSpacing(10)
            
            # Product name
            name_label = QLabel(item['name'])
            name_label.setFont(QFont("Segoe UI", 12, QFont.Bold))
            name_label.setStyleSheet("color: #0f172a;")
            name_label.setWordWrap(True)
            card_layout.addWidget(name_label, 0, 0, 1, 2)
            
            # Price
            price_label = QLabel(f"Price: ${item['price']:.2f}")
            price_label.setFont(QFont("Segoe UI", 11))
            price_label.setStyleSheet("color: #475569;")
            card_layout.addWidget(price_label, 1, 0)
            
            # Quantity control
            qty_widget = QWidget()
            qty_layout = QHBoxLayout(qty_widget)
            qty_layout.setContentsMargins(0, 0, 0, 0)
            qty_layout.setSpacing(5)
            
            qty_label = QLabel("Qty:")
            qty_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
            qty_layout.addWidget(qty_label)
            
            qty_spin = QSpinBox()
            qty_spin.setRange(1, item['available_stock'])
            qty_spin.setValue(item['quantity'])
            qty_spin.setProperty("row", i)
            qty_spin.valueChanged.connect(self.update_item_quantity)
            qty_spin.setFixedWidth(70)
            qty_spin.setFixedHeight(30)
            qty_spin.setButtonSymbols(QSpinBox.PlusMinus)
            qty_spin.setStyleSheet("""
                QSpinBox {
                    font-size: 13px;
                    padding: 3px;
                    background-color: white;
                    color: black;
                    border: 1px solid #334155;
                    border-radius: 4px;
                }
            """)
            qty_layout.addWidget(qty_spin)
            
            card_layout.addWidget(qty_widget, 1, 1, Qt.AlignRight)
            
            # Total and tax row
            totals_widget = QWidget()
            totals_layout = QHBoxLayout(totals_widget)
            totals_layout.setContentsMargins(0, 0, 0, 0)
            totals_layout.setSpacing(20)
            
            # Subtotal
            total_label = QLabel(f"Subtotal: ${item['total']:.2f}")
            total_label.setFont(QFont("Segoe UI", 11, QFont.Bold))
            total_label.setStyleSheet("color: #0f172a;")
            totals_layout.addWidget(total_label)
            
            # Tax
            tax_label = QLabel(f"Tax: ${item['tax']:.2f}")
            tax_label.setFont(QFont("Segoe UI", 11))
            tax_label.setStyleSheet("color: #475569;")
            totals_layout.addWidget(tax_label)
            
            card_layout.addWidget(totals_widget, 2, 0)
            
            # Remove button
            remove_btn = QPushButton("Remove")
            remove_btn.setProperty("row", i)
            remove_btn.clicked.connect(self.remove_item)
            remove_btn.setFixedWidth(80)
            remove_btn.setFixedHeight(30)
            remove_btn.setStyleSheet("""
                QPushButton {
                    background-color: #ef4444;
                    color: white;
                    font-weight: bold;
                    font-size: 12px;
                    border-radius: 4px;
                    border: none;
                }
                QPushButton:hover {
                    background-color: #dc2626;
                }
            """)
            
            card_layout.addWidget(remove_btn, 2, 1, Qt.AlignRight)
            
            # Add the card to the layout
            self.cart_items_layout.addWidget(card)
        
        # Add a stretch at the end to push all items to the top
        self.cart_items_layout.addStretch(1)
    
    def update_item_quantity(self):
        """Update item quantity when spinner changes"""
        spinner = self.sender()
        row = spinner.property("row")
        new_quantity = spinner.value()
        
        self.cart_items[row]['quantity'] = new_quantity
        self.cart_items[row]['total'] = new_quantity * self.cart_items[row]['price']
        self.cart_items[row]['tax'] = self.cart_items[row]['total'] * 0.08  # 8% tax
        
        # Update UI
        self.update_cart_table()
        self.calculate_totals()
    
    def remove_item(self):
        """Remove item from cart"""
        button = self.sender()
        row = button.property("row")
        
        if 0 <= row < len(self.cart_items):
            del self.cart_items[row]
            
            # Update UI
            self.update_cart_table()
            self.calculate_totals()
    
    def update_discount_format(self):
        """Update discount input format based on selected type"""
        if self.discount_type.currentText() == "Percentage":
            self.discount_value.setPrefix("")
            self.discount_value.setSuffix("%")
            self.discount_value.setRange(0, 100)
        else:  # Fixed Amount
            self.discount_value.setPrefix("$ ")
            self.discount_value.setSuffix("")
            self.discount_value.setRange(0, 999999)
        
        # Recalculate totals
        self.calculate_totals()
    
    def calculate_change(self):
        """Calculate change based on payment amount"""
        payment = self.payment_amount.value()
        change = payment - self.net_amount
        
        if change >= 0:
            self.change_value.setText(f"${change:.2f}")
        else:
            self.change_value.setText("Insufficient payment")
    
    def calculate_totals(self):
        """Calculate bill totals"""
        # Calculate subtotal
        self.total_amount = sum(item['total'] for item in self.cart_items)
        self.subtotal_value.setText(f"${self.total_amount:.2f}")
        
        # Calculate discount
        if self.discount_type.currentText() == "Percentage":
            percentage = self.discount_value.value()
            self.discount_amount = (percentage / 100) * self.total_amount
        else:  # Fixed Amount
            self.discount_amount = min(self.discount_value.value(), self.total_amount)
        
        self.discount_amount_value.setText(f"${self.discount_amount:.2f}")
        
        # Calculate tax
        self.tax_amount = sum(item['tax'] for item in self.cart_items)
        self.tax_value.setText(f"${self.tax_amount:.2f}")
        
        # Calculate net amount
        self.net_amount = self.total_amount - self.discount_amount + self.tax_amount
        self.total_value.setText(f"${self.net_amount:.2f}")
        
        # Update payment field
        self.payment_amount.setValue(self.net_amount)
        
        # Update change
        self.calculate_change()
    
    def clear_cart(self):
        """Clear all items from cart"""
        if not self.cart_items:
            return
            
        reply = QMessageBox.question(self, "Clear Cart", 
                                    "Are you sure you want to clear the cart?",
                                    QMessageBox.Yes | QMessageBox.No)
        
        if reply == QMessageBox.Yes:
            self.cart_items = []
            self.update_cart_table()
            self.calculate_totals()
            self.customer_name.clear()
            self.customer_phone.clear()
    
    def checkout(self):
        """Process checkout and save sale"""
        if not self.cart_items:
            QMessageBox.warning(self, "Empty Cart", "Please add items to the cart before checkout")
            return
            
        if self.payment_amount.value() < self.net_amount:
            QMessageBox.warning(self, "Insufficient Payment", 
                               f"Please enter payment of at least ${self.net_amount:.2f}")
            return
            
        try:
            invoice_number = f"INV-{datetime.datetime.now().strftime('%Y%m%d')}-{random.randint(1000, 9999)}"
            
            # In a real app, this would save the sale to the database
            # and update inventory quantities
            
            # For demo, just show success message
            QMessageBox.information(self, "Checkout Complete", 
                                  f"Sale completed successfully!\nInvoice: {invoice_number}")
            
            # Generate receipt
            self.generate_receipt(invoice_number)
            
            # Clear cart after successful checkout
            self.cart_items = []
            self.update_cart_table()
            self.calculate_totals()
            self.customer_name.clear()
            self.customer_phone.clear()
            
        except Exception as e:
            QMessageBox.critical(self, "Checkout Error", f"Error processing sale: {str(e)}")
    
    def print_receipt(self):
        """Print current receipt"""
        if not self.cart_items:
            QMessageBox.warning(self, "Empty Cart", "Please add items to the cart before printing a receipt")
            return
            
        invoice_number = f"INV-{datetime.datetime.now().strftime('%Y%m%d')}-{random.randint(1000, 9999)}"
        self.generate_receipt(invoice_number)
    
    def generate_receipt(self, invoice_number):
        """Generate and show receipt"""
        # Create receipt dialog
        receipt_dialog = QDialog(self)
        receipt_dialog.setWindowTitle("Receipt")
        receipt_dialog.setMinimumSize(400, 600)
        
        # Set background color to match application theme
        receipt_dialog.setStyleSheet("""
            QDialog {
                background-color: white;
                color: #1e293b;
            }
            QLabel {
                color: #1e293b;
                font-family: 'Segoe UI', Arial, sans-serif;
            }
            QScrollArea {
                border: none;
                background-color: white;
            }
            QWidget#receipt-content {
                background-color: white;
            }
            QPushButton {
                background-color: #4361ee;
                color: white;
                border-radius: 4px;
                padding: 8px 15px;
                min-width: 100px;
                font-weight: bold;
            }
            QPushButton:hover {
                background-color: #3a56d4;
            }
        """)
        
        layout = QVBoxLayout(receipt_dialog)
        layout.setContentsMargins(15, 15, 15, 15)
        
        # Receipt content
        scroll_area = QScrollArea()
        scroll_area.setWidgetResizable(True)
        scroll_area.setFrameShape(QFrame.NoFrame)
        
        receipt_widget = QWidget()
        receipt_widget.setObjectName("receipt-content")
        receipt_layout = QVBoxLayout(receipt_widget)
        receipt_layout.setSpacing(8)
        
        # Store header
        store_name = QLabel("Store Management System")
        store_name.setFont(QFont("Segoe UI", 18, QFont.Bold))
        store_name.setAlignment(Qt.AlignCenter)
        store_name.setStyleSheet("margin-bottom: 5px; color: #1e293b;")
        receipt_layout.addWidget(store_name)
        
        store_address = QLabel("123 Main Street, City, State 12345")
        store_address.setAlignment(Qt.AlignCenter)
        store_address.setFont(QFont("Segoe UI", 11))
        store_address.setStyleSheet("color: #475569;")
        receipt_layout.addWidget(store_address)
        
        store_phone = QLabel("Phone: (123) 456-7890")
        store_phone.setAlignment(Qt.AlignCenter)
        store_phone.setFont(QFont("Segoe UI", 11))
        store_phone.setStyleSheet("color: #475569; margin-bottom: 10px;")
        receipt_layout.addWidget(store_phone)
        
        # Separator line
        separator1 = QFrame()
        separator1.setFrameShape(QFrame.HLine)
        separator1.setFrameShadow(QFrame.Sunken)
        separator1.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 10px 0;")
        receipt_layout.addWidget(separator1)
        
        # Invoice details
        details_widget = QWidget()
        details_layout = QFormLayout(details_widget)
        details_layout.setSpacing(8)
        details_layout.setContentsMargins(5, 5, 5, 15)
        
        invoice_label = QLabel(f"{invoice_number}")
        invoice_label.setFont(QFont("Segoe UI", 11))
        invoice_title = QLabel("Invoice #:")
        invoice_title.setFont(QFont("Segoe UI", 11, QFont.Bold))
        details_layout.addRow(invoice_title, invoice_label)
        
        date_label = QLabel(f"{self.invoice_date.date().toString('MM/dd/yyyy')}")
        date_label.setFont(QFont("Segoe UI", 11))
        date_title = QLabel("Date:")
        date_title.setFont(QFont("Segoe UI", 11, QFont.Bold))
        details_layout.addRow(date_title, date_label)
        
        if self.customer_name.text():
            customer_label = QLabel(f"{self.customer_name.text()}")
            customer_label.setFont(QFont("Segoe UI", 11))
            customer_title = QLabel("Customer:")
            customer_title.setFont(QFont("Segoe UI", 11, QFont.Bold))
            details_layout.addRow(customer_title, customer_label)
            
        if self.customer_phone.text():
            phone_label = QLabel(f"{self.customer_phone.text()}")
            phone_label.setFont(QFont("Segoe UI", 11))
            phone_title = QLabel("Phone:")
            phone_title.setFont(QFont("Segoe UI", 11, QFont.Bold))
            details_layout.addRow(phone_title, phone_label)
        
        receipt_layout.addWidget(details_widget)
        
        # Separator line
        separator2 = QFrame()
        separator2.setFrameShape(QFrame.HLine)
        separator2.setFrameShadow(QFrame.Sunken)
        separator2.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 5px 0 15px 0;")
        receipt_layout.addWidget(separator2)
        
        # Items header
        items_header = QLabel("Items:")
        items_header.setFont(QFont("Segoe UI", 12, QFont.Bold))
        items_header.setStyleSheet("color: #1e293b; margin-bottom: 10px;")
        receipt_layout.addWidget(items_header)
        
        # Items table
        items_frame = QFrame()
        items_frame.setStyleSheet("background-color: #f8fafc; border-radius: 5px; padding: 10px;")
        items_layout = QVBoxLayout(items_frame)
        
        for item in self.cart_items:
            item_widget = QWidget()
            item_layout = QVBoxLayout(item_widget)
            item_layout.setSpacing(2)
            item_layout.setContentsMargins(5, 5, 5, 5)
            
            # Item name and quantity
            item_header = QLabel(f"{item['quantity']} x {item['name']}")
            item_header.setFont(QFont("Segoe UI", 11, QFont.Bold))
            item_header.setStyleSheet("color: #334155;")
            item_layout.addWidget(item_header)
            
            # Item price
            price_label = QLabel(f"${item['price']:.2f} each")
            price_label.setFont(QFont("Segoe UI", 10))
            price_label.setStyleSheet("color: #64748b; margin-left: 15px;")
            item_layout.addWidget(price_label)
            
            # Item total
            total_label = QLabel(f"Subtotal: ${item['total']:.2f}")
            total_label.setFont(QFont("Segoe UI", 10, QFont.Bold))
            total_label.setStyleSheet("color: #0f172a; margin-left: 15px;")
            total_label.setAlignment(Qt.AlignRight)
            item_layout.addWidget(total_label)
            
            # Add a line below each item except the last one
            if item != self.cart_items[-1]:
                line = QFrame()
                line.setFrameShape(QFrame.HLine)
                line.setFrameShadow(QFrame.Sunken)
                line.setStyleSheet("background-color: #e2e8f0; min-height: 1px; margin: 8px 0;")
                item_layout.addWidget(line)
            
            items_layout.addWidget(item_widget)
        
        receipt_layout.addWidget(items_frame)
        
        # Separator line
        separator3 = QFrame()
        separator3.setFrameShape(QFrame.HLine)
        separator3.setFrameShadow(QFrame.Sunken)
        separator3.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 15px 0;")
        receipt_layout.addWidget(separator3)
        
        # Totals section
        totals_frame = QFrame()
        totals_layout = QFormLayout(totals_frame)
        totals_layout.setSpacing(10)
        totals_layout.setContentsMargins(10, 10, 10, 10)
        totals_layout.setFieldGrowthPolicy(QFormLayout.AllNonFixedFieldsGrow)
        totals_layout.setLabelAlignment(Qt.AlignLeft)
        
        # Subtotal
        subtotal_title = QLabel("Subtotal:")
        subtotal_title.setFont(QFont("Segoe UI", 11))
        subtotal_title.setStyleSheet("color: #475569;")
        subtotal_value = QLabel(f"${self.total_amount:.2f}")
        subtotal_value.setFont(QFont("Segoe UI", 11))
        subtotal_value.setAlignment(Qt.AlignRight)
        totals_layout.addRow(subtotal_title, subtotal_value)
        
        # Discount if applicable
        if self.discount_amount > 0:
            discount_title = QLabel("Discount:")
            discount_title.setFont(QFont("Segoe UI", 11))
            discount_title.setStyleSheet("color: #475569;")
            discount_value = QLabel(f"-${self.discount_amount:.2f}")
            discount_value.setFont(QFont("Segoe UI", 11))
            discount_value.setStyleSheet("color: #ef4444;")
            discount_value.setAlignment(Qt.AlignRight)
            totals_layout.addRow(discount_title, discount_value)
        
        # Tax
        tax_title = QLabel("Tax (8%):")
        tax_title.setFont(QFont("Segoe UI", 11))
        tax_title.setStyleSheet("color: #475569;")
        tax_value = QLabel(f"${self.tax_amount:.2f}")
        tax_value.setFont(QFont("Segoe UI", 11))
        tax_value.setAlignment(Qt.AlignRight)
        totals_layout.addRow(tax_title, tax_value)
        
        # Add a separator before total
        total_separator = QFrame()
        total_separator.setFrameShape(QFrame.HLine)
        total_separator.setFrameShadow(QFrame.Sunken)
        total_separator.setStyleSheet("background-color: #e2e8f0; min-height: 1px;")
        totals_layout.addRow("", total_separator)
        
        # Total
        total_title = QLabel("Total:")
        total_title.setFont(QFont("Segoe UI", 12, QFont.Bold))
        total_title.setStyleSheet("color: #0f172a;")
        total_value = QLabel(f"${self.net_amount:.2f}")
        total_value.setFont(QFont("Segoe UI", 12, QFont.Bold))
        total_value.setStyleSheet("color: #16a34a;")
        total_value.setAlignment(Qt.AlignRight)
        totals_layout.addRow(total_title, total_value)
        
        receipt_layout.addWidget(totals_frame)
        
        # Payment info
        payment_frame = QFrame()
        payment_layout = QFormLayout(payment_frame)
        payment_layout.setSpacing(8)
        payment_layout.setContentsMargins(10, 10, 10, 10)
        payment_layout.setFieldGrowthPolicy(QFormLayout.AllNonFixedFieldsGrow)
        
        # Payment method
        method_title = QLabel("Payment Method:")
        method_title.setFont(QFont("Segoe UI", 11))
        method_title.setStyleSheet("color: #475569;")
        method_value = QLabel(f"{self.payment_method.currentText()}")
        method_value.setFont(QFont("Segoe UI", 11))
        method_value.setAlignment(Qt.AlignRight)
        payment_layout.addRow(method_title, method_value)
        
        # Amount paid
        paid_title = QLabel("Amount Paid:")
        paid_title.setFont(QFont("Segoe UI", 11))
        paid_title.setStyleSheet("color: #475569;")
        paid_value = QLabel(f"${self.payment_amount.value():.2f}")
        paid_value.setFont(QFont("Segoe UI", 11))
        paid_value.setAlignment(Qt.AlignRight)
        payment_layout.addRow(paid_title, paid_value)
        
        # Change
        change = self.payment_amount.value() - self.net_amount
        change_title = QLabel("Change:")
        change_title.setFont(QFont("Segoe UI", 11))
        change_title.setStyleSheet("color: #475569;")
        change_value = QLabel(f"${change:.2f}")
        change_value.setFont(QFont("Segoe UI", 11))
        change_value.setAlignment(Qt.AlignRight)
        payment_layout.addRow(change_title, change_value)
        
        receipt_layout.addWidget(payment_frame)
        
        # Separator line
        separator4 = QFrame()
        separator4.setFrameShape(QFrame.HLine)
        separator4.setFrameShadow(QFrame.Sunken)
        separator4.setStyleSheet("background-color: #e2e8f0; min-height: 2px; margin: 10px 0;")
        receipt_layout.addWidget(separator4)
        
        # Footer
        thank_you = QLabel("Thank you for your purchase!")
        thank_you.setAlignment(Qt.AlignCenter)
        thank_you.setFont(QFont("Segoe UI", 12, QFont.Bold))
        thank_you.setStyleSheet("color: #4361ee; margin: 10px 0;")
        receipt_layout.addWidget(thank_you)
        
        # Add current date time
        current_datetime = QLabel(f"Printed: {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        current_datetime.setAlignment(Qt.AlignCenter)
        current_datetime.setFont(QFont("Segoe UI", 9))
        current_datetime.setStyleSheet("color: #94a3b8; margin-top: 5px;")
        receipt_layout.addWidget(current_datetime)
        
        scroll_area.setWidget(receipt_widget)
        layout.addWidget(scroll_area)
        
        # Buttons
        button_layout = QHBoxLayout()
        
        ok_btn = QPushButton("OK")
        ok_btn.setMinimumHeight(40)
        ok_btn.setMinimumWidth(120)
        ok_btn.clicked.connect(receipt_dialog.accept)
        
        save_btn = QPushButton("Save")
        save_btn.setMinimumHeight(40)
        save_btn.setMinimumWidth(120)
        save_btn.clicked.connect(lambda: self.save_receipt(invoice_number))
        
        button_layout.addStretch()
        button_layout.addWidget(ok_btn)
        button_layout.addWidget(save_btn)
        
        layout.addLayout(button_layout)
        
        receipt_dialog.exec_()
    
    def save_receipt(self, invoice_number):
        """Save receipt to file"""
        QMessageBox.information(self, "Save Receipt", 
                              f"Receipt for {invoice_number} would be saved to file.\n(This is a placeholder for the actual file save functionality)")