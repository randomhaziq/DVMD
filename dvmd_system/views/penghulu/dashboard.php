<?php
// views/penghulu/dashboard.php

// 1. Connection & Session
require_once __DIR__ . '/../../api/dbconnect.php';
$user = $_SESSION['user_data'] ?? null;

// Ensure user is logged in and has a village assigned
if (!$user || empty($user['village'])) {
    echo "<div style='padding:20px; color:red;'>Error: No village assigned to your account. Please contact HQ to update your profile.</div>";
    exit;
}

$my_village = $conn->real_escape_string($user['village']);

// ---------------------------------------------------------
// 2. ACTION HANDLER (VERIFY REPORTS)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Verify Incident
    if ($_POST['action'] === 'verify_incident') {
        $inc_id = intval($_POST['incident_id']);
        // Update status to 'Verified'
        $conn->query("UPDATE incidents SET status = 'Verified' WHERE id = $inc_id");
        $msg = "Incident #$inc_id verified successfully.";
        $msg_type = "success";
    }

    // Reject Incident
    if ($_POST['action'] === 'reject_incident') {
        $inc_id = intval($_POST['incident_id']);
        $conn->query("UPDATE incidents SET status = 'Rejected' WHERE id = $inc_id");
        $msg = "Incident #$inc_id marked as false report.";
        $msg_type = "warning";
    }

    // Post Village Broadcast (Using Incidents Table per your system)
    if ($_POST['action'] === 'village_broadcast') {
        $title = $conn->real_escape_string($_POST['title']);
        $message = $conn->real_escape_string($_POST['message']);
        $created_by = $user['id'];
        
        // We reuse incidents table for announcements
        // Address -> Title, Description -> Message, Type -> Announcement
        $sqlBroadcast = "INSERT INTO incidents (incident_type, address, description, severity, status, reported_by, lat, lng, created_at) 
                         VALUES ('Announcement', '$title', '$message', 'Low', 'Active', $created_by, 0, 0, NOW())";
        
        if ($conn->query($sqlBroadcast)) {
            $msg = "Announcement sent to residents.";
            $msg_type = "success";
        } else {
            $msg = "Error sending announcement.";
            $msg_type = "error";
        }
    }
}

// ---------------------------------------------------------
// 3. DATA FETCHING (SCOPED TO VILLAGE)
// ---------------------------------------------------------

// A. Stats: Active SOS in this village (FIXED LOGIC: Last 24 Hours)
$sqlSOS = "SELECT COUNT(*) as count FROM sos_alerts s 
           JOIN users u ON s.user_id = u.id 
           WHERE u.village = '$my_village' 
           AND TIMESTAMPDIFF(HOUR, s.created_at, NOW()) <= 24"; 
$statSOS = $conn->query($sqlSOS)->fetch_assoc()['count'];

// B. Stats: Active Incidents in this village
// Counts anything that is NOT Resolved or Rejected
$sqlInc = "SELECT COUNT(*) as count FROM incidents i 
           JOIN users u ON i.reported_by = u.id 
           WHERE u.village = '$my_village' 
           AND i.incident_type != 'Announcement'
           AND i.status NOT IN ('Resolved', 'Rejected')";
$statInc = $conn->query($sqlInc)->fetch_assoc()['count'];

// C. Verification Queue (Pending Incidents)
$verifyQ = "SELECT i.*, u.name as reporter_name, u.phone 
            FROM incidents i 
            JOIN users u ON i.reported_by = u.id 
            WHERE u.village = '$my_village' 
            AND i.status = 'Pending' 
            AND i.incident_type != 'Announcement'
            ORDER BY i.created_at DESC";
$resVerify = $conn->query($verifyQ);

// D. Map Data (All active incidents in village)
$mapQ = "SELECT i.lat, i.lng, i.incident_type, i.description, i.severity, i.status 
         FROM incidents i 
         JOIN users u ON i.reported_by = u.id 
         WHERE u.village = '$my_village' 
         AND i.lat IS NOT NULL 
         AND i.lng IS NOT NULL
         AND i.incident_type != 'Announcement'";
