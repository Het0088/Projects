<?php
require_once 'config.php';
require_login();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// First check if user already has an ongoing chat with an employee
$query = "SELECT DISTINCT employee_id 
          FROM messages 
          WHERE (sender_id = ? AND receiver_id IN (SELECT id FROM employees))
          OR (receiver_id = ? AND sender_id IN (SELECT id FROM employees))
          ORDER BY timestamp DESC LIMIT 1";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['employee_id' => $row['employee_id']]);
    exit();
}

// If no ongoing chat, find least busy employee
$query = "SELECT e.id, COUNT(m.id) as message_count
          FROM employees e
          LEFT JOIN messages m ON e.id = m.receiver_id
          WHERE m.timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
          GROUP BY e.id
          ORDER BY message_count ASC
          LIMIT 1";

$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['employee_id' => $row['id']]);
} else {
    // If no employees found, get any employee
    $query = "SELECT id FROM employees ORDER BY RAND() LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['employee_id' => $row['id']]);
    } else {
        echo json_encode(['error' => 'No employees available']);
    }
}
?> 