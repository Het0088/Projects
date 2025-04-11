<?php
// Initialize user authentication tables
require_once 'config.php';

$output = "<h1>User Authentication Setup</h1>";

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $output .= "<p class='success'>Connected to database successfully!</p>";
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
        shop_id INT,
        reset_token VARCHAR(100) DEFAULT NULL,
        reset_token_expiry DATETIME DEFAULT NULL,
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE SET NULL
    )");
    
    $output .= "<p class='success'>User authentication tables created successfully!</p>";
    
    // Check if there's an admin user already
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount == 0) {
        // Create a default admin user (with hashed password)
        $defaultAdminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, name, email, password, role)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'admin',
            'System Administrator',
            'admin@example.com',
            $defaultAdminPassword,
            'admin'
        ]);
        
        $output .= "<p class='success'>Default admin user created!</p>";
        $output .= "<p>Username: <strong>admin</strong></p>";
        $output .= "<p>Password: <strong>admin123</strong></p>";
        $output .= "<p class='warning'>Please change this default password immediately after logging in!</p>";
    } else {
        $output .= "<p>Admin user already exists.</p>";
    }
    
    // Add a default employee user for demonstration
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'employee'");
    $stmt->execute();
    $employeeCount = $stmt->fetchColumn();
    
    if ($employeeCount == 0) {
        // Create a default employee (with hashed password)
        $defaultEmployeePassword = password_hash("employee123", PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, name, email, password, role, shop_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'employee',
            'Demo Employee',
            'employee@example.com',
            $defaultEmployeePassword,
            'employee',
            1 // Assigned to Shop 1
        ]);
        
        $output .= "<p class='success'>Default employee user created!</p>";
        $output .= "<p>Username: <strong>employee</strong></p>";
        $output .= "<p>Password: <strong>employee123</strong></p>";
    }
    
    $output .= "<h2>Setup Complete!</h2>";
    $output .= "<p>The authentication system has been successfully set up.</p>";
    $output .= "<p><a href='login.php' class='btn'>Go to Login Page</a></p>";
    
} catch (PDOException $e) {
    $output .= "<h2>Error</h2>";
    $output .= "<p class='error'>Database Error: " . $e->getMessage() . "</p>";
    
    // Provide helpful troubleshooting
    if (strpos($e->getMessage(), "Access denied") !== false) {
        $output .= "<p>This might be a MySQL authentication issue. Check your username and password in config.php.</p>";
    } elseif (strpos($e->getMessage(), "Connection refused") !== false) {
        $output .= "<p>Make sure MySQL server is running on your system.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Setup</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1, h2 {
            color: #2c3e50;
        }
        p {
            margin-bottom: 15px;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <?php echo $output; ?>
</body>
</html> 