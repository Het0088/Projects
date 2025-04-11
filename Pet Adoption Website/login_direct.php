<?php
require_once 'config.php';

// Ensure no output before headers
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Start timing the request for rate limiting
$request_time = microtime(true);

// Basic CSRF protection
session_start();
$token = $_POST['csrf_token'] ?? '';
$stored_token = $_SESSION['csrf_token'] ?? '';

// Rate limiting - check for too many failed attempts
function checkRateLimit($ip, $email) {
    global $conn;
    
    // Clean up old login attempts (older than 1 hour)
    $cleanup_query = "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    mysqli_query($conn, $cleanup_query);
    
    // Check for too many recent attempts
    $check_query = "SELECT COUNT(*) as attempt_count FROM login_attempts 
                    WHERE (ip_address = ? OR email = ?) AND success = 0 
                    AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ss", $ip, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    return $row['attempt_count'] >= 5; // Limit to 5 attempts in 15 minutes
}

// Record login attempt
function recordLoginAttempt($ip, $email, $success) {
    global $conn;
    
    $query = "INSERT INTO login_attempts (ip_address, email, success, attempt_time) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    $success_int = $success ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "ssi", $ip, $email, $success_int);
    mysqli_stmt_execute($stmt);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get and sanitize input
        $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $return_url = isset($_POST['return_url']) ? sanitize_input($_POST['return_url']) : '';
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Validate CSRF token
        if (empty($token) || $token !== $stored_token) {
            error_log("CSRF token mismatch for email: $email from IP: $ip_address");
            header("Location: login.html?error=security_error");
            exit();
        }
        
        // Reset CSRF token after checking
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Basic input validation
        if (empty($email) || empty($password)) {
            header("Location: login.html?error=empty_fields");
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: login.html?error=invalid_email");
            exit();
        }
        
        // Check rate limiting
        if (checkRateLimit($ip_address, $email)) {
            error_log("Rate limit exceeded for email: $email from IP: $ip_address");
            header("Location: login.html?error=too_many_attempts");
            exit();
        }
        
        $query = "SELECT id, password, full_name, role, account_status FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Check if account is active
            if ($user['account_status'] !== 'active') {
                recordLoginAttempt($ip_address, $email, false);
                error_log("Inactive account login attempt: $email from IP: $ip_address");
                header("Location: login.html?error=inactive_account");
                exit();
            }
            
            // Check password
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                // If using plain text password, hash it for future use
                if ($password === $user['password']) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET password = ? WHERE id = ?";
                    $update_stmt = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user['id']);
                    mysqli_stmt_execute($update_stmt);
                }
                
                // Record successful login
                recordLoginAttempt($ip_address, $email, true);
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Handle redirection
                if (!empty($return_url)) {
                    // Security check - only allow relative URLs or URLs to the same domain
                    if (strpos($return_url, 'http') === 0) {
                        $return_url = 'index.html'; // Reject external URLs
                    }
                    
                    // Role-based access check for employee pages
                    if (strpos($return_url, 'employee_') !== false && $user['role'] !== 'employee') {
                        $redirect_to = 'index.html'; // Redirect to home if not an employee
                    } else {
                        $redirect_to = $return_url;
                    }
                } else {
                    // No return URL, use role-based default
                    $redirect_to = $user['role'] === 'employee' ? 'employee_portal.php' : 'index.html';
                }
                
                // Log successful login
                error_log("Successful login: $email (ID: {$user['id']}) as {$user['role']} from IP: $ip_address");
                
                // Redirect to the appropriate page
                header("Location: $redirect_to");
                exit();
            }
        }
        
        // Record failed login attempt
        recordLoginAttempt($ip_address, $email, false);
        
        // Log failed login attempt
        error_log("Failed login for email: $email from IP: $ip_address");
        header("Location: login.html?error=invalid_credentials");
        exit();
    } else {
        // Redirect to login page if not a POST request
        header("Location: login.html");
        exit();
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header("Location: login.html?error=system_error");
    exit();
}
?> 