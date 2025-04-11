from PyQt5.QtWidgets import (QDialog, QVBoxLayout, QHBoxLayout, QLabel, QPushButton,
                            QComboBox, QSpinBox, QTableWidget, QTableWidgetItem,
                            QFormLayout, QMessageBox)
from PyQt5.QtCore import Qt

from database.db import get_session
from database.models import Product, Inventory, Location

class StockTransferDialog(QDialog):
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setWindowTitle("Transfer Stock")
        self.setMinimumSize(600, 400)
        self.init_ui()
        
    def init_ui(self):
        layout = QVBoxLayout(self)
        
        form_layout = QFormLayout()
        
        # Source location
        self.source_location = QComboBox()
        self.load_locations(self.source_location)
        form_layout.addRow("From Location:", self.source_location)
        
        # Destination location
        self.dest_location = QComboBox()
        self.load_locations(self.dest_location)
        form_layout.addRow("To Location:", self.dest_location)
        
        layout.addLayout(form_layout)
        
        # Add items section
        layout.addWidget(QLabel("Items to Transfer:"))
        
        self.items_table = QTableWidget()
        self.items_table.setColumnCount(4)
        self.items_table.setHorizontalHeaderLabels(["Product", "Available", "Transfer Quantity", "Actions"])
        self.items_table.setRowCount(1)
        
        # Add first row
        self.add_item_row(0)
        
        layout.addWidget(self.items_table)
        
        # Add button
        add_row_btn = QPushButton("Add Another Item")
        add_row_btn.clicked.connect(self.add_another_item)
        layout.addWidget(add_row_btn)
        
        # Buttons
        buttons_layout = QHBoxLayout()
        cancel_btn = QPushButton("Cancel")
        cancel_btn.clicked.connect(self.reject)
        
        transfer_btn = QPushButton("Complete Transfer")
        transfer_btn.clicked.connect(self.process_transfer)
        
        buttons_layout.addWidget(cancel_btn)
        buttons_layout.addWidget(transfer_btn)
        layout.addLayout(buttons_layout)
        
        # Connect signals
        self.source_location.currentIndexChanged.connect(self.refresh_products)
    
    def load_locations(self, combo):
        # In a real app, this would load from database
        sample_locations = [
            ("Main Store", "store"),
            ("Warehouse A", "warehouse"),
            ("Warehouse B", "warehouse")
        ]
        
        for name, location_type in sample_locations:
            combo.addItem(name, location_type)
    
    def refresh_products(self):
        source_id = self.source_location.currentData()
        if not source_id:
            return
            
        # Clear existing rows
        self.items_table.setRowCount(1)
        self.add_item_row(0)
    
    def add_item_row(self, row):
        # Product dropdown
        product_combo = QComboBox()
        self.load_products_for_location(product_combo, self.source_location.currentData())
        
        # Available quantity (read-only)
        available_qty = QTableWidgetItem("0")
        available_qty.setFlags(available_qty.flags() & ~Qt.ItemIsEditable)
        
        # Transfer quantity spinner
        transfer_qty = QSpinBox()
        transfer_qty.setMinimum(1)
        transfer_qty.setMaximum(9999)
        
        # Remove button
        remove_btn = QPushButton("Remove")
        remove_btn.clicked.connect(lambda: self.remove_item_row(row))
        
        self.items_table.setCellWidget(row, 0, product_combo)
        self.items_table.setItem(row, 1, available_qty)
        self.items_table.setCellWidget(row, 2, transfer_qty)
        self.items_table.setCellWidget(row, 3, remove_btn)
        
        # Connect signals to update available quantity
        product_combo.currentIndexChanged.connect(
            lambda: self.update_available_qty(row, product_combo.currentData()))
    
    def load_products_for_location(self, combo, location_id):
        if not location_id:
            return
            
        # In a real app, this would load from database
        sample_products = [
            ("T-Shirt L", 25),
            ("T-Shirt M", 30),
            ("Jeans 32", 15),
            ("Jeans 34", 12),
            ("Cap Blue", 20)
        ]
            
        for name, quantity in sample_products:
            combo.addItem(f"{name} (Available: {quantity})", (name, quantity))
    
    def update_available_qty(self, row, data):
        if not data:
            return
            
        product_name, quantity = data
        available_item = QTableWidgetItem(str(quantity))
        available_item.setFlags(available_item.flags() & ~Qt.ItemIsEditable)
        self.items_table.setItem(row, 1, available_item)
        
        # Update max quantity for spinner
        qty_spinner = self.items_table.cellWidget(row, 2)
        qty_spinner.setMaximum(quantity)
    
    def add_another_item(self):
        current_rows = self.items_table.rowCount()
        self.items_table.setRowCount(current_rows + 1)
        self.add_item_row(current_rows)
    
    def remove_item_row(self, row):
        if self.items_table.rowCount() <= 1:
            return
            
        self.items_table.removeRow(row)
    
    def process_transfer(self):
        source = self.source_location.currentText()
        destination = self.dest_location.currentText()
        
        if source == destination:
            QMessageBox.warning(self, "Invalid Transfer", 
                              "Source and destination locations cannot be the same.")
            return
        
        # Build transfer items list
        transfer_items = []
        
        for row in range(self.items_table.rowCount()):
            product_combo = self.items_table.cellWidget(row, 0)
            if not product_combo or product_combo.currentIndex() == -1:
                continue
                
            product_data = product_combo.currentData()
            if not product_data:
                continue
                
            product_name, _ = product_data
            
            qty_spinner = self.items_table.cellWidget(row, 2)
            quantity = qty_spinner.value()
            
            if quantity <= 0:
                continue
                
            transfer_items.append((product_name, quantity))
        
        if not transfer_items:
            QMessageBox.warning(self, "Empty Transfer", 
                              "Please add at least one item to transfer.")
            return
        
        # Process the transfer
        # In a real app, this would update the database
        
        items_text = "\n".join([f"- {quantity} x {product}" for product, quantity in transfer_items])
        
        QMessageBox.information(self, "Transfer Complete", 
                               f"Transfer from {source} to {destination} completed successfully:\n\n{items_text}")
        self.accept()
