<?php
// views/kplb_hq/manage_incidents.php

// 1. ROBUST DATABASE CONNECTION
$rootPath = $_SERVER['DOCUMENT_ROOT'] . '/dvmd_system';
$dbPath = $rootPath . '/dbconnect.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    $conn = new mysqli("localhost", "root", "", "dvmd_db");
}

// 2. GET LOGGED-IN USER'S DISTRICT
$userDistrict = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $userQuery = $conn->prepare("SELECT district FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    
    if ($userRow = $userResult->fetch_assoc()) {
        $userDistrict = $userRow['district'];
    }
}

// 3. HANDLE UPDATES (DISPATCH)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['incident_id'])) {
    $id = intval($_POST['incident_id']);
    $status = $_POST['status'];
    $resources = $_POST['resources'];

    $stmt = $conn->prepare("UPDATE incidents SET status = ?, assigned_resources = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $resources, $id);

    if ($stmt->execute()) {
        $msg = "Incident dispatched successfully.";
        if (function_exists('logAction')) {
            logAction($_SESSION['user_id'] ?? 0, 'KPLB HQ', 'DISPATCH', "Updated Incident #$id");
        }
    }
}

// 4. BUILD FILTERS - Automatically filter by user's district
$filter_district = $userDistrict; // Default to user's district
$filter_severity = $_GET['severity'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Base Query
$sql = "SELECT i.*, u.district as district_name, u.name as reporter_name 
        FROM incidents i 
        LEFT JOIN users u ON i.reported_by = u.id 
        WHERE u.district = ?"; // Always filter by user's district

$params = [$userDistrict];
$paramTypes = "s";

// Add additional filters if provided
if ($filter_severity) {
    $sql .= " AND i.severity = ?";
    $params[] = $filter_severity;
    $paramTypes .= "s";
}

if ($filter_status) {
    $sql .= " AND i.status = ?";
    $params[] = $filter_status;
    $paramTypes .= "s";
}

$sql .= " ORDER BY i.created_at DESC";

// Prepare and execute query with parameters
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// 5. GET DISTRICT LIST FOR DROPDOWN (only show user's district)
$distResult = $conn->query("SELECT DISTINCT district FROM users WHERE district = '$userDistrict' ORDER BY district ASC");

$mapData = [];
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
/* ... (Same styles as before) ... */
.hq-controls {
    display: flex;
    gap: 15px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
    flex-wrap: wrap;
    align-items: end;
}

.filter-group label {
    display: block;
    font-weight: bold;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.filter-group select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-width: 150px;
}

.btn-filter {
    background: #2c3e50;
    color: white;
    padding: 8px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-reset {
    background: #95a5a6;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.9rem;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    color: white;
    font-size: 0.8rem;
    font-weight: bold;
}

.bg-pending {
    background: #e74c3c;
}

.bg-progress {
    background: #3498db;
}

.bg-resolved {
    background: #27ae60;
}

.district-badge {
    display: inline-block;
    background: #3498db;
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-left: 10px;
}
</style>

<div class="incidents-container" style="padding: 20px;">
    <div class="welcome-message">
        <div style="margin-bottom: 20px;">
            <h2>District Incident Command Center</h2>
            <p style="color: #666;">Monitor and manage incidents in
                (<strong><?php echo htmlspecialchars($userDistrict); ?></strong>).</p>
            <?php if (isset($msg))
            echo "<div style='background:#d4edda;color:#155724;padding:10px;border-radius:5px;'>$msg</div>"; ?>
        </div>
    </div>

    <form method="GET" class="hq-controls">
        <input type="hidden" name="page" value="manage_incidents">

        <div class="filter-group">
            <label>Severity</label>
            <select name="severity">
                <option value="">All Levels</option>
                <option value="Critical" <?php if ($filter_severity == 'Critical') echo 'selected'; ?>>Critical</option>
                <option value="High" <?php if ($filter_severity == 'High') echo 'selected'; ?>>High</option>
                <option value="Medium" <?php if ($filter_severity == 'Medium') echo 'selected'; ?>>Medium</option>
                <option value="Low" <?php if ($filter_severity == 'Low') echo 'selected'; ?>>Low</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All Statuses</option>
                <option value="Pending" <?php if ($filter_status == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="In Progress" <?php if ($filter_status == 'In Progress') echo 'selected'; ?>>In Progress
                </option>
                <option value="Resolved" <?php if ($filter_status == 'Resolved') echo 'selected'; ?>>Resolved</option>
            </select>
        </div>

        <button type="submit" class="btn-filter">Apply Filters</button>
        <a href="?page=manage_incidents" class="btn-reset">Reset Filters</a>
    </form>

    <div id="nationalMap"
        style="height: 400px; width: 100%; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 1;">
    </div>

    <div style="background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="background: #f8f9fa; padding: 15px; border-bottom: 1px solid #eee;">
            <strong>Showing incidents for: <?php echo htmlspecialchars($userDistrict); ?> District</strong>
            <span style="float: right; color: #666;">
                Total: <?php echo $result->num_rows; ?> incident(s)
            </span>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #2c3e50; color: white;">
                <tr>
                    <th style="padding: 15px; text-align: left;">Date</th>
                    <th style="padding: 15px; text-align: left;">Location</th>
                    <th style="padding: 15px; text-align: left;">Type</th>
                    <th style="padding: 15px; text-align: left;">Severity</th>
                    <th style="padding: 15px; text-align: left;">Assigned Resources</th>
                    <th style="padding: 15px; text-align: center;">Status</th>
                    <th style="padding: 15px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                        // Map Data
                        $mapData[] = [
                            'lat' => $row['lat'],
                            'lng' => $row['lng'],
                            'type' => $row['incident_type'],
                            'desc' => $row['description'],
                            'severity' => $row['severity']
                        ];

                        //color code the severity
                        $sevColor = match ($row['severity']) {
                            'Critical' => '#e74c3c', // Red
                            'High' => '#e67e22', // Orange
                            'Medium' => '#f1c40f', // Yellow
                            'Low' => '#27ae60', // Green
                            default => '#27ae60'
                        };
                        $statClass = match ($row['status']) { 'Pending' => 'bg-pending', 'In Progress' => 'bg-progress', 'Resolved' => 'bg-resolved', default => 'bg-pending'};
                        ?>
                <tr style="border-bottom: 1px solid #eee; font-size: 14px;">
                    <td style="padding: 15px; color: #666; font-size: 0.9rem;">
                        <?php echo date('d M Y', strtotime($row['created_at'])); ?><br>
                        <?php echo date('h:i A', strtotime($row['created_at'])); ?>
                    </td>
                    <td style="padding: 15px; font-size: 14px;">
                        <?php echo htmlspecialchars($row['address'] ?? 'Unknown Location'); ?><br>
                    </td>
                    <td style="padding: 15px;"><?php echo htmlspecialchars($row['incident_type']); ?></td>
                    <td style="padding: 15px;">
                        <span style="color: <?php echo $sevColor; ?>; font-weight: bold;">●
                            <?php echo $row['severity']; ?></span>
                    </td>
                    <td style="padding: 15px; font-size: 14px; color: #444;">
                        <?php if (!empty($row['assigned_resources'])): ?>
                        <i class="fas fa-truck-medical" style="color: #3498db;"></i>
                        <?php echo htmlspecialchars($row['assigned_resources']); ?>
                        <?php else: ?>
                        <span style="color: #ccc;">- No resources -</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px; font-size: 14px; text-align: center;">
                        <span class="status-badge <?php echo $statClass; ?>"><?php echo $row['status']; ?></span>
                    </td>
                    <td style="padding: 15px; font-size: 14px; text-align: center;">
                        <button onclick='openDispatch(<?php echo json_encode($row); ?>)'
                            style="background: #2c3e50; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-tasks"></i> Dispatch
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" style="padding: 20px; text-align: center;">
                        No incidents found in <?php echo htmlspecialchars($userDistrict); ?> district.
                        <?php if ($filter_severity || $filter_status): ?>
                        <br><small>Try adjusting your filters.</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="dispatchModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div
        style="background: white; padding: 30px; border-radius: 10px; width: 500px; max-width: 90%; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 15px;">Dispatch Resources</h3>
        <form method="POST">
            <input type="hidden" name="incident_id" id="modalId">
            <p id="modalDesc" style="background: #f8f9fa; padding: 10px; border-radius: 5px; color: #555;"></p>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Update Status:</label>
                <select name="status" id="modalStatus"
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Resolved">Resolved</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Assign Resources:</label>
                <textarea name="resources" id="modalResources" rows="4"
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></textarea>
            </div>

            <div style="text-align: right;">
                <button type="button" onclick="document.getElementById('dispatchModal').style.display='none'"
                    style="padding: 10px 20px; background: #eee; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">Cancel</button>
                <button type="submit"
                    style="padding: 10px 20px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer;">Update
                    & Dispatch</button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// 1. Initialize Map
var map = L.map('nationalMap').setView([4.2105, 101.9758], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// 2. Add Pins & Collect them for Auto-Zoom
var incidents = <?php echo json_encode($mapData); ?>;
var markers = [];

incidents.forEach(inc => {
    // 4-COLOR LOGIC
    let color = 'green'; // Default (Low)
    if (inc.severity === 'Medium') color = 'gold'; // Yellow/Gold
    if (inc.severity === 'High') color = 'orange'; // Orange
    if (inc.severity === 'Critical') color = 'red'; // Red

    // Create the marker
    var marker = L.circleMarker([inc.lat, inc.lng], {
            color: color,
            fillColor: color,
            fillOpacity: 0.8,
            radius: 10
        })
        .addTo(map)
        .bindPopup(`<b>${inc.type}</b><br>${inc.desc}<br>Severity: ${inc.severity}`);

    markers.push(marker);
});

// 3. Smart Zoom
if (markers.length > 0) {
    var group = L.featureGroup(markers);
    map.fitBounds(group.getBounds(), {
        padding: [50, 50]
    });
}

// 4. ADD LEGEND
var legend = L.control({
    position: 'bottomright'
});

legend.onAdd = function(map) {
    var div = L.DomUtil.create('div', 'info legend');
    div.style.background = "white";
    div.style.padding = "10px";
    div.style.borderRadius = "5px";
    div.style.boxShadow = "0 0 15px rgba(0,0,0,0.2)";
    div.style.fontSize = "12px";

    // Add the color items
    div.innerHTML +=
        '<div style="margin-bottom: 5px;"><i style="background:red; width:10px; height:10px; display:inline-block; border-radius:50%; margin-right:5px;"></i> <b>Critical</b></div>';
    div.innerHTML +=
        '<div style="margin-bottom: 5px;"><i style="background:orange; width:10px; height:10px; display:inline-block; border-radius:50%; margin-right:5px;"></i> <b>High</b></div>';
    div.innerHTML +=
        '<div style="margin-bottom: 5px;"><i style="background:gold; width:10px; height:10px; display:inline-block; border-radius:50%; margin-right:5px;"></i> <b>Medium</b></div>';
    div.innerHTML +=
        '<div><i style="background:green; width:10px; height:10px; display:inline-block; border-radius:50%; margin-right:5px;"></i> <b>Low</b></div>';

    return div;
};
legend.addTo(map);

// 5. Modal Function
function openDispatch(data) {
    document.getElementById('dispatchModal').style.display = 'flex';
    document.getElementById('modalId').value = data.id;
    document.getElementById('modalStatus').value = data.status;
    document.getElementById('modalResources').value = data.assigned_resources || '';
    document.getElementById('modalDesc').innerText =
        `Incident: ${data.incident_type} @ ${data.district_name || 'Unknown'}`;
}
</script>