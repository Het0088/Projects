<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration for XAMPP
$db_host = 'localhost';
$db_name = 'employee_portal';
$db_user = 'root';
$db_pass = '';  // XAMPP MySQL has no password by default

// Database connection timeout settings
ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300);

// Other application settings
$app_name = 'Employee Portal';
$company_name = 'Kalarambh';
$app_url = 'http://localhost/website'; // Change this to your actual URL

// Error reporting settings (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?> 