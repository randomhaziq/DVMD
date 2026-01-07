<?php
// api/send_sos.php
session_start();
header('Content-Type: application/json');

// --------------------------------------
// 1. Authentication
// --------------------------------------
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Not authenticated'
    ]);
    exit();
}

// --------------------------------------
// 2. Read JSON payload
// --------------------------------------
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request payload'
    ]);
    exit();
}

// --------------------------------------
// 3. Validate required fields
// --------------------------------------
if (empty($input['type'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Emergency type required'
    ]);
    exit();
}

/**
 * 🔒 STRICT RULE:
 * Location MUST come from live browser detection.
 * If geolocation timed out, this field will be empty.
 */
if (empty($input['location']) || $input['location'] === 'Location request timed out') {
    echo json_encode([
        'success' => false,
        'error' => 'Live location not available. Please enable location services.'
    ]);
    exit();
}

// --------------------------------------
// 4. Sanitize inputs
// --------------------------------------
$user_id = (int) $_SESSION['user_id'];

$sos_id = 'SOS-' . date('YmdHis') . rand(100, 999);

$type = htmlspecialchars(trim($input['type']));
$info = isset($input['additional_info'])
    ? htmlspecialchars(trim($input['additional_info']))
    : '';

$location = htmlspecialchars(trim($input['location']));

// --------------------------------------
// 5. Database connection
// --------------------------------------
require_once __DIR__ . '/dbconnect.php';

if (!$conn) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]);
    exit();
}

// --------------------------------------
// 6. Insert SOS alert
// --------------------------------------
$sql = "INSERT INTO sos_alerts 
        (user_id, sos_id, emergency_type, additional_info, location, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'error' => 'SQL preparation failed'
    ]);
    exit();
}

$stmt->bind_param(
    "issss",
    $user_id,
    $sos_id,
    $type,
    $info,
    $location
);

// --------------------------------------
// 7. Execute
// --------------------------------------
if ($stmt->execute()) {
    echo json_encode([
        'success'   => true,
        'sos_id'    => $sos_id,
        'alert_id'  => $stmt->insert_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $stmt->error
    ]);
}

// --------------------------------------
// 8. Cleanup
// --------------------------------------
$stmt->close();
$conn->close();
?>
