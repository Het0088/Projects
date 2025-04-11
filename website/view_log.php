<?php
// View API error log file
$logFile = 'api_errors.log';

echo "<h1>API Error Log</h1>";

if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    
    if (empty($logContent)) {
        echo "<p>Log file is empty.</p>";
    } else {
        echo "<p>Last 50 log entries:</p>";
        echo "<pre style='background-color: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; max-height: 500px;'>";
        
        // Get last 50 lines
        $lines = explode("\n", $logContent);
        $lines = array_filter($lines); // Remove empty lines
        $lines = array_slice($lines, -50); // Get last 50 lines
        
        foreach ($lines as $line) {
            // Highlight errors
            if (strpos($line, 'error') !== false || strpos($line, 'Error') !== false || strpos($line, 'failed') !== false) {
                echo "<span style='color: red;'>$line</span>\n";
            } else {
                echo htmlspecialchars($line) . "\n";
            }
        }
        
        echo "</pre>";
        
        // Add option to clear log
        echo "<form method='post'>";
        echo "<button type='submit' name='clear' style='padding: 10px 20px; background-color: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;'>Clear Log</button>";
        echo "</form>";
    }
} else {
    echo "<p>Log file does not exist yet. It will be created when errors occur in the API.</p>";
}

// Handle clear log action
if (isset($_POST['clear'])) {
    file_put_contents($logFile, '');
    echo "<p>Log file cleared.</p>";
    echo "<meta http-equiv='refresh' content='1'>"; // Refresh page after 1 second
}

echo "<p><a href='test_db_connection.php' style='padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Back to Database Test</a></p>";
?> 