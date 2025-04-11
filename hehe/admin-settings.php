<?php
session_start();
require_once 'db_config.php';

// Check if logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin-login.php");
    exit();
}

$success = '';
$error = '';

// Get current settings
$settings = [
    'site_name' => 'SmartScope',
    'site_description' => 'AI-Powered Research Paper Generator',
    'max_papers_per_day' => 10,
    'enable_registration' => 1,
    'maintenance_mode' => 0
];

// Load settings from database if exists
$settingsQuery = "SELECT * FROM settings LIMIT 1";
$settingsResult = $conn->query($settingsQuery);

if ($settingsResult && $settingsResult->num_rows > 0) {
    $dbSettings = $settingsResult->fetch_assoc();
    $settings = array_merge($settings, $dbSettings);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $site_name = trim($_POST['site_name']);
        $site_description = trim($_POST['site_description']);
        $max_papers_per_day = (int)$_POST['max_papers_per_day'];
        $enable_registration = isset($_POST['enable_registration']) ? 1 : 0;
        $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
        
        // Check if settings table exists
        $checkTableQuery = "SHOW TABLES LIKE 'settings'";
        $tableExists = $conn->query($checkTableQuery)->num_rows > 0;
        
        if (!$tableExists) {
            // Create settings table
            $createTableQuery = "CREATE TABLE settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                site_name VARCHAR(100) NOT NULL,
                site_description TEXT,
                max_papers_per_day INT DEFAULT 10,
                enable_registration TINYINT(1) DEFAULT 1,
                maintenance_mode TINYINT(1) DEFAULT 0,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            $conn->query($createTableQuery);
        }
        
        // Check if settings exist
        $checkSettingsQuery = "SELECT id FROM settings LIMIT 1";
        $settingsExist = $conn->query($checkSettingsQuery)->num_rows > 0;
        
        if ($settingsExist) {
            // Update settings
            $updateQuery = "UPDATE settings SET 
                site_name = ?,
                site_description = ?,
                max_papers_per_day = ?,
                enable_registration = ?,
                maintenance_mode = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssiii", $site_name, $site_description, $max_papers_per_day, $enable_registration, $maintenance_mode);
        } else {
            // Insert settings
            $insertQuery = "INSERT INTO settings (
                site_name, site_description, max_papers_per_day, enable_registration, maintenance_mode
            ) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssiii", $site_name, $site_description, $max_papers_per_day, $enable_registration, $maintenance_mode);
        }
        
        if ($stmt->execute()) {
            $success = "Settings updated successfully";
            
            // Update local settings
            $settings['site_name'] = $site_name;
            $settings['site_description'] = $site_description;
            $settings['max_papers_per_day'] = $max_papers_per_day;
            $settings['enable_registration'] = $enable_registration;
            $settings['maintenance_mode'] = $maintenance_mode;
        } else {
            throw new Exception("Error updating settings: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - SmartScope Admin</title>
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

        .profile {
            display: flex;
            align-items: center;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #1a237e;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .profile-info {
            line-height: 1.4;
        }

        .profile-name {
            font-weight: 500;
        }

        .profile-role {
            font-size: 12px;
            color: #666;
        }

        .logout-btn {
            padding: 8px 15px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn:hover {
            color: #f44336;
        }

        .content-title {
            font-size: 24px;
            color: #333;
        }

        .settings-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #1a237e;
            outline: none;
            box-shadow: 0 0 0 2px rgba(26, 35, 126, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .form-check input {
            margin-right: 10px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 4px;
            border: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #1a237e;
            color: white;
        }

        .btn-primary:hover {
            background: #151c60;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .section-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
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
            <a href="admin-dashboard.php" class="menu-item">
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
            <a href="admin-settings.php" class="menu-item active">
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
            <h1 class="content-title">Settings</h1>
            <div class="profile">
                <div class="profile-avatar">
                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'A'; ?>
                </div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></div>
                    <div class="profile-role">Administrator</div>
                </div>
                <a href="admin-logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="settings-container">
            <form action="admin-settings.php" method="POST">
                <h3 class="section-title">General Settings</h3>
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="site_description">Site Description</label>
                    <textarea class="form-control" id="site_description" name="site_description"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                </div>

                <h3 class="section-title">Usage Settings</h3>
                <div class="form-group">
                    <label for="max_papers_per_day">Maximum Papers Per Day</label>
                    <input type="number" class="form-control" id="max_papers_per_day" name="max_papers_per_day" value="<?php echo (int)$settings['max_papers_per_day']; ?>" min="1" max="100">
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="enable_registration" name="enable_registration" <?php echo $settings['enable_registration'] ? 'checked' : ''; ?>>
                        <label for="enable_registration">Enable User Registration</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                        <label for="maintenance_mode">Maintenance Mode</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</body>
</html> 