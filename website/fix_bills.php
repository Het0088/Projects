<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

// Ensure we don't cache this page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Get current user ID
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$isLoggedIn = !empty($userId);

// Function to log messages to the screen
function logMessage($message, $type = 'info') {
    echo "<div class='log-message $type'>" . htmlspecialchars($message) . "</div>";
    flush();
    ob_flush();
}

// Log important diagnostic information
function logDiagnosticInfo($conn) {
    logMessage("PHP Version: " . phpversion(), 'info');
    logMessage("MySQL Info: " . $conn->server_info, 'info');
    
    // Check if the database was selected correctly
    logMessage("Current database: " . ($conn->query("SELECT DATABASE()")->fetch_row()[0] ?? 'None'), 'info');
    
    // Check for the existence of critical tables
    $tables = ['users', 'bills', 'bill_statistics', 'shops'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $result && $result->num_rows > 0;
        logMessage("Table '$table': " . ($exists ? 'Exists' : 'Missing'), $exists ? 'success' : 'warning');
    }
}

// Function to check and fix database issues
function checkAndFixDatabase($conn) {
    // Check for bills table
    $tableExists = false;
    try {
        $result = $conn->query("SHOW TABLES LIKE 'bills'");
        $tableExists = $result && $result->num_rows > 0;
    } catch (Exception $e) {
        logMessage("Error checking tables: " . $e->getMessage(), 'error');
    }

    if (!$tableExists) {
        logMessage("Bills table not found. Creating...", 'warning');
        
        // Create the bills table
        $sql = "CREATE TABLE IF NOT EXISTS bills (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(255) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            product_number VARCHAR(50),
            description TEXT,
            status ENUM('pending', 'processing', 'completed') DEFAULT 'pending',
            shop_id INT(11) DEFAULT 1,
            user_id INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        try {
            $conn->query($sql);
            logMessage("Bills table created successfully", 'success');
        } catch (Exception $e) {
            logMessage("Error creating bills table: " . $e->getMessage(), 'error');
        }
    } else {
        logMessage("Bills table exists", 'success');
    }
    
    // Check for bill_statistics table
    $tableExists = false;
    try {
        $result = $conn->query("SHOW TABLES LIKE 'bill_statistics'");
        $tableExists = $result && $result->num_rows > 0;
    } catch (Exception $e) {
        logMessage("Error checking tables: " . $e->getMessage(), 'error');
    }

    if (!$tableExists) {
        logMessage("Bill statistics table not found. Creating...", 'warning');
        
        // Create the bill_statistics table - without foreign key constraint to avoid issues
        $sql = "CREATE TABLE IF NOT EXISTS bill_statistics (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            total_bills INT(11) DEFAULT 0,
            total_amount DECIMAL(15,2) DEFAULT 0.00,
            completed_bills INT(11) DEFAULT 0,
            pending_bills INT(11) DEFAULT 0,
            processing_bills INT(11) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        try {
            $result = $conn->query($sql);
            if ($result) {
                logMessage("Bill statistics table created successfully", 'success');
                
                // Add index for performance
                $conn->query("CREATE INDEX bill_stats_user_id ON bill_statistics(user_id)");
                logMessage("Added index to bill_statistics table", 'success');
            } else {
                logMessage("Error creating bill statistics table: " . $conn->error, 'error');
            }
        } catch (Exception $e) {
            logMessage("Error creating bill statistics table: " . $e->getMessage(), 'error');
        }
    } else {
        logMessage("Bill statistics table exists", 'success');
    }
    
    return true;
}

