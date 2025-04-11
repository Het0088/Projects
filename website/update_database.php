<?php
require_once 'config.php';

echo "<h2>Database Update Tool</h2>";

// Check if username column exists in users table
$query = "SHOW COLUMNS FROM users LIKE 'username'";
$result = $conn->query($query);

if ($result && $result->num_rows == 0) {
    // Username column doesn't exist, let's add it
    echo "<p>Adding username column to users table...</p>";
    
    $alterQuery = "ALTER TABLE users ADD COLUMN username VARCHAR(50) AFTER id";
    if ($conn->query($alterQuery)) {
        echo "<p>Username column added successfully.</p>";
        
        // Now update the username column with values (using email or name if available)
        echo "<p>Updating username values...</p>";
        $updateQuery = "UPDATE users SET username = SUBSTRING_INDEX(email, '@', 1) WHERE username IS NULL";
        if ($conn->query($updateQuery)) {
            echo "<p>Username values updated successfully.</p>";
            
            // Add a unique index to username
            $indexQuery = "ALTER TABLE users ADD UNIQUE INDEX idx_username (username)";
            if ($conn->query($indexQuery)) {
                echo "<p>Unique index added to username column.</p>";
            } else {
                echo "<p>Error adding unique index to username: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Error updating username values: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Error adding username column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Username column already exists in users table.</p>";
}

// Check if shop_id column exists in users table
$query = "SHOW COLUMNS FROM users LIKE 'shop_id'";
$result = $conn->query($query);

if ($result && $result->num_rows == 0) {
    // shop_id column doesn't exist, let's add it
    echo "<p>Adding shop_id column to users table...</p>";
    
    $alterQuery = "ALTER TABLE users ADD COLUMN shop_id INT NULL";
    if ($conn->query($alterQuery)) {
        echo "<p>shop_id column added successfully.</p>";
    } else {
        echo "<p>Error adding shop_id column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>shop_id column already exists in users table.</p>";
}

// Check if password_resets table exists
$query = "SHOW TABLES LIKE 'password_resets'";
$result = $conn->query($query);

if ($result && $result->num_rows == 0) {
    // password_resets table doesn't exist, let's create it
    echo "<p>Creating password_resets table...</p>";
    
    $createQuery = "CREATE TABLE password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($createQuery)) {
        echo "<p>password_resets table created successfully.</p>";
    } else {
        echo "<p>Error creating password_resets table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>password_resets table already exists.</p>";
}

// Show current structure of users table
$query = "DESCRIBE users";
$result = $conn->query($query);

if ($result) {
    echo "<h3>Current Users Table Structure:</h3>";
    echo "<table border='1'>
            <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Null</th>
                <th>Key</th>
                <th>Default</th>
                <th>Extra</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['Field']}</td>
                <td>{$row['Type']}</td>
                <td>{$row['Null']}</td>
                <td>{$row['Key']}</td>
                <td>{$row['Default']}</td>
                <td>{$row['Extra']}</td>
              </tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Error fetching users table structure: " . $conn->error . "</p>";
}

echo "<p>Database update completed.</p>";
echo "<p><a href='db_check.php'>Go to Database Check</a> | <a href='login.php'>Go to Login Page</a></p>";
?> 