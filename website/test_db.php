<?php
require_once 'config.php';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all bills
    $stmt = $pdo->query("SELECT * FROM bills ORDER BY created_at DESC");
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Display results
    echo "<h1>Database Connection Test</h1>";
    
    if (count($bills) > 0) {
        echo "<h2>Bills found in database:</h2>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Client</th><th>Amount</th><th>Date</th><th>Status</th></tr>";
        
        foreach ($bills as $bill) {
            echo "<tr>";
            echo "<td>{$bill['id']}</td>";
            echo "<td>{$bill['client_name']}</td>";
            echo "<td>\${$bill['amount']}</td>";
            echo "<td>{$bill['date']}</td>";
            echo "<td>{$bill['status']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No bills found in database. Please run db_initialize.php first.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Database Connection Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database settings in config.php</p>";
}
?> 