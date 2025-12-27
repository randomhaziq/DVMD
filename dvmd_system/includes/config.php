<?php
// includes/config.php
session_start();

// Include API files
require_once __DIR__ . '/../api/auth.php';

// User roles constants
define('ROLE_CITIZEN', 'citizen');
define('ROLE_KETUA_KAMPUNG', 'ketua_kampung');
define('ROLE_PENGHULU', 'penghulu');
define('ROLE_DISTRICT', 'district');
define('ROLE_HQ', 'hq');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user from session
function getCurrentUser() {
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }
    return null;
}

// Simple redirect function
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        // Show login.php content directly
        include 'login.php';
        exit();
    }
}

// Switch user role (for demo)
if (isset($_GET['switch_role'])) {
    $role = $_GET['switch_role'];
    $users = Auth::getUsersByRole($role);
    
    if (!empty($users)) {
        $_SESSION['user_data'] = $users[0];
        header('Location: index.php');
        exit();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>