// Function to update bill statistics
function updateBillStatistics($conn, $userId) {
    if (empty($userId)) {
        logMessage("User not logged in, skipping statistics update", 'warning');
        return false;
    }
    
    try {
        // Check if user exists in bill_statistics
        $sql = "SELECT id FROM bill_statistics WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        
        // Check if prepare was successful
        if ($stmt === false) {
            logMessage("Error preparing statement: " . $conn->error, 'error');
            return false;
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Create user statistics record
            $sql = "INSERT INTO bill_statistics (user_id) VALUES (?)";
            $stmt = $conn->prepare($sql);
            
            // Check if prepare was successful
            if ($stmt === false) {
                logMessage("Error preparing insert statement: " . $conn->error, 'error');
                return false;
            }
            
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            logMessage("Created new bill statistics record for user", 'success');
        }
        
        // Update statistics based on bills
        $sql = "
            UPDATE bill_statistics bs
            SET 
                bs.total_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ?),
                bs.total_amount = (SELECT COALESCE(SUM(amount), 0) FROM bills WHERE user_id = ?),
                bs.completed_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'completed'),
                bs.pending_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'pending'),
                bs.processing_bills = (SELECT COUNT(*) FROM bills WHERE user_id = ? AND status = 'processing'),
                bs.updated_at = NOW()
            WHERE bs.user_id = ?
        ";
        
        $stmt = $conn->prepare($sql);
        
        // Check if prepare was successful
        if ($stmt === false) {
            logMessage("Error preparing update statement: " . $conn->error, 'error');
            return false;
        }
        
        $stmt->bind_param("iiiiii", $userId, $userId, $userId, $userId, $userId, $userId);
        $stmt->execute();
        
        logMessage("Bill statistics updated successfully", 'success');
        return true;
    } catch (Exception $e) {
        logMessage("Error updating bill statistics: " . $e->getMessage(), 'error');
        return false;
    }
}

// Function to show recent bills
function showRecentBills($conn, $userId) {
    if (empty($userId)) {
        logMessage("User not logged in, cannot show bills", 'warning');
        return false;
    }
    
    try {
        $sql = "SELECT id, client_name, amount, date, status FROM bills 
                WHERE user_id = ? 
                ORDER BY created_at DESC LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            logMessage("No bills found for this user", 'info');
            return false;
        }
        
        echo "<h3>Your 10 most recent bills:</h3>";
        echo "<table class='bills-table'>";
        echo "<tr><th>ID</th><th>Client</th><th>Amount</th><th>Date</th><th>Status</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>#" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['client_name']) . "</td>";
            echo "<td>$" . number_format($row['amount'], 2) . "</td>";
            echo "<td>" . date("M j, Y", strtotime($row['date'])) . "</td>";
            echo "<td><span class='status-" . $row['status'] . "'>" . ucfirst($row['status']) . "</span></td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        logMessage("Recent bills displayed successfully", 'success');
        return true;
    } catch (Exception $e) {
        logMessage("Error showing recent bills: " . $e->getMessage(), 'error');
        return false;
    }
}

// Create redirect file for bill.html
function createHtmlRedirect() {
    $redirectContent = '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=bill.php">
    <title>Redirecting...</title>
    <script>
        window.location.href = "bill.php";
    </script>
</head>
<body>
    <p>Redirecting to <a href="bill.php">bill.php</a>...</p>
</body>
</html>';

    if (file_put_contents('bill.html', $redirectContent)) {
        logMessage("Created redirect from bill.html to bill.php", 'success');
        return true;
    } else {
        logMessage("Failed to create bill.html redirect", 'error');
        return false;
    }
}

