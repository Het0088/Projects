<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Check if bill ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: bill.php');
    exit;
}

$billId = intval($_GET['id']);

// Fetch bill details
try {
    $query = "SELECT b.*, s.name as shop_name 
              FROM bills b 
              LEFT JOIN shops s ON b.shop_id = s.id 
              WHERE b.id = ? AND b.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $billId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Bill not found or doesn't belong to user
        header('Location: bill.php');
        exit;
    }
    
    $bill = $result->fetch_assoc();
} catch (Exception $e) {
    $error = "Failed to fetch bill details: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .bill-details-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .bill-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .bill-title {
            font-size: 24px;
            margin: 0;
        }
        .bill-actions {
            display: flex;
            gap: 10px;
        }
        .bill-info {
            margin-bottom: 30px;
        }
        .bill-info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .bill-info-label {
            width: 150px;
            font-weight: bold;
        }
        .bill-info-value {
            flex: 1;
        }
        .bill-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #FFC107;
        }
        .status-processing {
            background-color: #2196F3;
            color: white;
        }
        .status-completed {
            background-color: #4CAF50;
            color: white;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #2196F3;
            color: white;
        }
        .btn-danger {
            background-color: #F44336;
            color: white;
        }
        .btn-back {
            background-color: #607D8B;
            color: white;
        }
        .bill-description {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            white-space: pre-wrap;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: white;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1000;
        }
        .notification.success {
            background-color: #4CAF50;
        }
        .notification.error {
            background-color: #F44336;
        }
        .notification.show {
            opacity: 1;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                font-size: 12pt;
            }
            .container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            .bill-details-container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'header.php'; ?>
        
        <div class="bill-details-container">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php else: ?>
                <div class="bill-header">
                    <h1 class="bill-title">Bill #<?php echo $bill['id']; ?></h1>
                    <div class="bill-actions no-print">
                        <button class="btn btn-back" onclick="location.href='bill.php'">
                            <i class="fas fa-arrow-left"></i> Back to Bills
                        </button>
                        <button class="btn btn-primary" onclick="printBill()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="btn btn-primary" onclick="location.href='bill.php?edit=<?php echo $bill['id']; ?>'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger" onclick="deleteBill(<?php echo $bill['id']; ?>)">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                
                <div class="bill-info">
                    <div class="bill-info-row">
                        <div class="bill-info-label">Client:</div>
                        <div class="bill-info-value"><?php echo htmlspecialchars($bill['client_name']); ?></div>
                    </div>
                    <div class="bill-info-row">
                        <div class="bill-info-label">Amount:</div>
                        <div class="bill-info-value">₹<?php echo number_format($bill['amount'], 2); ?></div>
                    </div>
                    <div class="bill-info-row">
                        <div class="bill-info-label">Date:</div>
                        <div class="bill-info-value"><?php echo date('F j, Y', strtotime($bill['date'])); ?></div>
                    </div>
                    <div class="bill-info-row">
                        <div class="bill-info-label">Product Number:</div>
                        <div class="bill-info-value"><?php echo htmlspecialchars($bill['product_number'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="bill-info-row">
                        <div class="bill-info-label">Shop:</div>
                        <div class="bill-info-value"><?php echo htmlspecialchars($bill['shop_name']); ?></div>
                    </div>
                    <div class="bill-info-row">
                        <div class="bill-info-label">Status:</div>
                        <div class="bill-info-value">
                            <span class="bill-status status-<?php echo strtolower($bill['status']); ?>">
                                <?php echo ucfirst($bill['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="bill-info-row">
                        <div class="bill-info-label">Created:</div>
                        <div class="bill-info-value"><?php echo date('F j, Y, g:i a', strtotime($bill['created_at'])); ?></div>
                    </div>
                    
                    <?php if (!empty($bill['description'])): ?>
                        <div class="bill-description">
                            <strong>Description:</strong><br>
                            <?php echo nl2br(htmlspecialchars($bill['description'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Notification -->
        <div class="notification" id="notification"></div>
    </div>
    
    <script>
        function printBill() {
            window.print();
        }
        
        function deleteBill(billId) {
            if (confirm('Are you sure you want to delete this bill?')) {
                fetch(`api/delete_bill.php?id=${billId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(data.message, 'success');
                            setTimeout(() => {
                                window.location.href = 'bill.php';
                            }, 1500);
                        } else {
                            showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Failed to delete bill', 'error');
                    });
            }
        }
        
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html> 