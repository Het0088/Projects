<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Dashboard'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f7fb;
            min-height: 100vh;
            display: flex;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .sidebar {
            width: 250px;
            background: #1a237e;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #f44336;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .menu-item svg {
            width: 18px;
            height: 18px;
            margin-right: 10px;
        }

        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: background 0.3s ease;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            transition: border-color 0.3s ease;
        }

        .content-title {
            font-size: 24px;
            color: #333;
            transition: color 0.3s ease;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            background: #1a237e;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 500;
        }

        .profile-info {
            line-height: 1.4;
        }

        .profile-name {
            color: #333;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .profile-role {
            color: #666;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .profile .btn {
            padding: 8px 16px;
            background: #f5f5f5;
            color: #333;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .profile .btn:hover {
            background: #e0e0e0;
        }

        /* Simple 3D Toggle Switch with Icons */
        .darkmode-toggle {
            position: relative;
            width: 50px;
            height: 24px;
            margin: 0 15px;
        }

        .darkmode-input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .darkmode-label {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .toggle-track {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 34px;
            background: #e0e0e0;
            box-shadow: 
                inset 0 1px 3px rgba(0,0,0,0.2),
                0 1px 1px rgba(255,255,255,0.8);
            transition: all 0.4s ease;
            overflow: hidden;
        }

        .toggle-icons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 0 5px;
            position: relative;
            z-index: 1;
        }

        .sun-icon, .moon-icon {
            width: 14px;
            height: 14px;
            transition: opacity 0.3s;
        }

        .sun-icon {
            color: #ff9800;
            margin-left: 2px;
        }

        .moon-icon {
            color: #3949ab;
            margin-right: 2px;
        }

        .toggle-thumb {
            position: absolute;
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            z-index: 2;
        }

        .darkmode-input:checked + .darkmode-label .toggle-track {
            background: #2a2a2a;
        }

        .darkmode-input:checked + .darkmode-label .toggle-thumb {
            transform: translateX(26px);
        }

        .darkmode-input:checked + .darkmode-label .sun-icon {
            opacity: 0.4;
        }

        .darkmode-input:not(:checked) + .darkmode-label .moon-icon {
            opacity: 0.4;
        }

        /* Dark Mode Theme */
        body.dark-mode {
            background: #121212;
            color: #e0e0e0;
        }

        .dark-mode .content {
            background: #121212;
        }

        .dark-mode .card,
        .dark-mode .stat-card {
            background: #1e1e1e;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .dark-mode .content-title,
        .dark-mode .card-title,
        .dark-mode .stat-info h3,
        .dark-mode .profile-name,
        .dark-mode th {
            color: #e0e0e0;
        }

        .dark-mode .stat-info p,
        .dark-mode .profile-role,
        .dark-mode td {
            color: #aaa;
        }

        .dark-mode .quick-action-link {
            background: #2a2a2a;
            color: #e0e0e0;
        }

        .dark-mode .quick-action-link:hover {
            background: #333;
        }

        .dark-mode .content-header,
        .dark-mode .card-header,
        .dark-mode th, 
        .dark-mode td {
            border-color: #333;
        }

        /* Add your page-specific styles below this line */
        <?php if (isset($additionalStyles)) echo $additionalStyles; ?>
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>SmartScope</h2>
            <p>Admin Panel</p>
        </div>
        <div class="sidebar-menu">
            <a href="admin-dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
            <a href="admin-users.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-users.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                User Management
            </a>
            <a href="admin-papers.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-papers.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Papers
            </a>
            <a href="admin-settings.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin-settings.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                Settings
            </a>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <h1 class="content-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h1>
            <div class="profile">
                <div class="profile-avatar">
                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'A'; ?>
                </div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></div>
                    <div class="profile-role">Administrator</div>
                </div>
                
                <!-- Dark Mode Toggle Switch -->
                <div class="darkmode-toggle">
                    <input type="checkbox" id="darkmode-toggle" class="darkmode-input">
                    <label for="darkmode-toggle" class="darkmode-label">
                        <div class="toggle-track">
                            <div class="toggle-icons">
                                <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="5"></circle>
                                    <line x1="12" y1="1" x2="12" y2="3"></line>
                                    <line x1="12" y1="21" x2="12" y2="23"></line>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                    <line x1="1" y1="12" x2="3" y2="12"></line>
                                    <line x1="21" y1="12" x2="23" y2="12"></line>
                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                                </svg>
                                <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                                </svg>
                            </div>
                            <div class="toggle-thumb"></div>
                        </div>
                    </label>
                </div>
                
                <a href="admin-logout.php" class="btn">Logout</a>
            </div>
        </div>
        
        <!-- Page content will go here -->
    </div>
</body>
</html> 