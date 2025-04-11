<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Global database connection function
function getDBConnection() {
    global $db_host, $db_name, $db_user, $db_pass;
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        logError("Database connection failed in auth_functions: " . $e->getMessage());
        return null;
    }
}

// Log errors to file
function logError($message) {
    $logFile = 'auth_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged in user is admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDBConnection();
    if (!$pdo) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logError("Error getting current user: " . $e->getMessage());
        return null;
    }
}

/**
 * Authenticate user with email/username and password
 * 
 * @param string $email Email or username for authentication
 * @param string $password User password
 * @return array Authentication result with success status and message
 */
function authenticateUser($email, $password) {
    global $conn;
    
    $result = ['success' => false, 'message' => '', 'user_id' => null, 'user_role' => null];
    
    try {
        // Check if input is email or username
        $field = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Query using the appropriate field
        $stmt = $conn->prepare("SELECT id, password, role, name FROM users WHERE $field = ?");
        if (!$stmt) {
            $result['message'] = 'Error preparing statement: ' . $conn->error;
            return $result;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $hashedPassword, $role, $name);
            $stmt->fetch();
            
            // Simple direct password comparison (no hashing)
            if ($password == $hashedPassword) {
                $result['success'] = true;
                $result['user_id'] = $userId;
                $result['user_role'] = $role;
                $result['user_name'] = $name;
                $result['message'] = 'Login successful';
                
                // Set session variables
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_name'] = $name;
                
                // Set role for backward compatibility
                $_SESSION['role'] = $role;
            } else {
                $result['message'] = 'Invalid password';
            }
        } else {
            $result['message'] = 'User not found';
        }
    } catch (Exception $e) {
        $result['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $result;
}

/**
 * Register a new user
 * 
 * @param string $name User name
 * @param string $email User email
 * @param string $password User password
 * @param string $role User role (default: 'user')
 * @return array Registration result with success status and message
 */
function registerUser($name, $email, $password, $role = 'user') {
    global $conn;
    
    $result = ['success' => false, 'message' => ''];
    
    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            $result['message'] = 'Error preparing statement: ' . $conn->error;
            return $result;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $result['message'] = 'Email already registered';
            return $result;
        }
        
        // Generate username from email if not provided
        $username = substr($email, 0, strpos($email, '@'));
        
        // Use password directly - no hashing
        $simplePassword = $password;
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $result['message'] = 'Error preparing statement: ' . $conn->error;
            return $result;
        }
        
        $stmt->bind_param("sssss", $username, $name, $email, $simplePassword, $role);
        
        if ($stmt->execute()) {
            $result['success'] = true;
            $result['message'] = 'Registration successful';
            $result['user_id'] = $conn->insert_id;
        } else {
            $result['message'] = 'Registration failed: ' . $stmt->error;
        }
    } catch (Exception $e) {
        $result['message'] = 'Database error: ' . $e->getMessage();
    }
    
    return $result;
}

/**
 * Generate a password reset token for a user
 * 
 * @param string $email User email
 * @return array Result with success status, message and token if successful
 */
function generateResetToken($email) {
    global $conn;
    
    $result = ['success' => false, 'message' => '', 'token' => null];
    
    try {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            $result['message'] = 'Error preparing statement: ' . $conn->error;
            return $result;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            $result['message'] = 'Email not found';
            return $result;
        }
        
        $stmt->bind_result($userId);
        $stmt->fetch();
        
        // Generate a random token
        $token = bin2hex(random_bytes(32));
        
        // Set expiry time (24 hours from now)
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Delete any existing tokens for this user
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
        if (!$stmt) {
            $result['message'] = 'Error preparing delete statement: ' . $conn->error;
            return $result;
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Insert the new token
        $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires) VALUES (?, ?, ?)");
        if (!$stmt) {
            $result['message'] = 'Error preparing insert statement: ' . $conn->error;
            return $result;
        }
        
        $stmt->bind_param("iss", $userId, $token, $expires);
        
        if ($stmt->execute()) {
            $result['success'] = true;
            $result['message'] = 'Reset token generated successfully';
            $result['token'] = $token;
        } else {
            $result['message'] = 'Failed to generate reset token: ' . $stmt->error;
        }
    } catch (Exception $e) {
        $result['message'] = 'Error: ' . $e->getMessage();
    }
    
    return $result;
}

/**
 * Verify a password reset token
 * 
 * @param string $token Reset token
 * @return array Verification result with success status, message and user_id if successful
 */
function verifyResetToken($token) {
    global $conn;
    
    $result = ['success' => false, 'message' => '', 'user_id' => null];
    
    try {
        // Get the token record
        $stmt = $conn->prepare("SELECT user_id, expires FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result_set = $stmt->get_result();
        
        if (!$result_set) {
            $result['message'] = 'Error retrieving token: ' . $conn->error;
            return $result;
        }
        
        if ($result_set->num_rows == 0) {
            $result['message'] = 'Invalid token';
            return $result;
        }
        
        $row = $result_set->fetch_assoc();
        $userId = $row['user_id'];
        $expires = $row['expires'];
        
        // Check if token has expired
        if (strtotime($expires) < time()) {
            $result['message'] = 'Token has expired';
            return $result;
        }
        
        $result['success'] = true;
        $result['message'] = 'Token is valid';
        $result['user_id'] = $userId;
    } catch (Exception $e) {
        $result['message'] = 'Error: ' . $e->getMessage();
    }
    
    return $result;
}

/**
 * Reset user password using a valid token
 * 
 * @param string $token Reset token
 * @param string $newPassword New password
 * @return array Reset result with success status and message
 */
function resetPassword($token, $newPassword) {
    global $conn;
    
    $result = ['success' => false, 'message' => ''];
    
    try {
        // Verify the token first
        $verification = verifyResetToken($token);
        
        if (!$verification['success']) {
            return $verification;
        }
        
        $userId = $verification['user_id'];
        
        // Use password directly - no hashing
        $simplePassword = $newPassword;
        
        // Update the user's password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if (!$stmt) {
            $result['message'] = 'Error preparing statement: ' . $conn->error;
            return $result;
        }
        
        $stmt->bind_param("si", $simplePassword, $userId);
        
        if ($stmt->execute()) {
            // Delete the used token
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            if (!$stmt) {
                $result['message'] = 'Password updated but error preparing token deletion: ' . $conn->error;
                return $result;
            }
            
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            $result['success'] = true;
            $result['message'] = 'Password reset successfully';
        } else {
            $result['message'] = 'Failed to reset password: ' . $stmt->error;
        }
    } catch (Exception $e) {
        $result['message'] = 'Error: ' . $e->getMessage();
    }
    
    return $result;
}

/**
 * Log out the current user by destroying the session
 */
function logoutUser() {
    // Clear all session variables
    $_SESSION = array();

    // If a session cookie is used, destroy it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
}

/**
 * Get all shops from the database
 * 
 * @return array List of all shops
 */
function getAllShops() {
    global $conn;
    
    $shops = [];
    
    try {
        $result = $conn->query("SELECT id, name, address, phone, email FROM shops ORDER BY name");
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $shops[] = $row;
            }
        }
    } catch (Exception $e) {
        // Log error
        error_log("Error fetching shops: " . $e->getMessage());
    }
    
    return $shops;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.html");
        exit;
    }
}
?> 