<?php
// Simple database connection test
require_once 'config.php';

// Try MySQL connection
try {
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Database Connection Test</h1>";
    echo "<p style='color: green;'>✓ Successfully connected to MySQL server at $db_host!</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
    $dbExists = $stmt->fetchColumn();
    
    if ($dbExists) {
        echo "<p style='color: green;'>✓ Database '$db_name' exists.</p>";
        
        // Try connecting to specific database
        try {
            $dbPdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $dbPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<p style='color: green;'>✓ Successfully connected to database '$db_name'.</p>";
            
            // Check tables
            $tables = [];
            foreach ($dbPdo->query("SHOW TABLES") as $row) {
                $tables[] = $row[0];
            }
            
            echo "<h2>Tables in database:</h2>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
                
                // For bill tables, show count
                if (strpos($table, 'bills_shop_') === 0) {
                    $count = $dbPdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                    echo " - $count bills";
                }
            }
            echo "</ul>";
            
            // Show direct link to initialize database
            echo "<p><a href='db_initialize.php' style='padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Initialize Database (if needed)</a></p>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Error connecting to database: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Database '$db_name' does not exist.</p>";
        echo "<p>Please run the database initialization script: <a href='db_initialize.php'>db_initialize.php</a></p>";
    }
} catch (PDOException $e) {
    echo "<h1>Database Connection Failed</h1>";
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    
    echo "<h2>Troubleshooting:</h2>";
    echo "<ol>";
    echo "<li>Make sure MySQL server is running (check XAMPP control panel)</li>";
    echo "<li>Verify username and password are correct in config.php</li>";
    echo "<li>Check if MySQL server is running on the specified host ($db_host)</li>";
    echo "</ol>";
}

// Server information
echo "<h2>Server Information:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
echo "<tr><td>Current script path</td><td>" . __FILE__ . "</td></tr>";
echo "</table>";

echo "<p>Check the API error log: <a href='view_log.php'>View Log</a></p>";
?> 