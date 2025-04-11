<?php
// Database connection test
require_once 'config.php';

$output = "<h1>Database Connection Test</h1>";

// Test connection
try {
    // Try to connect
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $output .= "<div class='success'>✓ Connected to MySQL server successfully!</div>";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
    $dbExists = $stmt->rowCount() > 0;
    
    if ($dbExists) {
        $output .= "<div class='success'>✓ Database '$db_name' exists!</div>";
        
        // Connect to database
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if shops table exists
        $shopsTable = $pdo->query("SHOW TABLES LIKE 'shops'")->rowCount();
        
        if ($shopsTable > 0) {
            $output .= "<div class='success'>✓ Table 'shops' exists!</div>";
            
            // Get shops
            $shops = $pdo->query("SELECT * FROM shops ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            $shopsCount = count($shops);
            
            $output .= "<div class='success'>✓ Found $shopsCount shops in the database</div>";
            
            $output .= "<h2>Shops</h2>";
            $output .= "<table border='1' cellpadding='5' cellspacing='0'>";
            $output .= "<tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th></tr>";
            
            foreach ($shops as $shop) {
                $output .= "<tr>";
                $output .= "<td>{$shop['id']}</td>";
                $output .= "<td>{$shop['name']}</td>";
                $output .= "<td>{$shop['address']}</td>";
                $output .= "<td>{$shop['phone']}</td>";
                $output .= "</tr>";
            }
            
            $output .= "</table>";
            
            // Check each shop's bills table
            $output .= "<h2>Shop Bills Tables</h2>";
            
            for ($shopId = 1; $shopId <= 3; $shopId++) {
                $tableName = "bills_shop_" . $shopId;
                $tableExists = $pdo->query("SHOW TABLES LIKE '$tableName'")->rowCount() > 0;
                
                $output .= "<h3>Shop $shopId</h3>";
                
                if ($tableExists) {
                    $output .= "<div class='success'>✓ Table '$tableName' exists!</div>";
                    
                    // Count bills
                    $count = $pdo->query("SELECT COUNT(*) FROM $tableName")->fetchColumn();
                    $output .= "<div class='success'>✓ Found $count bills for Shop $shopId</div>";
                    
                    if ($count > 0) {
                        // Show bills
                        $bills = $pdo->query("SELECT * FROM $tableName ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                        
                        $output .= "<h4>Recent Bills</h4>";
                        $output .= "<table border='1' cellpadding='5' cellspacing='0'>";
                        $output .= "<tr><th>ID</th><th>Client</th><th>Amount</th><th>Date</th><th>Status</th></tr>";
                        
                        foreach ($bills as $bill) {
                            $output .= "<tr>";
                            $output .= "<td>{$bill['id']}</td>";
                            $output .= "<td>{$bill['client_name']}</td>";
                            $output .= "<td>\${$bill['amount']}</td>";
                            $output .= "<td>{$bill['date']}</td>";
                            $output .= "<td>{$bill['status']}</td>";
                            $output .= "</tr>";
                        }
                        
                        $output .= "</table>";
                        $output .= "<p><a href='bill.html?shop=$shopId' class='btn-link'>Go to Shop $shopId Bills</a></p>";
                    } else {
                        $output .= "<div class='warning'>⚠ No bills found for Shop $shopId.</div>";
                    }
                } else {
                    $output .= "<div class='warning'>⚠ Table '$tableName' does not exist!</div>";
                }
                
                $output .= "<hr>";
            }
        } else {
            $output .= "<div class='warning'>⚠ Table 'shops' does not exist!</div>";
            $output .= "<p>You should run <a href='db_initialize.php'>db_initialize.php</a> to create the shops table and add sample data.</p>";
        }
    } else {
        $output .= "<div class='warning'>⚠ Database '$db_name' does not exist!</div>";
        $output .= "<p>You should run <a href='db_initialize.php'>db_initialize.php</a> to create the database.</p>";
    }
    
} catch (PDOException $e) {
    $output .= "<div class='error'>✗ Database Connection Error: " . $e->getMessage() . "</div>";
    
    // Troubleshooting advice
    if (strpos($e->getMessage(), "Access denied") !== false) {
        $output .= "<p>This might be a MySQL authentication issue. Check your username and password in config.php.</p>";
        $output .= "<p>Current settings:</p>";
        $output .= "<ul>";
        $output .= "<li>Host: $db_host</li>";
        $output .= "<li>User: $db_user</li>";
        $output .= "<li>Password: " . (empty($db_pass) ? "empty" : "set") . "</li>";
        $output .= "</ul>";
    } elseif (strpos($e->getMessage(), "Connection refused") !== false) {
        $output .= "<p>Make sure MySQL server is running on your system.</p>";
        $output .= "<p>For XAMPP users:</p>";
        $output .= "<ol>";
        $output .= "<li>Open XAMPP Control Panel</li>";
        $output .= "<li>Start MySQL service if it's not running</li>";
        $output .= "</ol>";
    }
}

// Actions
$output .= "<h2>Actions</h2>";
$output .= "<div class='actions'>";
$output .= "<a href='fix_database.php' class='btn btn-primary'><i class='fas fa-magic'></i> Auto-Fix Database</a>";
$output .= "<a href='db_initialize.php' class='btn btn-secondary'>Initialize Database</a>";
$output .= "<a href='index.html' class='btn btn-secondary'>Go to Home Page</a>";
$output .= "</div>";

// Show PHP info
$output .= "<h2>PHP and Server Information</h2>";
$output .= "<table border='1' cellpadding='5' cellspacing='0'>";
$output .= "<tr><th>PHP Version</th><td>" . phpversion() . "</td></tr>";
$output .= "<tr><th>Server Software</th><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
$output .= "<tr><th>Document Root</th><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
$output .= "</table>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3, h4 {
            color: #2c3e50;
        }
        p {
            margin-bottom: 15px;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
            padding: 5px;
            margin-bottom: 5px;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
            padding: 5px;
            margin-bottom: 5px;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
            padding: 5px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .actions {
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-primary {
            background-color: #3498db;
        }
        .btn-secondary {
            background-color: #2ecc71;
        }
        .btn-link {
            display: inline-block;
            padding: 5px 10px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php echo $output; ?>
</body>
</html> 