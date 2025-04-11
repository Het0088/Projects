import mysql.connector
from mysql.connector import Error

def get_db_connection():
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="login"
        )
        return connection
    except Error as e:
        print(f"Error connecting to MySQL Database: {e}")
        return None

def record_paper_generation(topic, filename, user_id=None):
    connection = get_db_connection()
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        
        # If no user_id provided, use a default value of 1 (admin)
        if user_id is None:
            user_id = 1
            
        query = """
        INSERT INTO papers (user_id, topic, filename, status) 
        VALUES (%s, %s, %s, 'completed')
        """
        cursor.execute(query, (user_id, topic, filename))
        connection.commit()
        return True
        
    except Error as e:
        print(f"Error recording paper generation: {e}")
        return False
        
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close() 