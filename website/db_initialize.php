<?php
// Initialize database with sample data
require_once 'config.php';

$output = "<h1>Database Initialization</h1>";

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $output .= "<p class='success'>Connected to MySQL server successfully!</p>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name");
    $pdo->exec("USE $db_name");
    
    $output .= "<p class='success'>Database '$db_name' created/selected successfully!</p>";
    
    // Create users table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        shop_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $output .= "<p class='success'>Table 'users' created successfully!</p>";
    
    // Create password_resets table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(100) NOT NULL UNIQUE,
        expires DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    $output .= "<p class='success'>Table 'password_resets' created successfully!</p>";
    
    // Create shops table
    $pdo->exec("CREATE TABLE IF NOT EXISTS shops (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        address VARCHAR(255),
        phone VARCHAR(20),
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $output .= "<p class='success'>Table 'shops' created successfully!</p>";
    
    // Create bills tables for each shop
    for ($shopId = 1; $shopId <= 3; $shopId++) {
        $tableName = "bills_shop_" . $shopId;
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(255) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            product_number VARCHAR(50),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        $output .= "<p>Table '$tableName' created successfully!</p>";
        
        // Check if table is empty before adding sample data
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tableName");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Add sample data for each shop
            $sampleBills = [
                [
                    'Tech Solutions Inc.', 
                    1250.00 + ($shopId * 100), 
                    date('Y-m-d', strtotime('-5 days')), 
                    'pending',
                    'PRD-' . rand(1000, 9999),
                    'Monthly software maintenance - Shop ' . $shopId
                ],
                [
                    'Global Enterprises', 
                    3450.75 + ($shopId * 50), 
                    date('Y-m-d', strtotime('-10 days')), 
                    'processing',
                    'PRD-' . rand(1000, 9999),
                    'Website development - Phase ' . $shopId
                ],
                [
                    'Acme Corporation', 
                    780.50 + ($shopId * 75), 
                    date('Y-m-d', strtotime('-15 days')), 
                    'completed',
                    'PRD-' . rand(1000, 9999),
                    'Network infrastructure setup - Shop ' . $shopId
                ]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO $tableName (client_name, amount, date, status, product_number, description)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($sampleBills as $bill) {
                $stmt->execute($bill);
            }
            
            $output .= "<p>Added sample bills to $tableName!</p>";
        } else {
            $output .= "<p>Table $tableName already contains $count bills. No sample data added.</p>";
            
            // Add product_number column to existing table if it doesn't exist
            try {
                $pdo->exec("ALTER TABLE $tableName ADD COLUMN IF NOT EXISTS product_number VARCHAR(50) AFTER status");
                $output .= "<p>Added product_number column to existing $tableName table.</p>";
            } catch (PDOException $e) {
                $output .= "<p>Note: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Insert default admin if not exists
    $checkAdmin = $pdo->query("SELECT id FROM users WHERE email = 'admin@example.com'");
    if ($checkAdmin->rowCount() == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (username, name, email, password, role) 
                    VALUES ('admin', 'Admin User', 'admin@example.com', '$adminPassword', 'admin')");
        $output .= "<p class='success'>Default admin user created</p>";
        $output .= "<p class='success'>Username: admin</p>";
        $output .= "<p class='success'>Email: admin@example.com</p>";
        $output .= "<p class='success'>Password: admin123</p>";
    } else {
        $output .= "<p class='success'>Admin user already exists</p>";
    }
    
    // Insert sample shops if none exist
    $checkShops = $pdo->query("SELECT id FROM shops");
    if ($checkShops->rowCount() == 0) {
        $shops = [
            ["Shop A", "123 Main St, City A", "555-1234", "shopa@example.com"],
            ["Shop B", "456 Park Ave, City B", "555-5678", "shopb@example.com"],
            ["Shop C", "789 Broadway, City C", "555-9012", "shopc@example.com"]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO shops (name, address, phone, email) 
                    VALUES (?, ?, ?, ?)");
        
        foreach ($shops as $shop) {
            $stmt->execute($shop);
            $output .= "<p class='success'>Sample shop created: $shop[0]</p>";
        }
    } else {
        $output .= "<p class='success'>Shops already exist in database</p>";
    }
    
    $output .= "<h2>Success!</h2>";
    $output .= "<p>Database has been initialized successfully with shops and bills for each shop.</p>";
    $output .= "<p><a href='index.html' style='display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px;'>Go to Home Page</a></p>";
    
} catch (PDOException $e) {
    $output .= "<h2>Error</h2>";
    $output .= "<p>Database Error: " . $e->getMessage() . "</p>";
    
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
    <title>Database Initialization</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
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
    </style>
</head>
<body>
    <?php echo $output; ?>
</body>
</html> 