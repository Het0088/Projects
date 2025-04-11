<?php
// Quick fix script for specific database issues
require_once 'config.php';

// Set headers
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Quick Database Fix</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .log { background: #f5f5f5; padding: 10px; border-radius: 4px; margin-top: 10px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; 
              text-decoration: none; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Quick Database Fix</h1>
    <p>Fixing specific issues based on error logs...</p>
    <div class='log'>";

try {
    // Connect to MySQL
    echo "<p>Connecting to MySQL...</p>";
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✓ Connected to MySQL</p>";
    
    // Use the database
    echo "<p>Selecting database $db_name...</p>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name");
    $pdo->exec("USE $db_name");
    echo "<p class='success'>✓ Using database $db_name</p>";
    
    // Fix issue 1: Missing bills_shop_1 table
    echo "<p>Checking for bills_shop_1 table...</p>";
    $shop1TableExists = $pdo->query("SHOW TABLES LIKE 'bills_shop_1'")->rowCount() > 0;
    
    if (!$shop1TableExists) {
        echo "<p>Table bills_shop_1 not found. Creating it...</p>";
        
        // Create table
        $pdo->exec("CREATE TABLE bills_shop_1 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(255) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Add sample data
        $sampleBills = [
            ['Tech Solutions Inc.', 1350.00, date('Y-m-d', strtotime('-5 days')), 'pending', 'Monthly software maintenance'],
            ['Global Enterprises', 3500.75, date('Y-m-d', strtotime('-10 days')), 'processing', 'Website development'],
            ['Acme Corporation', 855.50, date('Y-m-d', strtotime('-15 days')), 'completed', 'Network infrastructure']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO bills_shop_1 (client_name, amount, date, status, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($sampleBills as $bill) {
            $stmt->execute($bill);
        }
        
        echo "<p class='success'>✓ Created bills_shop_1 table and added sample data</p>";
    } else {
        echo "<p class='success'>✓ Table bills_shop_1 already exists</p>";
    }
    
    // Fix issue 2: Missing bills_shop_2 table
    echo "<p>Checking for bills_shop_2 table...</p>";
    $shop2TableExists = $pdo->query("SHOW TABLES LIKE 'bills_shop_2'")->rowCount() > 0;
    
    if (!$shop2TableExists) {
        echo "<p>Table bills_shop_2 not found. Creating it...</p>";
        
        // Create table
        $pdo->exec("CREATE TABLE bills_shop_2 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(255) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Add sample data
        $sampleBills = [
            ['Tech Solutions Inc.', 1450.00, date('Y-m-d', strtotime('-5 days')), 'pending', 'Monthly software maintenance'],
            ['Global Enterprises', 3550.75, date('Y-m-d', strtotime('-10 days')), 'processing', 'Website development'],
            ['Acme Corporation', 930.50, date('Y-m-d', strtotime('-15 days')), 'completed', 'Network infrastructure']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO bills_shop_2 (client_name, amount, date, status, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($sampleBills as $bill) {
            $stmt->execute($bill);
        }
        
        echo "<p class='success'>✓ Created bills_shop_2 table and added sample data</p>";
    } else {
        echo "<p class='success'>✓ Table bills_shop_2 already exists</p>";
    }
    
    // Also check for bills_shop_3 just to be thorough
    echo "<p>Checking for bills_shop_3 table...</p>";
    $shop3TableExists = $pdo->query("SHOW TABLES LIKE 'bills_shop_3'")->rowCount() > 0;
    
    if (!$shop3TableExists) {
        echo "<p>Table bills_shop_3 not found. Creating it...</p>";
        
        // Create table
        $pdo->exec("CREATE TABLE bills_shop_3 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(255) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Add sample data
        $sampleBills = [
            ['Tech Solutions Inc.', 1550.00, date('Y-m-d', strtotime('-5 days')), 'pending', 'Monthly software maintenance'],
            ['Global Enterprises', 3600.75, date('Y-m-d', strtotime('-10 days')), 'processing', 'Website development'],
            ['Acme Corporation', 1005.50, date('Y-m-d', strtotime('-15 days')), 'completed', 'Network infrastructure']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO bills_shop_3 (client_name, amount, date, status, description) VALUES (?, ?, ?, ?, ?)");
        foreach ($sampleBills as $bill) {
            $stmt->execute($bill);
        }
        
        echo "<p class='success'>✓ Created bills_shop_3 table and added sample data</p>";
    } else {
        echo "<p class='success'>✓ Table bills_shop_3 already exists</p>";
    }
    
    // Check for shops table
    echo "<p>Checking for shops table...</p>";
    $shopsTableExists = $pdo->query("SHOW TABLES LIKE 'shops'")->rowCount() > 0;
    
    if (!$shopsTableExists) {
        echo "<p>Creating shops table...</p>";
        
        // Create shops table
        $pdo->exec("CREATE TABLE shops (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            address VARCHAR(255),
            phone VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Add sample shops
        $sampleShops = [
            [1, 'Shop No. 1', 'Address for Shop 1', '123-456-7890'],
            [2, 'Shop No. 2', 'Address for Shop 2', '234-567-8901'],
            [3, 'Shop No. 3', 'Address for Shop 3', '345-678-9012']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO shops (id, name, address, phone) VALUES (?, ?, ?, ?)");
        foreach ($sampleShops as $shop) {
            $stmt->execute($shop);
        }
        
        echo "<p class='success'>✓ Created shops table and added sample shops</p>";
    } else {
        echo "<p class='success'>✓ Shops table already exists</p>";
        
        // Check if all 3 shop IDs exist
        $shopCount = $pdo->query("SELECT COUNT(*) FROM shops")->fetchColumn();
        echo "<p>Found $shopCount shops</p>";
        
        if ($shopCount < 3) {
            echo "<p>Adding missing shops...</p>";
            
            // Get existing shop IDs
            $existingIds = [];
            foreach ($pdo->query("SELECT id FROM shops") as $row) {
                $existingIds[] = $row['id'];
            }
            
            // Add missing shops
            $shopData = [
                1 => ['Shop No. 1', 'Address for Shop 1', '123-456-7890'],
                2 => ['Shop No. 2', 'Address for Shop 2', '234-567-8901'],
                3 => ['Shop No. 3', 'Address for Shop 3', '345-678-9012']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO shops (id, name, address, phone) VALUES (?, ?, ?, ?)");
            foreach ([1, 2, 3] as $id) {
                if (!in_array($id, $existingIds)) {
                    $data = [$id, $shopData[$id][0], $shopData[$id][1], $shopData[$id][2]];
                    $stmt->execute($data);
                    echo "<p class='success'>✓ Added missing shop: Shop $id</p>";
                }
            }
        }
    }
    
    // Clear the error log
    echo "<p>Clearing error log...</p>";
    file_put_contents('api_errors.log', '');
    echo "<p class='success'>✓ Error log cleared</p>";
    
    echo "<h2>All issues fixed!</h2>";
    echo "<p>You should now be able to view bills for all shops.</p>";
    
    // Links to shop pages
    echo "<div>";
    echo "<a href='bill.html?shop=1' class='btn'>Go to Shop 1</a> ";
    echo "<a href='bill.html?shop=2' class='btn'>Go to Shop 2</a> ";
    echo "<a href='bill.html?shop=3' class='btn'>Go to Shop 3</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</div></body></html>";
?> 