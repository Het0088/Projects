<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET request to fetch shops
if ($method === 'GET') {
    try {
        $query = "SELECT * FROM shops ORDER BY name ASC";
        $result = $conn->query($query);
        
        if (!$result) {
            throw new Exception("Error fetching shops: " . $conn->error);
        }
        
        $shops = [];
        while ($row = $result->fetch_assoc()) {
            $shops[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'address' => $row['address'],
                'phone' => $row['phone']
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'shops' => $shops
        ]);
    } catch (Exception $e) {
        error_log("Error in shops.php GET: " . $e->getMessage());
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to retrieve shops'
        ]);
    }
}
// Handle POST request to create or update a shop
else if ($method === 'POST') {
    try {
        // Get JSON data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['name'])) {
            throw new Exception("Invalid data provided");
        }
        
        $id = isset($data['id']) ? intval($data['id']) : null;
        $name = $data['name'];
        $address = isset($data['address']) ? $data['address'] : '';
        $phone = isset($data['phone']) ? $data['phone'] : '';
        
        // Update or Insert
        if ($id) {
            $query = "UPDATE shops SET name = ?, address = ?, phone = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $name, $address, $phone, $id);
            $message = "Shop updated successfully";
        } else {
            $query = "INSERT INTO shops (name, address, phone) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $name, $address, $phone);
            $message = "Shop created successfully";
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error saving shop: " . $stmt->error);
        }
        
        if (!$id) {
            $id = $conn->insert_id;
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $id
        ]);
    } catch (Exception $e) {
        error_log("Error in shops.php POST: " . $e->getMessage());
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save shop'
        ]);
    }
}
// Handle DELETE request to delete a shop
else if ($method === 'DELETE') {
    try {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if (!$id) {
            throw new Exception("No shop ID provided");
        }
        
        // Check if shop has bills associated
        $checkQuery = "SELECT COUNT(*) as count FROM bills WHERE shop_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            throw new Exception("Cannot delete shop with existing bills");
        }
        
        // Delete shop
        $query = "DELETE FROM shops WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error deleting shop: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("Shop not found or already deleted");
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Shop deleted successfully'
        ]);
    } catch (Exception $e) {
        error_log("Error in shops.php DELETE: " . $e->getMessage());
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 