<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth_functions.php';

// Log the user out
logoutUser();

// Redirect to login page
header("Location: login.php");
exit;
?> 