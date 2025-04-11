<?php
session_start();

// Clear admin-specific session variables
unset($_SESSION['admin_username']);
unset($_SESSION['is_admin']);
unset($_SESSION['admin_logged_in']);

// Redirect to admin login
header("Location: admin-login.php");
exit();
?> 