<?php
/**
 * API for Futuristic Coca-Cola Website Database Interactions
 * 
 * This file provides API endpoints for:
 * - User authentication (login/signup)
 * - Product data retrieval
 * - Cart management
 * - Sustainability stats retrieval
 */

// Set headers for API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cocacola_future";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    sendResponse(500, "Database connection failed: " . $conn->connect_error);
    exit;
}

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Handle CORS preflight request
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data && $method !== 'GET') {
    $data = $_POST;
}

// Route API requests
switch ($request) {
    case 'login':
        handleLogin($conn, $data);
        break;
    case 'signup':
        handleSignup($conn, $data);
        break;
    case 'products':
        handleProducts($conn, $method, $data);
        break;
    case 'product':
        handleProduct($conn, $method, $data);
        break;
    case 'cart':
        handleCart($conn, $method, $data);
        break;
    case 'sustainability':
        handleSustainability($conn);
        break;
    default:
        sendResponse(404, "Endpoint not found");
        break;
}

// Close connection
$conn->close();

/**
 * Handle user login
 */
function handleLogin($conn, $data) {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(405, "Method not allowed");
        return;
    }

    // Check required fields
    if (!isset($data['email']) || !isset($data['password'])) {
        sendResponse(400, "Email and password are required");
        return;
    }

    $email = $conn->real_escape_string($data['email']);
    
    // Get user from database
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        sendResponse(401, "Invalid email or password");
        return;
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($data['password'], $user['password'])) {
        sendResponse(401, "Invalid email or password");
        return;
    }
    
    // Create session token
    $token = bin2hex(random_bytes(32));
    $userId = $user['id'];
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Store token in database
    $sql = "INSERT INTO sessions (user_id, token, expires_at) VALUES ('$userId', '$token', '$expires')";
    if ($conn->query($sql) === FALSE) {
        sendResponse(500, "Error creating session: " . $conn->error);
        return;
    }
    
    // Return user data with token
    $userData = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'token' => $token
    ];
    
    sendResponse(200, "Login successful", $userData);
}

/**
 * Handle user signup
 */
function handleSignup($conn, $data) {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(405, "Method not allowed");
        return;
    }

    // Check required fields
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        sendResponse(400, "Name, email and password are required");
        return;
    }

    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        sendResponse(409, "Email already in use");
        return;
    }
    
    // Create user
    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
    if ($conn->query($sql) === FALSE) {
        sendResponse(500, "Error creating user: " . $conn->error);
        return;
    }
    
    // Get new user ID
    $userId = $conn->insert_id;
    
    // Create session token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Store token in database
    $sql = "INSERT INTO sessions (user_id, token, expires_at) VALUES ('$userId', '$token', '$expires')";
    if ($conn->query($sql) === FALSE) {
        sendResponse(500, "Error creating session: " . $conn->error);
        return;
    }
    
    // Return user data with token
    $userData = [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'role' => 'user',
        'token' => $token
    ];
    
    sendResponse(201, "User created successfully", $userData);
}

/**
 * Handle products listing
 */
function handleProducts($conn, $method, $data) {
    // Only GET method allowed
    if ($method !== 'GET') {
        sendResponse(405, "Method not allowed");
        return;
    }
    
    // Get all products
    $sql = "SELECT id, name, slug, tagline, price, image, stock, rating FROM products";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        sendResponse(200, "No products found", []);
        return;
    }
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    sendResponse(200, "Products retrieved successfully", $products);
}

/**
 * Handle single product
 */
function handleProduct($conn, $method, $data) {
    // Only GET method allowed
    if ($method !== 'GET') {
        sendResponse(405, "Method not allowed");
        return;
    }
    
    // Get product slug
    $slug = isset($_GET['slug']) ? $conn->real_escape_string($_GET['slug']) : '';
    
    if (empty($slug)) {
        sendResponse(400, "Product slug is required");
        return;
    }
    
    // Get product
    $sql = "SELECT * FROM products WHERE slug = '$slug'";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        sendResponse(404, "Product not found");
        return;
    }
    
    $product = $result->fetch_assoc();
    
    // Parse JSON fields
    $product['features'] = json_decode($product['features'], true);
    $product['nutrition'] = json_decode($product['nutrition'], true);
    
    sendResponse(200, "Product retrieved successfully", $product);
}

/**
 * Handle cart operations
 */
