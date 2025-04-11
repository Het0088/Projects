<?php
require_once 'config.php';

echo "<h2>Bills Table Creation</h2>";

// Check if bills table exists
$checkQuery = "SHOW TABLES LIKE 'bills'";
$result = $conn->query($checkQuery);

if ($result->num_rows == 0) {
    echo "<p>Bills table does not exist. Creating it now...</p>";
    
    $createQuery = "CREATE TABLE bills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_name VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        date DATE NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        description TEXT,
        product_number VARCHAR(100),
        shop_id INT NOT NULL DEFAULT 1,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($createQuery)) {
        echo "<p>Bills table created successfully.</p>";
    } else {
        echo "<p>Error creating bills table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Bills table already exists.</p>";
    
    // Check if required columns exist
    $columnsToCheck = ['product_number', 'shop_id', 'user_id'];
    
    foreach ($columnsToCheck as $column) {
        $columnQuery = "SHOW COLUMNS FROM bills LIKE '$column'";
        $columnResult = $conn->query($columnQuery);
        
        if ($columnResult->num_rows == 0) {
            echo "<p>Adding missing column '$column' to bills table...</p>";
            
            $alterQuery = "";
            switch ($column) {
                case 'product_number':
                    $alterQuery = "ALTER TABLE bills ADD COLUMN product_number VARCHAR(100) AFTER description";
                    break;
                case 'shop_id':
                    $alterQuery = "ALTER TABLE bills ADD COLUMN shop_id INT NOT NULL DEFAULT 1 AFTER product_number";
                    break;
                case 'user_id':
                    $alterQuery = "ALTER TABLE bills ADD COLUMN user_id INT NOT NULL AFTER shop_id";
                    break;
            }
            
            if ($conn->query($alterQuery)) {
                echo "<p>Added column '$column' successfully.</p>";
            } else {
                echo "<p>Error adding column '$column': " . $conn->error . "</p>";
            }
        }
    }
}

// Also ensure shops table exists
$checkShopsQuery = "SHOW TABLES LIKE 'shops'";
$shopsResult = $conn->query($checkShopsQuery);

if ($shopsResult->num_rows == 0) {
    echo "<p>Shops table does not exist. Creating it now...</p>";
    
    $createShopsQuery = "CREATE TABLE shops (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        address VARCHAR(255),
        phone VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createShopsQuery)) {
        echo "<p>Shops table created successfully.</p>";
        
        // Insert default shop
        $insertShopQuery = "INSERT INTO shops (name, address, phone) VALUES ('Shop 1', '123 Main Street', '(123) 456-7890')";
        if ($conn->query($insertShopQuery)) {
            echo "<p>Default shop created successfully.</p>";
        } else {
            echo "<p>Error creating default shop: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Error creating shops table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Shops table already exists.</p>";
}

echo "<p><a href='db_check.php'>Check Database Status</a> | <a href='bill.php'>Go to Billing Page</a></p>";
?> 