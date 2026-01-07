<?php
// views/kplb_hq/dashboard.php

// 1. Connection & Session
require_once __DIR__ . '/../../api/dbconnect.php';
$user = $_SESSION['user_data'] ?? ['name' => 'HQ Officer', 'email' => 'admin@kplb.gov.my', 'role' => 'KPLB HQ'];

// ---------------------------------------------------------
// DATA FETCHING LOGIC
// ---------------------------------------------------------

// A. Map Data (JOIN to get district)
$locations_query = "SELECT i.lat, i.lng, i.incident_type, i.description, i.severity, i.status, u.district 
                    FROM incidents i 
                    LEFT JOIN users u ON i.reported_by = u.id 
                    WHERE i.lat IS NOT NULL AND i.lng IS NOT NULL";
$locations_result = $conn->query($locations_query);
$mapData = [];
if ($locations_result) {
    while ($row = $locations_result->fetch_assoc()) {
        $mapData[] = $row;
    }
}

// B. Analytics: Incident Stats
$stats_query = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'In Progress' THEN 1 END) as active,
    COUNT(CASE WHEN status = 'Resolved' THEN 1 END) as resolved
    FROM incidents";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// C. Analytics: SOS Data
$sos_query = "SELECT COUNT(*) as total FROM sos_alerts";
$total_sos = $conn->query($sos_query)->fetch_assoc()['total'] ?? 0;

// SOS Breakdown by Type
$sosTypeRes = $conn->query("SELECT emergency_type, COUNT(*) as count FROM sos_alerts GROUP BY emergency_type");

// SOS Breakdown by Village
$sosVilRes = $conn->query("SELECT u.village, COUNT(*) as count FROM sos_alerts s JOIN users u ON s.user_id = u.id GROUP BY u.village LIMIT 5");

// D. Analytics: Type & Severity Tables
$typeRes = $conn->query("SELECT incident_type, COUNT(*) as count FROM incidents GROUP BY incident_type");
$sevRes = $conn->query("SELECT severity, COUNT(*) as count FROM incidents GROUP BY severity");
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
/* DASHBOARD STYLES (MATCHING CITIZEN PALETTE) */
:root {
    /* Citizen Dashboard Colors */
    --primary: #1a5f7a;       /* Deep Blue */
    --secondary: #57c5b6;     /* Teal */
    --light-bg: #f5f5f5;      /* Light Gray */
    
    /* Status Colors */
    --red: #e74c3c;
    --orange: #f39c12;
    --blue: #3498db;
    --green: #2ecc71;
}

