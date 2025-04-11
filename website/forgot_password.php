<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// If user is already logged in, redirect to appropriate page
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = '';
$success = '';

// Process forgot password form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    
    // Validate input
    if (empty($email)) {
        $error = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email exists
        $sql = "SELECT id, username FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $userId = $user['id'];
            
            // Generate token
            $token = bin2hex(random_bytes(32));
            
            // Set expiration time (1 hour from now)
            $expires = date('Y-m-d H:i:s', time() + 3600);
            
            try {
                // Delete any existing tokens for this email
                $sql = "DELETE FROM password_resets WHERE email = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Failed to prepare delete statement: " . $conn->error);
                }
                $stmt->bind_param("s", $email);
                $stmt->execute();
                
                // Insert new token
                $sql = "INSERT INTO password_resets (user_id, email, token, expires) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Failed to prepare insert statement: " . $conn->error);
                }
                $stmt->bind_param("isss", $userId, $email, $token, $expires);
                
                if ($stmt->execute()) {
                    // In a real application, you would send an email with the reset link
                    // For demonstration purposes, we'll show the link
                    $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                    $success = "A password reset link has been generated. In a production environment, this would be emailed to you.<br><br>For testing, you can <a href='$resetUrl'>click here</a> to reset your password.";
                } else {
                    throw new Exception("Failed to insert reset token: " . $stmt->error);
                }
            } catch (Exception $e) {
                $error = "Reset token generation failed: " . $e->getMessage();
                if (strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
                    $error .= "<br>The password reset system needs to be initialized. Please visit <a href='db_initialize.php'>the database initialization page</a> first.";
                }
            }
        } else {
            $error = "Email not found in our records";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Employee Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .reset-container {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #4CAF50;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
        a {
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Forgot Password</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php else: ?>
            <p>Enter your email address to receive a password reset link.</p>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit">Request Reset Link</button>
            </form>
        <?php endif; ?>
        
        <div class="links">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html> 