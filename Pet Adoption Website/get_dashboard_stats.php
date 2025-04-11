<?php
require_once 'config.php';
require_login();

// Check if user is an employee
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

$employee_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get chat statistics
$chat_stats = $conn->query("
    SELECT 
        COUNT(DISTINCT CASE WHEN m.timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 
            CASE 
                WHEN m.sender_id = $employee_id THEN m.receiver_id
                WHEN m.receiver_id = $employee_id THEN m.sender_id
            END 
        END) as active_chats,
        COUNT(CASE WHEN m.receiver_id = $employee_id AND m.is_read = 0 THEN 1 END) as pending_messages,
        COUNT(DISTINCT CASE 
            WHEN DATE(m.timestamp) = '$today' AND m.sender_id = $employee_id THEN m.receiver_id
        END) as resolved_chats
    FROM messages m
    WHERE m.sender_id = $employee_id OR m.receiver_id = $employee_id
")->fetch_assoc();

// Get task statistics
$task_stats = $conn->query("
    SELECT 
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_tasks,
        COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_tasks,
        COUNT(CASE WHEN status = 'completed' AND DATE(completed_at) = '$today' THEN 1 END) as completed_tasks
    FROM tasks
    WHERE employee_id = $employee_id
")->fetch_assoc();

// Get recent notifications
$notifications = [];
$notification_result = $conn->query("
    SELECT type, message, created_at
    FROM notifications
    WHERE employee_id = $employee_id
    ORDER BY created_at DESC
    LIMIT 5
");

while ($row = $notification_result->fetch_assoc()) {
    $icon = 'fas fa-info-circle';
    switch ($row['type']) {
        case 'chat':
            $icon = 'fas fa-comments';
            break;
        case 'task':
            $icon = 'fas fa-tasks';
            break;
        case 'alert':
            $icon = 'fas fa-exclamation-circle';
            break;
    }
    
    $notifications[] = [
        'icon' => $icon,
        'message' => $row['message'],
        'time' => date('g:i A', strtotime($row['created_at']))
    ];
}

// Return all statistics
echo json_encode([
    'activeChats' => (int)$chat_stats['active_chats'],
    'pendingMessages' => (int)$chat_stats['pending_messages'],
    'resolvedChats' => (int)$chat_stats['resolved_chats'],
    'pendingTasks' => (int)$task_stats['pending_tasks'],
    'inProgressTasks' => (int)$task_stats['in_progress_tasks'],
    'completedTasks' => (int)$task_stats['completed_tasks'],
    'notifications' => $notifications
]); 