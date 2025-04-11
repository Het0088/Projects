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
    <title>Register</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h2 {
            color: #333;
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
        }

        label {
            display: block;
            color: #666;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input:focus {
            border-color: #23a6d5;
            outline: none;
            box-shadow: 0 0 10px rgba(35, 166, 213, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #23a6d5, #23d5ab);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(35, 166, 213, 0.3);
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #23a6d5;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #23d5ab;
        }

        .error {
            background: #ffe6e6;
            color: #ff4444;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .success {
            background: #e6ffe6;
            color: #44ff44;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
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
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="links">
            <span>Already have an account? <a href="login.php">Login here</a></span>
        </div>
    </div>
</body>
</html> 