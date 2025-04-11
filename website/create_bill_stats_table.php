<?php
require_once 'config.php';

echo "<h2>Creating Bill Statistics Table</h2>";

// Create bill_statistics table
$query = "CREATE TABLE IF NOT EXISTS bill_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_bills INT DEFAULT 0,
    total_amount DECIMAL(12,2) DEFAULT 0,
    completed_bills INT DEFAULT 0,
    pending_bills INT DEFAULT 0,
    processing_bills INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($query)) {
    echo "<p>Bill statistics table created successfully.</p>";
    
    // Check if we need to create records for existing users
    $usersQuery = "SELECT id FROM users WHERE id NOT IN (SELECT user_id FROM bill_statistics)";
    $result = $conn->query($usersQuery);
    
    if ($result && $result->num_rows > 0) {
        echo "<p>Creating statistics records for " . $result->num_rows . " users...</p>";
        
        while ($row = $result->fetch_assoc()) {
            $userId = $row['id'];
            $insertQuery = "INSERT INTO bill_statistics (user_id) VALUES ($userId)";
            if ($conn->query($insertQuery)) {
                echo "<p>Added record for user ID: $userId</p>";
            } else {
                echo "<p>Error adding record for user ID: $userId - " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p>No new users to add to statistics table.</p>";
    }
    
    // Initialize bill counts from existing data
    echo "<p>Updating statistics based on existing bills...</p>";
    
    $updateQuery = "
    UPDATE bill_statistics bs
    JOIN (
        SELECT 
            user_id,
            COUNT(*) as total,
            SUM(amount) as amount_sum,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing
        FROM bills
        GROUP BY user_id
    ) b ON bs.user_id = b.user_id
    SET 
        bs.total_bills = b.total,
        bs.total_amount = b.amount_sum,
        bs.completed_bills = b.completed,
        bs.pending_bills = b.pending,
        bs.processing_bills = b.processing
    ";
    
    if ($conn->query($updateQuery)) {
        echo "<p>Statistics updated successfully.</p>";
    } else {
        echo "<p>Error updating statistics: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Error creating bill statistics table: " . $conn->error . "</p>";
}

echo "<p><a href='bill.php'>Go to Billing Page</a></p>";
?> 