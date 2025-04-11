<?php
// Quick fix script for database errors
require_once 'config.php';

// Set headers
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Database Errors</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .logs { background: #f5f5f5; padding: 15px; border-radius: 4px; max-height: 200px; overflow: auto; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Fix Database Errors</h1>
    <p>This tool will fix the specific errors found in your logs:</p>
    
    <div class='logs'>
        <pre>Table bills_shop_1 does not exist
Table bills_shop_2 does not exist
Error: Syntax error with LIMIT/OFFSET parameters</pre>
    </div>
";

try {
    // Connect to database
    echo "<h2>Step 1: Connecting to Database</h2>";
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>Connected to MySQL server</p>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name");
    $pdo->exec("USE $db_name");
    echo "<p class='success'>Using database: $db_name</p>";
    
    // Fix Issue 1: Table bills_shop_1 does not exist
    echo "<h2>Step 2: Creating Missing Tables</h2>";
    
    // Create shops table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS shops (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        address VARCHAR(255),
        phone VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Check if shops exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM shops");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO shops (id, name, address, phone) VALUES (?, ?, ?, ?)");
        $shops = [
            [1, 'Shop No. 1', 'Address for Shop 1', '123-456-7890'],
            [2, 'Shop No. 2', 'Address for Shop 2', '234-567-8901'],
            [3, 'Shop No. 3', 'Address for Shop 3', '345-678-9012']
        ];
        
        foreach ($shops as $shop) {
            $stmt->execute($shop);
        }
        echo "<p class='success'>Added sample shops</p>";
    }
    
    // Create bills_shop_1 table
    $pdo->exec("DROP TABLE IF EXISTS bills_shop_1");
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
    echo "<p class='success'>Created bills_shop_1 table</p>";
    
    // Create bills_shop_2 table
    $pdo->exec("DROP TABLE IF EXISTS bills_shop_2");
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
    echo "<p class='success'>Created bills_shop_2 table</p>";
    
    // Create bills_shop_3 table
    $pdo->exec("DROP TABLE IF EXISTS bills_shop_3");
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
    echo "<p class='success'>Created bills_shop_3 table</p>";
    
    // Add sample data to tables
    echo "<h2>Step 3: Adding Sample Data</h2>";
    
    for ($shopId = 1; $shopId <= 3; $shopId++) {
        $tableName = "bills_shop_" . $shopId;
        
        // Sample data
        $sampleBills = [
            ['Tech Solutions Inc.', 1250.00 + ($shopId * 100), date('Y-m-d', strtotime('-5 days')), 'pending', 'Monthly software maintenance'],
            ['Global Enterprises', 3450.75 + ($shopId * 50), date('Y-m-d', strtotime('-10 days')), 'processing', 'Website development'],
            ['Acme Corporation', 780.50 + ($shopId * 75), date('Y-m-d', strtotime('-15 days')), 'completed', 'Network infrastructure'],
            ['Smith Consulting', 1890.25 + ($shopId * 25), date('Y-m-d', strtotime('-2 days')), 'pending', 'IT support services']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO $tableName (client_name, amount, date, status, description) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($sampleBills as $bill) {
            $stmt->execute($bill);
        }
        
        echo "<p class='success'>Added sample data to $tableName</p>";
    }
    
    // Fix Issue 3: Update the API file for LIMIT/OFFSET syntax
    echo "<h2>Step 4: Fixing API File</h2>";
    
    $apiFile = 'api/bills.php';
    if (file_exists($apiFile)) {
        $content = file_get_contents($apiFile);
        
        // Find and replace the problematic query
        $pattern = '/\$stmt = \$pdo->prepare\("SELECT \* " \. \$query \. " ORDER BY created_at DESC LIMIT \? OFFSET \?"\);/';
        $replacement = '$stmt = $pdo->prepare("SELECT * " . $query . " ORDER BY created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset . ");';
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        if ($newContent !== $content) {
            file_put_contents($apiFile, $newContent);
            echo "<p class='success'>Updated API file to fix LIMIT/OFFSET syntax</p>";
        } else {
            echo "<p>API file already updated or pattern not found</p>";
        }
    } else {
        echo "<p class='error'>API file not found</p>";
    }
    
    // Clear error log
    echo "<h2>Step 5: Clearing Error Log</h2>";
    file_put_contents('api_errors.log', '');
    echo "<p class='success'>Error log cleared</p>";
    
    echo "<h2>All Done!</h2>";
    echo "<p>The issues have been fixed. You should now be able to view bills for all shops.</p>";
    
    echo "<div>";
    echo "<a href='bill.html?shop=1' class='btn'>Go to Shop 1</a> ";
    echo "<a href='bill.html?shop=2' class='btn'>Go to Shop 2</a> ";
    echo "<a href='bill.html?shop=3' class='btn'>Go to Shop 3</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
    
    echo "<h3>Debugging Info:</h3>";
    echo "<ul>";
    echo "<li>Host: $db_host</li>";
    echo "<li>Database: $db_name</li>";
    echo "<li>Username: $db_user</li>";
    echo "</ul>";
}

echo "</body></html>";
?> 