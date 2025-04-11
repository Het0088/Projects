from PyQt5.QtWidgets import (QWidget, QVBoxLayout, QHBoxLayout, 
                            QLabel, QPushButton, QTableWidget, QTableWidgetItem,
                            QTabWidget, QDialog, QMessageBox, QSpinBox, QDoubleSpinBox,
                            QLineEdit, QComboBox, QTextEdit, QDialogButtonBox, QFormLayout)
from PyQt5.QtCore import Qt
from PyQt5.QtGui import QFont, QIcon
from PyQt5.QtCore import QTimer
from PyQt5.QtWidgets import QApplication

from database.db import get_session
from database.models import Product, Inventory, Location, Activity
import datetime
import random
import string
import time

class InventoryWidget(QWidget):
    def __init__(self, user):
        super().__init__()
        self.user = user
        self.init_ui()
        
    def init_ui(self):
        # Main layout
        main_layout = QVBoxLayout(self)
        
        # Header
        header = QLabel("Inventory Management")
        header.setObjectName("page-header")
        main_layout.addWidget(header)
        
        # Create tab widget
        tab_widget = QTabWidget()
        
        # Products tab
        products_tab = QWidget()
        products_layout = QVBoxLayout(products_tab)
        
        # Button container for Add and Refresh buttons
        button_container = QWidget()
        button_layout = QHBoxLayout(button_container)
        button_layout.setContentsMargins(0, 0, 0, 0)
        
        # Add new product button
        add_product_btn = QPushButton("Add New Product")
        add_product_btn.clicked.connect(self.add_new_product)
        button_layout.addWidget(add_product_btn)
        
        # Add refresh button
        refresh_btn = QPushButton("Refresh Data")
        refresh_btn.setIcon(QIcon.fromTheme("view-refresh"))
        refresh_btn.clicked.connect(self.refresh_all_data)
        button_layout.addWidget(refresh_btn)
        
        # Add spacer to push buttons to left
        button_layout.addStretch()
        
        products_layout.addWidget(button_container)
        
        # Products table
        self.products_table = self.create_products_table()
        products_layout.addWidget(self.products_table)
        
        tab_widget.addTab(products_tab, "Products")
        
        # Store Inventory tab
        store_tab = QWidget()
        store_layout = QVBoxLayout(store_tab)
        
        # Button container for inventory tab
        store_button_container = QWidget()
        store_button_layout = QHBoxLayout(store_button_container)
        store_button_layout.setContentsMargins(0, 0, 0, 0)
        
        # Transfer stock button
        transfer_btn = QPushButton("Transfer Stock")
        transfer_btn.clicked.connect(self.transfer_stock)
        store_button_layout.addWidget(transfer_btn)
        
        # Add refresh button for store inventory
        store_refresh_btn = QPushButton("Refresh Data")
        store_refresh_btn.setIcon(QIcon.fromTheme("view-refresh"))
        store_refresh_btn.clicked.connect(self.refresh_all_data)
        store_button_layout.addWidget(store_refresh_btn)
        
        # Add spacer
        store_button_layout.addStretch()
        
        store_layout.addWidget(store_button_container)
        
        # Store inventory table
        self.store_inventory_table = self.create_inventory_table()
        store_layout.addWidget(self.store_inventory_table)
        
        tab_widget.addTab(store_tab, "Store Inventory")
        
        # Warehouse tab
        warehouse_tab = QWidget()
        warehouse_layout = QVBoxLayout(warehouse_tab)
        
        # Button container for warehouse tab
        warehouse_button_container = QWidget()
        warehouse_button_layout = QHBoxLayout(warehouse_button_container)
        warehouse_button_layout.setContentsMargins(0, 0, 0, 0)
        
        # Add refresh button for warehouse
        warehouse_refresh_btn = QPushButton("Refresh Data")
        warehouse_refresh_btn.setIcon(QIcon.fromTheme("view-refresh"))
        warehouse_refresh_btn.clicked.connect(self.refresh_all_data)
        warehouse_button_layout.addWidget(warehouse_refresh_btn)
        
        # Add spacer
        warehouse_button_layout.addStretch()
        
        warehouse_layout.addWidget(warehouse_button_container)
        
        # Warehouse inventory table
        self.warehouse_inventory_table = self.create_warehouse_table()
        warehouse_layout.addWidget(self.warehouse_inventory_table)
        
        tab_widget.addTab(warehouse_tab, "Warehouse Stock")
        
        main_layout.addWidget(tab_widget)
    
    def create_products_table(self):
        table = QTableWidget()
        table.setColumnCount(6)
        table.setHorizontalHeaderLabels(["SKU", "Name", "Category", "Price", "Cost", "Actions"])
        table.setMinimumHeight(400)  # Make table taller to show more products
        
        # Apply stylesheet for better visibility
        table.setStyleSheet("""
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
        
        # Get products with all attributes loaded
        products_data = []
        try:
            with get_session() as session:
                products = session.query(Product).all()
                # Collect all data needed before session closes
                for product in products:
                    products_data.append({
                        'id': product.id,
                        'sku': product.sku if product.sku else "",
                        'name': product.name if product.name else "",
                        'category': product.category if product.category else "",
                        'price': product.price if product.price else 0.0,
                        'cost_price': product.cost_price if product.cost_price else 0.0
                    })
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error loading products: {str(e)}")
            return table
        
        table.setRowCount(len(products_data))
        
        for i, product in enumerate(products_data):
            # Create table items with improved visibility
            sku_item = QTableWidgetItem(product['sku'])
            sku_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 0, sku_item)
            
            name_item = QTableWidgetItem(product['name'])
            name_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 1, name_item)
            
            category_item = QTableWidgetItem(product['category'])
            category_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 2, category_item)
            
            price_item = QTableWidgetItem(f"${product['price']:.2f}")
            price_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 3, price_item)
            
            cost_item = QTableWidgetItem(f"${product['cost_price']:.2f}" if product['cost_price'] else "")
            cost_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 4, cost_item)
            
            # Actions
            actions_widget = QWidget()
            actions_layout = QHBoxLayout(actions_widget)
            
            edit_btn = QPushButton("Edit")
            edit_btn.setProperty("product_id", product['id'])
            edit_btn.clicked.connect(lambda _, id=product['id']: self.edit_product(id))
            edit_btn.setStyleSheet("""
                QPushButton {
                    background-color: #3b82f6;
                    color: white;
                    border-radius: 4px;
                    padding: 4px 8px;
                    font-weight: bold;
                }
                QPushButton:hover {
                    background-color: #2563eb;
                }
            """)
            
            delete_btn = QPushButton("Delete")
            delete_btn.setProperty("product_id", product['id'])
            delete_btn.clicked.connect(lambda _, id=product['id']: self.delete_product(id))
            delete_btn.setStyleSheet("""
                QPushButton {
                    background-color: #ef4444;
                    color: white;
                    border-radius: 4px;
                    padding: 4px 8px;
                    font-weight: bold;
                }
                QPushButton:hover {
                    background-color: #dc2626;
                }
            """)
            
            actions_layout.addWidget(edit_btn)
            actions_layout.addWidget(delete_btn)
            actions_layout.setContentsMargins(0, 0, 0, 0)
            
            table.setCellWidget(i, 5, actions_widget)
        
        # Set row height to be larger for better visibility
        for i in range(table.rowCount()):
            table.setRowHeight(i, 45)
            
        table.setSortingEnabled(True)
        table.resizeColumnsToContents()
        
        # Add product count label at the bottom
        status_label = QLabel(f"Total Products: {len(products_data)}")
        status_label.setAlignment(Qt.AlignRight)
        status_label.setFont(QFont("Segoe UI", 12))
        status_label.setStyleSheet("color: #1e293b; margin-top: 5px;")
        
        # Safety check for parent and layout
        if table.parent() is not None:
            parent_layout = table.parent().layout()
            if parent_layout is not None:
                parent_layout.addWidget(status_label)
            else:
                # Create a temporary container for the label if needed
                container = QWidget()
                container_layout = QVBoxLayout(container)
                container_layout.addWidget(table)
                container_layout.addWidget(status_label)
        
        return table
    
    def create_inventory_table(self):
        table = QTableWidget()
        table.setColumnCount(5)
        table.setHorizontalHeaderLabels(["Product", "Location", "Quantity", "Min Level", "Actions"])
        table.setMinimumHeight(350)  # Make table taller to show more items
        
        # Apply stylesheet for better visibility
        table.setStyleSheet("""
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
        
        # Get inventory for store locations with all data eagerly loaded
        inventory_data = []
        try:
            with get_session() as session:
                store_inventory = session.query(
                    Inventory, Product, Location).join(
                    Product, Inventory.product_id == Product.id).join(
                    Location, Inventory.location_id == Location.id).filter(
                    Location.type == "STORE").all()
                
                # Collect all data needed before session closes
                for inventory, product, location in store_inventory:
                    inventory_data.append({
                        'id': inventory.id,
                        'product_name': product.name,
                        'location_name': location.name,
                        'quantity': inventory.quantity,
                        'min_stock_level': inventory.min_stock_level
                    })
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error loading store inventory: {str(e)}")
            return table
        
        table.setRowCount(len(inventory_data))
        
        for i, inventory in enumerate(inventory_data):
            # Create table items with improved visibility
            product_item = QTableWidgetItem(inventory['product_name'])
            product_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 0, product_item)
            
            location_item = QTableWidgetItem(inventory['location_name'])
            location_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 1, location_item)
            
            quantity_item = QTableWidgetItem(str(inventory['quantity']))
            quantity_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 2, quantity_item)
            
            min_level_item = QTableWidgetItem(str(inventory['min_stock_level']))
            min_level_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 3, min_level_item)
            
            # Actions
            actions_widget = QWidget()
            actions_layout = QHBoxLayout(actions_widget)
            
            adjust_btn = QPushButton("Adjust")
            adjust_btn.setProperty("inventory_id", inventory['id'])
            adjust_btn.clicked.connect(lambda _, id=inventory['id']: self.adjust_inventory(id))
            adjust_btn.setStyleSheet("""
                QPushButton {
                    background-color: #3b82f6;
                    color: white;
                    border-radius: 4px;
                    padding: 4px 8px;
                    font-weight: bold;
                }
                QPushButton:hover {
                    background-color: #2563eb;
                }
            """)
            
            actions_layout.addWidget(adjust_btn)
            actions_layout.setContentsMargins(0, 0, 0, 0)
            
            table.setCellWidget(i, 4, actions_widget)
        
        # Set row height to be larger for better visibility
        for i in range(table.rowCount()):
            table.setRowHeight(i, 45)
            
        table.setSortingEnabled(True)
        table.resizeColumnsToContents()
        
        # Add inventory count label at the bottom
        status_label = QLabel(f"Total Store Inventory Items: {len(inventory_data)}")
        status_label.setAlignment(Qt.AlignRight)
        status_label.setFont(QFont("Segoe UI", 12))
        status_label.setStyleSheet("color: #1e293b; margin-top: 5px;")
        
        # Safety check for parent and layout
        if table.parent() is not None:
            parent_layout = table.parent().layout()
            if parent_layout is not None:
                parent_layout.addWidget(status_label)
            else:
                # Create a temporary container for the label if needed
                container = QWidget()
                container_layout = QVBoxLayout(container)
                container_layout.addWidget(table)
                container_layout.addWidget(status_label)
        
        return table
    
    def create_warehouse_table(self):
        table = QTableWidget()
        table.setColumnCount(5)
        table.setHorizontalHeaderLabels(["Product", "Location", "Quantity", "Min Level", "Actions"])
        table.setMinimumHeight(350)  # Make table taller to show more items
        
        # Apply stylesheet for better visibility
        table.setStyleSheet("""
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
        
        # Get inventory for warehouse locations with all data eagerly loaded
        inventory_data = []
        try:
            with get_session() as session:
                warehouse_inventory = session.query(
                    Inventory, Product, Location).join(
                    Product, Inventory.product_id == Product.id).join(
                    Location, Inventory.location_id == Location.id).filter(
                    Location.type == "WAREHOUSE").all()
                
                # Collect all data needed before session closes
                for inventory, product, location in warehouse_inventory:
                    inventory_data.append({
                        'id': inventory.id,
                        'product_name': product.name,
                        'location_name': location.name,
                        'quantity': inventory.quantity,
                        'min_stock_level': inventory.min_stock_level
                    })
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error loading warehouse inventory: {str(e)}")
            return table
        
        table.setRowCount(len(inventory_data))
        
        for i, inventory in enumerate(inventory_data):
            # Create table items with improved visibility
            product_item = QTableWidgetItem(inventory['product_name'])
            product_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 0, product_item)
            
            location_item = QTableWidgetItem(inventory['location_name'])
            location_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 1, location_item)
            
            quantity_item = QTableWidgetItem(str(inventory['quantity']))
            quantity_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 2, quantity_item)
            
            min_level_item = QTableWidgetItem(str(inventory['min_stock_level']))
            min_level_item.setFont(QFont("Segoe UI", 12))
            table.setItem(i, 3, min_level_item)
            
            # Actions
            actions_widget = QWidget()
            actions_layout = QHBoxLayout(actions_widget)
            
            adjust_btn = QPushButton("Adjust")
            adjust_btn.setProperty("inventory_id", inventory['id'])
            adjust_btn.clicked.connect(lambda _, id=inventory['id']: self.adjust_inventory(id))
            adjust_btn.setStyleSheet("""
                QPushButton {
                    background-color: #3b82f6;
                    color: white;
                    border-radius: 4px;
                    padding: 4px 8px;
                    font-weight: bold;
                }
                QPushButton:hover {
                    background-color: #2563eb;
                }
            """)
            
            actions_layout.addWidget(adjust_btn)
            actions_layout.setContentsMargins(0, 0, 0, 0)
            
            table.setCellWidget(i, 4, actions_widget)
        
        # Set row height to be larger for better visibility
        for i in range(table.rowCount()):
            table.setRowHeight(i, 45)
            
        table.setSortingEnabled(True)
        table.resizeColumnsToContents()
        
        # Add warehouse inventory count label at the bottom
        status_label = QLabel(f"Total Warehouse Inventory Items: {len(inventory_data)}")
        status_label.setAlignment(Qt.AlignRight)
        status_label.setFont(QFont("Segoe UI", 12))
        status_label.setStyleSheet("color: #1e293b; margin-top: 5px;")
        
        # Safety check for parent and layout
        if table.parent() is not None:
            parent_layout = table.parent().layout()
            if parent_layout is not None:
                parent_layout.addWidget(status_label)
            else:
                # Create a temporary container for the label if needed
                container = QWidget()
                container_layout = QVBoxLayout(container)
                container_layout.addWidget(table)
                container_layout.addWidget(status_label)
        
        return table
    def add_new_product(self):
        dialog = QDialog(self)
        dialog.setWindowTitle("Add New Product")
        dialog.setMinimumWidth(400)
        
        # Set appropriate styling for the dialog
        dialog.setStyleSheet("""
            QDialog {
                background-color: white;
                color: #333333;
            }
            QLabel {
                color: #333333;
                font-weight: 500;
            }
            QLineEdit, QComboBox, QDoubleSpinBox, QSpinBox, QTextEdit {
                padding: 8px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                background-color: white;
                color: #333333;
                min-height: 20px;
            }
            QDoubleSpinBox, QSpinBox {
                min-width: 100px;
            }
            QPushButton {
                background-color: #4361ee;
                color: white;
                border: none;
                border-radius: 4px;
                padding: 8px 16px;
                font-weight: bold;
            }
            QPushButton:hover {
                background-color: #3a56d4;
            }
            QPushButton:pressed {
                background-color: #2e45b5;
            }
            QPushButton[text="Cancel"] {
                background-color: #64748b;
            }
            QPushButton[text="Cancel"]:hover {
                background-color: #475569;
            }
        """)
        
        layout = QVBoxLayout(dialog)
        layout.setContentsMargins(20, 20, 20, 20)
        layout.setSpacing(15)
        
        form_layout = QFormLayout()
        form_layout.setVerticalSpacing(12)
        form_layout.setLabelAlignment(Qt.AlignRight | Qt.AlignVCenter)
        
        # Product name
        name_input = QLineEdit()
        form_layout.addRow("Product Name:", name_input)
        
        # SKU
        sku_layout = QHBoxLayout()
        sku_input = QLineEdit()
        scan_btn = QPushButton("Scan Barcode")
        scan_btn.setIcon(QIcon("resources/barcode-icon.png"))
        scan_btn.clicked.connect(lambda: self.simulate_barcode_scan(sku_input))
        sku_layout.addWidget(sku_input)
        sku_layout.addWidget(scan_btn)
        form_layout.addRow("SKU:", sku_layout)
        
        # Category
        category_combo = QComboBox()
        category_combo.addItems(["", "Clothing", "Footwear", "Accessories", "Electronics"])
        form_layout.addRow("Category:", category_combo)
        
        # Price
        price_input = QDoubleSpinBox()
        price_input.setMaximum(9999.99)
        price_input.setMinimum(0.01)
        price_input.setValue(19.99)
        form_layout.addRow("Price:", price_input)
        
        # Cost price
        cost_input = QDoubleSpinBox()
        cost_input.setMaximum(9999.99)
        cost_input.setMinimum(0.01)
        cost_input.setValue(9.99)
        form_layout.addRow("Cost Price:", cost_input)
        
        # Description
        description_input = QTextEdit()
        description_input.setMaximumHeight(100)
        form_layout.addRow("Description:", description_input)
        
        # Initial stock
        stock_input = QSpinBox()
        stock_input.setMinimum(0)
        stock_input.setMaximum(9999)
        form_layout.addRow("Initial Stock:", stock_input)
        
        # Location
        location_combo = QComboBox()
        location_combo.addItems(["Main Store", "Warehouse A", "Warehouse B"])
        form_layout.addRow("Location:", location_combo)
        
        layout.addLayout(form_layout)
        
        # Buttons
        button_box = QDialogButtonBox(QDialogButtonBox.Ok | QDialogButtonBox.Cancel)
        button_box.accepted.connect(dialog.accept)
        button_box.rejected.connect(dialog.reject)
        layout.addWidget(button_box)
        
        result = dialog.exec_()
        
        if result == QDialog.Accepted:
            try:
                # Get all values before database operations
                product_name = name_input.text()
                product_sku = sku_input.text()
                product_category = category_combo.currentText()
                product_price = price_input.value()
                product_cost = cost_input.value()
                product_description = description_input.toPlainText()
                product_stock = stock_input.value()
                location_name = location_combo.currentText()
                location_type = "STORE" if location_name == "Main Store" else "WAREHOUSE"
                
                # Save the new product
                with get_session() as session:
                    try:
                        # Create new product
                        product = Product(
                            name=product_name,
                            sku=product_sku,
                            description=product_description,
                            category=product_category,
                            price=product_price,
                            cost_price=product_cost
                        )
                        session.add(product)
                        session.flush()  # This assigns an ID to the product before commit
                        
                        # Create or get location
                        location = session.query(Location).filter_by(name=location_name).first()
                        if not location:
                            location = Location(name=location_name, type=location_type)
                            session.add(location)
                            session.flush()
                        
                        # Add inventory record if initial stock > 0
                        if product_stock > 0:
                            inventory = Inventory(
                                product_id=product.id,
                                location_id=location.id,
                                quantity=product_stock
                            )
                            session.add(inventory)
                        
                        # Log the activity
                        activity = Activity(
                            activity_type="Product Added",
                            details=f"Added new product: {product_name} (SKU: {product_sku})",
                            user_id=self.user.id,
                            user_name=self.user.username
                        )
                        session.add(activity)
                        
                        # Commit all changes
                        session.commit()
                        
                        QMessageBox.information(self, "Success", f"Product '{product_name}' added successfully!")
                        self.refresh_all_tables()
                    except Exception as e:
                        QMessageBox.critical(self, "Error", f"Failed to add product: {str(e)}")
                
            except Exception as e:
                QMessageBox.critical(self, "Error", f"Failed to add product: {str(e)}")
    
    def refresh_all_tables(self):
        """Refresh all inventory tables"""
        try:
            # Create new tables first
            new_products_table = self.create_products_table()
            new_store_inventory_table = self.create_inventory_table()
            new_warehouse_inventory_table = self.create_warehouse_table()
            
            # Get the tab widget
            tab_widget = None
            if self.layout() and self.layout().count() > 2:
                tab_widget = self.layout().itemAt(2).widget()
                
            if tab_widget:
                # Refresh Products table
                if tab_widget.count() > 0:
                    products_tab = tab_widget.widget(0)
                    if products_tab and products_tab.layout():
                        products_layout = products_tab.layout()
                        
                        # Remove old table if it exists
                        if products_layout.count() > 1:  # Button and table
                            old_table = products_layout.itemAt(1).widget()
                            if old_table:
                                products_layout.removeWidget(old_table)
                                old_table.deleteLater()
                                
                        # Add new table
                        products_layout.addWidget(new_products_table)
                        self.products_table = new_products_table
                
                # Refresh Store Inventory table
                if tab_widget.count() > 1:
                    store_tab = tab_widget.widget(1)
                    if store_tab and store_tab.layout():
                        store_layout = store_tab.layout()
                        
                        # Remove old table if it exists
                        if store_layout.count() > 1:  # Button and table
                            old_store_table = store_layout.itemAt(1).widget()
                            if old_store_table:
                                store_layout.removeWidget(old_store_table)
                                old_store_table.deleteLater()
                                
                        # Add new table
                        store_layout.addWidget(new_store_inventory_table)
                        self.store_inventory_table = new_store_inventory_table
                
                # Refresh Warehouse Inventory table
                if tab_widget.count() > 2:
                    warehouse_tab = tab_widget.widget(2)
                    if warehouse_tab and warehouse_tab.layout():
                        warehouse_layout = warehouse_tab.layout()
                        
                        # Remove old table if it exists
                        if warehouse_layout.count() > 0:
                            old_warehouse_table = warehouse_layout.itemAt(0).widget()
                            if old_warehouse_table:
                                warehouse_layout.removeWidget(old_warehouse_table)
                                old_warehouse_table.deleteLater()
                                
                        # Add new table
                        warehouse_layout.addWidget(new_warehouse_inventory_table)
                        self.warehouse_inventory_table = new_warehouse_inventory_table
            else:
                # If tab widget doesn't exist, just update the references
                self.products_table = new_products_table
                self.store_inventory_table = new_store_inventory_table
                self.warehouse_inventory_table = new_warehouse_inventory_table
                
        except Exception as e:
            QMessageBox.warning(self, "UI Refresh Error", f"Error refreshing tables: {str(e)}")
            # If UI refresh fails, force a complete refresh of the window
            self.refresh_window()

    def transfer_stock(self):
        from ui.dialogs.transfer_dialog import StockTransferDialog
        
        dialog = StockTransferDialog(self)
        result = dialog.exec_()
        
        if result == QDialog.Accepted:
            # Refresh inventory tables
            self.store_inventory_table = self.create_inventory_table()
            self.warehouse_inventory_table = self.create_warehouse_table()

    def edit_product(self, product_id):
        QMessageBox.information(self, "Edit Product", 
                              f"This would open a dialog to edit product ID: {product_id}")
    
    def delete_product(self, product_id):
        reply = QMessageBox.question(self, "Confirm Delete",
                                   f"Are you sure you want to delete product ID: {product_id}?",
                                   QMessageBox.Yes | QMessageBox.No)
        
        if reply == QMessageBox.Yes:
            # In a real app, this would delete from the database
            QMessageBox.information(self, "Product Deleted", 
                                  f"Product ID: {product_id} has been deleted.")
            
            # Refresh product table
            self.products_table = self.create_products_table()
    
    def adjust_inventory(self, inventory_id):
        from PyQt5.QtWidgets import QDialog, QVBoxLayout, QFormLayout, QSpinBox, QTextEdit, QDialogButtonBox
        
        dialog = QDialog(self)
        dialog.setWindowTitle("Adjust Inventory")
        dialog.setMinimumWidth(350)
        
        layout = QVBoxLayout(dialog)
        
        form_layout = QFormLayout()
        
        # New quantity
        quantity_input = QSpinBox()
        quantity_input.setMinimum(0)
        quantity_input.setMaximum(9999)
        quantity_input.setValue(10) # This would be the current value in a real app
        form_layout.addRow("New Quantity:", quantity_input)
        
        # Reason for adjustment
        reason_input = QTextEdit()
        reason_input.setMaximumHeight(100)
        form_layout.addRow("Reason:", reason_input)
        
        layout.addLayout(form_layout)
        
        # Buttons
        button_box = QDialogButtonBox(QDialogButtonBox.Ok | QDialogButtonBox.Cancel)
        button_box.accepted.connect(dialog.accept)
        button_box.rejected.connect(dialog.reject)
        layout.addWidget(button_box)
        
        result = dialog.exec_()
        
        if result == QDialog.Accepted:
            # In a real app, this would update the database
            QMessageBox.information(self, "Inventory Updated", 
                                  f"Inventory ID: {inventory_id} has been updated to {quantity_input.value()} units.")
            
            # Refresh inventory tables
            self.store_inventory_table = self.create_inventory_table()
            self.warehouse_inventory_table = self.create_warehouse_table()

    def refresh_window(self):
        """Force a complete window refresh by recreating the inventory window"""
        try:
            # Create a new instance of the inventory window
            new_window = InventoryWidget(self.user)
            new_window.show()
            # Close the current window
            self.close()
        except Exception as e:
            QMessageBox.critical(self, "Fatal Error", 
                              f"Unable to refresh the application. Please restart the application. Error: {str(e)}")

    def simulate_barcode_scan(self, sku_input):
        """Simulates scanning a barcode and fills the SKU field"""
        # Show scanning animation/dialog
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
        
        # Generate a random barcode (EAN-13 format)
        ean_prefix = random.choice(['789', '790', '380', '500', '600'])  # Some common prefixes
        random_digits = ''.join(random.choices(string.digits, k=9))
        barcode = f"{ean_prefix}{random_digits}"
        
        # Show the dialog
        scan_dialog.show()
        
        # Use a timer to simulate scanning delay
        def complete_scan():
            scan_dialog.accept()
            sku_input.setText(barcode)
            QMessageBox.information(self, "Barcode Scanned", f"Barcode scanned successfully: {barcode}")
        
        # Delay for 1.5 seconds to simulate scanning
        QTimer.singleShot(1500, complete_scan)
        
        # Process any pending events to show the dialog
        QApplication.processEvents()

    def refresh_all_data(self):
        """Refresh all inventory data from the database"""
        try:
            QApplication.setOverrideCursor(Qt.WaitCursor)
            self.refresh_all_tables()
            QMessageBox.information(self, "Refresh Complete", "Data has been refreshed successfully.")
            QApplication.restoreOverrideCursor()
        except Exception as e:
            QApplication.restoreOverrideCursor()
            QMessageBox.critical(self, "Refresh Error", f"Failed to refresh data: {str(e)}")
            # If UI refresh fails, force a complete refresh of the window
            self.refresh_window()
