<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set cache control headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');

// Include required files
require_once '../config.php';
require_once '../auth_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Debug logging
error_log("BILLS API: Request received. User ID: " . $_SESSION['user_id']);

try {
    global $conn;
    
    // Get user ID
    $userId = $_SESSION['user_id'];
    $shopId = isset($_GET['shop']) ? intval($_GET['shop']) : null;
    
    // Log info about this request
    error_log("BILLS API: Shop ID: " . ($shopId ?? 'not specified'));
    
    // Check for specific bill ID request
    if (isset($_GET['id'])) {
        $billId = intval($_GET['id']);
        error_log("BILLS API: Fetching specific bill ID: $billId");
        
        $query = "SELECT b.*, s.name as shop_name 
                  FROM bills b 
                  LEFT JOIN shops s ON b.shop_id = s.id 
                  WHERE b.id = ? AND b.user_id = ?";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $billId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($bill = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'bill' => $bill
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Bill not found'
            ]);
        }
        exit;
    }
    
    // For paginated list of bills
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10; // Bills per page
    $offset = ($page - 1) * $limit;
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'recent';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    error_log("BILLS API: List request - Page: $page, Tab: $tab, Search: $search");
    
    // Base query for counting total bills
    $countQuery = "SELECT COUNT(*) as total FROM bills b WHERE user_id = ?";
    $params = [$userId];
    $types = "i";
    
    // Base query for fetching bills
    $query = "SELECT b.*, s.name as shop_name 
              FROM bills b 
              LEFT JOIN shops s ON b.shop_id = s.id 
              WHERE b.user_id = ?";
    
    // Add shop filter if provided
    if ($shopId) {
        $query .= " AND b.shop_id = ?";
        $countQuery .= " AND b.shop_id = ?";
        $params[] = $shopId;
        $types .= "i";
    }
    
    // Add search filter if provided
    if ($search) {
        $searchTerm = "%$search%";
        $query .= " AND (b.client_name LIKE ? OR b.product_number LIKE ? OR b.description LIKE ?)";
        $countQuery .= " AND (b.client_name LIKE ? OR b.product_number LIKE ? OR b.description LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }
    
    // Add tab filter
    if ($tab === 'pending') {
        $query .= " AND b.status = 'pending'";
        $countQuery .= " AND b.status = 'pending'";
    } elseif ($tab === 'completed') {
        $query .= " AND b.status = 'completed'";
        $countQuery .= " AND b.status = 'completed'";
    } elseif ($tab === 'processing') {
        $query .= " AND b.status = 'processing'";
        $countQuery .= " AND b.status = 'processing'";
    }
    
    // Add order and pagination
    $query .= " ORDER BY b.date DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    // Log the queries for debugging
    error_log("BILLS API: Count Query: $countQuery");
    error_log("BILLS API: Main Query: $query");
    
    // Execute count query
    $stmt = $conn->prepare($countQuery);
    if (!$stmt) {
        throw new Exception("Error preparing count statement: " . $conn->error);
    }
    
    // Bind params for count query
    $countTypes = substr($types, 0, -2); // Remove the last two 'i's for limit and offset
    $countParams = array_slice($params, 0, -2);
    if (!empty($countParams)) {
        $bindParams = array_merge([$countTypes], $countParams);
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }
    
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalBills = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalBills / $limit);
    
    // Execute main query for bills
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparing main statement: " . $conn->error);
    }
    
    // Bind params for main query
    $bindParams = array_merge([$types], $params);
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bills = [];
    while ($row = $result->fetch_assoc()) {
        $bills[] = $row;
    }
    
    // Get shop details
    $shopDetails = null;
    if ($shopId) {
        $shopQuery = "SELECT * FROM shops WHERE id = ?";
        $stmt = $conn->prepare($shopQuery);
        $stmt->bind_param("i", $shopId);
        $stmt->execute();
        $shopResult = $stmt->get_result();
        if ($shopRow = $shopResult->fetch_assoc()) {
            $shopDetails = $shopRow;
        }
    }
    
    echo json_encode([
        'success' => true,
        'bills' => $bills,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalBills' => $totalBills,
            'billsPerPage' => $limit
        ],
        'shop' => $shopDetails
    ]);
    
} catch (Exception $e) {
    error_log("Error in bills.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 