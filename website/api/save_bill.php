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
        'message' => 'User not logged in'
    ]);
    exit;
}

// Get current user ID
$userId = $_SESSION['user_id'];

// For debugging - log all incoming data
error_log('SAVE_BILL DEBUG - Request method: ' . $_SERVER['REQUEST_METHOD']);
error_log('SAVE_BILL DEBUG - Raw input: ' . file_get_contents('php://input'));
error_log('SAVE_BILL DEBUG - POST data: ' . print_r($_POST, true));

try {
    // Accept both JSON and form data
    $data = null;
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        // Get JSON data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        error_log('SAVE_BILL DEBUG - JSON data: ' . print_r($data, true));
    } else {
        // Get form data
        $data = $_POST;
        error_log('SAVE_BILL DEBUG - Form data: ' . print_r($data, true));
    }
    
    if (!$data) {
        throw new Exception("No data received");
    }
    
    // Check if we're updating status only
    $statusOnly = isset($data['status_update']) && $data['status_update'] === true;
    
    if ($statusOnly) {
        // Status-only update requires id and status
        if (!isset($data['id']) || !isset($data['status'])) {
            throw new Exception("Missing required fields for status update");
        }
        
        $id = intval($data['id']);
        $status = $data['status'];
        
        // Log this status update
        error_log("STATUS UPDATE: Bill ID: $id changing to $status by user $userId");
        
        // Validate status
        $validStatuses = ['pending', 'processing', 'completed'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid status. Must be one of: " . implode(", ", $validStatuses));
        }
        
        // Check if bill exists and belongs to this user
        $checkQuery = "SELECT id, status FROM bills WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Bill not found or you don't have permission to edit it");
        }

        $row = $result->fetch_assoc();
        $oldStatus = $row['status'];
        
        // Update only the status
        $query = "UPDATE bills SET status = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $status, $id, $userId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating bill status: " . $stmt->error);
        }
        
        error_log("SAVE_BILL DEBUG - Status updated successfully for bill ID $id from '$oldStatus' to '$status'");
        
        // Also update bill_statistics
        $updateStatsQuery = "
            UPDATE bill_statistics 
            SET 
                completed_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'completed'),
                pending_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'pending'),
                processing_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'processing'),
                updated_at = NOW()
            WHERE user_id = ?
        ";
        $stmt = $conn->prepare($updateStatsQuery);
        if ($stmt) {
            $stmt->bind_param("iiii", $userId, $userId, $userId, $userId);
            $stmt->execute();
            error_log("SAVE_BILL DEBUG - Statistics updated for bill ID $id status change");
        } else {
            error_log("SAVE_BILL WARNING - Could not update statistics: " . $conn->error);
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Bill status updated to $status",
            'id' => $id,
            'status' => $status,
            'old_status' => $oldStatus
        ]);
        exit;
    }
    
    // Regular bill save (new or update)
    if (!isset($data['client_name']) || !isset($data['amount']) || !isset($data['date'])) {
        throw new Exception("Required fields missing: client name, amount, and date are required");
    }
    
    $id = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : null;
    $clientName = $data['client_name'];
    $amount = floatval($data['amount']);
    $date = $data['date'];
    $productNumber = isset($data['product_number']) ? $data['product_number'] : '';
    $description = isset($data['description']) ? $data['description'] : '';
    $status = isset($data['status']) ? $data['status'] : 'pending';
    $shopId = isset($data['shop_id']) ? intval($data['shop_id']) : 1;
    
    error_log("SAVE_BILL DEBUG - Processing bill save - ID: $id, Client: $clientName, Status: $status, Shop: $shopId");
    
    // Validate data
    if (empty($clientName)) {
        throw new Exception("Client name is required");
    }
    
    if ($amount <= 0) {
        throw new Exception("Amount must be greater than zero");
    }
    
    // Check if date is valid
    $dateObj = new DateTime($date);
    $formattedDate = $dateObj->format('Y-m-d');
    
    // Update or Insert
    if ($id) {
        // Check if bill exists and belongs to this user
        $checkQuery = "SELECT id FROM bills WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Bill not found or you don't have permission to edit it");
        }
        
        $query = "UPDATE bills SET 
                    client_name = ?, 
                    amount = ?, 
                    date = ?, 
                    product_number = ?, 
                    description = ?, 
                    status = ?, 
                    shop_id = ? 
                  WHERE id = ? AND user_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdssssiii", $clientName, $amount, $formattedDate, $productNumber, $description, $status, $shopId, $id, $userId);
        
        if (!$stmt->execute()) {
            error_log("SAVE_BILL ERROR - Failed to update bill: " . $stmt->error);
            throw new Exception("Error updating bill: " . $stmt->error);
        }
        
        error_log("SAVE_BILL DEBUG - Bill updated successfully with ID $id");
        $message = "Bill updated successfully";
    } else {
        $query = "INSERT INTO bills 
                    (client_name, amount, date, product_number, description, status, shop_id, user_id) 
                  VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdsssiii", $clientName, $amount, $formattedDate, $productNumber, $description, $status, $shopId, $userId);
        
        if (!$stmt->execute()) {
            error_log("SAVE_BILL ERROR - Failed to create bill: " . $stmt->error);
            throw new Exception("Error creating bill: " . $stmt->error);
        }
        
        $id = $conn->insert_id;
        error_log("SAVE_BILL DEBUG - New bill created with ID $id");
        $message = "Bill created successfully";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'id' => $id,
        'status' => $status
    ]);
} catch (Exception $e) {
    error_log("SAVE_BILL ERROR - " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 