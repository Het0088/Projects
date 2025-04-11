<?php
require_once 'config.php';

echo "<h2>Database Structure Check</h2>";

// Check users table
$query = "DESCRIBE users";
$result = $conn->query($query);

if ($result) {
    echo "<h3>Users Table Structure:</h3>";
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

// Get sample users
$query = "SELECT id, username, email, name, role, shop_id FROM users LIMIT 5";
$result = $conn->query($query);

if ($result) {
    echo "<h3>Sample Users:</h3>";
    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Shop ID</th>
                </tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['role']}</td>
                    <td>{$row['shop_id']}</td>
                  </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
    }
} else {
    echo "<p>Error fetching users: " . $conn->error . "</p>";
}
?> 