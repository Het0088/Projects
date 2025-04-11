<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    echo json_encode([
        'loggedIn' => true,
        'username' => $_SESSION['username']
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
?> 