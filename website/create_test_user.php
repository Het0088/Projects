<?php
require_once 'config.php';

echo "<h2>Test User Creation Tool</h2>";

// Check if users exist
$checkQuery = "SELECT COUNT(*) as user_count FROM users";
$result = $conn->query($checkQuery);
$row = $result->fetch_assoc();
$userCount = $row['user_count'];

echo "<p>Current user count: $userCount</p>";

if ($userCount == 0) {
    // No users exist, create a test admin user
    $username = "admin";
    $email = "admin@example.com";
    $name = "Administrator";
    $password = password_hash("admin123", PASSWORD_DEFAULT);
    $role = "admin";
    
    $insertQuery = "INSERT INTO users (username, email, name, password, role) 
                    VALUES (?, ?, ?, ?, ?)";
                    
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        echo "<p>Error preparing statement: " . $conn->error . "</p>";
    } else {
        $stmt->bind_param("sssss", $username, $email, $name, $password, $role);
        
        if ($stmt->execute()) {
            echo "<p>Test admin user created successfully!</p>";
            echo "<p>Username: admin</p>";
            echo "<p>Password: admin123</p>";
        } else {
            echo "<p>Error creating test user: " . $stmt->error . "</p>";
        }
    }
    
    // Create a regular employee user as well
    $username = "employee";
    $email = "employee@example.com";
    $name = "Test Employee";
    $password = password_hash("employee123", PASSWORD_DEFAULT);
    $role = "employee";
    
    $insertQuery = "INSERT INTO users (username, email, name, password, role) 
                    VALUES (?, ?, ?, ?, ?)";
                    
    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        echo "<p>Error preparing statement: " . $conn->error . "</p>";
    } else {
        $stmt->bind_param("sssss", $username, $email, $name, $password, $role);
        
        if ($stmt->execute()) {
            echo "<p>Test employee user created successfully!</p>";
            echo "<p>Username: employee</p>";
            echo "<p>Password: employee123</p>";
        } else {
            echo "<p>Error creating test user: " . $stmt->error . "</p>";
        }
    }
} else {
    // Show existing users
    $query = "SELECT id, username, email, name, role FROM users LIMIT 10";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<h3>Existing Users:</h3>";
        echo "<table border='1'>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Role</th>
                </tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['role']}</td>
                  </tr>";
        }
        
        echo "</table>";
    }
}

echo "<p><a href='login.php'>Go to Login Page</a></p>";
?> 