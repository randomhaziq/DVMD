<?php
// Get user data from session
$user = $_SESSION['user_data'] ?? null;

// Direct database connection
require_once __DIR__ . '/../../api/dbconnect.php';


// Fetch SOS alerts
$sos_query = "SELECT 
    COUNT(*) as total_count,
    COUNT(CASE WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 24 THEN 1 END) as active_count,
    COUNT(CASE WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) > 24 THEN 1 END) as pending_count
    FROM sos_alerts";
$sos_result = mysqli_query($conn, $sos_query);
$sos_data = mysqli_fetch_assoc($sos_result);

// Fetch incidents
$incidents_query = "SELECT 
    incident_type,
    COUNT(*) as count
    FROM incidents 
    WHERE status IN ('active', 'ongoing', 'investigating', 'reported')
    GROUP BY incident_type";
$incidents_result = mysqli_query($conn, $incidents_query);
$incidents_by_type = [];
$total_incidents = 0;

while ($row = mysqli_fetch_assoc($incidents_result)) {
    $incidents_by_type[$row['incident_type']] = $row['count'];
    $total_incidents += $row['count'];
}

// Fetch recent incidents
$recent_query = "SELECT * FROM incidents WHERE status != 'resolved' ORDER BY created_at DESC LIMIT 4";
$recent_result = mysqli_query($conn, $recent_query);

// Fetch map locations
$locations_query = "SELECT lat, lng, incident_type, description FROM incidents WHERE lat IS NOT NULL AND lng IS NOT NULL";
$locations_result = mysqli_query($conn, $locations_query);
?>

<!-- JUST THE DASHBOARD CONTENT STARTS HERE -->

<div class="welcome-message">
    <h2>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</h2>
    <p>You are logged in as <?php echo htmlspecialchars($user['role'] ?? 'Role'); ?>.</p>
    <p>Email: <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
</div>

<!-- Dashboard Cards -->
<div class="dashboard-cards">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ACTIVE SOS ALERTS</h3>
            <div class="card-icon emergency">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="card-content">
            <h3><?php echo $sos_data['active_count'] ?? 0; ?></h3>
            <p>Active • <?php echo $sos_data['pending_count'] ?? 0; ?> Older</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">LIVE INCIDENTS</h3>
            <div class="card-icon warning">
                <i class="fas fa-fire"></i>
            </div>
        </div>
        <div class="card-content">
            <h3><?php echo $total_incidents; ?></h3>
            <p>
                <?php
                $type_labels = [
                    'fire' => 'Fire',
                    'flood' => 'Flood',
                    'landslide' => 'Landslide'
                ];
                
                $display_types = [];
                foreach ($incidents_by_type as $type => $count) {
                    $label = $type_labels[$type] ?? ucfirst($type);
                    $display_types[] = "$label: $count";
                }
                
                echo $display_types ? implode(' • ', $display_types) : 'No active incidents';
                ?>
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DISASTER RISK MONITOR</h3>
            <div class="card-icon info">
                <i class="fas fa-cloud-rain"></i>
            </div>
        </div>
        <div class="card-content">
            <h3>Moderate</h3>
            <p>Heavy Rain Warning</p>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <h3 class="section-title">GIS MAP VISUALIZATION</h3>
        <a href="gis.php" class="section-action">Full Screen <i class="fas fa-expand"></i></a>
    </div>
    <div class="map-container" style="position: relative;">
        <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
        <div class="map-loading" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 1000;">
            <div class="spinner"></div>
            <span>Loading map...</span>
        </div>
    </div>
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background-color: #e74c3c;"></div>
            <span>Active SOS</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #f39c12;"></div>
            <span>Fire Incident</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #3498db;"></div>
            <span>Flood Incident</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #9b59b6;"></div>
            <span>Landslide</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #2ecc71;"></div>
            <span>Other Incidents</span>
        </div>
    </div>
</div>

<!-- Recent Incidents Section (Full Width) -->
<div class="section">
    <div class="section-header">
        <h3 class="section-title">RECENT INCIDENTS</h3>
        <a href="incident-management.php" class="section-action">View All</a>
    </div>
    <ul class="incident-list">
        <li class="incident-item">
            <div class="incident-icon" style="background-color: #f39c12;">
                <i class="fas fa-fire"></i>
            </div>
            <div class="incident-details">
                <div class="incident-title">Fire at Kampung Merdeka</div>
                <div class="incident-meta">
                    <span class="incident-location"><i class="fas fa-map-marker-alt"></i> Kg. Merdeka</span>
                    <span><i class="far fa-clock"></i> 45 mins ago</span>
                </div>
            </div>
            <div class="incident-status status-dispatched">Team Dispatched</div>
        </li>
        <li class="incident-item">
            <div class="incident-icon" style="background-color: #3498db;">
                <i class="fas fa-water"></i>
            </div>
            <div class="incident-details">
                <div class="incident-title">Flood at Kampung Luna</div>
                <div class="incident-meta">
                    <span class="incident-location"><i class="fas fa-map-marker-alt"></i> Kg. Luna</span>
                    <span><i class="far fa-clock"></i> 2 hours ago</span>
                </div>
            </div>
            <div class="incident-status status-verified">Verifying</div>
        </li>
        <li class="incident-item">
            <div class="incident-icon" style="background-color: #9b59b6;">
                <i class="fas fa-mountain"></i>
            </div>
            <div class="incident-details">
                <div class="incident-title">Landslide at Kampung Kona</div>
                <div class="incident-meta">
                    <span class="incident-location"><i class="fas fa-map-marker-alt"></i> Kg. Kona</span>
                    <span><i class="far fa-clock"></i> 5 hours ago</span>
                </div>
            </div>
            <div class="incident-status status-dispatched">Team Dispatched</div>
        </li>
        <li class="incident-item">
            <div class="incident-icon" style="background-color: #3498db;">
                <i class="fas fa-water"></i>
            </div>
            <div class="incident-details">
                <div class="incident-title">Flood at Kampung Luna</div>
                <div class="incident-meta">
                    <span class="incident-location"><i class="fas fa-map-marker-alt"></i> Kg. Luna</span>
                    <span><i class="far fa-clock"></i> 1 day ago</span>
                </div>
            </div>
            <div class="incident-status status-verified">Verifying</div>
        </li>
    </ul>
</div>
<!-- END OF CONTENT - NO CLOSING HTML/BODY TAGS -->