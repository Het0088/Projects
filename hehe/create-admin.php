<?php
require_once 'db_config.php';

// First, check if the admin column exists in the users table
$checkColumnQuery = "SHOW COLUMNS FROM users LIKE 'is_admin'";
$columnExists = $conn->query($checkColumnQuery)->num_rows > 0;

// If the column doesn't exist, add it
if (!$columnExists) {
    $alterQuery = "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
    $conn->query($alterQuery);
    echo "Added is_admin column to users table.<br>";
}

// Check if an admin user already exists
$checkAdminQuery = "SELECT * FROM users WHERE is_admin = 1 LIMIT 1";
$adminExists = $conn->query($checkAdminQuery)->num_rows > 0;

// Create admin user if none exists
if (!$adminExists) {
    $username = "admin";
    $password = "admin123"; // You should change this to a secure password
    
    $insertQuery = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ss", $username, $password);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully.<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<strong>Important:</strong> Please change this password after first login!";
    } else {
        echo "Error creating admin user: " . $stmt->error;
    }
} else {
    echo "Admin user already exists.";
}

echo "<br><br><a href='admin-login.php'>Go to Admin Login</a>";
?> 