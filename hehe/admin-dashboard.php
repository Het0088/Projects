<?php
session_start();
require_once 'db_config.php';

// Check if logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin-login.php");
    exit();
}

// Initialize statistics array
$stats = array(
    'total_users' => 0,
    'admin_users' => 0,
    'regular_users' => 0,
    'total_papers' => 0,
    'papers_today' => 0
);

try {
    // Get user statistics with a simpler query first
    $userStatsQuery = "SELECT COUNT(*) as total_users FROM users";
    $userStats = $conn->query($userStatsQuery);
    if ($userStats && $row = $userStats->fetch_assoc()) {
        $stats['total_users'] = $row['total_users'];
    }

    // Get admin count
    $adminStatsQuery = "SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1";
    $adminStats = $conn->query($adminStatsQuery);
    if ($adminStats && $row = $adminStats->fetch_assoc()) {
        $stats['admin_users'] = $row['admin_count'];
        $stats['regular_users'] = $stats['total_users'] - $stats['admin_users'];
    }

    // Get papers statistics - directly query without checking table existence
    try {
        // Get total papers
        $paperStatsQuery = "SELECT COUNT(*) as total_papers FROM papers";
        $paperStats = $conn->query($paperStatsQuery);
        if ($paperStats && $row = $paperStats->fetch_assoc()) {
            $stats['total_papers'] = $row['total_papers'];
        }

        // Get today's papers
        $todayStatsQuery = "SELECT COUNT(*) as papers_today FROM papers WHERE DATE(generated_at) = CURDATE()";
        $todayStats = $conn->query($todayStatsQuery);
        if ($todayStats && $row = $todayStats->fetch_assoc()) {
            $stats['papers_today'] = $row['papers_today'];
        }
    } catch (Exception $e) {
        error_log("Papers query error: " . $e->getMessage());
    }

    // Get recent users
    $recentUsersQuery = "SELECT id, username, email, is_admin, created_at 
                        FROM users 
                        ORDER BY created_at DESC 
                        LIMIT 5";
    $recentUsersResult = $conn->query($recentUsersQuery);

    // Replace the system info section with quick actions
    $quickActions = array(
        array(
            'title' => 'Manage Users',
            'url' => 'admin-users.php',
            'icon' => 'users'
        ),
        array(
            'title' => 'Manage Papers',
            'url' => 'admin-papers.php',
            'icon' => 'file'
        ),
        array(
            'title' => 'Site Settings',
            'url' => 'admin-settings.php',
            'icon' => 'settings'
        ),
        array(
            'title' => 'View Statistics',
            'url' => 'admin-stats.php',
            'icon' => 'bar-chart'
        ),
        array(
            'title' => 'Backup System',
            'url' => 'admin-backup.php',
            'icon' => 'database'
        ),
        array(
            'title' => 'Popular Papers',
            'url' => 'admin-popular-papers.php',
            'icon' => 'star'
        ),
        array(
            'title' => 'Security Settings',
            'url' => 'admin-security.php',
            'icon' => 'shield'
        )
    );

    // Get site name from settings
    $siteNameQuery = "SELECT site_name FROM settings LIMIT 1";
    $siteNameResult = $conn->query($siteNameQuery);
    $siteName = ($siteNameResult && $siteNameResult->num_rows > 0) 
                ? $siteNameResult->fetch_assoc()['site_name'] 
                : "SmartScope Admin";

} catch (Exception $e) {
    // Log error and continue with default values
    error_log("Dashboard Error: " . $e->getMessage());
}