// HTML header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill System Fix Tool</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f9fc;
        }
        h1 {
            color: #3498db;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .log-message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
        }
        .info {
            background-color: #e8f4fd;
            border-left: 5px solid #3498db;
        }
        .success {
            background-color: #dff0d8;
            border-left: 5px solid #5cb85c;
        }
        .warning {
            background-color: #fcf8e3;
            border-left: 5px solid #f0ad4e;
        }
        .error {
            background-color: #f2dede;
            border-left: 5px solid #d9534f;
        }
        .buttons {
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .bills-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .bills-table th, .bills-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .bills-table th {
            background-color: #f2f2f2;
        }
        .bills-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-pending {
            display: inline-block;
            padding: 3px 6px;
            background-color: #f0ad4e;
            color: white;
            border-radius: 3px;
            font-size: 0.8em;
        }
        .status-processing {
            display: inline-block;
            padding: 3px 6px;
            background-color: #5bc0de;
            color: white;
            border-radius: 3px;
            font-size: 0.8em;
        }
        .status-completed {
            display: inline-block;
            padding: 3px 6px;
            background-color: #5cb85c;
            color: white;
            border-radius: 3px;
            font-size: 0.8em;
        }
        .fix-instructions {
            background-color: #fcf8e3;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .fix-instructions h3 {
            margin-top: 0;
            color: #8a6d3b;
        }
        .fix-instructions ol {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-tools"></i> Bill System Fix Tool</h1>
        
        <?php if (!$isLoggedIn): ?>
            <div class="log-message warning">
                <strong>You are not logged in.</strong> Some features of this tool may not work properly. 
                <a href="login.php" class="btn">Log In</a>
            </div>
        <?php endif; ?>
        
        <div class="fix-instructions">
            <h3><i class="fas fa-exclamation-triangle"></i> Common Issues:</h3>
            <ol>
                <li>If you marked a bill as "completed" or made other changes but don't see them reflected, click the "Force Full Refresh" button below.</li>
                <li>New bills might not appear immediately due to caching issues - clicking "Clear Site Cache" should fix this.</li>
                <li>Bills are stored in the database and only visible when using bill.php (not bill.html).</li>
                <li>Try clicking directly on the status badge of a bill to change its status (pending → processing → completed).</li>
            </ol>
        </div>
        
        <h2>System Diagnostics</h2>
        
        <div class="log-section">
            <?php
            // Test database connection
            if ($conn) {
                logMessage("Database connection successful", 'success');
                
                // Run diagnostic checks
                logMessage("Running system diagnostics...", 'info');
                logDiagnosticInfo($conn);
            } else {
                logMessage("Database connection failed: " . mysqli_connect_error(), 'error');
                die("</div></div></body></html>");
            }
            
            // Check and fix database structure
            try {
                checkAndFixDatabase($conn);
            } catch (Exception $e) {
                logMessage("Critical error in database fix: " . $e->getMessage(), 'error');
            }
            
            // Create HTML redirect
            try {
                createHtmlRedirect();
            } catch (Exception $e) {
                logMessage("Error creating redirect: " . $e->getMessage(), 'error');
            }
            
            // Update bill statistics
            if ($isLoggedIn) {
                try {
                    logMessage("Attempting to update bill statistics for user ID: " . $userId, 'info');
                    $statsUpdated = updateBillStatistics($conn, $userId);
                    if (!$statsUpdated) {
                        logMessage("Failed to update statistics - see errors above", 'warning');
                    }
                } catch (Exception $e) {
                    logMessage("Error in statistics update: " . $e->getMessage(), 'error');
                }
            }
            
            // Show recent bills
            if ($isLoggedIn) {
                try {
                    showRecentBills($conn, $userId);
                } catch (Exception $e) {
                    logMessage("Error showing bills: " . $e->getMessage(), 'error');
                }
            }
            ?>
        </div>
        
        <h2>Clear Browser Cache</h2>
        <p>Click the button below to clear your browser cache for this site:</p>
        <button id="clearCacheBtn" class="btn">Clear Site Cache</button>
        
        <div class="buttons">
            <a href="bill.php" class="btn">Back to Bills</a>
            <a href="fix_bills.php?force=1" class="btn btn-danger">Force Full Refresh</a>
        </div>
    </div>
    
    <script>
        // Clear cache function
        document.getElementById('clearCacheBtn').addEventListener('click', function() {
            // Force clear cache by reloading page
            const message = document.createElement('div');
            message.className = 'log-message info';
            message.textContent = 'Clearing cache...';
            document.querySelector('.log-section').appendChild(message);
            
            // Clear localStorage cache
            localStorage.clear();
            sessionStorage.clear();
            
            // Force reload without cache
            setTimeout(function() {
                message.textContent = 'Cache cleared! Redirecting to bills page...';
                
                // Redirect with cache busting parameter
                setTimeout(function() {
                    window.location.href = 'bill.php?nocache=' + new Date().getTime();
                }, 1500);
            }, 500);
        });
    </script>
</body>
</html>
