<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dvmd_db";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Optional: Set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

function logAction($user_id, $role, $action, $details)
{
    global $conn; // Uses your existing database connection

    // Safety check: If no connection, don't crash the site
    if (!$conn)
        return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, role, action_type, details, ip_address) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $role, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
?>