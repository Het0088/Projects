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

// Ensure user is an admin
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit;
}

// Get user's information
$userName = $_SESSION['user_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Billing Portal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #9b59b6;
            --primary-dark: #8e44ad;
            --secondary-color: #3498db;
            --secondary-dark: #2980b9;
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
        
        .admin-badge {
            background-color: var(--primary-color);
            color: white;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 10px;
            margin-left: 10px;
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
        
        .card-icon.purple {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }
        
        .card-icon.blue {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        
        .card-icon.green {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
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
        
        .users-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .section-title {
            font-size: 18px;
            margin: 0;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            font-weight: 500;
            color: var(--text-light);
            font-size: 14px;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status.active {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .status.inactive {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            color: var(--text-light);
            transition: all 0.3s ease;
            margin-right: 5px;
        }
        
        .action-btn:hover {
            background-color: #f5f5f5;
            color: var(--primary-color);
        }
        
        .system-info {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--text-light);
        }
        
        .info-value {
            font-weight: 500;
        }
        
        .add-user-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .add-user-btn i {
            margin-right: 5px;
        }
        
        .add-user-btn:hover {
            box-shadow: 0 5px 15px rgba(155, 89, 182, 0.4);
            transform: translateY(-2px);
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
            
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-info {
                margin-top: 10px;
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
                <p>Admin Dashboard</p>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-store"></i>
                        <span>Shops</span>
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
                        <i class="fas fa-chart-bar"></i>
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
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                    </div>
                    <div class="user-name">
                        <?php echo htmlspecialchars($userName); ?>
                        <span class="admin-badge">Admin</span>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Users</h3>
                        <div class="card-icon purple">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value">15</div>
                    <p class="card-description">Registered users in the system</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Shops</h3>
                        <div class="card-icon blue">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                    <div class="card-value">3</div>
                    <p class="card-description">Active shops in the system</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Bills</h3>
                        <div class="card-icon green">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="card-value">48</div>
                    <p class="card-description">Bills across all shops</p>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Total Revenue</h3>
                        <div class="card-icon orange">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="card-value">$12,458</div>
                    <p class="card-description">Revenue from all shops</p>
                </div>
            </div>
            
            <div class="users-table">
                <div class="section-header">
                    <h3 class="section-title">Recent Users</h3>
                    <button class="add-user-btn">
                        <i class="fas fa-plus"></i> Add New User
                    </button>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Assigned Shop</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Doe</td>
                                <td>john@example.com</td>
                                <td>Admin</td>
                                <td>All Shops</td>
                                <td><span class="status active">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="action-btn"><i class="fas fa-key"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td>
                                <td>jane@example.com</td>
                                <td>User</td>
                                <td>Shop A</td>
                                <td><span class="status active">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="action-btn"><i class="fas fa-key"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>Robert Johnson</td>
                                <td>robert@example.com</td>
                                <td>User</td>
                                <td>Shop B</td>
                                <td><span class="status active">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="action-btn"><i class="fas fa-key"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>Michael Williams</td>
                                <td>michael@example.com</td>
                                <td>User</td>
                                <td>Shop C</td>
                                <td><span class="status inactive">Inactive</span></td>
                                <td>
                                    <a href="#" class="action-btn"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="action-btn"><i class="fas fa-key"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="system-info">
                <div class="section-header">
                    <h3 class="section-title">System Information</h3>
                </div>
                
                <ul class="info-list">
                    <li class="info-item">
                        <span class="info-label">PHP Version</span>
                        <span class="info-value"><?php echo phpversion(); ?></span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">MySQL Version</span>
                        <span class="info-value"><?php echo $conn->server_info; ?></span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">Server</span>
                        <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                    </li>
                    <li class="info-item">
                        <span class="info-label">Date & Time</span>
                        <span class="info-value"><?php echo date('Y-m-d H:i:s'); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html> 