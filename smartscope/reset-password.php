<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';  // Include the database configuration

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    
    if (!empty($username)) {
        // Check if username exists
        $check_sql = "SELECT * FROM hehe WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Delete any existing reset tokens for this user
            $delete_sql = "DELETE FROM password_resets WHERE username = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("s", $username);
            $delete_stmt->execute();

            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            
            // Set expiry to 1 hour from current time using server's timezone
            date_default_timezone_set('Asia/Kolkata'); // Set to Indian timezone
            $expiry = date('Y-m-d H:i:s', time() + 3600); // Current time + 1 hour
            
            // Store the token in the database
            $sql = "INSERT INTO password_resets (username, token, expiry) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $token, $expiry);
            
            if ($stmt->execute()) {
                // Verify token was stored
                $verify_sql = "SELECT * FROM password_resets WHERE token = ?";
                $verify_stmt = $conn->prepare($verify_sql);
                $verify_stmt->bind_param("s", $token);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                
                if ($verify_result->num_rows > 0) {
                    $reset_link = "http://localhost/hehe/reset-password-form.html?token=" . $token;
                    
                    echo "<div style='text-align: center; padding: 20px; font-family: Arial, sans-serif;'>";
                    echo "<h2>Password Reset Link</h2>";
                    echo "<p>Username: " . htmlspecialchars($username) . "</p>";
                    echo "<p>Token: " . htmlspecialchars($token) . "</p>";
                    echo "<p>Expiry: " . htmlspecialchars($expiry) . "</p>";
                    echo "<p>For testing purposes, here is your reset link:</p>";
                    echo "<a href='$reset_link'>$reset_link</a>";
                    echo "</div>";
                } else {
                    echo "Error: Token not stored properly";
                }
            } else {
                header("Location: forgot-password.html?error=" . urlencode("Error generating reset link"));
            }
        } else {
            header("Location: forgot-password.html?error=" . urlencode("Username not found"));
            exit();
        }
    }
}

$conn->close();
?> 