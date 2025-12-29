<?php
// index.php - Simple redirector to login
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: /dvmd_system/dashboard.php');
    exit();
} else {
    // Otherwise redirect to login
    header('Location: /dvmd_system/login.php');
    exit();
}
?>