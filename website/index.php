<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth_functions.php';

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$userName = $_SESSION['user_name'] ?? '';
$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .user-info a:hover {
            color: #3498db;
        }
        
        .user-info i {
            margin-right: 5px;
            font-size: 18px;
        }
        
        .user-welcome {
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
            margin-top: 5px;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: all 0.2s ease;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-building"></i>
                <h1>Billing Portal</h1>
            </div>
            <div class="user-info">
                <?php if ($isLoggedIn): ?>
                    <div class="dropdown">
                        <div class="user-welcome">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($userName, 0, 1)); ?>
                            </div>
                            <span>Welcome, <?php echo htmlspecialchars($userName); ?></span>
                            <i class="fas fa-angle-down" style="margin-left: 5px;"></i>
                        </div>
                        <div class="dropdown-content">
                            <?php if ($isAdmin): ?>
                                <a href="admin.php"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
                            <?php else: ?>
                                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                            <?php endif; ?>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="login-link">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <a href="register.php" class="register-link">
                        <i class="fas fa-user-plus"></i>
                        <span>Register</span>
                    </a>
                <?php endif; ?>
            </div>
        </header>
        
        <main>
            <h2 class="title">Document Management</h2>
            <p class="subtitle">Select the document type you want to access</p>
            
            <div class="buttons">
                <button class="btn bill-btn">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Bill</span>
                </button>
                <button class="btn challan-btn">
                    <i class="fas fa-receipt"></i>
                    <span>Challan</span>
                </button>
            </div>
            
            <!-- Shop Selection Modal -->
            <div id="shopModal" class="modal">
                <div class="modal-content">
                    <span class="close-shop-btn">&times;</span>
                    <h3><i class="fas fa-store-alt"></i> Select Shop Location</h3>
                    <p class="modal-subtitle">Choose a shop to manage its bills</p>
                    <div class="shop-buttons">
                        <a href="bill.php?shop=1" class="shop-btn">
                            <i class="fas fa-store"></i>
                            <div class="shop-details">
                                <span class="shop-name">Shop No. 1</span>
                                <span class="shop-subtitle">123-456-7890 | Main Branch</span>
                            </div>
                            <span class="shop-arrow"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <a href="bill.php?shop=2" class="shop-btn">
                            <i class="fas fa-store"></i>
                            <div class="shop-details">
                                <span class="shop-name">Shop No. 2</span>
                                <span class="shop-subtitle">234-567-8901 | City Center</span>
                            </div>
                            <span class="shop-arrow"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <a href="bill.php?shop=3" class="shop-btn">
                            <i class="fas fa-store"></i>
                            <div class="shop-details">
                                <span class="shop-name">Shop No. 3</span>
                                <span class="shop-subtitle">345-678-9012 | South Point</span>
                            </div>
                            <span class="shop-arrow"><i class="fas fa-chevron-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2023 Company Name. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Help</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="test_db_connection.php" title="Check Database Status">DB Status</a>
                <?php if (!$isLoggedIn): ?>
                    <a href="login.php" title="Login to your account">Login</a>
                    <a href="register.php" title="Create a new account">Register</a>
                <?php else: ?>
                    <?php if ($isAdmin): ?>
                        <a href="admin.php" title="Go to admin dashboard">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="dashboard.php" title="Go to your dashboard">Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php" title="Log out of your account">Logout</a>
                <?php endif; ?>
            </div>
        </footer>
    </div>

    <script src="script.js"></script>
</body>
</html> 