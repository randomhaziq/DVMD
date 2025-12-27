<?php
// test_db.php - Test database connection and table
session_start();
require_once 'dbconnect.php';

// Test data
$test_user_id = 1; // Change to an existing user_id in your users table
$test_sos_id = 'SOS-TEST-' . time();
$test_type = 'medical';
$test_location = 'Test Location';
$test_info = 'This is a test SOS alert';

echo "<h2>Testing SOS Alert Database</h2>";

try {
    // Test 1: Check connection
    echo "<p>✓ Database connection successful</p>";
    
    // Test 2: Insert test record
    $sql = "INSERT INTO sos_alerts 
            (user_id, sos_id, emergency_type, additional_info, location) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $test_user_id, $test_sos_id, $test_type, $test_info, $test_location);
    
    if ($stmt->execute()) {
        $alert_id = $stmt->insert_id;
        echo "<p>✓ Test alert saved successfully! Alert ID: {$alert_id}</p>";
        
        // Test 3: Retrieve the test record
        $sql = "SELECT * FROM sos_alerts WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $alert_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "<p>✓ Test alert retrieved successfully:</p>";
            echo "<pre>" . print_r($row, true) . "</pre>";
        }
        
        // Clean up: Delete test record
        $sql = "DELETE FROM sos_alerts WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $alert_id);
        $stmt->execute();
        echo "<p>✓ Test alert cleaned up</p>";
        
    } else {
        echo "<p>✗ Failed to save test alert: " . $stmt->error . "</p>";
    }
    
    // Test 4: Show existing alerts count
    $sql = "SELECT COUNT(*) as total FROM sos_alerts";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        echo "<p>✓ Total SOS alerts in database: " . $row['total'] . "</p>";
    }
    
    // Test 5: Show table structure
    echo "<h3>Table Structure:</h3>";
    $sql = "DESCRIBE sos_alerts";
    $result = $conn->query($sql);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>