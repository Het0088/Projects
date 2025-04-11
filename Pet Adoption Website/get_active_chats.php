<?php
require_once 'config.php';
require_login();

header('Content-Type: application/json');

$employee_id = $_SESSION['user_id'];

// Get unique users who have had conversations with this employee
$query = "SELECT DISTINCT 
            u.id,
            u.username as name,
            (SELECT COUNT(*) FROM messages 
             WHERE receiver_id = ? 
             AND sender_id = u.id 
             AND is_read = 0) as unread_count
          FROM users u
          INNER JOIN messages m 
          ON (m.sender_id = u.id AND m.receiver_id = ?)
          OR (m.sender_id = ? AND m.receiver_id = u.id)
          ORDER BY unread_count DESC, 
          (SELECT MAX(timestamp) FROM messages 
           WHERE (sender_id = u.id AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = u.id)) DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iiiii", $employee_id, $employee_id, $employee_id, $employee_id, $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$chats = [];
while ($row = mysqli_fetch_assoc($result)) {
    $chats[] = $row;
}

echo json_encode($chats);
?> 