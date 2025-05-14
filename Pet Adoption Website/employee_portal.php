<?php
require_once 'config.php';
require_employee(); // This will handle both login check and employee role check

// Get employee details
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Portal - Streeties</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .portal-container {
            display: flex;
            min-height: calc(100vh - 60px);
            margin-top: 60px;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 0;
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: #34495e;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            background: #f8f9fa;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .dashboard-card h3 {
            margin: 0 0 15px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dashboard-card i {
            color: #e67e22;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #e67e22;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .action-btn:hover {
            background: #d35400;
        }

        .notification {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .notification:last-child {
            border-bottom: none;
        }

        .notification i {
            color: #e67e22;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #e67e22;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo-container">
                <a href="employee_portal.php">
                    <img src="logo.png" alt="Streeties Logo" class="logo">
                </a>
            </div>
            <nav class="nav-links">
                <a href="employee_portal.php" class="active">Dashboard</a>
                <a href="employee_chat.php">Chat System</a>
                <a href="employee_tasks.php">Tasks</a>
                <a href="employee_reports.php">Reports</a>
            </nav>
            <div class="auth-buttons">
                <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="portal-container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="employee_portal.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="employee_chat.php"><i class="fas fa-comments"></i> Chat System</a></li>
                <li><a href="employee_tasks.php"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="employee_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="employee_schedule.php"><i class="fas fa-calendar"></i> Schedule</a></li>
                <li><a href="employee_profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="employee_settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h1>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3><i class="fas fa-comments"></i> Chat Overview</h3>
                    <div class="stats">
                        <div class="stat-item">
                            <div class="stat-value" id="activeChats">0</div>
                            <div class="stat-label">Active Chats</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="pendingMessages">0</div>
                            <div class="stat-label">Pending Messages</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="resolvedChats">0</div>
                            <div class="stat-label">Resolved Today</div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3><i class="fas fa-tasks"></i> Tasks</h3>
                    <div class="stats">
                        <div class="stat-item">
                            <div class="stat-value" id="pendingTasks">0</div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="inProgressTasks">0</div>
                            <div class="stat-label">In Progress</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="completedTasks">0</div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="quick-actions">
                    <a href="employee_chat.php" class="action-btn">
                        <i class="fas fa-comments"></i>
                        Open Chat System
                    </a>
                    <a href="employee_tasks.php" class="action-btn">
                        <i class="fas fa-tasks"></i>
                        View Tasks
                    </a>
                    <a href="employee_reports.php" class="action-btn">
                        <i class="fas fa-chart-bar"></i>
                        Generate Report
                    </a>
                </div>
            </div>

            <div class="dashboard-card">
                <h3><i class="fas fa-bell"></i> Recent Notifications</h3>
                <div id="notifications">
                    <!-- Notifications will be loaded here -->
                </div>
            </div>
        </main>
    </div>

    <script>
        // Function to update dashboard statistics
        async function updateDashboardStats() {
            try {
                const response = await fetch('get_dashboard_stats.php');
                const data = await response.json();
                
                // Update chat statistics
                document.getElementById('activeChats').textContent = data.activeChats;
                document.getElementById('pendingMessages').textContent = data.pendingMessages;
                document.getElementById('resolvedChats').textContent = data.resolvedChats;
                
                // Update task statistics
                document.getElementById('pendingTasks').textContent = data.pendingTasks;
                document.getElementById('inProgressTasks').textContent = data.inProgressTasks;
                document.getElementById('completedTasks').textContent = data.completedTasks;
                
                // Update notifications
                const notificationsContainer = document.getElementById('notifications');
                notificationsContainer.innerHTML = data.notifications.map(notification => `
                    <div class="notification">
                        <i class="${notification.icon}"></i>
                        <div>
                            <div>${notification.message}</div>
                            <small>${notification.time}</small>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error updating dashboard:', error);
            }
        }

        // Update dashboard every 30 seconds
        updateDashboardStats();
        setInterval(updateDashboardStats, 30000);
    </script>
</body>
</html> 