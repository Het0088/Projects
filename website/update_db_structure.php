<?php
require_once 'config.php';

$output = '';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $output .= "<p class='success'>Connected to database successfully!</p>";
    
    // Create a single bills table that references both users and shops
    $pdo->exec("CREATE TABLE IF NOT EXISTS bills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        shop_id INT NOT NULL,
        client_name VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        date DATE NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        product_number VARCHAR(50),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE
    )");
    
    $output .= "<p class='success'>Bills table created/updated successfully!</p>";
    
    // Migrate data from old shop-specific tables to the new bills table
    for ($shopId = 1; $shopId <= 3; $shopId++) {
        $oldTableName = "bills_shop_" . $shopId;
        
        // Check if old table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '$oldTableName'");
        if ($stmt->rowCount() > 0) {
            // Get all bills from old table
            $bills = $pdo->query("SELECT * FROM $oldTableName")->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($bills)) {
                // Get a random user ID (you may want to modify this logic)
                $userId = $pdo->query("SELECT id FROM users LIMIT 1")->fetch(PDO::FETCH_COLUMN);
                
                // Insert bills into new table
                $stmt = $pdo->prepare("
                    INSERT INTO bills (user_id, shop_id, client_name, amount, date, status, product_number, description)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                foreach ($bills as $bill) {
                    $stmt->execute([
                        $userId,
                        $shopId,
                        $bill['client_name'],
                        $bill['amount'],
                        $bill['date'],
                        $bill['status'],
                        $bill['product_number'],
                        $bill['description']
                    ]);
                }
                
                $output .= "<p class='success'>Migrated bills from shop $shopId successfully!</p>";
            }
            
            // Drop old table
            $pdo->exec("DROP TABLE IF EXISTS $oldTableName");
            $output .= "<p class='success'>Removed old bills table for shop $shopId</p>";
        }
    }
    
    // Update users table to add shop_id if not exists
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS shop_id INT");
        $pdo->exec("ALTER TABLE users ADD FOREIGN KEY IF NOT EXISTS (shop_id) REFERENCES shops(id)");
        $output .= "<p class='success'>Updated users table structure!</p>";
    } catch (PDOException $e) {
        $output .= "<p class='warning'>Note: Shop ID column might already exist in users table</p>";
    }
    
    $output .= "<div class='success-message'>Database structure updated successfully!</div>";
    
} catch (PDOException $e) {
    $output .= "<div class='error-message'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database Structure</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            max-width: 800px;
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
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        p {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
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
        <h1>Update Database Structure</h1>
        <?php echo $output; ?>
        <div style="text-align: center;">
            <a href="index.php" class="back-link">Go back to home</a>
        </div>
    </div>
</body>
</html> 