function handleCart($conn, $method, $data) {
    // Verify authentication
    $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
    $token = str_replace('Bearer ', '', $authHeader);
    
    if (empty($token)) {
        sendResponse(401, "Authentication required");
        return;
    }
    
    // Verify token
    $sql = "SELECT user_id FROM sessions WHERE token = '$token' AND expires_at > NOW()";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        sendResponse(401, "Invalid or expired token");
        return;
    }
    
    $row = $result->fetch_assoc();
    $userId = $row['user_id'];
    
    // Handle different methods
    switch ($method) {
        case 'GET':
            // Get cart items
            $sql = "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image 
                    FROM cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.user_id = '$userId'";
            $result = $conn->query($sql);
            
            $cartItems = [];
            $total = 0;
            
            while ($row = $result->fetch_assoc()) {
                $itemTotal = $row['price'] * $row['quantity'];
                $total += $itemTotal;
                
                $cartItems[] = [
                    'id' => $row['id'],
                    'product_id' => $row['product_id'],
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity'],
                    'image' => $row['image'],
                    'item_total' => $itemTotal
                ];
            }
            
            sendResponse(200, "Cart retrieved successfully", [
                'items' => $cartItems,
                'total' => $total,
                'item_count' => count($cartItems)
            ]);
            break;
            
        case 'POST':
            // Add to cart
            if (!isset($data['product_id']) || !isset($data['quantity'])) {
                sendResponse(400, "Product ID and quantity are required");
                return;
            }
            
            $productId = $conn->real_escape_string($data['product_id']);
            $quantity = (int)$data['quantity'];
            
            if ($quantity <= 0) {
                sendResponse(400, "Quantity must be greater than 0");
                return;
            }
            
            // Check if product exists
            $sql = "SELECT id, stock FROM products WHERE id = '$productId'";
            $result = $conn->query($sql);
            
            if ($result->num_rows === 0) {
                sendResponse(404, "Product not found");
                return;
            }
            
            $product = $result->fetch_assoc();
            
            // Check if stock is available
            if ($quantity > $product['stock']) {
                sendResponse(400, "Not enough stock available");
                return;
            }
            
            // Check if product already in cart
            $sql = "SELECT id, quantity FROM cart WHERE user_id = '$userId' AND product_id = '$productId'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                // Update quantity
                $row = $result->fetch_assoc();
                $newQuantity = $row['quantity'] + $quantity;
                
                if ($newQuantity > $product['stock']) {
                    sendResponse(400, "Not enough stock available");
                    return;
                }
                
                $sql = "UPDATE cart SET quantity = '$newQuantity' WHERE id = '{$row['id']}'";
            } else {
                // Add new item
                $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$userId', '$productId', '$quantity')";
            }
            
            if ($conn->query($sql) === FALSE) {
                sendResponse(500, "Error updating cart: " . $conn->error);
                return;
            }
            
            sendResponse(200, "Product added to cart");
            break;
            
        case 'PUT':
            // Update cart item
            if (!isset($data['cart_id']) || !isset($data['quantity'])) {
                sendResponse(400, "Cart ID and quantity are required");
                return;
            }
            
            $cartId = $conn->real_escape_string($data['cart_id']);
            $quantity = (int)$data['quantity'];
            
            // Check if cart item exists and belongs to user
            $sql = "SELECT c.product_id, p.stock 
                    FROM cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.id = '$cartId' AND c.user_id = '$userId'";
            $result = $conn->query($sql);
            
            if ($result->num_rows === 0) {
                sendResponse(404, "Cart item not found");
                return;
            }
            
            $row = $result->fetch_assoc();
            
            if ($quantity <= 0) {
                // Remove item
                $sql = "DELETE FROM cart WHERE id = '$cartId'";
            } else {
                // Check stock
                if ($quantity > $row['stock']) {
                    sendResponse(400, "Not enough stock available");
                    return;
                }
                
                // Update quantity
                $sql = "UPDATE cart SET quantity = '$quantity' WHERE id = '$cartId'";
            }
            
            if ($conn->query($sql) === FALSE) {
                sendResponse(500, "Error updating cart: " . $conn->error);
                return;
            }
            
            sendResponse(200, "Cart updated successfully");
            break;
            
        case 'DELETE':
            // Remove from cart
            if (!isset($data['cart_id'])) {
                sendResponse(400, "Cart ID is required");
                return;
            }
            
            $cartId = $conn->real_escape_string($data['cart_id']);
            
            // Check if cart item exists and belongs to user
            $sql = "SELECT id FROM cart WHERE id = '$cartId' AND user_id = '$userId'";
            $result = $conn->query($sql);
            
            if ($result->num_rows === 0) {
                sendResponse(404, "Cart item not found");
                return;
            }
            
            // Delete item
            $sql = "DELETE FROM cart WHERE id = '$cartId'";
            
            if ($conn->query($sql) === FALSE) {
                sendResponse(500, "Error removing from cart: " . $conn->error);
                return;
            }
            
            sendResponse(200, "Item removed from cart");
            break;
            
        default:
            sendResponse(405, "Method not allowed");
            break;
    }
}

/**
 * Handle sustainability stats
 */
function handleSustainability($conn) {
    // Only GET method allowed
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(405, "Method not allowed");
        return;
    }
    
    // Get sustainability stats
    $sql = "SELECT stat_name, stat_value, stat_label FROM sustainability_stats ORDER BY display_order";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        sendResponse(200, "No sustainability stats found", []);
        return;
    }
    
    $stats = [];
    while ($row = $result->fetch_assoc()) {
        $stats[] = $row;
    }
    
    sendResponse(200, "Sustainability stats retrieved successfully", $stats);
}

/**
 * Send JSON response
 */
function sendResponse($statusCode, $message, $data = null) {
    http_response_code($statusCode);
    
    $response = [
        'status' => $statusCode < 400 ? 'success' : 'error',
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}
?>