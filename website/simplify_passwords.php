<?php
require_once 'config.php';

echo "<h2>Password Simplification Tool</h2>";

// Update all user passwords to '1'
$query = "UPDATE users SET password = '1'";
if ($conn->query($query)) {
    echo "<p>All passwords have been updated to '1' (without hashing).</p>";
} else {
    echo "<p>Error updating passwords: " . $conn->error . "</p>";
}

// Show updated users
$query = "SELECT id, username, email, name, role, password FROM users";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h3>Updated Users:</h3>";
    echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Name</th>
                <th>Role</th>
                <th>Password</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['username']}</td>
                <td>{$row['email']}</td>
                <td>{$row['name']}</td>
                <td>{$row['role']}</td>
                <td>{$row['password']}</td>
              </tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

echo "<p><a href='login.php'>Go to Login Page</a></p>";
?> 