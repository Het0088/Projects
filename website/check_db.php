<?php
require_once 'config.php';

$output = "<h2>Database Diagnostic Information</h2>";

try {
    // Check database connection
    $output .= "<h3>Database Connection</h3>";
    if ($conn && !$conn->connect_error) {
        $output .= "<p class='success'>✅ Connected to MySQL database successfully</p>";
    } else {
        $output .= "<p class='error'>❌ Connection failed: " . $conn->connect_error . "</p>";
    }
    
    // Check if users table exists
    $output .= "<h3>Table Structure Check</h3>";
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    if (in_array('users', $tables)) {
        $output .= "<p class='success'>✅ Users table exists</p>";
        
        // Check users table structure
        $result = $conn->query("DESCRIBE users");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row['Type'];
        }
        
        $output .= "<p><strong>Users Table Columns:</strong></p>";
        $output .= "<ul>";
        foreach ($columns as $column => $type) {
            $output .= "<li>$column - $type</li>";
        }
        $output .= "</ul>";
        
        // Check for sample user data
        try {
            // First check which columns exist
            $columns = [];
            $result = $conn->query("DESCRIBE users");
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            // Build the SQL query based on existing columns
            $selectColumns = ["id"];
            if (in_array('username', $columns)) $selectColumns[] = "username";
            if (in_array('name', $columns)) $selectColumns[] = "name"; 
            if (in_array('role', $columns)) $selectColumns[] = "role";
            if (in_array('shop_id', $columns)) $selectColumns[] = "shop_id";
            if (in_array('email', $columns)) $selectColumns[] = "email";
            
            $columnStr = implode(", ", $selectColumns);
            $result = $conn->query("SELECT $columnStr FROM users LIMIT 5");
            
            if ($result->num_rows > 0) {
                $output .= "<p class='success'>✅ Users table has data</p>";
                $output .= "<p><strong>Sample Users:</strong></p>";
                $output .= "<table border='1' cellpadding='5'>";
                
                // Create table header based on available columns
                $output .= "<tr>";
                foreach ($selectColumns as $col) {
                    $output .= "<th>" . ucfirst($col) . "</th>";
                }
                $output .= "</tr>";
                
                // Add data rows
                while ($row = $result->fetch_assoc()) {
                    $output .= "<tr>";
                    foreach ($selectColumns as $col) {
                        $value = isset($row[$col]) ? htmlspecialchars($row[$col]) : 'NULL';
                        $output .= "<td>" . $value . "</td>";
                    }
                    $output .= "</tr>";
                }
                $output .= "</table>";
            } else {
                $output .= "<p class='error'>❌ Users table is empty</p>";
            }
        } catch (Exception $e) {
            $output .= "<p class='error'>Error retrieving user data: " . $e->getMessage() . "</p>";
        }
    } else {
        $output .= "<p class='error'>❌ Users table does not exist</p>";
    }
    
    // Check if shops table exists
    if (in_array('shops', $tables)) {
        $output .= "<p class='success'>✅ Shops table exists</p>";
        
        // Check shops table structure
        $result = $conn->query("DESCRIBE shops");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = $row['Type'];
        }
        
        $output .= "<p><strong>Shops Table Columns:</strong></p>";
        $output .= "<ul>";
        foreach ($columns as $column => $type) {
            $output .= "<li>$column - $type</li>";
        }
        $output .= "</ul>";
        
        // Check for sample shop data
        $result = $conn->query("SELECT * FROM shops LIMIT 5");
        if ($result->num_rows > 0) {
            $output .= "<p class='success'>✅ Shops table has data</p>";
        } else {
            $output .= "<p class='error'>❌ Shops table is empty</p>";
        }
    } else {
        $output .= "<p class='error'>❌ Shops table does not exist</p>";
    }
    
    // Check if bills table exists
    if (in_array('bills', $tables)) {
        $output .= "<p class='success'>✅ Bills table exists</p>";
    } else {
        $output .= "<p class='error'>❌ Bills table does not exist. <a href='update_db_structure.php'>Run the database update script</a> to create it.</p>";
    }
    
    // List all tables in the database
    $output .= "<h3>All Tables in Database</h3>";
    $output .= "<ul>";
    foreach ($tables as $table) {
        $output .= "<li>$table</li>";
    }
    $output .= "</ul>";
    
} catch (Exception $e) {
    $output .= "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Structure Check</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #2c3e50;
        }
        p {
            margin-bottom: 15px;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        ul {
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        th {
            background-color: #f2f2f2;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php echo $output; ?>
        <div style="text-align: center; margin-top: 30px;">
            <a href="db_initialize.php" class="back-link">Initialize Database</a>
            <a href="index.php" class="back-link" style="margin-left: 10px;">Go to Home</a>
        </div>
    </div>
</body>
</html> 