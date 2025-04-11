<?php
session_start();
require_once 'db_config.php';

// Check if logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin-login.php");
    exit();
}

// Check if paper ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-papers.php");
    exit();
}

$paperId = $_GET['id'];

// Get paper info
$stmt = $conn->prepare("SELECT * FROM papers WHERE id = ? AND status = 'completed'");
$stmt->bind_param("i", $paperId);
$stmt->execute();
$result = $stmt->get_result();

if ($paper = $result->fetch_assoc()) {
    $filepath = dirname(__DIR__) . '/smartscope/generated_pdfs/' . $paper['filename'];
    
    if (file_exists($filepath)) {
        // Set headers for file download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($paper['filename']) . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        
        // Output file
        readfile($filepath);
        exit();
    }
}

// If we get here, either the paper doesn't exist or the file is missing
$_SESSION['error'] = "Paper file not found.";
header("Location: admin-papers.php");
exit();
?> 