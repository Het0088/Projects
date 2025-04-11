<?php
// Enable error reporting at the top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_config.php';  // Include the database configuration

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login";

// Create connection with error handling
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            
            if (empty($username) || empty($password) || empty($confirm_password)) {
                throw new Exception("All fields are required");
            }
            
            if ($password !== $confirm_password) {
                throw new Exception("Passwords do not match");
            }
            
            // Check if username exists
            $check_sql = "SELECT * FROM users WHERE username = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Username already exists");
            }
            
            // Insert new user
            $insert_sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ss", $username, $password);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                throw new Exception("Registration failed");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: register.php");
            exit();
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: register.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 400px;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeIn 0.6s ease-out forwards;
        }

        @keyframes fadeIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, #23a6d5, #23d5ab);
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            color: #34495e;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            position: static;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: transparent;
        }

        input:focus {
            transform: translateY(-2px);
            border-color: #23a6d5;
            outline: none;
            box-shadow: 0 0 10px rgba(35, 166, 213, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #23a6d5, #23d5ab);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(35, 166, 213, 0.3);
        }

        button:hover::before {
            left: 100%;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.3s forwards;
        }

        .login-link a {
            color: #23a6d5;
            text-decoration: none;
            font-weight: 500;
            position: relative;
        }

        .login-link a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(to right, #23a6d5, #23d5ab);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        .login-link a:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .error {
            background: #fee;
            color: #e74c3c;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            transform: translateY(-10px);
            opacity: 0;
            animation: slideDown 0.3s ease-out forwards;
        }

        @keyframes slideDown {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success {
            background: #e6ffe6;
            color: #2ecc71;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            transform: translateY(-10px);
            opacity: 0;
            animation: slideDown 0.3s ease-out forwards;
        }

        .password-requirements {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        input:focus + .password-requirements {
            opacity: 1;
        }

        .password-strength {
            height: 4px;
            width: 100%;
            background: #ddd;
            margin-top: 8px;
            border-radius: 2px;
            position: relative;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
            border-radius: 2px;
        }

        .password-feedback {
            font-size: 12px;
            margin-top: 5px;
            color: #7f8c8d;
        }

        .weak { background-color: #e74c3c; width: 33.33%; }
        .medium { background-color: #f1c40f; width: 66.66%; }
        .strong { background-color: #2ecc71; width: 100%; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create Account</h2>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<div class='success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
            unset($_SESSION['success']);
        }
        ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
                <div class="password-strength">
                    <div class="password-strength-bar"></div>
                </div>
                <p class="password-feedback">Password should contain at least 8 characters</p>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            <button type="submit">Create Account</button>
        </form>
        <p class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>

    <script>
        const password = document.getElementById('password');
        const strengthBar = document.querySelector('.password-strength-bar');
        const feedback = document.querySelector('.password-feedback');

        password.addEventListener('input', function() {
            const value = password.value;
            let strength = 0;
            let feedback_text = '';

            // Check length
            if (value.length >= 8) strength += 1;
            
            // Check for numbers
            if (/\d/.test(value)) strength += 1;
            
            // Check for special characters
            if (/[!@#$%^&*]/.test(value)) strength += 1;
            
            // Check for uppercase and lowercase
            if (/[A-Z]/.test(value) && /[a-z]/.test(value)) strength += 1;

            // Update strength bar
            switch(strength) {
                case 0:
                    strengthBar.className = 'password-strength-bar';
                    feedback_text = 'Password should contain at least 8 characters';
                    break;
                case 1:
                    strengthBar.className = 'password-strength-bar weak';
                    feedback_text = 'Weak - Add numbers or special characters';
                    break;
                case 2:
                    strengthBar.className = 'password-strength-bar medium';
                    feedback_text = 'Medium - Add uppercase letters';
                    break;
                case 3:
                case 4:
                    strengthBar.className = 'password-strength-bar strong';
                    feedback_text = 'Strong password!';
                    break;
            }

            feedback.textContent = feedback_text;
        });
    </script>
</body>
</html> 