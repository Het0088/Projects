<?php
require_once 'db_config.php';

try {
    // Create users table with proper structure
    $createUsersTable = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        is_admin TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createUsersTable)) {
        echo "Users table created or already exists.<br>";
        
        // Check if email column exists
        $checkEmailColumn = "SHOW COLUMNS FROM users LIKE 'email'";
        $result = $conn->query($checkEmailColumn);
        
        if ($result->num_rows === 0) {
            // Add email column if it doesn't exist
            $addEmailColumn = "ALTER TABLE users ADD COLUMN email VARCHAR(255)";
            if ($conn->query($addEmailColumn)) {
                echo "Email column added to users table.<br>";
            } else {
                throw new Exception("Error adding email column: " . $conn->error);
            }
        }
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }

    // Create papers table
    $createPapersTable = "CREATE TABLE IF NOT EXISTS papers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        topic VARCHAR(255) NOT NULL,
        filename VARCHAR(255) NOT NULL,
        generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        error_message TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($createPapersTable)) {
        echo "Papers table created or already exists.<br>";
    } else {
        throw new Exception("Error creating papers table: " . $conn->error);
    }

    // Create settings table
    $createSettingsTable = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        site_name VARCHAR(100) NOT NULL,
        site_description TEXT,
        max_papers_per_day INT DEFAULT 10,
        enable_registration TINYINT(1) DEFAULT 1,
        maintenance_mode TINYINT(1) DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createSettingsTable)) {
        echo "Settings table created or already exists.<br>";
        
        // Check if settings table is empty
        $checkSettings = "SELECT COUNT(*) as count FROM settings";
        $result = $conn->query($checkSettings);
        $row = $result->fetch_assoc();
        
        if ($row['count'] == 0) {
            // Insert default settings
            $insertDefaultSettings = "INSERT INTO settings (site_name, site_description, max_papers_per_day, enable_registration, maintenance_mode) 
                                    VALUES ('SmartScope Admin', 'Your SmartScope Admin Panel', 10, 1, 0)";
            if ($conn->query($insertDefaultSettings)) {
                echo "Default settings inserted successfully.<br>";
            } else {
                throw new Exception("Error inserting default settings: " . $conn->error);
            }
        }
    } else {
        throw new Exception("Error creating settings table: " . $conn->error);
    }

    // Check if admin user exists
    $checkAdmin = "SELECT COUNT(*) as count FROM users WHERE is_admin = 1";
    $result = $conn->query($checkAdmin);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Create default admin user
        $username = "admin";
        $password = "admin123"; // You should change this immediately after setup
        
        $insertAdmin = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($insertAdmin);
        $stmt->bind_param("ss", $username, $password);
        
        if ($stmt->execute()) {
            echo "Default admin user created successfully.<br>";
            echo "Username: admin<br>";
            echo "Password: admin123<br>";
            echo "<strong>Important:</strong> Please change this password after first login!<br>";
        } else {
            throw new Exception("Error creating admin user: " . $stmt->error);
        }
    }

    echo "<br>Setup completed successfully!<br>";
    echo "<a href='admin-login.php'>Go to Admin Login</a>";

} catch (Exception $e) {
    echo "Error during setup: " . $e->getMessage();
}
?> 