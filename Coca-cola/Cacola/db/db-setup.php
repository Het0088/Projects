<?php
/**
 * Database Setup for Futuristic Coca-Cola Website
 * 
 * This file creates the necessary database structure for the website:
 * - Users table for authentication
 * - Products table for the product catalog
 * - Orders table for user purchases
 * - Sustainability stats table for dynamic content
 */

// Database connection parameters
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty)
$dbname = "cocacola_future";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

echo "Database created successfully or already exists<br>";

// Use the database
$conn->select_db($dbname);

// Create Users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

echo "Users table created successfully<br>";

// Create Products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    tagline VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    stock INT(11) DEFAULT 0,
    rating DECIMAL(3, 1) DEFAULT 0,
    features JSON,
    nutrition JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating products table: " . $conn->error);
}

echo "Products table created successfully<br>";

// Create Orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating orders table: " . $conn->error);
}

echo "Orders table created successfully<br>";

// Create Order Items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) UNSIGNED,
    product_id INT(11) UNSIGNED,
    quantity INT(11) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating order_items table: " . $conn->error);
}

echo "Order Items table created successfully<br>";

// Create Sustainability Stats table
$sql = "CREATE TABLE IF NOT EXISTS sustainability_stats (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stat_name VARCHAR(100) NOT NULL,
    stat_value INT(11) NOT NULL,
    stat_label VARCHAR(100) NOT NULL,
    display_order INT(5) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating sustainability_stats table: " . $conn->error);
}

echo "Sustainability Stats table created successfully<br>";

// Insert sample data for products
$sql = "INSERT INTO products (name, slug, description, tagline, price, image, stock, rating, features, nutrition) VALUES
    ('Quantum Cola', 'quantum-cola', 'Experience the revolution in refreshment with Quantum Cola. Our signature flavor has been enhanced at the molecular level to deliver an unprecedented taste experience that adapts to your preferences.', 'The classic taste reimagined with molecular enhancement technology', 3.99, 'quantum-cola.png', 150, 4.8, 
    '{\"features\": [
        {\"icon\": \"fa-atom\", \"text\": \"Adaptive flavor profile\"},
        {\"icon\": \"fa-sparkles\", \"text\": \"Quantum particle infusion\"},
        {\"icon\": \"fa-dna\", \"text\": \"Molecular enhancement\"},
        {\"icon\": \"fa-lightbulb\", \"text\": \"Sensory activation\"}
    ]}',
    '{\"nutrition\": [
        {\"name\": \"Energy\", \"value\": \"80 kcal\", \"percentage\": 25},
        {\"name\": \"Quantum Nutrients\", \"value\": \"12g\", \"percentage\": 80},
        {\"name\": \"Sugars\", \"value\": \"0g\", \"percentage\": 0},
        {\"name\": \"Flavor Enhancers\", \"value\": \"5g\", \"percentage\": 45}
    ]}'
    )";

// Only insert if not already exists
$check = $conn->query("SELECT COUNT(*) as count FROM products WHERE slug = 'quantum-cola'");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    if ($conn->query($sql) === FALSE) {
        echo "Error inserting product data: " . $conn->error;
    } else {
        echo "Sample product data inserted successfully<br>";
    }
} else {
    echo "Sample product data already exists<br>";
}

// Insert sample sustainability stats
$sql = "INSERT INTO sustainability_stats (stat_name, stat_value, stat_label, display_order) VALUES
    ('renewable_energy', 100, 'Percent Renewable Energy', 1),
    ('water_restored', 200, 'Million Liters Water Restored', 2),
    ('carbon_reduction', 75, 'Percent Carbon Reduction', 3),
    ('trees_planted', 500, 'Thousand Trees Planted', 4)";

// Only insert if not already exists
$check = $conn->query("SELECT COUNT(*) as count FROM sustainability_stats");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    if ($conn->query($sql) === FALSE) {
        echo "Error inserting sustainability stats: " . $conn->error;
    } else {
        echo "Sample sustainability stats inserted successfully<br>";
    }
} else {
    echo "Sustainability stats already exist<br>";
}

// Create admin user if not exists
$adminEmail = "admin@cocacola.com";
$adminName = "Admin";
$adminPassword = password_hash("admin123", PASSWORD_DEFAULT); // In production, use a secure password

$check = $conn->query("SELECT COUNT(*) as count FROM users WHERE email = '$adminEmail'");
$row = $check->fetch_assoc();
if ($row['count'] == 0) {
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$adminName', '$adminEmail', '$adminPassword', 'admin')";
    if ($conn->query($sql) === FALSE) {
        echo "Error creating admin user: " . $conn->error;
    } else {
        echo "Admin user created successfully<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

echo "<br>Database setup completed successfully!";

// Close connection
$conn->close();
?> 