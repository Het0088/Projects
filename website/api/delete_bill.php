<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../config.php';
require_once '../auth_functions.php';

// Set cache control headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You are not logged in. Please login to continue.'
    ]);
    exit;
}

// Get current user ID
$userId = $_SESSION['user_id'];

// Get bill ID from request
$billId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$billId) {
    echo json_encode([
        'success' => false,
        'message' => 'Bill ID is required'
    ]);
    exit;
}

// Use the global mysqli connection
global $conn;

try {
    // First check if the bill exists and belongs to the user
    $checkQuery = "SELECT id FROM bills WHERE id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    
    if (!$checkStmt) {
        throw new Exception('Failed to prepare check statement: ' . $conn->error);
    }
    
    $checkStmt->bind_param("ii", $billId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Bill not found or you do not have permission to delete it'
        ]);
        exit;
    }
    
    // Delete the bill
    $deleteQuery = "DELETE FROM bills WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    
    if (!$deleteStmt) {
        throw new Exception('Failed to prepare delete statement: ' . $conn->error);
    }
    
    $deleteStmt->bind_param("i", $billId);
    
    if ($deleteStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Bill deleted successfully'
        ]);
        
        // Update bill statistics
        include_once '../update_bill_stats.php';
        updateBillStatistics($userId);
    } else {
        throw new Exception('Failed to delete bill: ' . $deleteStmt->error);
    }
} catch (Exception $e) {
    error_log('Error in delete_bill.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?> 