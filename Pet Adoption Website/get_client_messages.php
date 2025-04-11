<?php
require_once 'config.php';
require_login();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// Get messages between user and any employee
$query = "SELECT m.*, 
          UNIX_TIMESTAMP(m.timestamp) as timestamp_unix,
          e.username as employee_name
          FROM messages m
          LEFT JOIN employees e ON (m.sender_id = e.id OR m.receiver_id = e.id)
          WHERE (sender_id = ? AND receiver_id IN (SELECT id FROM employees))
          OR (receiver_id = ? AND sender_id IN (SELECT id FROM employees))
          ORDER BY timestamp ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'sender_id' => $row['sender_id'],
        'timestamp' => $row['timestamp_unix'],
        'is_read' => $row['is_read'],
        'employee_name' => $row['employee_name']
    ];
}

echo json_encode($messages);
?> 