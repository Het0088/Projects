<?php
require_once 'config.php';

$output = "<h2>Database Structure Update</h2>";

try {
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $output .= "<p class='success'>Connected to database successfully!</p>";
    
    // Check if username column exists in users table
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
    if ($result->num_rows == 0) {
        // Add username column if it doesn't exist
        $conn->query("ALTER TABLE users ADD COLUMN username VARCHAR(100) AFTER id");
        
        // Update existing users to have a username based on their email
        $conn->query("UPDATE users SET username = SUBSTRING_INDEX(email, '@', 1) WHERE username IS NULL");
        
        // Add unique constraint
        $conn->query("ALTER TABLE users ADD UNIQUE (username)");
        
        $output .= "<p class='success'>Added 'username' column to users table</p>";
    } else {
        $output .= "<p class='info'>Username column already exists in users table</p>";
    }
    
    // Check if shop_id column exists in users table
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'shop_id'");
    if ($result->num_rows == 0) {
        // Add shop_id column if it doesn't exist
        $conn->query("ALTER TABLE users ADD COLUMN shop_id INT AFTER role");
        
        // Add foreign key
        try {
            $conn->query("ALTER TABLE users ADD FOREIGN KEY (shop_id) REFERENCES shops(id)");
            $output .= "<p class='success'>Added 'shop_id' column and foreign key to users table</p>";
        } catch (Exception $e) {
            $output .= "<p class='warning'>Added 'shop_id' column but couldn't add foreign key: " . $e->getMessage() . "</p>";
        }
    } else {
        $output .= "<p class='info'>Shop ID column already exists in users table</p>";
    }
    
    // Create the bills table if it doesn't exist
    $result = $conn->query("SHOW TABLES LIKE 'bills'");
    if ($result->num_rows == 0) {
        $conn->query("CREATE TABLE bills (
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
        
        $output .= "<p class='success'>Created 'bills' table</p>";
    } else {
        $output .= "<p class='info'>Bills table already exists</p>";
    }
    
    // Migrate data from old shop-specific tables if they exist
    for ($shopId = 1; $shopId <= 3; $shopId++) {
        $oldTableName = "bills_shop_" . $shopId;
        
        $result = $conn->query("SHOW TABLES LIKE '$oldTableName'");
        if ($result->num_rows > 0) {
            // Check if bills table exists
            $result = $conn->query("SHOW TABLES LIKE 'bills'");
            if ($result->num_rows > 0) {
                // Get a user ID (first admin user)
                $result = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                if ($result->num_rows == 0) {
                    // If no admin, get any user
                    $result = $conn->query("SELECT id FROM users LIMIT 1");
                }
                
                if ($result->num_rows > 0) {
                    $userId = $result->fetch_assoc()['id'];
                    
                    // Migrate data
                    $stmt = $conn->prepare("
                        INSERT INTO bills (user_id, shop_id, client_name, amount, date, status, product_number, description)
                        SELECT ?, ?, client_name, amount, date, status, product_number, description
                        FROM $oldTableName
                    ");
                    $stmt->bind_param("ii", $userId, $shopId);
                    $stmt->execute();
                    
                    $migratedRows = $stmt->affected_rows;
                    $output .= "<p class='success'>Migrated $migratedRows bills from shop $shopId</p>";
                    
                    // Drop old table
                    $conn->query("DROP TABLE $oldTableName");
                    $output .= "<p class='success'>Dropped old bills table for shop $shopId</p>";
                }
            }
        }
    }
    
    $output .= "<div class='success-message'>Database structure updated successfully!</div>";
    
} catch (Exception $e) {
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
        h2 {
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
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
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
        .links {
            text-align: center;
            margin-top: 30px;
        }
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin: 0 5px;
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
        <div class="links">
            <a href="check_db.php" class="back-link">Check Database</a>
            <a href="index.php" class="back-link">Go to Home</a>
            <a href="login.php" class="back-link">Go to Login</a>
        </div>
    </div>
</body>
</html> 