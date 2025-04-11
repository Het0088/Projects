import os

def setup_directories():
    # Get the current directory
    current_dir = os.path.dirname(os.path.abspath(__file__))
    
    # Create generated_pdfs directory
    pdfs_dir = os.path.join(current_dir, 'generated_pdfs')
    
    try:
        # Create directory if it doesn't exist
        if not os.path.exists(pdfs_dir):
            os.makedirs(pdfs_dir)
            print(f"Created directory: {pdfs_dir}")
        else:
            print(f"Directory already exists: {pdfs_dir}")
            
        # Test write permissions
        test_file = os.path.join(pdfs_dir, 'test.txt')
        with open(test_file, 'w') as f:
            f.write('test')
        os.remove(test_file)
        print("Write permissions verified")
        
        return True
    except Exception as e:
        print(f"Error during setup: {str(e)}")
        return False

if __name__ == "__main__":
    if setup_directories():
        print("Setup completed successfully!")
    else:
        print("Setup failed!") 