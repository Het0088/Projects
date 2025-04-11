<?php
// This file is meant to be included in bill processing pages
// or called via AJAX to update bill statistics

require_once 'config.php';

function updateBillStatistics($userId) {
    global $conn;
    
    // Check if the user has a statistics record
    $checkQuery = "SELECT id FROM bill_statistics WHERE user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    if (!$stmt) return false;
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If no record exists, create one
    if ($result->num_rows == 0) {
        $stmt->close();
        $insertQuery = "INSERT INTO bill_statistics (user_id) VALUES (?)";
        $stmt = $conn->prepare($insertQuery);
        if (!$stmt) return false;
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
    }
    
    // Update the statistics based on current bill data
    $updateQuery = "
    UPDATE bill_statistics bs
    SET 
        bs.total_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ?),
        bs.total_amount = (SELECT COALESCE(SUM(amount), 0) FROM bills WHERE user_id = ?),
        bs.completed_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'completed'),
        bs.pending_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'pending'),
        bs.processing_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'processing')
    WHERE bs.user_id = ?
    ";
    
    $stmt = $conn->prepare($updateQuery);
    if (!$stmt) return false;
    
    $stmt->bind_param("iiiiii", $userId, $userId, $userId, $userId, $userId, $userId);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// If this file is called directly, it can update all users' statistics
if (basename($_SERVER['PHP_SELF']) == 'update_bill_stats.php') {
    // Update stats for all users
    $usersQuery = "SELECT DISTINCT user_id FROM bills";
    $result = $conn->query($usersQuery);
    
    if ($result && $result->num_rows > 0) {
        echo "<h2>Updating Bill Statistics</h2>";
        
        while ($row = $result->fetch_assoc()) {
            $userId = $row['user_id'];
            if (updateBillStatistics($userId)) {
                echo "<p>Updated statistics for user ID: $userId</p>";
            } else {
                echo "<p>Error updating statistics for user ID: $userId</p>";
            }
        }
        
        echo "<p><a href='bill.php'>Return to Billing Page</a></p>";
    }
}
?> 