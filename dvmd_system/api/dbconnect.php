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
?>