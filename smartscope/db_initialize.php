<?php
// Include database configuration
require_once 'db_config.php';

// Connect to MySQL server without database selection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if($conn === false){
    die("ERROR: Could not connect to MySQL server. " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if(mysqli_query($conn, $sql)){
    echo "Database created successfully or already exists<br>";
} else {
    die("ERROR: Could not create database. " . mysqli_error($conn));
}

// Select the database
mysqli_select_db($conn, DB_NAME);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $sql)){
    echo "Users table created successfully or already exists<br>";
} else {
    echo "ERROR: Could not create users table. " . mysqli_error($conn);
}

// You can add more tables here as needed for your application

echo "Database initialization completed!";
mysqli_close($conn);
?> 