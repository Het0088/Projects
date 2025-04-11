# database/models.py
from sqlalchemy import Column, Integer, String, Float, Boolean, DateTime, ForeignKey, Text
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import relationship
import datetime

Base = declarative_base()

class User(Base):
    __tablename__ = 'users'
    
    id = Column(Integer, primary_key=True)
    username = Column(String(50), unique=True, nullable=False)
    password_hash = Column(String(128), nullable=False)
    full_name = Column(String(100), nullable=False)
    email = Column(String(100), unique=True)
    role = Column(String(20), default='employee')  # admin, manager, employee
    is_active = Column(Boolean, default=True)
    last_login = Column(DateTime)
    created_at = Column(DateTime, default=datetime.datetime.utcnow)

class Product(Base):
    __tablename__ = 'products'
    
    id = Column(Integer, primary_key=True)
    sku = Column(String(50), unique=True, nullable=False)
    name = Column(String(100), nullable=False)
    description = Column(Text)
    category = Column(String(50))
    price = Column(Float, nullable=False)
    cost_price = Column(Float)
    tax_rate = Column(Float, default=0.0)
    image_path = Column(String(255))
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=datetime.datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.datetime.utcnow, onupdate=datetime.datetime.utcnow)
    
    # Relationships
    inventory_items = relationship('Inventory', back_populates='product')

class Location(Base):
    __tablename__ = 'locations'
    
    id = Column(Integer, primary_key=True)
    name = Column(String(100), nullable=False)
    type = Column(String(20))  # STORE, WAREHOUSE, SECTION
    parent_id = Column(Integer, ForeignKey('locations.id'), nullable=True)
    address = Column(String(255))
    is_active = Column(Boolean, default=True)
    
    # Relationships
    inventory_items = relationship('Inventory', back_populates='location')
    parent = relationship('Location', remote_side=[id], back_populates='children')
    children = relationship('Location', back_populates='parent', overlaps="parent")

class Inventory(Base):
    __tablename__ = 'inventory'
    
    id = Column(Integer, primary_key=True)
    product_id = Column(Integer, ForeignKey('products.id'), nullable=False)
    location_id = Column(Integer, ForeignKey('locations.id'), nullable=False)
    quantity = Column(Integer, default=0)
    min_stock_level = Column(Integer, default=5)
    reorder_quantity = Column(Integer, default=10)
    last_updated = Column(DateTime, default=datetime.datetime.utcnow)
    
    # Relationships
    product = relationship('Product', back_populates='inventory_items')
    location = relationship('Location', back_populates='inventory_items')

class Activity(Base):
    __tablename__ = 'activities'
    
    id = Column(Integer, primary_key=True)
    activity_type = Column(String(50), nullable=False)  # e.g., 'PRODUCT_ADDED', 'SALE', 'INVENTORY_ADJUSTED'
    details = Column(Text)
    user_id = Column(Integer, ForeignKey('users.id'))
    user_name = Column(String(100))  # Denormalized for performance
    timestamp = Column(DateTime, default=datetime.datetime.utcnow)
    
    # Relationships
    user = relationship('User')
    
    def __repr__(self):
        return f"<Activity {self.activity_type} by {self.user_name} at {self.timestamp}>"

class Invoice(Base):
    __tablename__ = 'invoices'
    
    id = Column(Integer, primary_key=True)
    invoice_number = Column(String(50), unique=True, nullable=False)
    customer_name = Column(String(100))
    customer_phone = Column(String(20))
    customer_email = Column(String(100))
    subtotal = Column(Float, nullable=False)
    discount_type = Column(String(20))  # 'PERCENTAGE', 'FIXED'
    discount_value = Column(Float, default=0)
    discount_amount = Column(Float, default=0)
    tax_rate = Column(Float, default=0.08)  # 8% default tax
    tax_amount = Column(Float, default=0)
    total_amount = Column(Float, nullable=False)
    payment_method = Column(String(20))  # 'CASH', 'CREDIT_CARD', 'DEBIT_CARD', 'ONLINE'
    payment_status = Column(String(20), default='PAID')  # 'PAID', 'PENDING', 'CANCELLED'
    notes = Column(Text)
    created_at = Column(DateTime, default=datetime.datetime.utcnow)
    created_by = Column(Integer, ForeignKey('users.id'))
    
    # Relationships
    items = relationship('InvoiceItem', back_populates='invoice', cascade="all, delete-orphan")
    user = relationship('User')
    
    def __repr__(self):
        return f"<Invoice {self.invoice_number} total: {self.total_amount}>"

class InvoiceItem(Base):
    __tablename__ = 'invoice_items'
    
    id = Column(Integer, primary_key=True)
    invoice_id = Column(Integer, ForeignKey('invoices.id'), nullable=False)
    product_id = Column(Integer, ForeignKey('products.id'), nullable=False)
    quantity = Column(Integer, nullable=False)
    price = Column(Float, nullable=False)  # Price at time of sale
    tax_rate = Column(Float, default=0.08)
    tax_amount = Column(Float)
    total = Column(Float, nullable=False)
    
    # Relationships
    invoice = relationship('Invoice', back_populates='items')
    product = relationship('Product')
    
    def __repr__(self):
        return f"<InvoiceItem {self.product_id} qty: {self.quantity} total: {self.total}>"

class Payment(Base):
    __tablename__ = 'payments'
    
    id = Column(Integer, primary_key=True)
    invoice_id = Column(Integer, ForeignKey('invoices.id'), nullable=False)
    amount = Column(Float, nullable=False)
    payment_method = Column(String(20), nullable=False)  # 'CASH', 'CREDIT_CARD', 'DEBIT_CARD', 'ONLINE'
    payment_reference = Column(String(100))  # Reference number for payment
    payment_date = Column(DateTime, default=datetime.datetime.utcnow)
    
    # Relationships
    invoice = relationship('Invoice')
    
    def __repr__(self):
        return f"<Payment for Invoice {self.invoice_id} amount: {self.amount}>"