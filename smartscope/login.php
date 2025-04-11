<?php
session_start();
require_once 'db_config.php';  // Include the database configuration

// Set CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security verification failed. Please try again.");
        }
        
        $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
        $entered_password = trim($_POST['password']);
        
        if (empty($username) || empty($entered_password)) {
            throw new Exception("Please enter both username and password.");
        }
        
        // Check if username exists and get the hashed password
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Close the statement properly
        $hasResult = false;
        $user = null;
        
        if ($result && $result->num_rows === 1) {
            $hasResult = true;
            $user = $result->fetch_assoc();
        }
        
        $stmt->close();
        
        if ($hasResult) {
            // Verify password (using password_verify for hashed passwords)
            // Note: For demonstration, we're checking plain text since that's what your current DB uses
            // In production, use: if (password_verify($entered_password, $user['password']))
            if ($entered_password === $user['password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to dashboard
                header("Location: index.html");
                exit();
            } else {
                // Password does not match
                throw new Exception("Invalid username or password");
            }
        } else {
            // Username not found
            throw new Exception("Invalid username or password");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        // Don't expose the exact error for security reasons
        if (strpos($e->getMessage(), "Error preparing") !== false) {
            $_SESSION['error'] = "A system error occurred. Please try again later.";
        }
    }
}

// Generate a new CSRF token for the form
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartScope</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeIn 0.6s ease-out forwards;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        @keyframes fadeIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
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
            position: relative;
        }

        label {
            display: block;
            color: #555;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .form-group:focus-within label {
            color: #23a6d5;
        }

        input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }

        input:focus {
            border-color: #23a6d5;
            outline: none;
            box-shadow: 0 0 10px rgba(35, 166, 213, 0.1);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 39px;
            color: #aaa;
            transition: color 0.3s ease;
        }

        .form-group:focus-within .input-icon {
            color: #23a6d5;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #23a6d5, #23d5ab);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
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
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(35, 166, 213, 0.3);
        }

        button:hover::before {
            left: 100%;
        }

        .links {
            text-align: center;
            margin-top: 20px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 0.3s forwards;
        }

        .links a {
            color: #23a6d5;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }

        .links a::after {
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

        .links a:hover {
            color: #23d5ab;
        }

        .links a:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .error {
            background: rgba(255, 77, 77, 0.1);
            color: #ff4d4d;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            border-left: 4px solid #ff4d4d;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 39px;
            cursor: pointer;
            color: #aaa;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #23a6d5;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='error'><i class='fas fa-exclamation-circle'></i> " . htmlspecialchars($_SESSION['error']) . "</div>";
            unset($_SESSION['error']);
        }
        ?>
        <form action="login.php" method="POST">
            <!-- CSRF Protection -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
                <i class="fas fa-user input-icon"></i>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="links">
            <span>Don't have an account? <a href="register.php">Register here</a></span>
            <br><br>
            <a href="forgot-password.html">Forgot Password?</a>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            // Toggle password visibility
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Automatically remove error message after 5 seconds
        const errorMessage = document.querySelector('.error');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                errorMessage.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 500);
            }, 5000);
        }
    </script>
</body>
</html>