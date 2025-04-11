<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "streeties_chat";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // Set session cookie parameters for better security and persistence
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'httponly' => true
    ]);
}

try {
    $conn = mysqli_connect($host, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to sanitize user input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Function to check if user has employee role
function is_employee() {
    return is_logged_in() && $_SESSION['role'] === 'employee';
}

// Function to redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        $current_page = urlencode($_SERVER['REQUEST_URI']);
        header("Location: login.html?return_url=" . $current_page);
        exit();
    }
}

// Function to require employee role
function require_employee() {
    require_login(); // First ensure user is logged in
    if (!is_employee()) {
        // If logged in but not an employee, redirect to home
        header("Location: index.html");
        exit();
    }
}

// Function to get current user's role
function get_user_role() {
    return is_logged_in() ? $_SESSION['role'] : null;
}
?> 
?> 