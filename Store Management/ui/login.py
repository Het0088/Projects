from PyQt5.QtWidgets import (QMainWindow, QWidget, QVBoxLayout, QHBoxLayout, 
                            QLabel, QPushButton, QLineEdit, QMessageBox, QFormLayout,
                            QFrame, QGraphicsDropShadowEffect, QSizePolicy, QGraphicsOpacityEffect)
from PyQt5.QtCore import Qt, QSize, QTimer, QPropertyAnimation, QEasingCurve, QRect, QAbstractAnimation
from PyQt5.QtGui import QFont, QColor, QIcon, QPixmap
import datetime
import os

from database.db import get_session
from database.models import User
# Remove circular import
# from ui.dashboard import DashboardWindow

class LoginWindow(QMainWindow):
    def __init__(self):
        super().__init__()
        self.init_ui()
        
    def init_ui(self):
        self.setWindowTitle("Store Management System - Login")
        self.setMinimumSize(900, 600)  # Set minimum size for better appearance
        
        # Create central widget
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        
        # Main layout
        main_layout = QHBoxLayout(central_widget)
        main_layout.setContentsMargins(0, 0, 0, 0)
        main_layout.setSpacing(0)
        
        # Left side - Image or logo (blue area) with gradient
        left_widget = QWidget()
        left_widget.setStyleSheet("""
            background-color: #4361ee;
        """)
        left_widget.setMinimumWidth(380)
        left_layout = QVBoxLayout(left_widget)
        left_layout.setAlignment(Qt.AlignCenter)
        left_layout.setSpacing(15)
        left_layout.setContentsMargins(30, 30, 30, 30)
        
        # Create app logo
        logo_label = QLabel()
        logo_label.setAlignment(Qt.AlignCenter)
        
        # Check if logo file exists, otherwise display text
        logo_path = os.path.join("resources", "app-icon.png")
        if os.path.exists(logo_path):
            pixmap = QPixmap(logo_path).scaled(120, 120, Qt.KeepAspectRatio, Qt.SmoothTransformation)
            logo_label.setPixmap(pixmap)
        else:
            logo_label.setText("SM")
            logo_label.setStyleSheet("""
                font-size: 60px;
                font-weight: bold;
                color: white;
                background-color: rgba(255, 255, 255, 0.15);
                border-radius: 60px;
                min-width: 120px;
                min-height: 120px;
                padding: 10px;
                border: 3px solid rgba(255, 255, 255, 0.3);
            """)
            logo_label.setAlignment(Qt.AlignCenter)
        
        # Add slide-in and fade animation for logo
        self.logo_opacity = QGraphicsOpacityEffect(logo_label)
        self.logo_opacity.setOpacity(0)
        logo_label.setGraphicsEffect(self.logo_opacity)
        
        # Title container for better spacing
        title_container = QWidget()
        title_layout = QVBoxLayout(title_container)
        title_layout.setContentsMargins(0, 10, 0, 0)
        title_layout.setSpacing(8)
        
        app_name = QLabel("Store Management")
        app_name.setStyleSheet("color: white; font-size: 32px; font-weight: bold; letter-spacing: 1px;")
        app_name.setAlignment(Qt.AlignCenter)
        
        app_desc = QLabel("Inventory & Sales System")
        app_desc.setStyleSheet("color: rgba(255, 255, 255, 0.8); font-size: 18px; letter-spacing: 0.5px;")
        app_desc.setAlignment(Qt.AlignCenter)
        
        # Add animations for text
        self.app_name_opacity = QGraphicsOpacityEffect(app_name)
        self.app_name_opacity.setOpacity(0)
        app_name.setGraphicsEffect(self.app_name_opacity)
        
        self.app_desc_opacity = QGraphicsOpacityEffect(app_desc)
        self.app_desc_opacity.setOpacity(0)
        app_desc.setGraphicsEffect(self.app_desc_opacity)
        
        title_layout.addWidget(app_name)
        title_layout.addWidget(app_desc)
        
        # Add version info
        version_label = QLabel("Version 1.0")
        version_label.setStyleSheet("color: rgba(255, 255, 255, 0.6); font-size: 14px;")
        version_label.setAlignment(Qt.AlignCenter)
        
        # Add widgets to left layout
        left_layout.addStretch(1)
        left_layout.addWidget(logo_label)
        left_layout.addWidget(title_container)
        left_layout.addStretch(1)
        left_layout.addWidget(version_label)
        
        # Right side - Login form
        right_widget = QWidget()
        right_widget.setStyleSheet("""
            background-color: white;
        """)
        
        # Add drop shadow effect to the right widget
        shadow = QGraphicsDropShadowEffect()
        shadow.setBlurRadius(30)
        shadow.setColor(QColor(0, 0, 0, 80))
        shadow.setOffset(0, 0)
        right_widget.setGraphicsEffect(shadow)
        
        right_layout = QVBoxLayout(right_widget)
        right_layout.setContentsMargins(60, 50, 60, 50)
        right_layout.setSpacing(30)
        
        # Create a form container with fixed width for better alignment
        form_container = QWidget()
        form_container.setFixedWidth(400)
        form_layout = QVBoxLayout(form_container)
        form_layout.setContentsMargins(0, 0, 0, 0)
        form_layout.setSpacing(20)
        
        # Login form header with shadow effect
        header_container = QWidget()
        header_layout = QVBoxLayout(header_container)
        header_layout.setContentsMargins(0, 0, 0, 20)
        header_layout.setSpacing(8)
        
        login_header = QLabel("Welcome Back")
        login_header.setStyleSheet("font-size: 36px; font-weight: bold; color: #1e293b;")
        
        login_subheader = QLabel("Sign in to access your account")
        login_subheader.setStyleSheet("font-size: 18px; color: #64748b;")
        
        header_layout.addWidget(login_header)
        header_layout.addWidget(login_subheader)
        
        # Create form fields
        # Username field with proper alignment
        username_container = QWidget()
        username_layout = QVBoxLayout(username_container)
        username_layout.setContentsMargins(0, 0, 0, 0)
        username_layout.setSpacing(8)
        
        username_label = QLabel("Username")
        username_label.setStyleSheet("font-size: 16px; font-weight: 500; color: #475569;")
        
        self.username_input = QLineEdit()
        self.username_input.setPlaceholderText("Enter your username")
        self.username_input.setMinimumHeight(55)
        self.username_input.setStyleSheet("""
            QLineEdit {
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 10px 15px;
                font-size: 16px;
                background-color: #f8fafc;
            }
            QLineEdit:focus {
                border: 2px solid #4361ee;
                background-color: white;
            }
        """)
        
        username_layout.addWidget(username_label)
        username_layout.addWidget(self.username_input)
        
        # Password field with proper alignment
        password_container = QWidget()
        password_layout = QVBoxLayout(password_container)
        password_layout.setContentsMargins(0, 0, 0, 0)
        password_layout.setSpacing(8)
        
        password_label = QLabel("Password")
        password_label.setStyleSheet("font-size: 16px; font-weight: 500; color: #475569;")
        
        self.password_input = QLineEdit()
        self.password_input.setPlaceholderText("Enter your password")
        self.password_input.setEchoMode(QLineEdit.Password)
        self.password_input.setMinimumHeight(55)
        self.password_input.setStyleSheet("""
            QLineEdit {
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 10px 15px;
                font-size: 16px;
                background-color: #f8fafc;
            }
            QLineEdit:focus {
                border: 2px solid #4361ee;
                background-color: white;
            }
        """)
        
        password_layout.addWidget(password_label)
        password_layout.addWidget(self.password_input)
        
        # Error message
        self.error_label = QLabel("")
        self.error_label.setStyleSheet("color: #ef4444; font-size: 14px; margin-top: 5px;")
        self.error_label.setVisible(False)
        
        # Button container for consistent spacing
        button_container = QWidget()
        button_layout = QVBoxLayout(button_container)
        button_layout.setContentsMargins(0, 10, 0, 0)
        button_layout.setSpacing(15)
        
        # Login button with enhanced styling
        self.login_button = QPushButton("Sign In")
        self.login_button.setMinimumHeight(55)
        self.login_button.setStyleSheet("""
            QPushButton {
                background-color: #4361ee;
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 18px;
                font-weight: bold;
            }
            QPushButton:hover {
                background-color: #3a56d4;
            }
            QPushButton:pressed {
                background-color: #2a46c4;
            }
        """)
        self.login_button.setCursor(Qt.PointingHandCursor)
        self.login_button.clicked.connect(self.login)
        
        # Demo button with enhanced styling
        self.demo_button = QPushButton("Demo Mode")
        self.demo_button.setMinimumHeight(45)
        self.demo_button.setStyleSheet("""
            QPushButton {
                background-color: transparent;
                color: #4361ee;
                border: 1px solid #4361ee;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 500;
            }
            QPushButton:hover {
                background-color: rgba(67, 97, 238, 0.1);
            }
        """)
        self.demo_button.setCursor(Qt.PointingHandCursor)
        self.demo_button.clicked.connect(self.demo_login)
        
        button_layout.addWidget(self.login_button)
        button_layout.addWidget(self.demo_button)
        
        # Add all form elements to form layout
        form_layout.addWidget(header_container)
        form_layout.addWidget(username_container)
        form_layout.addWidget(password_container)
        form_layout.addWidget(self.error_label)
        form_layout.addWidget(button_container)
        
        # Center the form container
        right_center_layout = QHBoxLayout()
        right_center_layout.addStretch()
        right_center_layout.addWidget(form_container)
        right_center_layout.addStretch()
        
        right_layout.addLayout(right_center_layout)
        right_layout.addStretch()
        
        # Add copyright/footer
        footer = QLabel("© 2023 Store Management System. All rights reserved.")
        footer.setStyleSheet("color: #94a3b8; font-size: 14px;")
        footer.setAlignment(Qt.AlignCenter)
        right_layout.addWidget(footer)
        
        # Add widgets to main layout
        main_layout.addWidget(left_widget, 4)
        main_layout.addWidget(right_widget, 6)
        
        # Set focus to username field
        self.username_input.setFocus()
        
        # Start animations after a short delay
        QTimer.singleShot(100, self.start_animations)
        
    def login(self):
        # Add visual feedback for button press
        self.animate_button_press(self.login_button)
        
        username = self.username_input.text()
        password = self.password_input.text()
        
        if not username or not password:
            self.error_label.setText("Please enter both username and password")
            self.error_label.setVisible(True)
            return
            
        try:
            with get_session() as session:
                user = session.query(User).filter(User.username == username).first()
                
                if user and user.password_hash == password:  # In production, this should use proper password hashing
                    # Load all user attributes before session closes
                    user_data = {
                        'id': user.id,
                        'username': user.username,
                        'full_name': user.full_name,
                        'email': user.email,
                        'role': user.role,
                        'is_active': user.is_active,
                        'last_login': user.last_login
                    }
                    
                    # Create a detached user instance with loaded data
                    detached_user = User(
                        id=user_data['id'],
                        username=user_data['username'],
                        password_hash=user.password_hash,
                        full_name=user_data['full_name'],
                        email=user_data['email'],
                        role=user_data['role'],
                        is_active=user_data['is_active'],
                        last_login=user_data['last_login']
                    )
                    
                    # Update last login time
                    user.last_login = datetime.datetime.now()
                    session.commit()
                    
                    self.open_dashboard(detached_user)
                else:
                    self.error_label.setText("Invalid username or password")
                    self.error_label.setVisible(True)
                    
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error connecting to database: {str(e)}")
    
    def demo_login(self):
        """Demo login for testing purposes"""
        # Add visual feedback for button press
        self.animate_button_press(self.demo_button)
        
        try:
            with get_session() as session:
                admin = session.query(User).filter(User.username == "admin").first()
                if admin:
                    # Load all admin attributes before session closes
                    admin_data = {
                        'id': admin.id,
                        'username': admin.username,
                        'full_name': admin.full_name,
                        'email': admin.email,
                        'role': admin.role,
                        'is_active': admin.is_active,
                        'last_login': admin.last_login
                    }
                    # Create a new User instance with loaded data
                    detached_admin = User(
                        id=admin_data['id'],
                        username=admin_data['username'],
                        password_hash=admin.password_hash,
                        full_name=admin_data['full_name'],
                        email=admin_data['email'],
                        role=admin_data['role'],
                        is_active=admin_data['is_active'],
                        last_login=admin_data['last_login']
                    )
                    
                    # Update last login time
                    admin.last_login = datetime.datetime.now()
                    session.commit()
                    
                    self.open_dashboard(detached_admin)
                else:
                    self.error_label.setText("Admin user not found. Please use manual login.")
                    self.error_label.setVisible(True)
        except Exception as e:
            QMessageBox.critical(self, "Database Error", f"Error connecting to database: {str(e)}")
    
    def animate_button_press(self, button):
        """Create a button press animation"""
        animation = QPropertyAnimation(button, b"geometry")
        animation.setDuration(100)
        current_geo = button.geometry()
        
        # Slightly shrink the button when pressed
        pressed_geo = QRect(
            current_geo.x() + 2,
            current_geo.y() + 2,
            current_geo.width() - 4,
            current_geo.height() - 4
        )
        
        animation.setStartValue(current_geo)
        animation.setEndValue(pressed_geo)
        animation.setEasingCurve(QEasingCurve.OutQuad)
        animation.start(QAbstractAnimation.DeleteWhenStopped)
    
    def start_animations(self):
        """Start all animations for the login page"""
        # Animate logo fade-in
        self.logo_anim = QPropertyAnimation(self.logo_opacity, b"opacity")
        self.logo_anim.setDuration(800)
        self.logo_anim.setStartValue(0.0)
        self.logo_anim.setEndValue(1.0)
        self.logo_anim.setEasingCurve(QEasingCurve.OutCubic)
        self.logo_anim.start()
        
        # Animate logo slide-in from top
        logo_label = self.logo_opacity.parent()
        orig_pos = logo_label.pos()
        logo_label.move(orig_pos.x(), orig_pos.y() - 50)  # Start 50px above
        
        self.logo_slide = QPropertyAnimation(logo_label, b"pos")
        self.logo_slide.setDuration(800)
        self.logo_slide.setStartValue(logo_label.pos())
        self.logo_slide.setEndValue(orig_pos)
        self.logo_slide.setEasingCurve(QEasingCurve.OutCubic)
        self.logo_slide.start()
        
        # Animate app name fade-in with delay
        self.app_name_anim = QPropertyAnimation(self.app_name_opacity, b"opacity")
        self.app_name_anim.setDuration(800)
        self.app_name_anim.setStartValue(0.0)
        self.app_name_anim.setEndValue(1.0)
        self.app_name_anim.setEasingCurve(QEasingCurve.OutCubic)
        QTimer.singleShot(300, self.app_name_anim.start)
        
        # Animate app description fade-in with delay
        self.app_desc_anim = QPropertyAnimation(self.app_desc_opacity, b"opacity")
        self.app_desc_anim.setDuration(800)
        self.app_desc_anim.setStartValue(0.0)
        self.app_desc_anim.setEndValue(1.0)
        self.app_desc_anim.setEasingCurve(QEasingCurve.OutCubic)
        QTimer.singleShot(600, self.app_desc_anim.start)
    
    def showEvent(self, event):
        """Called when the window is shown"""
        super().showEvent(event)
        
        # Create fade-in animation
        self.fade_in_animation()
        
    def fade_in_animation(self):
        """Creates a fade-in animation for the login form"""
        # Start with opacity 0
        self.setWindowOpacity(0.0)
        
        # Create animation
        self.animation = QPropertyAnimation(self, b"windowOpacity")
        self.animation.setDuration(500)  # Animation duration in ms
        self.animation.setStartValue(0.0)
        self.animation.setEndValue(1.0)
        self.animation.setEasingCurve(QEasingCurve.OutCubic)
        self.animation.start()
        
    def open_dashboard(self, user):
        try:
            # Import here to avoid circular import
            from ui.dashboard import DashboardWindow
            
            dashboard = DashboardWindow(user)
            dashboard.show()
            self.close()
        except Exception as e:
            QMessageBox.critical(self, "Error", f"Error opening dashboard: {str(e)}")
            # Make sure the login window stays open
            self.show() 