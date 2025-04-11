<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth_functions.php';

// If user is already logged in, redirect to appropriate page
if (isLoggedIn()) {
    $redirectPage = (isAdmin()) ? 'admin.php' : 'dashboard.php';
    header("Location: $redirectPage");
    exit;
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Verify token
if (empty($token)) {
    header("Location: forgot_password.php");
    exit;
}

$verifyResult = verifyResetToken($token);
if (!$verifyResult['success']) {
    $error = "Invalid or expired token. Please request a new password reset link.";
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = "Please enter both password fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Reset password
        $result = resetPassword($token, $password);
        
        if ($result['success']) {
            $success = "Your password has been reset successfully. You can now <a href='login.php'>login</a> with your new password.";
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
    <title>Reset Password - Billing Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #3498db, #2980b9);
            --success-gradient: linear-gradient(135deg, #9b59b6, #8e44ad);
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f7fa;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            font-family: 'Roboto', sans-serif;
        }
        
        .auth-container {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 500px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .auth-image {
            flex: 1;
            background: var(--success-gradient);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .auth-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1607893378714-007fd47c8719?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') center/cover;
            opacity: 0.2;
        }
        
        .auth-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        .auth-content h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }
        
        .auth-content p {
            font-size: 18px;
            line-height: 1.6;
        }
        
        .auth-form {
            flex: 1;
            background: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .auth-form-header h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .auth-form-header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #9b59b6;
            box-shadow: 0 0 0 2px rgba(155, 89, 182, 0.2);
            outline: none;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 17px;
            color: #7f8c8d;
        }
        
        .form-error {
            color: #e74c3c;
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: rgba(231, 76, 60, 0.1);
            border-radius: 5px;
        }
        
        .form-success {
            color: #2ecc71;
            font-size: 14px;
            background-color: rgba(46, 204, 113, 0.1);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            line-height: 1.5;
            text-align: center;
        }
        
        .form-btn {
            background: var(--success-gradient);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .form-btn:hover {
            box-shadow: 0 5px 15px rgba(155, 89, 182, 0.4);
            transform: translateY(-2px);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
        }
        
        .form-footer a {
            color: #9b59b6;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .password-strength-meter {
            height: 5px;
            width: 100%;
            background: #ddd;
            margin-top: 5px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .password-strength-meter-fill {
            height: 100%;
            width: 0;
            background: #e74c3c;
            transition: width 0.3s ease, background 0.3s ease;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .auth-image {
                min-height: 200px;
                padding: 20px;
            }
            
            .auth-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <div class="auth-content">
                <h2>Reset Your Password</h2>
                <p>Create a new secure password for your account.</p>
            </div>
        </div>
        <div class="auth-form">
            <div class="auth-form-header">
                <h1>Create New Password</h1>
                <p>Enter your new password below</p>
            </div>
            
            <?php if ($error): ?>
                <div class="form-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="form-success"><?php echo $success; ?></div>
            <?php elseif (empty($error) || strpos($error, 'must be at least') !== false || strpos($error, 'do not match') !== false): ?>
                <form method="post" action="">
                    <div class="form-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="New Password" required>
                        <div class="password-strength">
                            <div class="password-strength-text">Password strength: <span id="strength-text">Weak</span></div>
                            <div class="password-strength-meter">
                                <div class="password-strength-meter-fill" id="strength-meter"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <i class="fas fa-check"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required>
                    </div>
                    
                    <button type="submit" name="reset_password" class="form-btn">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                    
                    <div class="form-footer">
                        <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                    </div>
                </form>
                
                <script>
                    // Simple password strength meter
                    document.getElementById('password').addEventListener('input', function() {
                        const password = this.value;
                        let strength = 0;
                        
                        if (password.length >= 8) strength += 25;
                        if (/[A-Z]/.test(password)) strength += 25;
                        if (/[0-9]/.test(password)) strength += 25;
                        if (/[^A-Za-z0-9]/.test(password)) strength += 25;
                        
                        const meter = document.getElementById('strength-meter');
                        const text = document.getElementById('strength-text');
                        
                        meter.style.width = strength + '%';
                        
                        if (strength <= 25) {
                            meter.style.background = '#e74c3c';
                            text.textContent = 'Weak';
                        } else if (strength <= 50) {
                            meter.style.background = '#f39c12';
                            text.textContent = 'Fair';
                        } else if (strength <= 75) {
                            meter.style.background = '#3498db';
                            text.textContent = 'Good';
                        } else {
                            meter.style.background = '#2ecc71';
                            text.textContent = 'Strong';
                        }
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 