<?php
session_start();
require_once '../config.php';

// Set cache control headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$shopId = isset($_GET['shopId']) ? intval($_GET['shopId']) : 1;

try {
    // Get total bills
    $totalQuery = "SELECT COUNT(*) as total FROM bills WHERE user_id = ? AND shop_id = ?";
    $stmt = $conn->prepare($totalQuery);
    $stmt->bind_param("ii", $userId, $shopId);
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $totalData = $totalResult->fetch_assoc();
    $total = $totalData['total'];
    
    // Get pending bills
    $pendingQuery = "SELECT COUNT(*) as pending FROM bills WHERE user_id = ? AND shop_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($pendingQuery);
    $stmt->bind_param("ii", $userId, $shopId);
    $stmt->execute();
    $pendingResult = $stmt->get_result();
    $pendingData = $pendingResult->fetch_assoc();
    $pending = $pendingData['pending'];
    
    // Get processing bills
    $processingQuery = "SELECT COUNT(*) as processing FROM bills WHERE user_id = ? AND shop_id = ? AND status = 'processing'";
    $stmt = $conn->prepare($processingQuery);
    $stmt->bind_param("ii", $userId, $shopId);
    $stmt->execute();
    $processingResult = $stmt->get_result();
    $processingData = $processingResult->fetch_assoc();
    $processing = $processingData['processing'];
    
    // Get completed bills
    $completedQuery = "SELECT COUNT(*) as completed FROM bills WHERE user_id = ? AND shop_id = ? AND status = 'completed'";
    $stmt = $conn->prepare($completedQuery);
    $stmt->bind_param("ii", $userId, $shopId);
    $stmt->execute();
    $completedResult = $stmt->get_result();
    $completedData = $completedResult->fetch_assoc();
    $completed = $completedData['completed'];
    
    // Get total amount
    $amountQuery = "SELECT SUM(amount) as total_amount FROM bills WHERE user_id = ? AND shop_id = ?";
    $stmt = $conn->prepare($amountQuery);
    $stmt->bind_param("ii", $userId, $shopId);
    $stmt->execute();
    $amountResult = $stmt->get_result();
    $amountData = $amountResult->fetch_assoc();
    $totalAmount = $amountData['total_amount'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'total' => $total,
        'pending' => $pending,
        'processing' => $processing,
        'completed' => $completed,
        'total_amount' => $totalAmount
    ]);
} catch (Exception $e) {
    error_log("Error in bill_statistics.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve bill statistics'
    ]);
}
?> 