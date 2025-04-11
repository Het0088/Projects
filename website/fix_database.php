<?php
// Auto-fix database and redirect to billing page
require_once 'config.php';

// Create an empty log file to start fresh
file_put_contents('api_errors.log', '');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Auto-Fix</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 20px auto; padding: 20px; }
        .progress { margin: 20px 0; background-color: #f3f3f3; height: 20px; border-radius: 10px; overflow: hidden; }
        .progress-bar { width: 0%; height: 100%; background-color: #4CAF50; transition: width 0.5s; }
        .log { background-color: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 300px; overflow: auto; margin-top: 20px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Database Auto-Fix Tool</h1>
    <p>Setting up your database automatically...</p>
    <div class='progress'><div class='progress-bar' id='progress-bar'></div></div>
    <div class='log' id='log'></div>
    
    <script>
        // Set up progress bar
        const progressBar = document.getElementById('progress-bar');
        const log = document.getElementById('log');
        
        function updateProgress(percent, message, isError = false) {
            progressBar.style.width = percent + '%';
            const entry = document.createElement('div');
            entry.textContent = message;
            entry.className = isError ? 'error' : 'success';
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
        }
        
        updateProgress(10, 'Starting database setup...');
    </script>
";
flush();

try {
    echo "<script>updateProgress(20, 'Connecting to MySQL server...');</script>";
    flush();
    
    // First check if MySQL is available
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<script>updateProgress(30, 'Connected to MySQL server successfully!');</script>";
    flush();
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        echo "<script>updateProgress(40, 'Creating database $db_name...');</script>";
        flush();
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name");
        
        echo "<script>updateProgress(50, 'Database created successfully!');</script>";
        flush();
    } else {
        echo "<script>updateProgress(50, 'Database $db_name already exists');</script>";
        flush();
    }
    
    // Use the database
    $pdo->exec("USE $db_name");
    
    echo "<script>updateProgress(60, 'Checking for shops table...');</script>";
    flush();
    
    // Create shops table
    $pdo->exec("CREATE TABLE IF NOT EXISTS shops (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        address VARCHAR(255),
        phone VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Check if shops table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM shops");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "<script>updateProgress(70, 'Adding sample shops...');</script>";
        flush();
        
        // Add sample shops
        $sampleShops = [
            ['Shop No. 1', 'Address for Shop 1', '123-456-7890'],
            ['Shop No. 2', 'Address for Shop 2', '234-567-8901'],
            ['Shop No. 3', 'Address for Shop 3', '345-678-9012']
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO shops (name, address, phone)
            VALUES (?, ?, ?)
        ");
        
        foreach ($sampleShops as $shop) {
            $stmt->execute($shop);
        }
    } else {
        echo "<script>updateProgress(70, 'Shops already exist in the database');</script>";
        flush();
    }
    
    echo "<script>updateProgress(80, 'Creating shop bill tables...');</script>";
    flush();
    
    // Create bill tables for each shop
    for ($shopId = 1; $shopId <= 3; $shopId++) {
        $tableName = "bills_shop_" . $shopId;
        
        // Create bills table
        $pdo->exec("CREATE TABLE IF NOT EXISTS $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(255) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Check if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tableName");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Add sample data
            $sampleBills = [
                [
                    'Tech Solutions Inc.', 
                    1250.00 + ($shopId * 100), 
                    date('Y-m-d', strtotime('-5 days')), 
                    'pending',
                    'Monthly software maintenance - Shop ' . $shopId
                ],
                [
                    'Global Enterprises', 
                    3450.75 + ($shopId * 50), 
                    date('Y-m-d', strtotime('-10 days')), 
                    'processing',
                    'Website development - Phase ' . $shopId
                ],
                [
                    'Acme Corporation', 
                    780.50 + ($shopId * 75), 
                    date('Y-m-d', strtotime('-15 days')), 
                    'completed',
                    'Network infrastructure setup - Shop ' . $shopId
                ],
                [
                    'Smith Consulting', 
                    1890.25 + ($shopId * 25), 
                    date('Y-m-d', strtotime('-2 days')), 
                    'pending',
                    'IT support services - Shop ' . $shopId
                ]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO $tableName (client_name, amount, date, status, description)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($sampleBills as $bill) {
                $stmt->execute($bill);
            }
            
            echo "<script>updateProgress(" . (80 + $shopId * 5) . ", 'Added sample bills for Shop $shopId');</script>";
            flush();
        }
    }
    
    echo "<script>
    updateProgress(100, 'Database setup complete! Redirecting to billing page...');
    setTimeout(() => {
        window.location.href = 'index.html';
    }, 2000);
    </script>";
    
} catch (PDOException $e) {
    echo "<script>updateProgress(100, 'Error: " . addslashes($e->getMessage()) . "', true);</script>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><a href='index.html'>Go back to home page</a></p>";
}

echo "</body></html>";
?> 