/* 1. WELCOME BANNER (Updated to match Citizen Theme) */
.welcome-section {
    /* Using the exact Primary and Secondary colors from Citizen Dashboard */
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 25px 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.welcome-section h2 { margin: 0; font-size: 1.8rem; font-weight: 600; }
.welcome-section p { margin: 5px 0 0; opacity: 0.9; }

/* 2. MAP SECTION */
.map-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 40px;
}
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.section-title { font-size: 1.2rem; font-weight: bold; color: #333; }
#hqMap { height: 450px; width: 100%; border-radius: 8px; z-index: 1; }

/* 3. ANALYTICS GRIDS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: white; padding: 20px; border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center;
    border-bottom: 4px solid #ccc;
    transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-3px); }

.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 12px; background: #f8f9fa; color: var(--primary); font-weight: 600; }
.data-table td { padding: 12px; border-bottom: 1px solid #eee; }

/* 4. SOS SECTION */
.sos-container {
    background-color: #ffebee;
    border: 1px solid #ffcdd2;
    border-radius: 10px;
    padding: 25px;
}
.sos-header-card {
    width: 100%; max-width: 400px; margin: 0 auto 30px;
    background: white; padding: 15px; border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #d32f2f;
    text-align: center;
}
.sos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.sos-card-header { background: #ef5350; color: white; padding: 10px 15px; font-weight: bold; }

/* Responsive */
@media (max-width: 1000px) { .stats-grid, .sos-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 600px) { .stats-grid, .sos-grid { grid-template-columns: 1fr; } }
</style>

<div class="dashboard-container" style="padding: 20px;">

    <div class="welcome-section">
        <h2>Welcome Back, <?php echo htmlspecialchars($user['name']); ?>!</h2>
        <p>You are logged in as <strong><?php echo htmlspecialchars($user['role']); ?></strong></p>
        <p style="font-size: 0.9rem; margin-top: 5px;"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
    </div>

    <div class="map-section">
        <div class="section-header">
            <h3 class="section-title"><i class="fas fa-map-marked-alt"></i> GIS Map Visualization</h3>
            <span style="font-size: 0.9rem; color: #666;">Live Severity Tracking</span>
        </div>
        
        <div id="hqMap"></div>

        <div style="display: flex; gap: 20px; margin-top: 15px; justify-content: center; background: #f9f9f9; padding: 10px; border-radius: 5px;">
            <div style="display:flex; align-items:center; gap:5px;"><span style="width:12px; height:12px; background:red; border-radius:50%;"></span> Critical</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="width:12px; height:12px; background:orange; border-radius:50%;"></span> High</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="width:12px; height:12px; background:gold; border-radius:50%;"></span> Medium</div>
            <div style="display:flex; align-items:center; gap:5px;"><span style="width:12px; height:12px; background:green; border-radius:50%;"></span> Low</div>
        </div>
    </div>

    <h3 style="margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; color: var(--primary);">
        <i class="fas fa-chart-pie"></i> Reports & Analytics
    </h3>

    <div class="stats-grid">
        <div class="stat-card" style="border-color: #2c3e50;">
            <h3 style="font-size: 2.5rem; color: #2c3e50; margin: 0;"><?php echo $stats['total']; ?></h3>
            <span style="color: #666; font-weight: bold;">Total Incidents</span>
        </div>
        <div class="stat-card" style="border-color: #f1c40f;">
            <h3 style="font-size: 2.5rem; color: #f1c40f; margin: 0;"><?php echo $stats['pending']; ?></h3>
            <span style="color: #666;">Pending</span>
        </div>
        <div class="stat-card" style="border-color: #3498db;">
            <h3 style="font-size: 2.5rem; color: #3498db; margin: 0;"><?php echo $stats['active']; ?></h3>
            <span style="color: #666;">In Progress</span>
        </div>
        <div class="stat-card" style="border-color: #2ecc71;">
            <h3 style="font-size: 2.5rem; color: #2ecc71; margin: 0;"><?php echo $stats['resolved']; ?></h3>
            <span style="color: #666;">Resolved</span>
        </div>
    </div>

    <div class="tables-grid">
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h4 style="margin-top: 0; color: var(--primary);">Incidents by Category</h4>
            <table class="data-table">
                <?php $typeRes->data_seek(0); while($r = $typeRes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $r['incident_type']; ?></td>
                    <td><strong><?php echo $r['count']; ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h4 style="margin-top: 0; color: var(--primary);">Severity Analysis</h4>
            <table class="data-table">
                <?php $sevRes->data_seek(0); while($r = $sevRes->fetch_assoc()): 
                    $color = match($r['severity']) { 'Critical'=>'red', 'High'=>'orange', 'Medium'=>'gold', default=>'green' };
                ?>
                <tr>
                    <td><span style="color:<?php echo $color; ?>">●</span> <?php echo $r['severity']; ?></td>
                    <td><strong><?php echo $r['count']; ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <div class="sos-container">
        <div class="sos-header-card">
            <h2 style="margin: 0; font-size: 3rem; color: #c62828;"><?php echo $total_sos; ?></h2>
            <div style="color: #b71c1c; font-weight: bold; margin-top: 5px;">
                <i class="fas fa-tower-broadcast"></i> Total SOS Alerts
            </div>
        </div>

        <div class="sos-grid">
            <div style="background: white; border-radius: 8px; overflow: hidden;">
                <div class="sos-card-header"><i class="fas fa-notes-medical"></i> By Type</div>
                <table class="data-table">
                    <?php if($sosTypeRes->num_rows > 0): while($r = $sosTypeRes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $r['emergency_type']; ?></td>
                        <td align="right"><strong><?php echo $r['count']; ?></strong></td>
                    </tr>
                    <?php endwhile; else: echo "<tr><td colspan='2' align='center'>No data</td></tr>"; endif; ?>
                </table>
            </div>

            <div style="background: white; border-radius: 8px; overflow: hidden;">
                <div class="sos-card-header"><i class="fas fa-map-marker-alt"></i> By Village</div>
                <table class="data-table">
                    <?php if($sosVilRes->num_rows > 0): while($r = $sosVilRes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $r['village'] ?: 'Unknown'; ?></td>
                        <td align="right"><strong><?php echo $r['count']; ?></strong></td>
                    </tr>
                    <?php endwhile; else: echo "<tr><td colspan='2' align='center'>No data</td></tr>"; endif; ?>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Initialize Map
    var map = L.map('hqMap').setView([4.2105, 101.9758], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    // Add Pins
    var data = <?php echo json_encode($mapData); ?>;
    var markers = [];

    data.forEach(inc => {
        let color = 'green';
        if (inc.severity === 'Medium') color = 'gold';
        if (inc.severity === 'High') color = 'orange';
        if (inc.severity === 'Critical') color = 'red';

        var district = inc.district ? inc.district : 'Unknown District';

        var marker = L.circleMarker([inc.lat, inc.lng], {
            color: color, fillColor: color, fillOpacity: 0.8, radius: 8
        }).addTo(map).bindPopup(`<b>${inc.incident_type}</b><br>${district}<br>Status: ${inc.status}`);
        
        markers.push(marker);
    });

    // Smart Zoom
    if (markers.length > 0) {
        var group = L.featureGroup(markers);
        map.fitBounds(group.getBounds(), { padding: [50, 50] });
    }
</script>