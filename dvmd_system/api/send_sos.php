<?php
// api/send_sos.php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate
if (empty($input['type'])) {
    echo json_encode(['success' => false, 'error' => 'Emergency type required']);
    exit();
}

// Include dbconnect.php from SAME folder
require_once __DIR__ . '/dbconnect.php';  // This is the key!

// Now use the connection...
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_data']['name'] ?? 'Unknown';

// Generate SOS ID
$sos_id = 'SOS-' . date('YmdHis') . rand(100, 999);

// Prepare SQL
$sql = "INSERT INTO sos_alerts (user_id, sos_id, emergency_type, additional_info, location) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Sanitize inputs
    $type = htmlspecialchars($input['type']);
    $info = isset($input['additional_info']) ? htmlspecialchars($input['additional_info']) : '';
    $location = isset($input['location']) ? htmlspecialchars($input['location']) : 'Location unknown';
    
    $stmt->bind_param("issss", $user_id, $sos_id, $type, $info, $location);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'sos_id' => $sos_id,
            'message' => 'SOS alert saved successfully',
            'alert_id' => $stmt->insert_id,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Database preparation failed: ' . $conn->error
    ]);
}

$conn->close();
?>