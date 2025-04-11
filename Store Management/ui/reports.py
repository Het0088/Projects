from PyQt5.QtWidgets import (QWidget, QVBoxLayout, QHBoxLayout,
                            QLabel, QPushButton, QTableWidget, QTableWidgetItem,
                            QTabWidget, QComboBox, QDateEdit, QGroupBox, QFormLayout, QMessageBox,
                            QFileDialog)
from PyQt5.QtCore import Qt, QDate
from PyQt5.QtGui import QFont

from database.db import get_session
from database.models import Product, Inventory, Location

class ReportsWidget(QWidget):
    def __init__(self, user):
        super().__init__()
        self.user = user
        self.init_ui()
        
    def init_ui(self):
        # Main layout
        main_layout = QVBoxLayout(self)
        
        # Header
        header = QLabel("Reports")
        header.setObjectName("page-header")
        main_layout.addWidget(header)
        
        # Create tab widget for different reports
        tab_widget = QTabWidget()
        
        # Sales Report tab
        sales_tab = QWidget()
        sales_layout = QVBoxLayout(sales_tab)
        
        # Filters
        filters_layout = QHBoxLayout()
        
        # Date range
        date_from = QDateEdit()
        date_from.setDate(QDate.currentDate().addDays(-30))
        date_to = QDateEdit()
        date_to.setDate(QDate.currentDate())
        
        filters_layout.addWidget(QLabel("From:"))
        filters_layout.addWidget(date_from)
        filters_layout.addWidget(QLabel("To:"))
        filters_layout.addWidget(date_to)
        
        # Location filter
        location_combo = QComboBox()
        location_combo.addItems(["All Locations", "Store 1", "Store 2", "Warehouse"])
        filters_layout.addWidget(QLabel("Location:"))
        filters_layout.addWidget(location_combo)
        
        # Generate button
        generate_btn = QPushButton("Generate Report")
        generate_btn.clicked.connect(self.generate_sales_report)
        filters_layout.addWidget(generate_btn)
        
        sales_layout.addLayout(filters_layout)
        
        # Sales report table
        self.sales_report_table = self.create_sales_report_table()
        sales_layout.addWidget(self.sales_report_table)
        
        tab_widget.addTab(sales_tab, "Sales Report")
        
        # Inventory Report tab
        inventory_tab = QWidget()
        inventory_layout = QVBoxLayout(inventory_tab)
        
        # Inventory report table
        self.inventory_report_table = self.create_inventory_report_table()
        inventory_layout.addWidget(self.inventory_report_table)
        
        tab_widget.addTab(inventory_tab, "Inventory Report")
        
        main_layout.addWidget(tab_widget)
    
    def create_sales_report_table(self):
        table = QTableWidget()
        table.setColumnCount(5)
        table.setHorizontalHeaderLabels(["Date", "Products Sold", "Total Sales", "Cost", "Profit"])
        
        # Placeholder data
        sales_data = [
            ("2024-03-14", "15", "$750.00", "$450.00", "$300.00"),
            ("2024-03-13", "8", "$400.00", "$240.00", "$160.00"),
            ("2024-03-12", "12", "$600.00", "$360.00", "$240.00"),
        ]
        
        table.setRowCount(len(sales_data))
        
        for i, (date, items, sales, cost, profit) in enumerate(sales_data):
            table.setItem(i, 0, QTableWidgetItem(date))
            table.setItem(i, 1, QTableWidgetItem(items))
            table.setItem(i, 2, QTableWidgetItem(sales))
            table.setItem(i, 3, QTableWidgetItem(cost))
            table.setItem(i, 4, QTableWidgetItem(profit))
        
        table.resizeColumnsToContents()
        return table
    
    def create_inventory_report_table(self):
        table = QTableWidget()
        table.setColumnCount(6)
        table.setHorizontalHeaderLabels(["Product", "Location", "Current Stock", "Min Level", "Value", "Status"])
        
        # Placeholder data
        inventory_data = [
            ("Product A", "Store 1", "50", "20", "$500.00", "OK"),
            ("Product B", "Store 1", "5", "15", "$75.00", "Low Stock"),
            ("Product C", "Warehouse", "200", "50", "$2000.00", "OK"),
        ]
        
        table.setRowCount(len(inventory_data))
        
        for i, (product, location, stock, min_level, value, status) in enumerate(inventory_data):
            table.setItem(i, 0, QTableWidgetItem(product))
            table.setItem(i, 1, QTableWidgetItem(location))
            table.setItem(i, 2, QTableWidgetItem(stock))
            table.setItem(i, 3, QTableWidgetItem(min_level))
            table.setItem(i, 4, QTableWidgetItem(value))
            table.setItem(i, 5, QTableWidgetItem(status))
        
        table.resizeColumnsToContents()
        return table
    
    def generate_sales_report(self):
        # TODO: Implement report generation with actual data
        pass

    def load_locations(self):
        # In a real app, this would load from database
        sample_locations = [
            ("Main Store", "store"),
            ("Warehouse A", "warehouse"),
            ("Warehouse B", "warehouse")
        ]
        
        for name, location_type in sample_locations:
            self.location_combo.addItem(name, location_type)
    
    def report_type_changed(self):
        report_type = self.report_combo.currentText()
        
        # Enable/disable date range based on report type
        date_required = report_type in ["Sales Summary", "Product Performance", "Stock Movement"]
        
        self.start_date.setEnabled(date_required)
        self.end_date.setEnabled(date_required)
    
    def generate_report(self):
        report_type = self.report_combo.currentText()
        location = self.location_combo.currentText()
        start_date = self.start_date.date().toString("yyyy-MM-dd")
        end_date = self.end_date.date().toString("yyyy-MM-dd")
        
        self.report_label.setText(f"{report_type} - {location} ({start_date} to {end_date})")
        
        # Generate different reports based on type
        if report_type == "Inventory Status":
            self.generate_inventory_status()
        elif report_type == "Low Stock Items":
            self.generate_low_stock_report()
        elif report_type == "Sales Summary":
            self.generate_sales_summary()
        elif report_type == "Product Performance":
            self.generate_product_performance()
        elif report_type == "Stock Movement":
            self.generate_stock_movement()
    
    def generate_inventory_status(self):
        self.report_table.clear()
        self.report_table.setColumnCount(5)
        self.report_table.setHorizontalHeaderLabels([
            "Product", "SKU", "Location", "Current Stock", "Value"
        ])
        
        # Sample data - in a real app, this would come from database
        sample_data = [
            ("T-Shirt L", "TSL001", "Main Store", 25, "$625.00"),
            ("T-Shirt M", "TSM001", "Main Store", 30, "$750.00"),
            ("Jeans 32", "JN032", "Main Store", 15, "$675.00"),
            ("Jeans 34", "JN034", "Main Store", 12, "$540.00"),
            ("Cap Blue", "CAP003", "Main Store", 20, "$300.00"),
            ("T-Shirt L", "TSL001", "Warehouse A", 100, "$2,500.00"),
            ("T-Shirt M", "TSM001", "Warehouse A", 120, "$3,000.00"),
            ("Jeans 32", "JN032", "Warehouse A", 50, "$2,250.00"),
            ("Jeans 34", "JN034", "Warehouse A", 55, "$2,475.00"),
            ("Cap Blue", "CAP003", "Warehouse A", 80, "$1,200.00"),
        ]
        
        # Filter by location if needed
        if self.location_combo.currentText() != "All Locations":
            sample_data = [item for item in sample_data if item[2] == self.location_combo.currentText()]
        
        self.report_table.setRowCount(len(sample_data))
        
        for i, (product, sku, location, stock, value) in enumerate(sample_data):
            self.report_table.setItem(i, 0, QTableWidgetItem(product))
            self.report_table.setItem(i, 1, QTableWidgetItem(sku))
            self.report_table.setItem(i, 2, QTableWidgetItem(location))
            self.report_table.setItem(i, 3, QTableWidgetItem(str(stock)))
            self.report_table.setItem(i, 4, QTableWidgetItem(value))
        
        self.report_table.resizeColumnsToContents()
    
    def generate_low_stock_report(self):
        self.report_table.clear()
        self.report_table.setColumnCount(6)
        self.report_table.setHorizontalHeaderLabels([
            "Product", "SKU", "Location", "Current Stock", "Min Level", "Reorder Amount"
        ])
        
        # Sample data
        sample_data = [
            ("T-Shirt S", "TSS001", "Main Store", 3, 10, 20),
            ("Jeans 36", "JN036", "Main Store", 2, 5, 10),
            ("Shoes Size 10", "SH010", "Main Store", 1, 5, 10),
            ("Socks L", "SK002", "Main Store", 4, 10, 30),
            ("Cap Red", "CAP002", "Warehouse A", 5, 20, 50),
        ]
        
        # Filter by location if needed
        if self.location_combo.currentText() != "All Locations":
            sample_data = [item for item in sample_data if item[2] == self.location_combo.currentText()]
        
        self.report_table.setRowCount(len(sample_data))
        
        for i, (product, sku, location, stock, min_level, reorder) in enumerate(sample_data):
            self.report_table.setItem(i, 0, QTableWidgetItem(product))
            self.report_table.setItem(i, 1, QTableWidgetItem(sku))
            self.report_table.setItem(i, 2, QTableWidgetItem(location))
            self.report_table.setItem(i, 3, QTableWidgetItem(str(stock)))
            self.report_table.setItem(i, 4, QTableWidgetItem(str(min_level)))
            self.report_table.setItem(i, 5, QTableWidgetItem(str(reorder)))
        
        self.report_table.resizeColumnsToContents()
    
    def generate_sales_summary(self):
        self.report_table.clear()
        self.report_table.setColumnCount(5)
        self.report_table.setHorizontalHeaderLabels([
            "Date", "Number of Sales", "Items Sold", "Total Revenue", "Avg Sale Value"
        ])
        
        # Sample data
        sample_data = [
            ("2023-03-26", 10, 25, "$1,250.00", "$125.00"),
            ("2023-03-25", 15, 32, "$1,450.00", "$96.67"),
            ("2023-03-24", 12, 18, "$850.00", "$70.83"),
            ("2023-03-23", 8, 15, "$720.00", "$90.00"),
            ("2023-03-22", 20, 45, "$2,100.00", "$105.00"),
            ("2023-03-21", 13, 28, "$1,380.00", "$106.15"),
            ("2023-03-20", 9, 17, "$850.00", "$94.44"),
        ]
        
        self.report_table.setRowCount(len(sample_data))
        
        for i, (date, sales, items, revenue, avg) in enumerate(sample_data):
            self.report_table.setItem(i, 0, QTableWidgetItem(date))
            self.report_table.setItem(i, 1, QTableWidgetItem(str(sales)))
            self.report_table.setItem(i, 2, QTableWidgetItem(str(items)))
            self.report_table.setItem(i, 3, QTableWidgetItem(revenue))
            self.report_table.setItem(i, 4, QTableWidgetItem(avg))
        
        self.report_table.resizeColumnsToContents()
    
    def generate_product_performance(self):
        self.report_table.clear()
        self.report_table.setColumnCount(5)
        self.report_table.setHorizontalHeaderLabels([
            "Product", "SKU", "Units Sold", "Revenue", "Profit"
        ])
        
        # Sample data
        sample_data = [
            ("T-Shirt L", "TSL001", 45, "$1,125.00", "$450.00"),
            ("Jeans 32", "JN032", 32, "$1,440.00", "$640.00"),
            ("Cap Blue", "CAP003", 60, "$900.00", "$450.00"),
            ("Shoes Size 9", "SH009", 15, "$900.00", "$375.00"),
            ("Socks", "SK001", 100, "$800.00", "$500.00"),
        ]
        
        self.report_table.setRowCount(len(sample_data))
        
        for i, (product, sku, units, revenue, profit) in enumerate(sample_data):
            self.report_table.setItem(i, 0, QTableWidgetItem(product))
            self.report_table.setItem(i, 1, QTableWidgetItem(sku))
            self.report_table.setItem(i, 2, QTableWidgetItem(str(units)))
            self.report_table.setItem(i, 3, QTableWidgetItem(revenue))
            self.report_table.setItem(i, 4, QTableWidgetItem(profit))
        
        self.report_table.resizeColumnsToContents()
    
    def generate_stock_movement(self):
        self.report_table.clear()
        self.report_table.setColumnCount(6)
        self.report_table.setHorizontalHeaderLabels([
            "Date", "Product", "From", "To", "Quantity", "Type"
        ])
        
        # Sample data
        sample_data = [
            ("2023-03-26", "T-Shirt L", "Warehouse A", "Main Store", 10, "Transfer"),
            ("2023-03-25", "Jeans 32", "Warehouse A", "Main Store", 5, "Transfer"),
            ("2023-03-25", "T-Shirt M", "Supplier", "Warehouse A", 50, "Purchase"),
            ("2023-03-24", "Jeans 34", "Warehouse A", "Main Store", 8, "Transfer"),
            ("2023-03-24", "Cap Blue", "Main Store", "N/A", 5, "Sale"),
            ("2023-03-23", "Shoes Size 9", "Main Store", "N/A", 2, "Sale"),
            ("2023-03-22", "Socks", "Main Store", "N/A", 12, "Sale"),
        ]
        
        # Filter by location if needed
        if self.location_combo.currentText() != "All Locations":
            location = self.location_combo.currentText()
            sample_data = [item for item in sample_data if item[2] == location or item[3] == location]
        
        self.report_table.setRowCount(len(sample_data))
        
        for i, (date, product, from_loc, to_loc, quantity, move_type) in enumerate(sample_data):
            self.report_table.setItem(i, 0, QTableWidgetItem(date))
            self.report_table.setItem(i, 1, QTableWidgetItem(product))
            self.report_table.setItem(i, 2, QTableWidgetItem(from_loc))
            self.report_table.setItem(i, 3, QTableWidgetItem(to_loc))
            self.report_table.setItem(i, 4, QTableWidgetItem(str(quantity)))
            self.report_table.setItem(i, 5, QTableWidgetItem(move_type))
        
        self.report_table.resizeColumnsToContents()
    
    def export_report(self):
        # In a real app, this would export the current report to an Excel file
        file_path, _ = QFileDialog.getSaveFileName(
            self, "Export Report", "", "Excel Files (*.xlsx);;CSV Files (*.csv)"
        )
        
        if file_path:
            QMessageBox.information(self, "Export Complete", 
                                  f"Report exported successfully to {file_path}")
    
    def back_to_dashboard(self):
        # Import here to avoid circular import
        from ui.dashboard import DashboardWindow
        
        self.dashboard = DashboardWindow(self.user)
        self.dashboard.show()
        self.close() 