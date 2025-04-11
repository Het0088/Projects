<?php
session_start();
require_once 'config.php';

// Set cache control headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Get current user ID
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$isLoggedIn = !empty($userId);

if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}

// Function to get bills by status
function getBillsByStatus($conn, $userId, $status = null) {
    $sql = "SELECT id, client_name, amount, date, status FROM bills WHERE user_id = ?";
    $params = [$userId];
    $types = "i";
    
    if ($status !== null) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $sql .= " ORDER BY date DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ["Error preparing statement: " . $conn->error];
    }
    
    if (count($params) > 1) {
        $stmt->bind_param($types, $params[0], $params[1]);
    } else {
        $stmt->bind_param($types, $params[0]);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bills = [];
    while ($row = $result->fetch_assoc()) {
        $bills[] = $row;
    }
    
    return $bills;
}

// Get bills for different statuses
$pendingBills = getBillsByStatus($conn, $userId, 'pending');
$processingBills = getBillsByStatus($conn, $userId, 'processing');
$completedBills = getBillsByStatus($conn, $userId, 'completed');
$allBills = getBillsByStatus($conn, $userId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tab Debug Tool</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f9fc;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #3498db;
            margin-top: 30px;
        }
        .bill-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .bill-list th, .bill-list td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .bill-list th {
            background-color: #f2f2f2;
            font-weight: 600;
        }
        .bill-list tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 500;
            color: white;
        }
        .status-pending {
            background-color: #f39c12;
        }
        .status-processing {
            background-color: #3498db;
        }
        .status-completed {
            background-color: #2ecc71;
        }
        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .tab-btn {
            padding: 10px 20px;
            margin-right: 5px;
            border: none;
            background: none;
            font-size: 1em;
            cursor: pointer;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
        }
        .tab-btn.active {
            color: #3498db;
            border-bottom-color: #3498db;
            font-weight: 600;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .btn {
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tab Debug Tool</h1>
        <p>This tool shows the raw data in your bills table for different statuses. Use this to diagnose issues with the tabs on your Bills page.</p>
        
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="all">All Bills (<?php echo count($allBills); ?>)</button>
            <button class="tab-btn" data-tab="pending">Pending (<?php echo count($pendingBills); ?>)</button>
            <button class="tab-btn" data-tab="processing">Processing (<?php echo count($processingBills); ?>)</button>
            <button class="tab-btn" data-tab="completed">Completed (<?php echo count($completedBills); ?>)</button>
        </div>
        
        <!-- All Bills Tab -->
        <div class="tab-content active" id="all-tab">
            <h2>All Bills</h2>
            <?php if (count($allBills) > 0): ?>
                <table class="bill-list">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allBills as $bill): ?>
                            <tr>
                                <td>#<?php echo $bill['id']; ?></td>
                                <td><?php echo htmlspecialchars($bill['client_name']); ?></td>
                                <td>$<?php echo number_format($bill['amount'], 2); ?></td>
                                <td><?php echo date("M j, Y", strtotime($bill['date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $bill['status']; ?>">
                                        <?php echo ucfirst($bill['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bills found.</p>
            <?php endif; ?>
        </div>
        
        <!-- Pending Bills Tab -->
        <div class="tab-content" id="pending-tab">
            <h2>Pending Bills</h2>
            <?php if (count($pendingBills) > 0): ?>
                <table class="bill-list">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingBills as $bill): ?>
                            <tr>
                                <td>#<?php echo $bill['id']; ?></td>
                                <td><?php echo htmlspecialchars($bill['client_name']); ?></td>
                                <td>$<?php echo number_format($bill['amount'], 2); ?></td>
                                <td><?php echo date("M j, Y", strtotime($bill['date'])); ?></td>
                                <td>
                                    <span class="status-badge status-pending">
                                        Pending
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending bills found.</p>
            <?php endif; ?>
        </div>
        
        <!-- Processing Bills Tab -->
        <div class="tab-content" id="processing-tab">
            <h2>Processing Bills</h2>
            <?php if (count($processingBills) > 0): ?>
                <table class="bill-list">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($processingBills as $bill): ?>
                            <tr>
                                <td>#<?php echo $bill['id']; ?></td>
                                <td><?php echo htmlspecialchars($bill['client_name']); ?></td>
                                <td>$<?php echo number_format($bill['amount'], 2); ?></td>
                                <td><?php echo date("M j, Y", strtotime($bill['date'])); ?></td>
                                <td>
                                    <span class="status-badge status-processing">
                                        Processing
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No processing bills found.</p>
            <?php endif; ?>
        </div>
        
        <!-- Completed Bills Tab -->
        <div class="tab-content" id="completed-tab">
            <h2>Completed Bills</h2>
            <?php if (count($completedBills) > 0): ?>
                <table class="bill-list">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completedBills as $bill): ?>
                            <tr>
                                <td>#<?php echo $bill['id']; ?></td>
                                <td><?php echo htmlspecialchars($bill['client_name']); ?></td>
                                <td>$<?php echo number_format($bill['amount'], 2); ?></td>
                                <td><?php echo date("M j, Y", strtotime($bill['date'])); ?></td>
                                <td>
                                    <span class="status-badge status-completed">
                                        Completed
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No completed bills found.</p>
            <?php endif; ?>
        </div>
        
        <div class="actions">
            <a href="bill.php" class="btn">Back to Bills</a>
            <a href="fix_bills.php" class="btn">Run Fix Tool</a>
        </div>
    </div>
    
    <script>
        // Tab switching
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Deactivate all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Activate clicked button and corresponding content
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html> 