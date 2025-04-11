<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth_functions.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Ensure user is not an admin (admins should go to admin.php)
if (isAdmin()) {
    header("Location: admin.php");
    exit;
}

// Get user's information
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Billing Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --secondary-dark: #27ae60;
            --text-color: #333;
            --text-light: #777;
            --bg-color: #f5f7fa;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            color: var(--text-color);
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            position: fixed;
            height: 100vh;
            padding: 20px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header h2 {
            margin: 0;
            font-size: 24px;
        }
        
        .sidebar-header p {
            margin: 5px 0 0;
            opacity: 0.8;
            font-size: 14px;
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-footer a {
            color: white;
            opacity: 0.8;
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-footer a:hover {
            opacity: 1;
        }
        
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            margin: 0;
            font-size: 18px;
            color: var(--text-color);
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .card-icon.blue {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        
        .card-icon.green {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }
        
        .card-icon.purple {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }
        
        .card-icon.orange {
            background: linear-gradient(135deg, #e67e22, #d35400);
        }
        
        .card-value {
            font-size: 28px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .card-description {
            color: var(--text-light);
            font-size: 14px;
            margin: 0;
        }
        
        .recent-activity {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            margin: 0;
        }
        
        .view-all {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .view-all:hover {
            color: var(--primary-dark);
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            font-size: 16px;
        }
        
        .activity-details {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 500;
            margin: 0 0 5px;
        }
        
        .activity-time {
            color: var(--text-light);
            font-size: 12px;
            margin: 0;
        }
        
        /* Responsive design */
        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
                overflow: hidden;
            }
            
            .sidebar-header {
                padding: 10px;
            }
            
            .sidebar-header h2, 
            .sidebar-header p, 
            .sidebar-menu span, 
            .sidebar-footer span {
                display: none;
            }
            
            .sidebar-menu a {
                padding: 15px 0;
                justify-content: center;
            }
            
            .sidebar-menu i {
                margin: 0;
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 60px;
            }
            
            .sidebar-footer {
                padding: 10px 0;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Billing Portal</h2>
                <p>User Dashboard</p>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="bill.html">
                        <i class="fas fa-file-invoice"></i>
                        <span>Bills</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-store"></i>
                        <span>Shops</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="topbar">
                <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Bills</h3>
                        <div class="card-icon blue">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="card-value">24</div>
                    <p class="card-description">Bills across all shops</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pending Bills</h3>
                        <div class="card-icon orange">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="card-value">8</div>
                    <p class="card-description">Bills awaiting payment</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Monthly Revenue</h3>
                        <div class="card-icon green">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="card-value">$4,285</div>
                    <p class="card-description">Revenue this month</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Shops</h3>
                        <div class="card-icon purple">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                    <div class="card-value">3</div>
                    <p class="card-description">Total shops managed</p>
                </div>
            </div>
            
            <div class="recent-activity">
                <div class="section-header">
                    <h3 class="section-title">Recent Activity</h3>
                    <a href="#" class="view-all">View All</a>
                </div>
                
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="activity-details">
                            <h4 class="activity-title">New bill created for Shop A</h4>
                            <p class="activity-time">Today, 2:30 PM</p>
                        </div>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="activity-details">
                            <h4 class="activity-title">Bill #1042 marked as paid</h4>
                            <p class="activity-time">Yesterday, 5:15 PM</p>
                        </div>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #e67e22, #d35400);">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="activity-details">
                            <h4 class="activity-title">Bill #1039 updated</h4>
                            <p class="activity-time">Yesterday, 11:30 AM</p>
                        </div>
                    </li>
                    
                    <li class="activity-item">
                        <div class="activity-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="activity-details">
                            <h4 class="activity-title">Accessed Shop B dashboard</h4>
                            <p class="activity-time">Aug 15, 2023, 3:45 PM</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html> 