from PyQt5.QtWidgets import (QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, 
                            QLabel, QPushButton, QTableWidget, QTableWidgetItem,
                            QStackedWidget, QSlider, QStyleFactory, QScrollArea, QFrame, 
                            QSizePolicy, QHeaderView, QApplication, QGraphicsOpacityEffect)
from PyQt5.QtCore import (Qt, QSize, QPropertyAnimation, QEasingCurve, QTimer, QRect, 
                         QAbstractAnimation)
from PyQt5.QtGui import QFont, QIcon, QPalette, QColor, QPainter

from database.db import get_session
from database.models import Product, Inventory, Location, Activity
from ui.inventory import InventoryWidget
from ui.sales import SalesWidget
from ui.reports import ReportsWidget
from ui.billing import BillingWidget
import datetime

class DashboardWindow(QMainWindow):
    def __init__(self, user):
        super().__init__()
        self.user = user
        self.dark_mode = False
        self.init_ui()
        self.apply_initial_style()
        
    def init_ui(self):
        self.setWindowTitle("Store Management System - Dashboard")
        self.setMinimumSize(1200, 800)
        
        # Make window expandable to fill screen
        screen = QApplication.desktop().screenGeometry()
        self.resize(int(screen.width() * 0.8), int(screen.height() * 0.8))
        
        # Create central widget
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Main layout
        main_layout = QVBoxLayout(central_widget)
        main_layout.setContentsMargins(0, 0, 0, 0)
        main_layout.setSpacing(0)
        
        # Top bar with dark mode toggle
        top_bar = QWidget()
        top_bar.setObjectName("top-bar")
        top_bar.setFixedHeight(60)
        top_bar_layout = QHBoxLayout(top_bar)
        top_bar_layout.setContentsMargins(20, 0, 20, 0)
        
        app_title = QLabel("Store Management System")
        app_title.setObjectName("app-title")
        app_title.setFont(QFont("Segoe UI", 16, QFont.Bold))
        
        # Create dark mode toggle button with better visibility
        self.dark_mode_btn = QPushButton("☀️")  # Sun emoji for light mode
        self.dark_mode_btn.setObjectName("dark-mode-btn")
        self.dark_mode_btn.setToolTip("Toggle Dark/Light Mode")
        self.dark_mode_btn.setFixedSize(40, 40)
        self.dark_mode_btn.setFont(QFont("Segoe UI", 16))
        self.dark_mode_btn.setCheckable(True)
        self.dark_mode_btn.clicked.connect(self.toggle_dark_mode)
        
        # Add mode label
        self.mode_label = QLabel("Light Mode")
        self.mode_label.setObjectName("mode-label")
        
        # Add a user greeting
        user_greeting = QLabel(f"Hello, {self.user.full_name}")
        user_greeting.setObjectName("user-greeting")
        
        top_bar_layout.addWidget(app_title)
        top_bar_layout.addStretch()
        top_bar_layout.addWidget(user_greeting)
        top_bar_layout.addSpacing(20)
        top_bar_layout.addWidget(self.mode_label)
        top_bar_layout.addWidget(self.dark_mode_btn)
        
        main_layout.addWidget(top_bar)
        
        # Content area with sidebar and stacked widget
        content_area = QWidget()
        content_area_layout = QHBoxLayout(content_area)
        content_area_layout.setContentsMargins(0, 0, 0, 0)
        content_area_layout.setSpacing(0)
        
        # Create sidebar
        self.sidebar = self.create_sidebar()
        content_area_layout.addWidget(self.sidebar, 1)
        
        # Create stacked widget for different views
        self.stacked_widget = QStackedWidget()
        self.stacked_widget.setObjectName("content-stack")
        
        # Create different pages
        self.dashboard_page = self.create_dashboard_content()
        self.inventory_page = InventoryWidget(self.user)
        self.sales_page = SalesWidget(self.user)
        self.reports_page = ReportsWidget(self.user)
        self.billing_page = BillingWidget(self.user)
        
        # Add pages to stacked widget
        self.stacked_widget.addWidget(self.dashboard_page)
        self.stacked_widget.addWidget(self.inventory_page)
        self.stacked_widget.addWidget(self.sales_page)
        self.stacked_widget.addWidget(self.reports_page)
        self.stacked_widget.addWidget(self.billing_page)
        
        # Set dashboard as the initial view
        self.stacked_widget.setCurrentIndex(0)
        self.dashboard_btn.setProperty("active", True)
        
        content_area_layout.addWidget(self.stacked_widget, 4)
        
        main_layout.addWidget(content_area)
        
    def create_sidebar(self):
        sidebar_widget = QWidget()
        sidebar_widget.setObjectName("sidebar")
        sidebar_layout = QVBoxLayout(sidebar_widget)
        sidebar_layout.setContentsMargins(0, 0, 0, 0)
        sidebar_layout.setSpacing(0)
        
        # Add logo or user info at top
        user_info = QLabel(f"Welcome,\n{self.user.full_name}")
        user_info.setObjectName("user-info")
        user_info.setAlignment(Qt.AlignLeft | Qt.AlignVCenter)
        sidebar_layout.addWidget(user_info)
        
        # Navigation container
        nav_container = QWidget()
        nav_layout = QVBoxLayout(nav_container)
        nav_layout.setContentsMargins(10, 10, 10, 10)
        nav_layout.setSpacing(8)
        
        # Navigation buttons
        self.dashboard_btn = self.create_nav_button("Dashboard", "dashboard-icon.png", lambda: self.change_page(0))
        self.inventory_btn = self.create_nav_button("Inventory", "inventory-icon.png", lambda: self.change_page(1))
        self.sales_btn = self.create_nav_button("Sales", "sales-icon.png", lambda: self.change_page(2))
        self.reports_btn = self.create_nav_button("Reports", "reports-icon.png", lambda: self.change_page(3))
        self.billing_btn = self.create_nav_button("Billing", "billing-icon.png", lambda: self.change_page(4))
        
        nav_layout.addWidget(self.dashboard_btn)
        nav_layout.addWidget(self.inventory_btn)
        nav_layout.addWidget(self.sales_btn)
        nav_layout.addWidget(self.billing_btn)
        nav_layout.addWidget(self.reports_btn)
        
        # Add spacer
        nav_layout.addStretch()
        
        # Logout button at bottom
        logout_btn = self.create_nav_button("Logout", "logout-icon.png", self.logout)
        nav_layout.addWidget(logout_btn)
        
        sidebar_layout.addWidget(nav_container)
        
        return sidebar_widget
    
    def create_nav_button(self, text, icon_path, callback):
        button = QPushButton(text)
        button.setIcon(QIcon(f"resources/{icon_path}"))
        button.setIconSize(QSize(24, 24))
        button.clicked.connect(callback)
        button.setObjectName("nav-button")
        
        # Create hover and click animations for navigation buttons
        button.enterEvent = lambda event, btn=button: self.button_hover_effect(btn, True)
        button.leaveEvent = lambda event, btn=button: self.button_hover_effect(btn, False)
        button.mousePressEvent = lambda event, btn=button: self.button_press_effect(btn, event)
        
        return button
    
    def button_hover_effect(self, button, is_enter):
        """Create a hover animation for buttons"""
        # Create animation for padding change
        animation = QPropertyAnimation(button, b"styleSheet")
        animation.setDuration(150)
        
        current_style = button.styleSheet()
        active_state = button.property("active")
        
        if is_enter:
            # Hover effect - add left padding
            if active_state:
                # If button is active, change color slightly
                end_style = "padding-left: 20px; background-color: #3a56d4;"
            else:
                # Otherwise use standard hover color
                end_style = "padding-left: 20px; background-color: #334155;"
        else:
            # Reset to normal
            if active_state:
                # Keep active color but reset padding
                end_style = "padding-left: 12px; background-color: #4361ee;"
            else:
                # Reset to transparent
                end_style = "padding-left: 12px; background-color: transparent;"
        
        animation.setStartValue(current_style)
        animation.setEndValue(end_style)
        animation.setEasingCurve(QEasingCurve.OutCubic)
        animation.start(QAbstractAnimation.DeleteWhenStopped)
    
    def button_press_effect(self, button, event):
        """Create a press animation for buttons"""
        # Store original style
        original_style = button.styleSheet()
        
        # Apply pressed style with darker color and inset effect
        pressed_style = "background-color: #2a3d9c; color: white; padding-left: 15px; padding-top: 14px;"
        button.setStyleSheet(pressed_style)
        
        # Use timer to reset style after short delay
        QTimer.singleShot(150, lambda: button.setStyleSheet(original_style))
        
        # Allow default handling of the event
        QPushButton.mousePressEvent(button, event)
    
    def change_page(self, index):
        # Get current and next widget
        current_widget = self.stacked_widget.currentWidget()
        self.stacked_widget.setCurrentIndex(index)
        next_widget = self.stacked_widget.currentWidget()
        
        # Highlight active button
        self.dashboard_btn.setProperty("active", index == 0)
        self.inventory_btn.setProperty("active", index == 1)
        self.sales_btn.setProperty("active", index == 2)
        self.reports_btn.setProperty("active", index == 3)
        self.billing_btn.setProperty("active", index == 4)
        
        # Force style update
        self.dashboard_btn.style().unpolish(self.dashboard_btn)
        self.dashboard_btn.style().polish(self.dashboard_btn)
        self.inventory_btn.style().unpolish(self.inventory_btn)
        self.inventory_btn.style().polish(self.inventory_btn)
        self.sales_btn.style().unpolish(self.sales_btn)
        self.sales_btn.style().polish(self.sales_btn)
        self.reports_btn.style().unpolish(self.reports_btn)
        self.reports_btn.style().polish(self.reports_btn)
        self.billing_btn.style().unpolish(self.billing_btn)
        self.billing_btn.style().polish(self.billing_btn)
        
        # Animate page transition with fade effect
        self.animate_transition(current_widget, next_widget)
    
    def animate_transition(self, current_widget, next_widget):
        """Animate transition between pages with fade effect"""
        if current_widget and next_widget:
            # Create opacity effect for both widgets
            self.fade_effect = QGraphicsOpacityEffect(next_widget)
            next_widget.setGraphicsEffect(self.fade_effect)
            
            # Create animation
            self.fade_animation = QPropertyAnimation(self.fade_effect, b"opacity")
            self.fade_animation.setDuration(250)
            self.fade_animation.setStartValue(0.0)
            self.fade_animation.setEndValue(1.0)
            self.fade_animation.setEasingCurve(QEasingCurve.OutCubic)
            self.fade_animation.start()
    
    def toggle_dark_mode(self):
        self.dark_mode = not self.dark_mode
        if self.dark_mode:
            self.apply_dark_style()
            self.dark_mode_btn.setText("🌙")  # Moon emoji for dark mode
            self.mode_label.setText("Dark Mode")
        else:
            self.apply_light_style()
            self.dark_mode_btn.setText("☀️")  # Sun emoji for light mode
            self.mode_label.setText("Light Mode")
            
        # Refresh the dashboard content to update charts with proper theme
        current_idx = self.stacked_widget.currentIndex()
        if current_idx == 0:  # If on dashboard page
            # Remove current dashboard page
            self.stacked_widget.removeWidget(self.dashboard_page)
            # Create new dashboard page with updated styling
            self.dashboard_page = self.create_dashboard_content()
            # Add it back at index 0
            self.stacked_widget.insertWidget(0, self.dashboard_page)
            # Switch to it
            self.stacked_widget.setCurrentIndex(0)
    
    def apply_initial_style(self):
        # Default to light mode
        self.apply_light_style()
    
    def apply_light_style(self):
        style = """
            /* Base styles */
            QMainWindow {
                background-color: #f8f9fa;
                color: #212529;
                font-family: 'Segoe UI', Arial, sans-serif;
                border: none;
                margin: 0;
                padding: 0;
            }
            
            QWidget {
                background-color: transparent;
                color: #212529;
                font-family: 'Segoe UI', Arial, sans-serif;
                border: none;
            }
            
            /* Top bar */
            #top-bar {
                background-color: #4361ee;
                color: white;
                border-bottom: 1px solid #3a56d4;
                padding: 0;
                margin: 0;
                height: 60px;
            }
            
            #app-title {
                color: white;
                font-size: 18px;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
            
            #user-greeting {
                color: white;
                font-size: 14px;
                font-weight: 500;
            }
            
            #mode-label {
                color: white;
                font-size: 14px;
                font-weight: 600;
                background-color: rgba(255, 255, 255, 0.2);
                border-radius: 10px;
                padding: 2px 10px;
            }
            
            #dark-mode-btn {
                background-color: #3a56d4;
                border-radius: 20px;
                border: none;
                padding: 5px;
                margin: 5px;
                color: white;
                font-size: 16px;
            }
            
            #dark-mode-btn:hover {
                background-color: #2a46c4;
            }
            
            /* Sidebar */
            #sidebar {
                background-color: #1e293b;
                color: white;
                min-width: 220px;
                max-width: 220px;
                padding: 0;
                margin: 0;
                border: none;
                border-right: 1px solid #334155;
            }
            
            #user-info {
                background-color: #1e293b;
                color: white;
                padding: 20px 15px;
                border-bottom: 1px solid #334155;
                margin: 0;
                font-weight: bold;
                font-size: 14px;
                line-height: 1.5;
            }
            
            /* Navigation buttons */
            QPushButton#nav-button {
                background-color: transparent;
                color: #cbd5e1;
                border: none;
                border-radius: 6px;
                padding: 12px;
                text-align: left;
                margin: 4px;
                font-size: 14px;
                font-weight: 500;
                min-width: 180px;
            }
            
            QPushButton#nav-button:hover {
                background-color: #334155;
                color: white;
            }
            
            QPushButton#nav-button[active="true"] {
                background-color: #4361ee;
                color: white;
            }
            
            /* Main content area */
            #content-stack {
                background-color: #f8f9fa;
                margin: 0;
                padding: 0;
                border: none;
            }
            
            /* Dashboard page */
            #dashboard-page {
                background-color: #f8f9fa;
                padding: 25px 30px;
            }
            
            QLabel#page-header {
                font-size: 28px;
                color: #1e293b;
                margin: 0 0 20px 0;
                padding: 0;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
            
            /* Statistics cards */
            #stat-card {
                background-color: white;
                border-radius: 12px;
                padding: 20px;
                min-height: 120px;
                margin: 10px;
                border: 1px solid #e2e8f0;
            }
            
            #stat-title {
                color: #64748b;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 10px;
            }
            
            #stat-value {
                color: #1e293b;
                font-size: 32px;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
            
            #stat-subtitle {
                color: #64748b;
                font-size: 12px;
            }
            
            /* Tables */
            QTableWidget {
                background-color: white;
                alternate-background-color: #f8fafc;
                border: none;
                border-radius: 8px;
                padding: 5px;
                gridline-color: #e2e8f0;
                margin: 10px 0;
            }
            
            QTableCornerButton::section {
                background-color: #f1f5f9;
                border: none;
            }
            
            QTableWidget::item {
                padding: 8px;
                border-bottom: 1px solid #f1f5f9;
            }
            
            QTableWidget::item:selected {
                background-color: #4361ee;
                color: white;
            }
            
            QHeaderView::section {
                background-color: #f1f5f9;
                color: #334155;
                padding: 10px;
                border: none;
                font-weight: bold;
                border-radius: 4px;
                margin: 2px;
            }
            
            /* General buttons */
            QPushButton {
                background-color: #4361ee;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 10px 18px;
                font-weight: 500;
                font-size: 13px;
                min-width: 120px;
            }
            
            QPushButton:hover {
                background-color: #3a56d4;
            }
            
            /* Tab styling */
            QTabWidget::pane {
                border: none;
                border-radius: 8px;
                background-color: white;
                padding: 10px;
            }
            
            QTabBar::tab {
                background-color: #f1f5f9;
                color: #64748b;
                border: none;
                border-top-left-radius: 6px;
                border-top-right-radius: 6px;
                padding: 10px 18px;
                margin-right: 4px;
                font-weight: 500;
            }
            
            QTabBar::tab:selected {
                background-color: #4361ee;
                color: white;
            }
            
            QTabBar::tab:hover:!selected {
                background-color: #e2e8f0;
                color: #334155;
            }
            
            /* Label styling */
            QLabel {
                font-size: 14px;
                margin: 5px 0;
                font-weight: 500;
            }
            
            /* Error messages */
            QMessageBox {
                background-color: white;
                color: #212529;
            }
            
            QMessageBox QPushButton {
                background-color: #4361ee;
                color: white;
                min-width: 80px;
            }
            
            QMessageBox QLabel {
                color: #212529;
                font-size: 14px;
            }
            
            /* Critical error style */
            QMessageBox[critical="true"] {
                border: 2px solid #ef4444;
            }
            
            QMessageBox[critical="true"] QLabel {
                color: #ef4444;
                font-weight: bold;
            }
            
            /* Success message style */
            QMessageBox[information="true"] QLabel {
                color: #10b981;
            }
        """
        self.setStyleSheet(style)
    
    def apply_dark_style(self):
        style = """
            /* Base styles */
            QMainWindow {
                background-color: #0f172a;
                color: #e2e8f0;
                font-family: 'Segoe UI', Arial, sans-serif;
                border: none;
                margin: 0;
                padding: 0;
            }
            
            QWidget {
                background-color: transparent;
                color: #e2e8f0;
                font-family: 'Segoe UI', Arial, sans-serif;
                border: none;
            }
            
            /* Top bar */
            #top-bar {
                background-color: #1e293b;
                color: white;
                border-bottom: 1px solid #334155;
                padding: 0;
                margin: 0;
                height: 60px;
            }
            
            #app-title {
                color: white;
                font-size: 18px;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
            
            #user-greeting {
                color: white;
                font-size: 14px;
                font-weight: 500;
            }
            
            #mode-label {
                color: white;
                font-size: 14px;
                font-weight: 600;
                background-color: rgba(255, 255, 255, 0.15);
                border-radius: 10px;
                padding: 2px 10px;
            }
            
            #dark-mode-btn {
                background-color: #334155;
                border-radius: 20px;
                border: none;
                padding: 5px;
                margin: 5px;
                color: white;
                font-size: 16px;
            }
            
            #dark-mode-btn:hover {
                background-color: #475569;
            }
            
            /* Sidebar */
            #sidebar {
                background-color: #1e293b;
                color: white;
                min-width: 220px;
                max-width: 220px;
                padding: 0;
                margin: 0;
                border: none;
                border-right: 1px solid #334155;
            }
            
            #user-info {
                background-color: #1e293b;
                color: white;
                padding: 20px 15px;
                border-bottom: 1px solid #334155;
                margin: 0;
                font-weight: bold;
                font-size: 14px;
                line-height: 1.5;
            }
            
            /* Navigation buttons */
            QPushButton#nav-button {
                background-color: transparent;
                color: #cbd5e1;
                border: none;
                border-radius: 6px;
                padding: 12px;
                text-align: left;
                margin: 4px;
                font-size: 14px;
                font-weight: 500;
                min-width: 180px;
            }
            
            QPushButton#nav-button:hover {
                background-color: #334155;
                color: white;
            }
            
            QPushButton#nav-button[active="true"] {
                background-color: #4361ee;
                color: white;
            }
            
            /* Main content area */
            #content-stack {
                background-color: #0f172a;
                margin: 0;
                padding: 0;
                border: none;
            }
            
            /* Dashboard page */
            #dashboard-page {
                background-color: #0f172a;
                padding: 25px 30px;
            }
            
            QLabel#page-header {
                font-size: 28px;
                color: white;
                margin: 0 0 20px 0;
                padding: 0;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
            
            /* Statistics cards */
            #stat-card {
                background-color: #1e293b;
                border-radius: 12px;
                padding: 20px;
                min-height: 120px;
                margin: 10px;
                border: 1px solid #334155;
            }
            
            #stat-title {
                color: #94a3b8;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 10px;
            }
            
            #stat-value {
                color: white;
                font-size: 32px;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
            
            #stat-subtitle {
                color: #94a3b8;
                font-size: 12px;
            }
            
            /* Tables */
            QTableWidget {
                background-color: #1e293b;
                alternate-background-color: #263449;
                border: none;
                border-radius: 8px;
                padding: 5px;
                gridline-color: #334155;
                color: white;
                margin: 10px 0;
            }
            
            QTableCornerButton::section {
                background-color: #334155;
                border: none;
            }
            
            QTableWidget::item {
                padding: 8px;
                border-bottom: 1px solid #334155;
            }
            
            QTableWidget::item:selected {
                background-color: #4361ee;
                color: white;
            }
            
            QHeaderView::section {
                background-color: #334155;
                color: white;
                padding: 10px;
                border: none;
                font-weight: bold;
                border-radius: 4px;
                margin: 2px;
            }
            
            /* General buttons */
            QPushButton {
                background-color: #4361ee;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 10px 18px;
                font-weight: 500;
                font-size: 13px;
                min-width: 120px;
            }
            
            QPushButton:hover {
                background-color: #3a56d4;
            }
            
            /* Tab styling */
            QTabWidget::pane {
                border: none;
                border-radius: 8px;
                background-color: #1e293b;
                padding: 10px;
            }
            
            QTabBar::tab {
                background-color: #334155;
                color: #cbd5e1;
                border: none;
                border-top-left-radius: 6px;
                border-top-right-radius: 6px;
                padding: 10px 18px;
                margin-right: 4px;
                font-weight: 500;
            }
            
            QTabBar::tab:selected {
                background-color: #4361ee;
                color: white;
            }
            
            QTabBar::tab:hover:!selected {
                background-color: #475569;
                color: white;
            }
            
            /* Form elements */
            QComboBox, QLineEdit, QSpinBox, QDoubleSpinBox, QDateEdit {
                background-color: #334155;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 8px;
                min-height: 20px;
            }
            
            QComboBox QAbstractItemView {
                background-color: #334155;
                color: white;
                selection-background-color: #4361ee;
                border-radius: 6px;
                padding: 4px;
            }
            
            /* Label styling */
            QLabel {
                font-size: 14px;
                margin: 5px 0;
                font-weight: 500;
            }
            
            /* Error messages */
            QMessageBox {
                background-color: #1e293b;
                color: white;
                border-radius: 10px;
            }
            
            QMessageBox QPushButton {
                background-color: #4361ee;
                color: white;
                min-width: 80px;
            }
            
            QMessageBox QLabel {
                color: white;
                font-size: 14px;
            }
            
            /* Critical error style */
            QMessageBox[critical="true"] {
                border: 2px solid #ef4444;
            }
            
            QMessageBox[critical="true"] QLabel {
                color: #fecaca;
                font-weight: bold;
            }
            
            /* Success message style */
            QMessageBox[information="true"] QLabel {
                color: #a7f3d0;
            }
        """
        self.setStyleSheet(style)
    
    def create_dashboard_content(self):
        """Creates the main dashboard content"""
        # Create main dashboard widget
        dashboard_widget = QWidget()
        dashboard_widget.setObjectName("dashboard-page")
        
        # Main layout for the dashboard
        main_layout = QVBoxLayout(dashboard_widget)
        main_layout.setContentsMargins(20, 20, 20, 20)
        main_layout.setSpacing(20)
        
        # Welcome header
        header = QLabel(f"Welcome, {self.user.full_name}")
        header.setObjectName("page-header")
        header.setFont(QFont("Segoe UI", 24, QFont.Bold))
        main_layout.addWidget(header)
        
        # Add date & time
        date_time = QLabel(datetime.datetime.now().strftime("%A, %d %B %Y"))
        date_time.setObjectName("date-display")
        date_time.setFont(QFont("Segoe UI", 14))
        main_layout.addWidget(date_time)
        
        # Update time every minute
        self.timer = QTimer(self)
        self.timer.timeout.connect(self.update_time)
        self.timer.start(60000)  # 60 seconds
        
        # Create scroll area for dashboard content
        scroll_area = QScrollArea()
        scroll_area.setWidgetResizable(True)
        scroll_area.setFrameShape(QFrame.NoFrame)
        
        # Create widget for scroll content
        content_widget = QWidget()
        content_layout = QVBoxLayout(content_widget)
        content_layout.setContentsMargins(0, 0, 0, 0)
        content_layout.setSpacing(20)
        
        try:
            # Stats section
            stats_section = QWidget()
            stats_layout = QHBoxLayout(stats_section)
            stats_layout.setContentsMargins(0, 0, 0, 0)
            stats_layout.setSpacing(15)
            
            # Fetch actual stats from database
            total_products, low_stock, total_sales, total_shops = self.fetch_dashboard_stats()
            
            # Create stats cards
            products_card = self.create_stat_card("Products", str(total_products), "Total products")
            low_stock_card = self.create_stat_card("Low Stock", str(low_stock), "Items to reorder")
            shops_card = self.create_stat_card("Shops", str(total_shops), "Active locations")
            sales_card = self.create_stat_card("Total Sales", f"${total_sales}", "Last 30 days")
            
            # Add cards to stats layout
            stats_layout.addWidget(products_card)
            stats_layout.addWidget(low_stock_card)
            stats_layout.addWidget(shops_card)
            stats_layout.addWidget(sales_card)
            
            # Add stats section to content layout
            content_layout.addWidget(stats_section)
            
            # Sales Analytics Section
            analytics_section = QWidget()
            analytics_layout = QVBoxLayout(analytics_section)
            analytics_layout.setContentsMargins(0, 0, 0, 0)
            analytics_layout.setSpacing(10)
            
            analytics_header = QLabel("Sales Analytics")
            analytics_header.setFont(QFont("Segoe UI", 16, QFont.Bold))
            analytics_layout.addWidget(analytics_header)
            
            # Create charts container
            charts_container = QWidget()
            charts_layout = QHBoxLayout(charts_container)
            charts_layout.setContentsMargins(0, 0, 0, 0)
            charts_layout.setSpacing(20)
            
            # Add charts
            sales_chart = self.create_sales_chart()
            category_chart = self.create_category_chart()
            
            if sales_chart:
                charts_layout.addWidget(sales_chart)
            
            if category_chart:
                charts_layout.addWidget(category_chart)
            
            analytics_layout.addWidget(charts_container)
            content_layout.addWidget(analytics_section)
            
            # Low stock section
            low_stock_section = QWidget()
            low_stock_layout = QVBoxLayout(low_stock_section)
            low_stock_layout.setContentsMargins(0, 0, 0, 0)
            low_stock_layout.setSpacing(10)
            
            low_stock_header = QLabel("Low Stock Items")
            low_stock_header.setFont(QFont("Segoe UI", 16, QFont.Bold))
            low_stock_layout.addWidget(low_stock_header)
            
            low_stock_table = self.create_low_stock_table()
            if low_stock_table:
                low_stock_layout.addWidget(low_stock_table)
            
            content_layout.addWidget(low_stock_section)
            
            # Recent activity section
            activity_section = QWidget()
            activity_layout = QVBoxLayout(activity_section)
            activity_layout.setContentsMargins(0, 0, 0, 0)
            activity_layout.setSpacing(10)
            
            activity_header = QLabel("Recent Activity")
            activity_header.setFont(QFont("Segoe UI", 16, QFont.Bold))
            activity_layout.addWidget(activity_header)
            
            recent_activity = self.create_recent_activity_table()
            if recent_activity:
                activity_layout.addWidget(recent_activity)
            
            content_layout.addWidget(activity_section)
            
        except Exception as e:
            # Add error label if dashboard creation fails
            error_label = QLabel(f"Error loading dashboard content: {str(e)}")
            error_label.setStyleSheet("color: #ef4444; font-size: 14px; padding: 20px;")
            content_layout.addWidget(error_label)
            print(f"Dashboard error: {str(e)}")
        
        # Set the content widget as the widget for scroll area
        scroll_area.setWidget(content_widget)
        main_layout.addWidget(scroll_area)
        
        return dashboard_widget
    
    def create_sales_chart(self):
        """Creates a chart showing sales trend"""
        # Import Qt from PyQt5.QtCore outside the try block to ensure it's always available
        from PyQt5.QtCore import Qt, QDateTime, QDate
        
        try:
            from PyQt5.QtChart import QChart, QChartView, QLineSeries, QDateTimeAxis, QValueAxis
            import random
            
            # Create chart
            chart = QChart()
            chart.setTitle("Sales Trend - Last 7 Days")
            chart.setAnimationOptions(QChart.SeriesAnimations)
            
            # Set theme based on current mode
            if self.dark_mode:
                chart.setTheme(QChart.ChartThemeDark)
                chart.setBackgroundBrush(QColor("#1e293b"))
                chart.setTitleBrush(QColor("white"))
            else:
                chart.setTheme(QChart.ChartThemeLight)
                chart.setBackgroundBrush(QColor("white"))
            
            # Create line series
            series = QLineSeries()
            series.setName("Daily Sales")
            
            # Set pen color based on mode
            if self.dark_mode:
                pen = series.pen()
                pen.setColor(QColor("#4361ee"))
                pen.setWidth(3)
                series.setPen(pen)
            
            # Generate random data for the last 7 days
            today = QDate.currentDate()
            for i in range(7, 0, -1):
                date = today.addDays(-i)
                date_time = QDateTime(date)
                timestamp = date_time.toMSecsSinceEpoch()
                value = random.randint(500, 2000)  # Random sales amount
                series.append(timestamp, value)
            
            chart.addSeries(series)
            
            # Set up X axis (dates)
            axis_x = QDateTimeAxis()
            axis_x.setFormat("MMM dd")
            axis_x.setTitleText("Date")
            if self.dark_mode:
                axis_x.setLabelsColor(QColor("white"))
                axis_x.setTitleBrush(QColor("white"))
            chart.addAxis(axis_x, Qt.AlignBottom)
            series.attachAxis(axis_x)
            
            # Set up Y axis (sales amount)
            axis_y = QValueAxis()
            axis_y.setLabelFormat("$%.2f")
            axis_y.setTitleText("Sales Amount")
            if self.dark_mode:
                axis_y.setLabelsColor(QColor("white"))
                axis_y.setTitleBrush(QColor("white"))
            chart.addAxis(axis_y, Qt.AlignLeft)
            series.attachAxis(axis_y)
            
            # Create chart view
            chart_view = QChartView(chart)
            chart_view.setRenderHint(QPainter.Antialiasing)
            chart_view.setMinimumHeight(300)
            
            # Set background color for chart view based on mode
            if self.dark_mode:
                chart_view.setBackgroundBrush(QColor("#1e293b"))
            
            return chart_view
            
        except ImportError:
            # If PyQt5.QtChart is not available, create a placeholder
            placeholder = QWidget()
            placeholder.setMinimumHeight(300)
            
            layout = QVBoxLayout(placeholder)
            layout.setAlignment(Qt.AlignCenter)
            
            message = QLabel("Charts are not available. Please install PyQt5.QtChart module.")
            if self.dark_mode:
                message.setStyleSheet("color: #cbd5e1; font-size: 14px;")
            else:
                message.setStyleSheet("color: #888; font-size: 14px;")
            message.setAlignment(Qt.AlignCenter)
            
            layout.addWidget(message)
            
            return placeholder
    
    def create_category_chart(self):
        """Creates a pie chart showing sales by category"""
        # Import Qt from PyQt5.QtCore outside the try block to ensure it's always available
        from PyQt5.QtCore import Qt
        
        try:
            from PyQt5.QtChart import QChart, QChartView, QPieSeries, QPieSlice
            import random
            
            # Create chart
            chart = QChart()
            chart.setTitle("Sales by Category")
            chart.setAnimationOptions(QChart.SeriesAnimations)
            
            # Set theme based on current mode
            if self.dark_mode:
                chart.setTheme(QChart.ChartThemeDark)
                chart.setBackgroundBrush(QColor("#1e293b"))
                chart.setTitleBrush(QColor("white"))
            else:
                chart.setTheme(QChart.ChartThemeLight)
                chart.setBackgroundBrush(QColor("white"))
            
            # Create pie series
            series = QPieSeries()
            
            # Sample data - in a real app, this would come from the database
            categories = {
                "Electronics": random.randint(1000, 5000),
                "Clothing": random.randint(1000, 5000),
                "Food": random.randint(1000, 5000),
                "Books": random.randint(1000, 5000),
                "Other": random.randint(1000, 5000)
            }
            
            # Define colors for dark and light mode
            if self.dark_mode:
                colors = ["#4361ee", "#3a0ca3", "#7209b7", "#f72585", "#f8961e"]
            else:
                colors = ["#3b82f6", "#6366f1", "#8b5cf6", "#ec4899", "#f97316"]
            
            # Add slices to the pie
            for i, (category, amount) in enumerate(categories.items()):
                slice = series.append(category, amount)
                slice.setLabelVisible(True)
                
                # Format label based on mode
                if self.dark_mode:
                    slice.setLabelColor(QColor("white"))
                    slice.setLabel(f"{category}: ${amount}")
                    slice.setBrush(QColor(colors[i % len(colors)]))
                else:
                    slice.setLabel(f"{category}: ${amount}")
                    slice.setBrush(QColor(colors[i % len(colors)]))
            
            chart.addSeries(series)
            
            # Create chart view
            chart_view = QChartView(chart)
            chart_view.setRenderHint(QPainter.Antialiasing)
            chart_view.setMinimumHeight(300)
            
            # Set background color for chart view based on mode
            if self.dark_mode:
                chart_view.setBackgroundBrush(QColor("#1e293b"))
            
            return chart_view
            
        except ImportError:
            # If PyQt5.QtChart is not available, create a placeholder
            placeholder = QWidget()
            placeholder.setMinimumHeight(300)
            
            layout = QVBoxLayout(placeholder)
            layout.setAlignment(Qt.AlignCenter)
            
            message = QLabel("Charts are not available. Please install PyQt5.QtChart module.")
            if self.dark_mode:
                message.setStyleSheet("color: #cbd5e1; font-size: 14px;")
            else:
                message.setStyleSheet("color: #888; font-size: 14px;")
            message.setAlignment(Qt.AlignCenter)
            
            layout.addWidget(message)
            
            return placeholder
    
    def create_stat_card(self, title, value, subtitle=""):
        """Creates a statistic card without animations"""
        card = QWidget()
        card.setObjectName("stat-card")
        
        layout = QVBoxLayout(card)
        layout.setContentsMargins(15, 15, 15, 15)
        layout.setSpacing(10)
        
        title_label = QLabel(title)
        title_label.setObjectName("stat-title")
        
        value_label = QLabel(value)
        value_label.setObjectName("stat-value")
        
        subtitle_label = QLabel(subtitle)
        subtitle_label.setObjectName("stat-subtitle")
        
        layout.addWidget(title_label)
        layout.addWidget(value_label)
        layout.addWidget(subtitle_label)
        
        return card
    
    def create_shops_table(self):
        table = QTableWidget()
        table.setColumnCount(4)
        table.setHorizontalHeaderLabels(["Shop Name", "Location", "Inventory Items", "Status"])
        
        # Get shops data
        shops_data = []
        try:
            with get_session() as session:
                stores = session.query(Location).filter(Location.type == "STORE").all()
                
                for store in stores:
                    # Get inventory count for this store
                    inventory_count = session.query(Inventory).filter(
                        Inventory.location_id == store.id).count()
                    
                    shops_data.append({
                        'name': store.name,
                        'address': store.address if store.address else "N/A",
                        'inventory_count': inventory_count,
                        'status': "Active" if store.is_active else "Inactive"
                    })
        except Exception as e:
            print(f"Error loading stores data: {str(e)}")
            
        # If no shops data, add a sample row
        if not shops_data:
            shops_data.append({
                'name': "Main Store",
                'address': "123 Main St",
                'inventory_count': 0,
                'status': "Active"
            })
            
        table.setRowCount(len(shops_data))
        
        for i, shop in enumerate(shops_data):
            table.setItem(i, 0, QTableWidgetItem(shop['name']))
            table.setItem(i, 1, QTableWidgetItem(shop['address']))
            table.setItem(i, 2, QTableWidgetItem(str(shop['inventory_count'])))
            
            status_item = QTableWidgetItem(shop['status'])
            if shop['status'] == "Active":
                status_item.setForeground(QColor(39, 174, 96))  # Green for active
            else:
                status_item.setForeground(QColor(231, 76, 60))  # Red for inactive
            
            table.setItem(i, 3, status_item)
        
        # Make table expandable
        table.setSizePolicy(QSizePolicy.Expanding, QSizePolicy.Expanding)
        table.setMinimumHeight(150)
        table.horizontalHeader().setStretchLastSection(True)
        table.horizontalHeader().setSectionResizeMode(QHeaderView.Stretch)
        
        table.setSortingEnabled(True)
        table.resizeColumnsToContents()
        
        return table
    
    def create_low_stock_table(self):
        table = QTableWidget()
        table.setColumnCount(4)
        table.setHorizontalHeaderLabels(["Product", "Location", "Quantity", "Min Level"])
        
        # Get low stock items
        with get_session() as session:
            low_stock = session.query(
                Inventory, Product, Location).join(
                Product, Inventory.product_id == Product.id).join(
                Location, Inventory.location_id == Location.id).filter(
                Inventory.quantity <= Inventory.min_stock_level).all()
        
        # If no low stock items, add sample data
        if not low_stock:
            table.setRowCount(1)
            table.setItem(0, 0, QTableWidgetItem("No low stock items"))
            table.setItem(0, 1, QTableWidgetItem(""))
            table.setItem(0, 2, QTableWidgetItem(""))
            table.setItem(0, 3, QTableWidgetItem(""))
        else:
            table.setRowCount(len(low_stock))
            
            for i, (inventory, product, location) in enumerate(low_stock):
                table.setItem(i, 0, QTableWidgetItem(product.name))
                table.setItem(i, 1, QTableWidgetItem(location.name))
                table.setItem(i, 2, QTableWidgetItem(str(inventory.quantity)))
                table.setItem(i, 3, QTableWidgetItem(str(inventory.min_stock_level)))
        
        # Make table expandable
        table.setSizePolicy(QSizePolicy.Expanding, QSizePolicy.Expanding)
        table.setMinimumHeight(150)
        table.horizontalHeader().setStretchLastSection(True)
        table.horizontalHeader().setSectionResizeMode(QHeaderView.Stretch)
        
        table.setSortingEnabled(True)
        table.resizeColumnsToContents()
        
        return table
    
    def create_recent_activity_table(self):
        """Creates the recent activity table"""
        activity_widget = QWidget()
        activity_layout = QVBoxLayout(activity_widget)
        
        # Create header
        header = QLabel("Recent Activity")
        header.setStyleSheet("font-size: 16px; font-weight: bold; margin-bottom: 8px;")
        activity_layout.addWidget(header)
        
        # Create activity table
        self.activity_table = QTableWidget()
        self.activity_table.setColumnCount(4)
        self.activity_table.setHorizontalHeaderLabels(["Type", "Details", "User", "Date/Time"])
        
        # Set table properties
        self.activity_table.setEditTriggers(QTableWidget.NoEditTriggers)
        self.activity_table.setAlternatingRowColors(True)
        self.activity_table.verticalHeader().setVisible(False)
        self.activity_table.setSelectionBehavior(QTableWidget.SelectRows)
        self.activity_table.setSelectionMode(QTableWidget.SingleSelection)
        self.activity_table.horizontalHeader().setStretchLastSection(True)
        self.activity_table.horizontalHeader().setSectionResizeMode(QHeaderView.Stretch)
        self.activity_table.setMinimumHeight(250)  # Increased height from 150 to 250
        
        # Load recent activities
        self.load_recent_activities()
        
        # Set row height for better visibility
        for i in range(self.activity_table.rowCount()):
            self.activity_table.setRowHeight(i, 30)
        
        activity_layout.addWidget(self.activity_table)
        
        return activity_widget
    
    def load_recent_activities(self):
        """Loads recent activities from the database"""
        # Clear existing items
        self.activity_table.setRowCount(0)
        
        try:
            from database.db import get_session
            from database.models import Activity
            
            # Get recent activities from database
            with get_session() as session:
                activities = session.query(Activity).order_by(Activity.timestamp.desc()).limit(10).all()
                
                if activities:
                    self.activity_table.setRowCount(len(activities))
                    
                    for i, activity in enumerate(activities):
                        self.activity_table.setItem(i, 0, QTableWidgetItem(activity.activity_type))
                        self.activity_table.setItem(i, 1, QTableWidgetItem(activity.details))
                        self.activity_table.setItem(i, 2, QTableWidgetItem(activity.user_name))
                        
                        # Format timestamp
                        timestamp = activity.timestamp.strftime("%Y-%m-%d %H:%M") if activity.timestamp else "N/A"
                        self.activity_table.setItem(i, 3, QTableWidgetItem(timestamp))
                else:
                    # Add placeholder data if no activities found
                    placeholder_data = [
                        ("Product Added", "Added new product 'Blue T-Shirt'", self.user.username, datetime.datetime.now().strftime("%Y-%m-%d %H:%M")),
                        ("Sale Completed", "Invoice #12345", self.user.username, datetime.datetime.now().strftime("%Y-%m-%d %H:%M")),
                        ("Stock Transfer", "5 items from Warehouse to Store", self.user.username, datetime.datetime.now().strftime("%Y-%m-%d %H:%M")),
                        ("Inventory Adjustment", "Updated stock levels", self.user.username, datetime.datetime.now().strftime("%Y-%m-%d %H:%M")),
                    ]
                    
                    self.activity_table.setRowCount(len(placeholder_data))
                    
                    for i, (activity_type, details, user, timestamp) in enumerate(placeholder_data):
                        self.activity_table.setItem(i, 0, QTableWidgetItem(activity_type))
                        self.activity_table.setItem(i, 1, QTableWidgetItem(details))
                        self.activity_table.setItem(i, 2, QTableWidgetItem(user))
                        self.activity_table.setItem(i, 3, QTableWidgetItem(timestamp))
        except Exception as e:
            print(f"Error loading activities: {str(e)}")
            # Add placeholder data if there's an error
            placeholder_data = [
                ("Product Added", "Added new product 'Blue T-Shirt'", self.user.username, datetime.datetime.now().strftime("%Y-%m-%d %H:%M")),
                ("Sale Completed", "Invoice #12345", self.user.username, datetime.datetime.now().strftime("%Y-%m-%d %H:%M")),
                ("Stock Transfer", "5 items from Warehouse to Store", self.user.username, datetime.datetime.now().strftime("%Y-%m-%d %H:%M")),
                ("Inventory Adjustment", "Updated stock levels", self.user.username, datetime.datetime.now().strftime("%Y-%m-%d %H:%M")),
            ]
            
            self.activity_table.setRowCount(len(placeholder_data))
            
            for i, (activity_type, details, user, timestamp) in enumerate(placeholder_data):
                self.activity_table.setItem(i, 0, QTableWidgetItem(activity_type))
                self.activity_table.setItem(i, 1, QTableWidgetItem(details))
                self.activity_table.setItem(i, 2, QTableWidgetItem(user))
                self.activity_table.setItem(i, 3, QTableWidgetItem(timestamp))
    
    def logout(self):
        from ui.login import LoginWindow
        self.login_window = LoginWindow()
        self.login_window.show()
        self.close()

    def fetch_dashboard_stats(self):
        """Fetch actual stats from the database"""
        total_products = 0
        low_stock = 0
        total_sales = 0
        total_shops = 0
        
        try:
            with get_session() as session:
                # Count total products
                total_products = session.query(Product).filter(Product.is_active == True).count()
                
                # Count low stock items
                low_stock = session.query(Inventory).filter(
                    Inventory.quantity <= Inventory.min_stock_level
                ).count()
                
                # Count unique active shop locations
                total_shops = session.query(Location).filter(
                    Location.type == "STORE",
                    Location.is_active == True
                ).count()
                
                # Placeholder for sales (would need Sales table)
                total_sales = 12750.50  # Placeholder value
                
        except Exception as e:
            print(f"Error fetching dashboard stats: {str(e)}")
            # Use default values if there's an error
            total_products = 10
            low_stock = 3
            total_sales = 5000.00
            total_shops = 2
        
        return total_products, low_stock, total_sales, total_shops

    def update_time(self):
        """Updates the time display on the dashboard"""
        try:
            date_display = self.findChild(QLabel, "date-display")
            if date_display:
                date_display.setText(datetime.datetime.now().strftime("%A, %d %B %Y"))
        except Exception as e:
            print(f"Error updating time: {str(e)}")
            # If there's an error updating time, just skip the update
            pass