$resMap = $conn->query($mapQ);
$mapData = [];
if ($resMap) {
    while($row = $resMap->fetch_assoc()) {
        $mapData[] = $row;
    }
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
/* Using Citizen Palette for Consistency */
:root {
    --primary: #1a5f7a;
    --secondary: #57c5b6;
    --red: #e74c3c;
    --orange: #f39c12;
    --green: #2ecc71;
    --light: #f8f9fa;
}

/* Dashboard Grid Layout */
.penghulu-grid {
    display: grid;
    grid-template-columns: 2fr 1fr; /* Left (Map/Verify) is wider, Right (Stats/Broadcast) narrower */
    gap: 25px;
}

@media (max-width: 1000px) { .penghulu-grid { grid-template-columns: 1fr; } }

/* Cards */
.p-card {
    background: white; border-radius: 10px; padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px;
}
.p-card-header {
    border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;
    display: flex; justify-content: space-between; align-items: center;
}
.p-title { margin: 0; font-size: 1.1rem; color: var(--primary); font-weight: 600; }

/* Stats Row */
.mini-stat-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
.mini-stat {
    background: white; padding: 15px; border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center;
    border-top: 4px solid var(--secondary);
}
.mini-stat.alert { border-top-color: var(--red); background: #fff5f5; }
.ms-val { font-size: 2rem; font-weight: bold; color: #333; display: block; }
.ms-label { font-size: 0.85rem; color: #666; text-transform: uppercase; }

/* Verification Table */
.v-table { width: 100%; border-collapse: collapse; }
.v-table th { text-align: left; padding: 10px; background: #f1f1f1; color: #555; font-size: 0.85rem; }
.v-table td { padding: 12px 10px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
.btn-verify {
    background: var(--green); color: white; border: none; padding: 5px 10px;
    border-radius: 5px; cursor: pointer; font-size: 0.8rem;
}
.btn-reject {
    background: var(--red); color: white; border: none; padding: 5px 10px;
    border-radius: 5px; cursor: pointer; font-size: 0.8rem; margin-left: 5px;
}
</style>

<div style="padding: 20px;">

    <div style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(26, 95, 122, 0.2);">
        <h2 style="margin: 0;">Dashboard Ketua Kampung</h2>
        <p style="margin: 5px 0 0; opacity: 0.9;">
            <i class="fas fa-map-marker-alt"></i> Village: <strong><?php echo htmlspecialchars($my_village); ?></strong>
        </p>
    </div>

    <?php if (isset($msg)): ?>
        <div style="padding: 15px; background: <?php echo $msg_type=='success'?'#d4edda':'#f8d7da'; ?>; color: <?php echo $msg_type=='success'?'#155724':'#721c24'; ?>; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="penghulu-grid">
        
        <div>
            <div class="p-card">
                <div class="p-card-header">
                    <h3 class="p-title"><i class="fas fa-tasks"></i> Pending Verification</h3>
                    <span style="background: var(--orange); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">
                        <?php echo $resVerify->num_rows; ?> New
                    </span>
                </div>
                
                <?php if ($resVerify->num_rows > 0): ?>
                    <table class="v-table">
                        <thead>
                            <tr>
                                <th>Incident</th>
                                <th>Reported By</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $resVerify->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['incident_type']); ?></strong><br>
                                    <small style="color:#666;"><?php echo htmlspecialchars($row['description']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['reporter_name']); ?><br>
                                    <small><?php echo htmlspecialchars($row['phone']); ?></small>
                                </td>
                                <td><?php echo date('d M, H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="verify_incident">
                                        <input type="hidden" name="incident_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-verify" title="Verify & Approve"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="reject_incident">
                                        <input type="hidden" name="incident_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-reject" title="Reject as False"><i class="fas fa-times"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: #999;">
                        <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 10px;"></i><br>
                        All clear. No pending reports.
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-card">
                <div class="p-card-header">
                    <h3 class="p-title"><i class="fas fa-map"></i> Village Map Overview</h3>
                </div>
                <div id="villageMap" style="height: 350px; border-radius: 8px; background: #eee;"></div>
            </div>
        </div>

        <div>
            <div class="mini-stat-row">
                <div class="mini-stat alert">
                    <span class="ms-val"><?php echo $statSOS; ?></span>
                    <span class="ms-label">Active SOS (24h)</span>
                </div>
                <div class="mini-stat">
                    <span class="ms-val"><?php echo $statInc; ?></span>
                    <span class="ms-label">Incidents</span>
                </div>
            </div>

            <div class="p-card">
                <div class="p-card-header">
                    <h3 class="p-title"><i class="fas fa-house-user"></i> PPS Status</h3>
                </div>
                <div style="text-align: center; padding: 10px;">
                    <h4 style="margin:0; color:#333;">Dewan Orang Ramai <?php echo htmlspecialchars($my_village); ?></h4>
                    <div style="margin: 10px 0; height: 10px; background: #eee; border-radius: 5px; overflow: hidden;">
                        <div style="width: 30%; background: var(--secondary); height: 100%;"></div>
                    </div>
                    <small>Occupancy: <strong>15 / 50</strong> Pax</small>
                </div>
            </div>

            <div class="p-card">
                <div class="p-card-header">
                    <h3 class="p-title"><i class="fas fa-bullhorn"></i> Village Announcement</h3>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="village_broadcast">
                    <div style="margin-bottom: 10px;">
                        <input type="text" name="title" placeholder="Title (e.g. Flood Warning)" required
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <textarea name="message" placeholder="Message to residents..." rows="3" required
                                  style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                    </div>
                    <button type="submit" style="width: 100%; background: var(--primary); color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">
                        Send to Residents
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // 1. Initialize Map
    // Default to Malaysia, will auto-zoom to pins
    var map = L.map('villageMap').setView([4.2105, 101.9758], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    // 2. Add Pins
    var data = <?php echo json_encode($mapData); ?>;
    var markers = [];

    data.forEach(inc => {
        let color = 'blue';
        if (inc.status === 'Verified') color = 'green';
        if (inc.status === 'Pending') color = 'orange';
        
        var marker = L.circleMarker([inc.lat, inc.lng], {
            color: color, fillColor: color, fillOpacity: 0.7, radius: 10
        }).addTo(map).bindPopup(`<b>${inc.incident_type}</b><br>${inc.description}`);
        
        markers.push(marker);
    });

    // 3. Auto Zoom
    if (markers.length > 0) {
        var group = L.featureGroup(markers);
        map.fitBounds(group.getBounds(), { padding: [50, 50] });
    }
</script>