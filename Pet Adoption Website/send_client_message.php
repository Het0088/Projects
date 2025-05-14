<?php
require_once 'config.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['message'])) {
    echo json_encode(['error' => 'Message is required']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$message = sanitize_input($_POST['message']);

// Find the employee this user is chatting with
$query = "SELECT DISTINCT 
            CASE 
                WHEN sender_id IN (SELECT id FROM employees) THEN sender_id
                WHEN receiver_id IN (SELECT id FROM employees) THEN receiver_id
            END as employee_id
          FROM messages 
          WHERE sender_id = ? OR receiver_id = ?
          ORDER BY timestamp DESC LIMIT 1";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $sender_id, $sender_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $receiver_id = $row['employee_id'];
} else {
    // If no previous chat, find an available employee
    $query = "SELECT id FROM employees ORDER BY 
              (SELECT COUNT(*) FROM messages WHERE receiver_id = employees.id) ASC 
              LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $receiver_id = $row['id'];
    } else {
        echo json_encode(['error' => 'No employees available']);
        exit();
    }
}

// Insert the message
$query = "INSERT INTO messages (sender_id, receiver_id, message) 
          VALUES (?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iis", $sender_id, $receiver_id, $message);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message_id' => mysqli_insert_id($conn)]);
} else {
    echo json_encode(['error' => 'Failed to send message']);
}
?> 