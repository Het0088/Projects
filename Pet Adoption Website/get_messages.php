<?php
require_once 'config.php';
require_login();

header('Content-Type: application/json');

if (!isset($_GET['user_id'])) {
    echo json_encode(['error' => 'User ID is required']);
    exit();
}

$employee_id = $_SESSION['user_id'];
$user_id = (int)$_GET['user_id'];

// Mark messages as read
$update_query = "UPDATE messages SET is_read = 1 
                WHERE receiver_id = ? AND sender_id = ?";
$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, "ii", $employee_id, $user_id);
mysqli_stmt_execute($stmt);

// Get messages
$query = "SELECT m.*, 
          UNIX_TIMESTAMP(m.timestamp) as timestamp_unix
          FROM messages m
          WHERE (sender_id = ? AND receiver_id = ?)
          OR (sender_id = ? AND receiver_id = ?)
          ORDER BY timestamp ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iiii", $employee_id, $user_id, $user_id, $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'sender_id' => $row['sender_id'],
        'timestamp' => $row['timestamp_unix'],
        'is_read' => $row['is_read']
    ];
}

echo json_encode($messages);
?> 