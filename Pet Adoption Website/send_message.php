<?php
require_once 'config.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['message']) || !isset($_POST['receiver_id'])) {
    echo json_encode(['error' => 'Message and receiver ID are required']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = (int)$_POST['receiver_id'];
$message = sanitize_input($_POST['message']);

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