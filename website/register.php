<?php
require_once 'auth_functions.php';

// If user is already logged in, redirect to appropriate page
if (isLoggedIn()) {
    $redirectPage = (isAdmin()) ? 'admin.php' : 'dashboard.php';
    header("Location: $redirectPage");
    exit;
}

$error = '';
$success = '';

// Get all shops for dropdown
$shops = getAllShops();

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    // Collect form data
    $data = [
        'username' => $_POST['username'] ?? '',
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'shop_id' => $_POST['shop_id'] !== '' ? $_POST['shop_id'] : null,
        'role' => 'employee' // Default role
    ];
    
    // Validate form data
    if (empty($data['username']) || empty($data['name']) || empty($data['email']) || 
        empty($data['password']) || empty($data['confirm_password'])) {
        $error = 'All fields are required';
    } elseif ($data['password'] !== $data['confirm_password']) {
        $error = 'Passwords do not match';
    } elseif (strlen($data['password']) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Attempt to register user
        $result = registerUser(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role']
        );
        
        if ($result['success']) {
            $success = $result['message'] . '. You can now <a href="login.php">login</a>.';
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
    <title>Register - Billing Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #3498db, #2980b9);
            --secondary-gradient: linear-gradient(135deg, #2ecc71, #27ae60);
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
            max-width: 1000px;
            min-height: 700px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .auth-image {
            flex: 1;
            background: var(--secondary-gradient);
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
            background: url('https://images.unsplash.com/photo-1533727937480-da3a97967e95?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') center/cover;
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
            overflow-y: auto;
        }
        
        .auth-form-header {
            text-align: center;
            margin-bottom: 30px;
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
            margin-bottom: 20px;
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
            border-color: #2ecc71;
            box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
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
            margin-top: 5px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .form-success {
            color: #2ecc71;
            font-size: 14px;
            margin-top: 5px;
            text-align: center;
            padding: 10px;
            background-color: rgba(46, 204, 113, 0.1);
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .form-btn {
            background: var(--secondary-gradient);
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
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
            transform: translateY(-2px);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .form-footer a {
            color: #2ecc71;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        select.form-control {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%237f8c8d' viewBox='0 0 16 16'%3E%3Cpath d='M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708l6-6z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
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
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-image">
            <div class="auth-content">
                <h2>Join Our Billing Portal</h2>
                <p>Create an account to manage shop billing and access our comprehensive billing tools.</p>
            </div>
        </div>
        <div class="auth-form">
            <div class="auth-form-header">
                <h1>Create a New Account</h1>
                <p>Fill in the details below to register</p>
            </div>
            
            <?php if ($error): ?>
                <div class="form-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="form-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-row">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <i class="fas fa-id-card"></i>
                        <input type="text" name="name" class="form-control" placeholder="Full Name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    
                    <div class="form-group">
                        <i class="fas fa-check"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <i class="fas fa-store"></i>
                    <select name="shop_id" class="form-control">
                        <option value="">Select Assigned Shop (Optional)</option>
                        <?php foreach ($shops as $shop): ?>
                            <option value="<?php echo $shop['id']; ?>" <?php echo (isset($_POST['shop_id']) && $_POST['shop_id'] == $shop['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($shop['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="register" class="form-btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
                
                <div class="form-footer">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 