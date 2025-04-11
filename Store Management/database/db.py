from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, scoped_session
from sqlalchemy.ext.declarative import declarative_base
import os
from contextlib import contextmanager
from database.models import Base, User

# Create database directory if it doesn't exist
if not os.path.exists('data'):
    os.makedirs('data')

# Database connection
engine = create_engine('sqlite:///data/store.db', connect_args={'check_same_thread': False})
session_factory = sessionmaker(bind=engine)
Session = scoped_session(session_factory)

@contextmanager
def get_session():
    """Get a database session"""
    session = Session()
    try:
        yield session
        session.commit()
    except:
        session.rollback()
        raise
    finally:
        session.close()

def create_admin_user():
    """Create the initial admin user if it doesn't exist"""
    with get_session() as session:
        # Check if admin user exists
        admin = session.query(User).filter(User.username == "admin").first()
        if not admin:
            # Create admin user
            admin = User(
                username="admin",
                password_hash="admin",  # In production, this should be properly hashed
                full_name="Admin User",
                email="admin@store.com",
                role="admin",
                is_active=True
            )
            session.add(admin)
            print("Admin user created successfully.")

def initialize_database():
    """Initialize the database and create tables"""
    Base.metadata.create_all(engine)
    create_admin_user()
    print("Database initialized successfully.")

def close_database():
    """Close database session"""
    Session.remove() 