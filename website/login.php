<?php
session_start();
require_once 'config.php';
require_once 'auth_functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: bill.php");
    }
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username']; // Using username field for email/username input
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Both email/username and password are required";
    } else {
        // Use the auth_functions.php authenticateUser function
        $result = authenticateUser($email, $password);
        
        if ($result['success']) {
            // Login successful - session variables are set in authenticateUser function
            
            // Redirect based on role
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: bill.php");
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Employee Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            box-sizing: border-box;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .error {
            color: #e74c3c;
            background-color: #fdf0ed;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .links {
            text-align: center;
            margin-top: 25px;
        }
        .links a {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .links a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        .divider {
            margin: 10px 0;
        }
        .note {
            margin-top: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 6px;
            font-size: 14px;
            color: #555;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Welcome Back!</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username or email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit">Sign In</button>
        </form>
        
        <div class="note">
            All passwords have been simplified to "1" for testing purposes.
        </div>
        
        <div class="links">
            <a href="register.php">Don't have an account? Register here</a>
            <div class="divider">•</div>
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>