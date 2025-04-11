<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'auth_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION['name'] ?? $_SESSION['user_name'] ?? 'Employee';
$isAdmin = $_SESSION['role'] === 'admin';
$shopId = $_SESSION['shop_id'] ?? null;
$userId = $_SESSION['user_id'] ?? 0;

// Get shop details
$shopName = 'Shop 1';
if ($shopId) {
    $stmt = $conn->prepare("SELECT name FROM shops WHERE id = ?");
    $stmt->bind_param("i", $shopId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $shopName = $row['name'];
    }
    $stmt->close();
}

// Get user's bill statistics
$totalBills = 0;
$totalAmount = 0;
$pendingBills = 0;
$completedBills = 0;
$processingBills = 0;

$statsQuery = "SELECT * FROM bill_statistics WHERE user_id = ?";
$stmt = $conn->prepare($statsQuery);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $totalBills = $row['total_bills'];
        $totalAmount = $row['total_amount'];
        $pendingBills = $row['pending_bills'];
        $completedBills = $row['completed_bills'];
        $processingBills = $row['processing_bills']; 
    }
    $stmt->close();
} else {
    // If bill_statistics table doesn't exist yet
    // Try to create it
    header("Location: create_bill_stats_table.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="bill.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .top-nav {
            background: linear-gradient(135deg, #3498db, #2980b9);
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .welcome-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            font-size: 18px;
        }
        
        .welcome-text {
            font-size: 16px;
        }
        
        .shop-name {
            font-weight: 600;
            color: #ecf0f1;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-links i {
            font-size: 16px;
        }
        
        /* Statistics Dashboard Styles */
        .stats-dashboard {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .stats-dashboard h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 18px;
        }
        
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            flex: 1;
            min-width: 150px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .stat-icon i {
            color: white;
            font-size: 18px;
        }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="welcome-section">
            <div class="user-avatar">
                <?php echo strtoupper(substr($userName, 0, 1)); ?>
            </div>
            <div class="welcome-text">
                <div>Welcome, <?php echo htmlspecialchars($userName); ?></div>
                <?php if ($shopName): ?>
                    <div class="shop-name"><?php echo htmlspecialchars($shopName); ?> Billing</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="bill.php" class="active"><i class="fas fa-file-invoice"></i> Bills</a>
            <?php if ($isAdmin): ?>
                <a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-building"></i>
                <h1>Employee Portal - <?php echo htmlspecialchars($shopName); ?> Billing</h1>
            </div>
            <div class="user-info">
                <a href="fix_database.php" class="db-fix-link" title="Fix Database Issues">
                    <i class="fas fa-database"></i>
                    <span>Fix DB</span>
                </a>
            </div>
        </header>
        
        <!-- Bill Statistics Dashboard -->
        <div class="stats-dashboard">
            <h3><i class="fas fa-chart-bar"></i> Your Billing Statistics</h3>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="stat-content">
                        <div class="stat-value" id="total-bills"><?php echo $totalBills; ?></div>
                        <div class="stat-label">Total Bills</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-content">
                        <div class="stat-value" id="total-amount">$<?php echo number_format($totalAmount, 2); ?></div>
                        <div class="stat-label">Total Amount</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-content">
                        <div class="stat-value" id="pending-bills"><?php echo $pendingBills; ?></div>
                        <div class="stat-label">Pending Bills</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                    <div class="stat-content">
                        <div class="stat-value" id="processing-bills"><?php echo $processingBills; ?></div>
                        <div class="stat-label">Processing</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-content">
                        <div class="stat-value" id="completed-bills"><?php echo $completedBills; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>
            <div class="manual-refresh">
                <button id="manualRefreshBtn" class="btn-primary"><i class="fas fa-sync-alt"></i> Force Refresh Data</button>
            </div>
        </div>
        
        <main class="billing-main">
            <div class="billing-header">
                <h2 class="title">Billing Management</h2>
                <div class="actions">
                    <button id="newBillBtn" class="btn-primary"><i class="fas fa-plus-circle"></i> New Bill</button>
                    <a href="quick_modal_fix.php" class="btn-secondary" style="margin-left: 10px; text-decoration: none;"><i class="fas fa-file-alt"></i> Use Alternate Form</a>
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search bills...">
                        <button id="searchBtn"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="tabs">
                <button class="tab-btn active" data-tab="recent">Recent Bills</button>
                <button class="tab-btn" data-tab="pending">Pending</button>
                <button class="tab-btn" data-tab="completed">Completed</button>
            </div>
            
            <div class="bills-container">
                <div class="loading-spinner" id="loadingSpinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <table id="billsTable">
                    <thead>
                        <tr>
                            <th>Bill #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="billsList">
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>
                <div id="noBillsMessage" class="no-data-message hidden">No bills found</div>
            </div>
            
            <div class="pagination">
                <button id="prevPage" class="page-btn"><i class="fas fa-chevron-left"></i></button>
                <span id="pageInfo">Page 1 of 1</span>
                <button id="nextPage" class="page-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
            
            <div class="troubleshooting-banner">
                <p><i class="fas fa-info-circle"></i> Having trouble viewing bills? Try our <a href="fix_bills.php">Quick Fix Tool</a></p>
            </div>
        </main>
        
        <!-- Modal for creating/editing bills -->
        <div id="billModal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h3 id="modalTitle">Create New Bill</h3>
                <form id="billForm">
                    <input type="hidden" id="billId">
                    <input type="hidden" id="shopId" value="<?php echo $shopId; ?>">
                    <div class="form-group">
                        <label for="clientName">Client Name</label>
                        <input type="text" id="clientName" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="billAmount">Amount</label>
                            <input type="number" id="billAmount" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="billDate">Date</label>
                            <input type="date" id="billDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="productNumber">Product Number</label>
                        <input type="text" id="productNumber" placeholder="e.g. PRD-1234">
                    </div>
                    <div class="form-group">
                        <label for="billStatus">Status</label>
                        <select id="billStatus" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="billDescription">Description</label>
                        <textarea id="billDescription" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" id="cancelBtn" class="btn-secondary">Cancel</button>
                        <button type="submit" id="saveBtn" class="btn-primary">Save Bill</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Modal for viewing bill details -->
        <div id="viewBillModal" class="modal">
            <div class="modal-content bill-detail-content">
                <span class="close-view-btn">&times;</span>
                <h3>Bill Details</h3>
                <div class="bill-detail-container" id="billDetailContainer">
                    <div class="bill-header">
                        <div class="company-info">
                            <h2><i class="fas fa-building"></i> Your Company Name</h2>
                            <p>123 Business Street, City, Country</p>
                            <p>Phone: (123) 456-7890 | Email: info@company.com</p>
                        </div>
                        <div class="bill-info">
                            <h3>INVOICE</h3>
                            <p id="viewBillId">Invoice #: </p>
                            <p id="viewBillDate">Date: </p>
                            <p id="viewBillStatus">Status: </p>
                            <p id="viewProductNumber">Product #: </p>
                        </div>
                    </div>
                    
                    <div class="client-info">
                        <h4>Billed To:</h4>
                        <p id="viewClientName"></p>
                    </div>
                    
                    <div class="bill-details">
                        <h4>Bill Details:</h4>
                        <div class="bill-description" id="viewBillDescription"></div>
                        <div class="bill-amount">
                            <p>Amount: <span id="viewBillAmount"></span></p>
                        </div>
                    </div>
                    
                    <div class="bill-actions">
                        <button id="printBillBtn" class="btn-primary">
                            <i class="fas fa-print"></i> Print Bill
                        </button>
                        <button id="closeViewBtn" class="btn-secondary">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 Company Name. All rights reserved.</p>
            <div class="footer-links">
                <a href="index.php">Home</a>
                <a href="#">Help</a>
                <a href="#">Privacy Policy</a>
                <?php if ($isAdmin): ?>
                    <a href="admin.php">Admin Dashboard</a>
                <?php else: ?>
                    <a href="dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </footer>
    </div>

    <script>
        // Pass PHP variables to JavaScript
        const currentShopId = <?php echo $shopId ? $shopId : 1; ?>;
        const currentUserId = <?php echo $_SESSION['user_id'] ?? 0; ?>;
        const isAdminUser = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    </script>
    <script src="bill.js"></script>
    
    <!-- Modal Fix Script -->
    <script src="modal_fix.js"></script>

    <!-- After the stats dashboard -->
    <div class="stats-dashboard">
        <!-- ... existing statistics code ... -->
        <div class="manual-refresh">
            <button id="manualRefreshBtn" class="btn-primary"><i class="fas fa-sync-alt"></i> Force Refresh Data</button>
        </div>
    </div>

    <!-- At the bottom of the file, just before the closing </body> tag -->
    <script>
    // Ensure bill.js is loaded properly with cache busting
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, adding fresh script');
        
        // Force clear any existing scripts
        const oldScript = document.getElementById('bill-script');
        if (oldScript) {
            oldScript.remove();
        }
        
        // Add fresh bill.js script
        const script = document.createElement('script');
        script.id = 'bill-script';
        script.src = 'bill.js?v=' + new Date().getTime(); // Add cache busting parameter
        document.body.appendChild(script);
        
        // Add manual refresh button handler
        document.getElementById('manualRefreshBtn').addEventListener('click', function() {
            // Force a complete page reload with cache busting
            window.location.href = 'bill.php?refresh=' + new Date().getTime();
        });
        
        // Create a global function to force reload tab content
        window.refreshCurrentTab = function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const activeTab = Array.from(tabBtns).find(btn => btn.classList.contains('active'));
            if (activeTab) {
                const tabName = activeTab.getAttribute('data-tab');
                console.log('Manually refreshing tab: ' + tabName);
                // Simulate a click on the tab button
                activeTab.click();
            } else {
                // If no active tab found, click the Recent Bills tab
                const recentTab = document.querySelector('.tab-btn[data-tab="recent"]');
                if (recentTab) {
                    recentTab.click();
                }
            }
        };
        
        // Add refresh button to each tab button
        document.querySelectorAll('.tab-btn').forEach(btn => {
            const refreshIcon = document.createElement('i');
            refreshIcon.className = 'fas fa-sync-alt tab-refresh-icon';
            refreshIcon.style.marginLeft = '5px';
            refreshIcon.style.fontSize = '0.8em';
            refreshIcon.title = 'Refresh this tab';
            
            refreshIcon.addEventListener('click', function(e) {
                e.stopPropagation(); // Don't trigger the tab button click
                const tabName = btn.getAttribute('data-tab');
                console.log('Refreshing tab: ' + tabName);
                
                // Force switch to this tab
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Refresh data
                if (window.switchToTab) {
                    window.switchToTab(tabName);
                } else {
                    // Fallback if switchToTab isn't available yet
                    window.location.href = 'bill.php?tab=' + tabName + '&refresh=' + new Date().getTime();
                }
            });
            
            btn.appendChild(refreshIcon);
        });
        
        // Auto refresh when switching tabs
        setTimeout(function() {
            window.refreshCurrentTab();
        }, 1000);
    });
    </script>
</body>
</html> 