// Debug output - comment out in production
error_log("Total papers: " . $stats['total_papers']);
error_log("Papers today: " . $stats['papers_today']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?> - Dashboard</title>
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
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .content-title {
            font-size: 24px;
            color: #333;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .users-icon {
            background: #e3f2fd;
            color: #1565c0;
        }

        .admin-icon {
            background: #fce4ec;
            color: #c2185b;
        }

        .papers-icon {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .today-icon {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .stat-icon svg {
            width: 24px;
            height: 24px;
        }

        .stat-info h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 14px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .card-title {
            font-size: 18px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            font-weight: 600;
            color: #333;
        }

        .system-info {
            list-style: none;
        }

        .system-info li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .system-info li:last-child {
            border-bottom: none;
        }

        .system-info .label {
            color: #666;
        }

        .system-info .value {
            color: #333;
            font-weight: 500;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #1a237e;
            color: white;
        }

        .btn-primary:hover {
            background: #151c60;
        }

        .admin-badge {
            background: #fce4ec;
            color: #c2185b;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            color: #ccc;
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
        }

        .profile-role {
            color: #666;
            font-size: 14px;
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

        .quick-actions {
            list-style: none;
            padding: 0;
        }

        .quick-actions li {
            margin-bottom: 10px;
        }

        .quick-action-link {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            background: #f5f7fb;
        }

        .quick-action-link:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }

        .quick-action-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .quick-action-icon svg {
            width: 20px;
            height: 20px;
        }

        .star-icon {
            background: #fff8e1;
            color: #ffa000;
        }

        .shield-icon {
            background: #e8eaf6;
            color: #3949ab;
        }

        .quick-action-text {
            line-height: 1.4;
        }

        .quick-action-title {
            font-weight: 500;
            font-size: 16px;
        }

        .btn-mode {
            background: none;
            border: none;
            padding: 0;
            font: inherit;
            cursor: pointer;
            outline: inherit;
        }

        /* Dark Mode Toggle Button */
        .btn-mode {
            background: #f5f5f5;
            color: #333;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            cursor: pointer;
            border: none;
            transition: transform 0.3s ease;
        }

        .btn-mode svg {
            width: 20px;
            height: 20px;
            transition: transform 0.5s ease;
        }

        .btn-mode:hover {
            background: #e0e0e0;
        }
        
        .btn-mode:active {
            transform: scale(0.9);
        }

        /* Animation for theme transition */
        body, .sidebar, .content, .card, .stat-card, .quick-action-link, 
        .content-title, .card-title, .stat-info h3, .profile-name, th,
        .stat-info p, .profile-role, td, .menu-item {
            transition: all 0.3s ease;
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

        .dark-mode .btn-mode {
            background: #333;
            color: #e0e0e0;
        }

        .dark-mode .btn-mode:hover {
            background: #444;
        }

        .dark-mode .btn-mode svg {
            transform: rotate(180deg);
        }

        .dark-mode .content-header,
        .dark-mode .card-header,
        .dark-mode th, 
        .dark-mode td {
            border-color: #333;
        }

        .dark-mode .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* 3D Toggle Switch */
        .theme-switch-wrapper {
            display: flex;
            align-items: center;
            margin-right: 15px;
        }

        .theme-switch {
            display: inline-block;
            height: 28px;
            position: relative;
            width: 56px;
            perspective: 100px;
        }

        .theme-switch input {
            display: none;
        }

        .slider {
            background-color: #ccc;
            bottom: 0;
            cursor: pointer;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: .4s;
            border-radius: 34px;
            box-shadow: 
                0 2px 4px rgba(0,0,0,0.2),
                inset 0 -2px 2px rgba(0,0,0,0.1),
                inset 0 2px 2px rgba(255,255,255,0.2);
            transform-style: preserve-3d;
            transform: rotateY(0deg);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .slider:before {
            background-color: #fff;
            bottom: 4px;
            content: "";
            height: 20px;
            left: 4px;
            position: absolute;
            transition: .4s;
            width: 20px;
            border-radius: 50%;
            box-shadow: 
                0 4px 6px rgba(0,0,0,0.2),
                0 1px 3px rgba(0,0,0,0.1);
            z-index: 2;
        }

        input:checked + .slider {
            background-color: #2a2a2a;
            transform: rotateY(180deg);
        }

        input:checked + .slider:before {
            transform: translateX(28px) rotateY(180deg);
            background-color: #555;
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .toggle-icons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 0 8px;
            position: relative;
            z-index: 1;
        }

        .sun-icon, .moon-icon {
            width: 14px;
            height: 14px;
            color: #fff;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .sun-icon {
            color: #ff9800;
        }

        .moon-icon {
            color: #f5f5f5;
        }

        input:checked + .slider .sun-icon {
            opacity: 0.3;
        }

        input:checked + .slider .moon-icon {
            opacity: 1;
        }

        /* Animation for theme transition */
        body, .sidebar, .content, .card, .stat-card, .quick-action-link, 
        .content-title, .card-title, .stat-info h3, .profile-name, th,
        .stat-info p, .profile-role, td, .content-header, 
        .card-header, th, td, .menu-item {
            transition: all 0.3s ease;
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

        .dark-mode .content-header,
        .dark-mode .card-header,
        .dark-mode th, 
        .dark-mode td {
            border-color: #333;
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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>SmartScope</h2>
            <p>Admin Panel</p>
        </div>
        <div class="sidebar-menu">
            <a href="admin-dashboard.php" class="menu-item active">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
            <a href="admin-users.php" class="menu-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                User Management
            </a>
            <a href="admin-papers.php" class="menu-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Papers
            </a>
            <a href="admin-settings.php" class="menu-item">
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
            <h1 class="content-title">Dashboard</h1>
            <div class="profile">
                <div class="profile-avatar">
                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'A'; ?>
                </div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></div>
                    <div class="profile-role">Administrator</div>
                </div>
                
                <!-- Replace the checkbox with this 3D toggle switch with day/night symbols -->
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

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon users-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_users']); ?></h3>
                    <p>Total Users</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon admin-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['admin_users']); ?></h3>
                    <p>Admin Users</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon papers-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_papers']); ?></h3>
                    <p>Total Papers</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon today-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['papers_today']); ?></h3>
                    <p>Papers Today</p>
                </div>
            </div>
        </div>

        <div class="grid-container">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Users</h2>
                    <a href="admin-users.php" class="btn btn-primary">View All</a>
                </div>
                
                <?php if ($recentUsersResult && $recentUsersResult->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recentUsersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="admin-badge">Admin</span>
                                        <?php else: ?>
                                            User
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin-edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <p>No users found. <a href="admin-add-user.php">Add a user</a> to get started.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <ul class="quick-actions">
                    <li>
                        <a href="admin-users.php" class="quick-action-link">
                            <div class="quick-action-icon users-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <span>Manage Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-papers.php" class="quick-action-link">
                            <div class="quick-action-icon papers-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                            </div>
                            <span>Manage Papers</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-popular-papers.php" class="quick-action-link">
                            <div class="quick-action-icon star-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </div>
                            <span>Popular Papers</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-settings.php" class="quick-action-link">
                            <div class="quick-action-icon settings-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                            </div>
                            <span>Site Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-security.php" class="quick-action-link">
                            <div class="quick-action-icon shield-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                            </div>
                            <span>Security Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkmode-toggle');
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('darkMode');
            
            // Apply dark mode if saved
            if (savedTheme === 'true') {
                document.body.classList.add('dark-mode');
                darkModeToggle.checked = true;
            }
            
            // Toggle dark mode on checkbox change
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'true');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'false');
                }
            });
        });
    </script>
</body>
</html>