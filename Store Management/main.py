import sys
import os
from PyQt5.QtWidgets import QApplication
from PyQt5.QtGui import QIcon, QPixmap, QPainter, QColor, QBrush, QPen
from PyQt5.QtCore import Qt, QSize, QDir, QPoint

from ui.login import LoginWindow
from database.db import initialize_database

def create_placeholder_icons():
    """Create placeholder icons for the application if they don't exist"""
    resources_dir = "resources"
    icon_path = os.path.join(resources_dir, "app-icon.png")
    light_mode_icon_path = os.path.join(resources_dir, "light-mode-icon.png")
    dark_mode_icon_path = os.path.join(resources_dir, "dark-mode-icon.png")
    
    # Make sure resources directory exists
    if not os.path.exists(resources_dir):
        os.makedirs(resources_dir)
        
    # Create app icon if it doesn't exist
    if not os.path.exists(icon_path):
        pixmap = QPixmap(128, 128)
        pixmap.fill(Qt.transparent)
        
        painter = QPainter(pixmap)
        painter.setRenderHint(QPainter.Antialiasing)
        
        # Draw a blue circle
        painter.setBrush(QBrush(QColor(52, 152, 219)))
        painter.setPen(Qt.NoPen)
        painter.drawEllipse(14, 14, 100, 100)
        
        # Draw a white store icon
        painter.setBrush(QBrush(QColor(255, 255, 255)))
        painter.setPen(QPen(QColor(255, 255, 255), 2))
        
        # Simple building shape
        painter.drawRect(44, 54, 40, 40)
        painter.drawPolygon([
            QPoint(40, 54),
            QPoint(64, 34),
            QPoint(88, 54)
        ])
        
        # Door
        painter.drawRect(54, 74, 20, 20)
        
        painter.end()
        pixmap.save(icon_path)
    
    # Create light mode icon if it doesn't exist
    if not os.path.exists(light_mode_icon_path):
        pixmap = QPixmap(64, 64)
        pixmap.fill(Qt.transparent)
        
        painter = QPainter(pixmap)
        painter.setRenderHint(QPainter.Antialiasing)
        
        # Draw a yellow sun icon
        painter.setBrush(QBrush(QColor(241, 196, 15)))
        painter.setPen(Qt.NoPen)
        painter.drawEllipse(12, 12, 40, 40)
        
        # Draw rays
        painter.setPen(QPen(QColor(241, 196, 15), 3))
        painter.drawLine(32, 4, 32, 12)  # top
        painter.drawLine(32, 52, 32, 60)  # bottom
        painter.drawLine(4, 32, 12, 32)  # left
        painter.drawLine(52, 32, 60, 32)  # right
        painter.drawLine(12, 12, 18, 18)  # top-left
        painter.drawLine(46, 46, 52, 52)  # bottom-right
        painter.drawLine(12, 52, 18, 46)  # bottom-left
        painter.drawLine(46, 18, 52, 12)  # top-right
        
        painter.end()
        pixmap.save(light_mode_icon_path)
    
    # Create dark mode icon if it doesn't exist
    if not os.path.exists(dark_mode_icon_path):
        pixmap = QPixmap(64, 64)
        pixmap.fill(Qt.transparent)
        
        painter = QPainter(pixmap)
        painter.setRenderHint(QPainter.Antialiasing)
        
        # Draw a blue moon icon
        painter.setBrush(QBrush(QColor(52, 73, 94)))
        painter.setPen(Qt.NoPen)
        painter.drawEllipse(12, 12, 40, 40)
        
        # Create moon shape with a lighter circle
        painter.setBrush(QBrush(QColor(44, 62, 80)))
        painter.drawEllipse(24, 12, 30, 30)
        
        # Add stars
        painter.setBrush(QBrush(QColor(255, 255, 255)))
        painter.drawEllipse(8, 8, 3, 3)   # small star
        painter.drawEllipse(52, 14, 4, 4) # medium star
        painter.drawEllipse(14, 50, 5, 5) # large star
        painter.drawEllipse(48, 40, 2, 2) # tiny star
        
        painter.end()
        pixmap.save(dark_mode_icon_path)

def main():
    # Make resource path absolute
    base_dir = os.path.dirname(os.path.abspath(__file__))
    resources_dir = os.path.join(base_dir, "resources")
    
    # Set up the application first
    app = QApplication(sys.argv)
    
    # Create placeholder icons (after QApplication is initialized)
    create_placeholder_icons()
    
    # Set up QT resources path
    QDir.addSearchPath("resources", resources_dir)
    
    # Get stylesheet
    style_path = os.path.join(resources_dir, "style.qss")
    style_sheet = ""
    if os.path.exists(style_path):
        with open(style_path, "r") as f:
            style_sheet = f.read()
    
    # Set application icon and style
    app.setWindowIcon(QIcon(os.path.join(resources_dir, "app-icon.png")))
    app.setStyleSheet(style_sheet)
    
    # Initialize database
    initialize_database()
    
    # Create and show the login window
    window = LoginWindow()
    window.show()
    
    # Start the application event loop
    sys.exit(app.exec_())

if __name__ == "__main__":
    main()
