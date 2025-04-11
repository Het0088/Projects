<?php
session_start();
require_once 'db_config.php';

// Debug session info
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if logged in as admin
if (!isset($_SESSION['admin_logged_in']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    $_SESSION['error'] = "Please login as administrator.";
    header("Location: admin-login.php");
    exit();
}

// Handle paper deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $paperId = $_GET['delete'];
    
    // Get paper info first to delete the file
    $stmt = $conn->prepare("SELECT filename FROM papers WHERE id = ?");
    $stmt->bind_param("i", $paperId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($paper = $result->fetch_assoc()) {
        // Delete the physical file if it exists
        $filepath = __DIR__ . '/generated_pdfs/' . $paper['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM papers WHERE id = ?");
        $deleteStmt->bind_param("i", $paperId);
        if ($deleteStmt->execute()) {
            $_SESSION['success'] = "Paper deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete paper.";
        }
    }
    
    header("Location: admin-papers.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total papers count
$countQuery = "SELECT COUNT(*) as total FROM papers";
$countResult = $conn->query($countQuery);
$totalPapers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalPapers / $perPage);

// Get papers with user info
$papersQuery = "SELECT p.*, u.username 
                FROM papers p 
                LEFT JOIN users u ON p.user_id = u.id 
                ORDER BY p.generated_at DESC 
                LIMIT ? OFFSET ?";
$stmt = $conn->prepare($papersQuery);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$papersResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papers Management - SmartScope Admin</title>
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

        .papers-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            font-weight: 600;
            color: #333;
            background: #f8f9fa;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #1a237e;
            color: white;
        }

        .btn-primary:hover {
            background: #151c60;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }

        .pagination .current {
            background: #1a237e;
            color: white;
            border-color: #1a237e;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        }

        .profile .btn:hover {
            background: #e0e0e0;
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
            <a href="admin-papers.php" class="menu-item active">
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
            <h1 class="content-title">Papers Management</h1>
            <div class="profile">
                <div class="profile-avatar">
                    <?php echo isset($_SESSION['admin_username']) ? strtoupper(substr($_SESSION['admin_username'], 0, 1)) : 'A'; ?>
                </div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin'; ?></div>
                    <div class="profile-role">Administrator</div>
                </div>
                <a href="admin-logout.php" class="btn">Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="papers-container">
            <?php if ($papersResult->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Topic</th>
                            <th>Generated By</th>
                            <th>Generated At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($paper = $papersResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $paper['id']; ?></td>
                                <td><?php echo htmlspecialchars($paper['topic']); ?></td>
                                <td><?php echo $paper['username'] ? htmlspecialchars($paper['username']) : 'Anonymous'; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($paper['generated_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $paper['status']; ?>">
                                        <?php echo ucfirst($paper['status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <?php if ($paper['status'] === 'completed'): ?>
                                        <a href="download_paper.php?id=<?php echo $paper['id']; ?>" class="btn btn-primary">Download</a>
                                    <?php endif; ?>
                                    <a href="admin-papers.php?delete=<?php echo $paper['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this paper?')" 
                                       class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="admin-papers.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <p>No papers generated yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 