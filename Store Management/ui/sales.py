from PyQt5.QtWidgets import (QWidget, QVBoxLayout, QHBoxLayout,
                            QLabel, QPushButton, QTableWidget, QTableWidgetItem,
                            QDialog, QFormLayout, QLineEdit, QDoubleSpinBox, QComboBox,
                            QMessageBox, QDateEdit, QFrame, QSplitter, QSpinBox,
                            QScrollArea, QGroupBox, QApplication)
from PyQt5.QtCore import Qt, QDate, QSize, QTimer
from PyQt5.QtGui import QFont, QIcon, QColor

from database.db import get_session
from database.models import Product, Inventory, Location, Activity
import datetime

class SalesWidget(QWidget):
    def __init__(self, user):
        super().__init__()
        self.user = user
        self.cart_items = []  # List to store items in the current cart
        self.init_ui()
        
    def init_ui(self):
        # Main layout
        main_layout = QVBoxLayout(self)
        main_layout.setContentsMargins(20, 20, 20, 20)
        main_layout.setSpacing(15)
        
        # Header
        header = QLabel("Point of Sale")
        header.setObjectName("page-header")
        header.setFont(QFont("Segoe UI", 24, QFont.Bold))
        main_layout.addWidget(header)
        
        # Create a splitter for the POS interface
        splitter = QSplitter(Qt.Horizontal)
        splitter.setHandleWidth(1)
        splitter.setChildrenCollapsible(False)
        
        # Left side - Products catalog
        products_widget = self.create_products_catalog()
        splitter.addWidget(products_widget)
        
        # Right side - Cart and checkout
        checkout_widget = self.create_checkout_panel()
        splitter.addWidget(checkout_widget)
        
        # Set the initial sizes of the splitter
        splitter.setSizes([600, 400])
        
        main_layout.addWidget(splitter)
        
        # Actions area at the bottom
        actions_widget = QWidget()
        actions_layout = QHBoxLayout(actions_widget)
        actions_layout.setContentsMargins(0, 10, 0, 0)
        
        # View all sales button
        view_sales_btn = QPushButton("View All Sales")
        view_sales_btn.setIcon(QIcon("resources/history-icon.png"))
        view_sales_btn.clicked.connect(self.view_all_sales)
        view_sales_btn.setMinimumHeight(40)
        actions_layout.addWidget(view_sales_btn)
        
        # Add other action buttons as needed
        main_layout.addWidget(actions_widget)
    
    def create_products_catalog(self):
        """Creates the products catalog panel"""
        catalog_widget = QWidget()
        catalog_layout = QVBoxLayout(catalog_widget)
        catalog_layout.setContentsMargins(0, 0, 10, 0)
        
        # Search bar
        search_layout = QHBoxLayout()
        search_label = QLabel("Search Products:")
        search_input = QLineEdit()
        search_input.setPlaceholderText("Enter product name, SKU or scan barcode...")
        search_btn = QPushButton()
        search_btn.setIcon(QIcon("resources/search-icon.png"))
        search_btn.clicked.connect(lambda: self.search_products(search_input.text()))
        
        search_layout.addWidget(search_label)
        search_layout.addWidget(search_input, 1)
        search_layout.addWidget(search_btn)
        
        catalog_layout.addLayout(search_layout)
        
        # Category filter
        category_layout = QHBoxLayout()
        category_label = QLabel("Filter by Category:")
        category_combo = QComboBox()
        category_combo.addItem("All Categories")
        category_combo.addItems(["Clothing", "Footwear", "Accessories", "Electronics"])
        category_combo.currentTextChanged.connect(self.filter_by_category)
        
        category_layout.addWidget(category_label)
        category_layout.addWidget(category_combo, 1)
        
        catalog_layout.addLayout(category_layout)
        
        # Products grid (using table for now)
        self.products_table = QTableWidget()
        self.products_table.setColumnCount(5)
        self.products_table.setHorizontalHeaderLabels(["SKU", "Name", "Price", "In Stock", "Action"])
        self.products_table.horizontalHeader().setStretchLastSection(True)
        self.products_table.setEditTriggers(QTableWidget.NoEditTriggers)
        self.products_table.setSelectionBehavior(QTableWidget.SelectRows)
        self.products_table.setAlternatingRowColors(True)
        
        # Load products
        self.load_products()
        
        catalog_layout.addWidget(self.products_table)
        
        return catalog_widget
    
    def create_checkout_panel(self):
        """Creates the checkout panel with cart and payment options"""
        checkout_widget = QWidget()
        checkout_layout = QVBoxLayout(checkout_widget)
        checkout_layout.setContentsMargins(10, 0, 0, 0)
        
        # Cart header
        cart_header = QLabel("Shopping Cart")
        cart_header.setFont(QFont("Segoe UI", 16, QFont.Bold))
        checkout_layout.addWidget(cart_header)
        
        # Cart table
        self.cart_table = QTableWidget()
        self.cart_table.setColumnCount(5)
        self.cart_table.setHorizontalHeaderLabels(["Item", "Price", "Qty", "Total", "Remove"])
        self.cart_table.horizontalHeader().setStretchLastSection(True)
        self.cart_table.setEditTriggers(QTableWidget.NoEditTriggers)
        self.cart_table.setAlternatingRowColors(True)
        checkout_layout.addWidget(self.cart_table)
        
        # Cart totals
        totals_frame = QFrame()
        totals_frame.setFrameShape(QFrame.StyledPanel)
        totals_frame.setStyleSheet("background-color: #f8fafc; border-radius: 8px; padding: 10px;")
        totals_layout = QFormLayout(totals_frame)
        
        self.subtotal_label = QLabel("$0.00")
        self.tax_label = QLabel("$0.00")
        self.total_label = QLabel("$0.00")
        self.total_label.setStyleSheet("font-weight: bold; font-size: 16px; color: #4361ee;")
        
        totals_layout.addRow("Subtotal:", self.subtotal_label)
        totals_layout.addRow("Tax (10%):", self.tax_label)
        totals_layout.addRow("Total:", self.total_label)
        
        checkout_layout.addWidget(totals_frame)
        
        # Payment buttons
        payment_layout = QHBoxLayout()
        
        cash_btn = QPushButton("Cash Payment")
        cash_btn.setIcon(QIcon("resources/cash-icon.png"))
        cash_btn.clicked.connect(self.process_cash_payment)
        cash_btn.setMinimumHeight(50)
        
        card_btn = QPushButton("Card Payment")
        card_btn.setIcon(QIcon("resources/card-icon.png"))
        card_btn.clicked.connect(self.process_card_payment)
        card_btn.setMinimumHeight(50)
        
        payment_layout.addWidget(cash_btn)
        payment_layout.addWidget(card_btn)
        
        checkout_layout.addLayout(payment_layout)
        
        # Cancel button
        cancel_btn = QPushButton("Cancel Sale")
        cancel_btn.setIcon(QIcon("resources/cancel-icon.png"))
        cancel_btn.clicked.connect(self.cancel_sale)
        cancel_btn.setStyleSheet("background-color: #ef4444; color: white;")
        cancel_btn.setMinimumHeight(40)
        
        checkout_layout.addWidget(cancel_btn)
        
        return checkout_widget
    
    def load_products(self):
        """Loads products from the database"""
        self.products_table.setRowCount(0)
        
        try:
            # Get products from database
            with get_session() as session:
                products = session.query(
                    Product, Inventory).join(
                    Inventory, Product.id == Inventory.product_id).filter(
                    Product.is_active == True,
                    Inventory.quantity > 0).all()
                
                if not products:
                    return
                
                self.products_table.setRowCount(len(products))
                
                for i, (product, inventory) in enumerate(products):
                    # SKU
                    sku_item = QTableWidgetItem(product.sku)
                    self.products_table.setItem(i, 0, sku_item)
                    
                    # Name
                    name_item = QTableWidgetItem(product.name)
                    self.products_table.setItem(i, 1, name_item)
                    
                    # Price
                    price_item = QTableWidgetItem(f"${product.price:.2f}")
                    self.products_table.setItem(i, 2, price_item)
                    
                    # Stock
                    stock_item = QTableWidgetItem(str(inventory.quantity))
                    self.products_table.setItem(i, 3, stock_item)
                    
                    # Add to cart button
                    add_btn_cell = QWidget()
                    add_btn_layout = QHBoxLayout(add_btn_cell)
                    add_btn_layout.setContentsMargins(0, 0, 0, 0)
                    
                    add_btn = QPushButton("Add")
                    add_btn.setProperty("product_id", product.id)
                    add_btn.setProperty("product_name", product.name)
                    add_btn.setProperty("product_price", product.price)
                    add_btn.setProperty("inventory_id", inventory.id)
                    add_btn.clicked.connect(lambda _, btn=add_btn: self.add_to_cart(
                        btn.property("product_id"),
                        btn.property("product_name"),
                        btn.property("product_price"),
                        btn.property("inventory_id")
                    ))
                    
                    add_btn_layout.addWidget(add_btn)
                    self.products_table.setCellWidget(i, 4, add_btn_cell)
        
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error loading products: {str(e)}")
    
    def add_to_cart(self, product_id, product_name, product_price, inventory_id):
        """Adds a product to the cart"""
        # Check if product is already in cart
        for item in self.cart_items:
            if item['product_id'] == product_id:
                item['quantity'] += 1
                item['total'] = item['quantity'] * item['price']
                self.update_cart_table()
                return
        
        # Add new item to cart
        self.cart_items.append({
            'product_id': product_id,
            'name': product_name,
            'price': product_price,
            'quantity': 1,
            'total': product_price,
            'inventory_id': inventory_id
        })
        
        self.update_cart_table()
    
    def update_cart_table(self):
        """Updates the cart table and totals"""
        self.cart_table.setRowCount(len(self.cart_items))
        
        subtotal = 0
        
        for i, item in enumerate(self.cart_items):
            # Name
            name_item = QTableWidgetItem(item['name'])
            self.cart_table.setItem(i, 0, name_item)
            
            # Price
            price_item = QTableWidgetItem(f"${item['price']:.2f}")
            self.cart_table.setItem(i, 1, price_item)
            
            # Quantity with spinner
            qty_cell = QWidget()
            qty_layout = QHBoxLayout(qty_cell)
            qty_layout.setContentsMargins(0, 0, 0, 0)
            
            qty_spinner = QSpinBox()
            qty_spinner.setMinimum(1)
            qty_spinner.setMaximum(100)
            qty_spinner.setValue(item['quantity'])
            qty_spinner.valueChanged.connect(lambda val, idx=i: self.update_item_quantity(idx, val))
            
            qty_layout.addWidget(qty_spinner)
            self.cart_table.setCellWidget(i, 2, qty_cell)
            
            # Total
            total_item = QTableWidgetItem(f"${item['total']:.2f}")
            self.cart_table.setItem(i, 3, total_item)
            
            # Remove button
            remove_cell = QWidget()
            remove_layout = QHBoxLayout(remove_cell)
            remove_layout.setContentsMargins(0, 0, 0, 0)
            
            remove_btn = QPushButton("✖")
            remove_btn.setMaximumWidth(30)
            remove_btn.clicked.connect(lambda _, idx=i: self.remove_from_cart(idx))
            
            remove_layout.addWidget(remove_btn)
            self.cart_table.setCellWidget(i, 4, remove_cell)
            
            # Add to subtotal
            subtotal += item['total']
        
        # Update totals
        tax = subtotal * 0.1  # 10% tax rate
        total = subtotal + tax
        
        self.subtotal_label.setText(f"${subtotal:.2f}")
        self.tax_label.setText(f"${tax:.2f}")
        self.total_label.setText(f"${total:.2f}")
    
    def update_item_quantity(self, item_index, new_quantity):
        """Updates the quantity of an item in the cart"""
        self.cart_items[item_index]['quantity'] = new_quantity
        self.cart_items[item_index]['total'] = new_quantity * self.cart_items[item_index]['price']
        self.update_cart_table()
    
    def remove_from_cart(self, item_index):
        """Removes an item from the cart"""
        del self.cart_items[item_index]
        self.update_cart_table()
    
    def search_products(self, search_text):
        """Searches products by name or SKU"""
        # Filter displayed products based on search text
        for row in range(self.products_table.rowCount()):
            show_row = False
            
            # Check SKU
            sku_item = self.products_table.item(row, 0)
            if sku_item and search_text.lower() in sku_item.text().lower():
                show_row = True
            
            # Check name
            name_item = self.products_table.item(row, 1)
            if name_item and search_text.lower() in name_item.text().lower():
                show_row = True
            
            self.products_table.setRowHidden(row, not show_row)
    
    def filter_by_category(self, category):
        """Filters products by category"""
        # This would filter from the database in a real application
        QMessageBox.information(self, "Filter Applied", f"Filtering by category: {category}")
    
    def process_cash_payment(self):
        """Processes a cash payment"""
        if not self.cart_items:
            QMessageBox.warning(self, "Empty Cart", "Please add items to the cart before checkout.")
            return
        
        total = float(self.total_label.text().replace('$', ''))
        
        # Create a cash payment dialog
        payment_dialog = QDialog(self)
        payment_dialog.setWindowTitle("Cash Payment")
        payment_dialog.setFixedSize(400, 300)
        
        layout = QVBoxLayout(payment_dialog)
        
        # Amount due
        amount_due_label = QLabel(f"Amount Due: ${total:.2f}")
        amount_due_label.setFont(QFont("Segoe UI", 16))
        amount_due_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(amount_due_label)
        
        # Cash input
        cash_form = QFormLayout()
        cash_input = QDoubleSpinBox()
        cash_input.setRange(total, 9999.99)
        cash_input.setValue(total)
        cash_input.setPrefix("$")
        cash_input.setDecimals(2)
        cash_input.setButtonSymbols(QDoubleSpinBox.NoButtons)
        cash_input.setFont(QFont("Segoe UI", 14))
        cash_form.addRow("Cash Received:", cash_input)
        
        # Change amount (calculated when cash amount changes)
        change_label = QLabel("$0.00")
        change_label.setFont(QFont("Segoe UI", 14))
        cash_form.addRow("Change:", change_label)
        
        # Update change amount when cash amount changes
        cash_input.valueChanged.connect(
            lambda value: change_label.setText(f"${value - total:.2f}")
        )
        
        layout.addLayout(cash_form)
        
        # Buttons
        button_layout = QHBoxLayout()
        
        cancel_btn = QPushButton("Cancel")
        cancel_btn.clicked.connect(payment_dialog.reject)
        
        complete_btn = QPushButton("Complete Sale")
        complete_btn.clicked.connect(payment_dialog.accept)
        complete_btn.setDefault(True)
        
        button_layout.addWidget(cancel_btn)
        button_layout.addWidget(complete_btn)
        
        layout.addLayout(button_layout)
        
        if payment_dialog.exec_() == QDialog.Accepted:
            self.complete_sale("Cash", total)
    
    def process_card_payment(self):
        """Processes a card payment"""
        if not self.cart_items:
            QMessageBox.warning(self, "Empty Cart", "Please add items to the cart before checkout.")
            return
        
        total = float(self.total_label.text().replace('$', ''))
        
        # In a real app, this would integrate with a payment processor
        # For demo purposes, we'll just show a simulated card processing dialog
        processing_dialog = QDialog(self)
        processing_dialog.setWindowTitle("Processing Payment")
        processing_dialog.setFixedSize(300, 200)
        
        layout = QVBoxLayout(processing_dialog)
        
        # Processing message
        message_label = QLabel("Processing card payment...")
        message_label.setAlignment(Qt.AlignCenter)
        message_label.setFont(QFont("Segoe UI", 14))
        layout.addWidget(message_label)
        
        processing_dialog.show()
        
        # Simulate processing delay
        QTimer.singleShot(2000, processing_dialog.accept)
        
        # Process any pending events to show the dialog
        QApplication.processEvents()
        
        if processing_dialog.result() == QDialog.Accepted:
            self.complete_sale("Card", total)
    
    def complete_sale(self, payment_method, total_amount):
        """Completes the sale and updates inventory"""
        try:
            with get_session() as session:
                # In a real app, create a Sale record and SaleItems
                # For now, just update inventory and create activity
                
                # Update inventory
                for item in self.cart_items:
                    inventory = session.query(Inventory).filter(
                        Inventory.id == item['inventory_id']
                    ).first()
                    
                    if inventory:
                        inventory.quantity -= item['quantity']
                    
                # Log activity
                activity = Activity(
                    activity_type="Sale Completed",
                    details=f"Sale completed with {payment_method} payment. Total: ${total_amount:.2f}",
                    user_id=self.user.id,
                    user_name=self.user.username
                )
                session.add(activity)
                
                session.commit()
                
                # Show receipt
                self.show_receipt(payment_method, total_amount)
                
                # Clear cart
                self.cart_items = []
                self.update_cart_table()
                
                # Refresh product list
                self.load_products()
                
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error completing sale: {str(e)}")
    
    def show_receipt(self, payment_method, total_amount):
        """Shows a receipt dialog"""
        receipt_dialog = QDialog(self)
        receipt_dialog.setWindowTitle("Receipt")
        receipt_dialog.setFixedSize(400, 500)
        
        layout = QVBoxLayout(receipt_dialog)
        
        # Store header
        store_label = QLabel("Store Management System")
        store_label.setFont(QFont("Segoe UI", 16, QFont.Bold))
        store_label.setAlignment(Qt.AlignCenter)
        layout.addWidget(store_label)
        
        # Date and time
        date_time = QLabel(datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
        date_time.setAlignment(Qt.AlignCenter)
        layout.addWidget(date_time)
        
        layout.addWidget(QLabel("--------------------------------------------"))
        
        # Items
        items_layout = QVBoxLayout()
        for item in self.cart_items:
            item_text = f"{item['name']} x {item['quantity']} @ ${item['price']:.2f} = ${item['total']:.2f}"
            items_layout.addWidget(QLabel(item_text))
        
        layout.addLayout(items_layout)
        
        layout.addWidget(QLabel("--------------------------------------------"))
        
        # Totals
        subtotal = sum(item['total'] for item in self.cart_items)
        tax = subtotal * 0.1
        
        totals_layout = QFormLayout()
        totals_layout.addRow("Subtotal:", QLabel(f"${subtotal:.2f}"))
        totals_layout.addRow("Tax (10%):", QLabel(f"${tax:.2f}"))
        totals_layout.addRow("Total:", QLabel(f"${total_amount:.2f}"))
        totals_layout.addRow("Payment Method:", QLabel(payment_method))
        
        layout.addLayout(totals_layout)
        
        layout.addWidget(QLabel("--------------------------------------------"))
        
        # Thank you message
        thank_you = QLabel("Thank you for your purchase!")
        thank_you.setAlignment(Qt.AlignCenter)
        layout.addWidget(thank_you)
        
        # Close button
        close_btn = QPushButton("Close")
        close_btn.clicked.connect(receipt_dialog.accept)
        layout.addWidget(close_btn)
        
        receipt_dialog.exec_()
    
    def cancel_sale(self):
        """Cancels the current sale"""
        if not self.cart_items:
            return
            
        reply = QMessageBox.question(self, "Confirm Cancel",
                                "Are you sure you want to cancel this sale?",
                                QMessageBox.Yes | QMessageBox.No)
                                
        if reply == QMessageBox.Yes:
            self.cart_items = []
            self.update_cart_table()
    
    def view_all_sales(self):
        """Shows all sales history"""
        # In a real app, this would show a list of all sales
        QMessageBox.information(self, "Sales History", "This would show a history of all sales.")
        
    def new_sale(self):
        """Starts a new sale"""
        self.cart_items = []
        self.update_cart_table()
        
    def back_to_dashboard(self):
        # Import here to avoid circular import
        from ui.dashboard import DashboardWindow
        
        self.dashboard = DashboardWindow(self.user)
        self.dashboard.show()
        self.close() 