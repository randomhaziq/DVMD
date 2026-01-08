<?php
// Get user data from session
$user = $_SESSION['user_data'] ?? null;
$user_id = $user['id'] ?? 0;

// Direct database connection
require_once __DIR__ . '/../../api/dbconnect.php';

// ---------------------------------------------------------
// 1. FETCH BROADCAST MESSAGES (NEW)
// ---------------------------------------------------------
$notices = [];
// Select latest 3 broadcasts. You can add "WHERE target_audience = 'All'" if needed later.
$notice_sql = "SELECT * FROM broadcasts ORDER BY created_at DESC LIMIT 3";
$notice_result = mysqli_query($conn, $notice_sql);

if ($notice_result) {
    while ($row = mysqli_fetch_assoc($notice_result)) {
        $notices[] = $row;
    }
}

// ---------------------------------------------------------
// 2. EXISTING QUERIES
// ---------------------------------------------------------

// Fetch SOS alerts FOR THIS SPECIFIC USER ONLY
$sos_query = "SELECT 
    COUNT(*) as total_count,
    COUNT(CASE WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 24 THEN 1 END) as active_count,
    COUNT(CASE WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) > 24 THEN 1 END) as pending_count
    FROM sos_alerts 
    WHERE user_id = " . intval($user_id);
$sos_result = mysqli_query($conn, $sos_query);
$sos_data = mysqli_fetch_assoc($sos_result);

// Fetch incidents FOR THIS SPECIFIC USER ONLY
$incidents_query = "SELECT 
    incident_type,
    COUNT(*) as count
    FROM incidents 
    WHERE reported_by = " . intval($user_id) . "
    GROUP BY incident_type";
$incidents_result = mysqli_query($conn, $incidents_query);
$incidents_by_type = [];
$total_incidents = 0;

while ($row = mysqli_fetch_assoc($incidents_result)) {
    $incidents_by_type[$row['incident_type']] = $row['count'];
    $total_incidents += $row['count'];
}

// Get incident counts by status FOR THIS USER ONLY
$status_query = "SELECT 
    COUNT(CASE WHEN severity = 'Critical' THEN 1 END) as critical_count,
    COUNT(CASE WHEN severity = 'High' THEN 1 END) as high_count,
    COUNT(CASE WHEN severity = 'Medium' THEN 1 END) as medium_count,
    COUNT(CASE WHEN severity = 'Low' THEN 1 END) as low_count
    FROM incidents 
    WHERE reported_by = " . intval($user_id);
$status_result = mysqli_query($conn, $status_query);
$status_data = mysqli_fetch_assoc($status_result);

// Fetch recent incidents FOR THIS USER ONLY
$recent_incidents = [];
$recent_query = "SELECT * FROM incidents 
                 WHERE reported_by = " . intval($user_id) . "
                 ORDER BY created_at DESC 
                 LIMIT 4";
$recent_result = mysqli_query($conn, $recent_query);
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent_incidents[] = $row;
}

// Fetch ALL map locations (for the public map - not user-specific)
$locations_query = "SELECT lat, lng, incident_type, description, severity, created_at, address 
                    FROM incidents 
                    WHERE lat IS NOT NULL AND lng IS NOT NULL 
                    ORDER BY created_at DESC";
$locations_result = mysqli_query($conn, $locations_query);

// Collect map data in PHP array for JavaScript
$mapData = [];
while ($row = mysqli_fetch_assoc($locations_result)) {
    $mapData[] = [
        'lat' => $row['lat'],
        'lng' => $row['lng'],
        'type' => $row['incident_type'],
        'desc' => $row['description'],
        'severity' => $row['severity'],
        'address' => $row['address'],
        'created_at' => $row['created_at']
    ];
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
/* Citizen Dashboard Specific Styles */
:root {
    --primary-blue: #1a5f7a;
    --secondary-teal: #57c5b6;
    --emergency-red: #e74c3c;
    --warning-orange: #f39c12;
    --info-blue: #3498db;
    --success-green: #2ecc71;
    --light-gray: #f8f9fa;
    --text-dark: #333;
    --text-light: #666;
}

/* Welcome Section */
.welcome-section {
    background: linear-gradient(135deg, var(--primary-blue), #2c82c9);
    color: white;
    padding: 25px 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(26, 95, 122, 0.2);
}

.welcome-section h2 {
    font-size: 1.8rem;
    margin-bottom: 8px;
    font-weight: 600;
}

.welcome-section p {
    opacity: 0.9;
    margin-bottom: 5px;
    font-size: 0.95rem;
}

/* Dashboard Cards Grid - 3 cards only */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
    margin-bottom: 40px;
}

.quick-stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border-top: 5px solid;
    position: relative;
    overflow: hidden;
}

.quick-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.quick-stat-card.emergency {
    border-top-color: var(--emergency-red);
}

.quick-stat-card.info {
    border-top-color: var(--info-blue);
}

.quick-stat-card.teal {
    border-top-color: var(--secondary-teal);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.card-title {
    font-size: 0.9rem;
    color: var(--text-light);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.4rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.emergency .card-icon {
    background: linear-gradient(135deg, var(--emergency-red), #ff7979);
}

.info .card-icon {
    background: linear-gradient(135deg, var(--info-blue), #5dade2);
}

.teal .card-icon {
    background: linear-gradient(135deg, var(--secondary-teal), #78e0dc);
}

.card-value {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--text-dark);
    line-height: 1;
}

.card-subtitle {
    font-size: 0.95rem;
    color: var(--text-light);
    margin-bottom: 15px;
}

.card-details {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 15px;
}

.detail-item {
    background: var(--light-gray);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Severity Badges */
.severity-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.severity-critical {
    background: rgba(231, 76, 60, 0.15);
    color: var(--emergency-red);
    border: 1px solid rgba(231, 76, 60, 0.3);
}

.severity-high {
    background: rgba(243, 156, 18, 0.15);
    color: var(--warning-orange);
    border: 1px solid rgba(243, 156, 18, 0.3);
}

.severity-medium {
    background: rgba(52, 152, 219, 0.15);
    color: var(--info-blue);
    border: 1px solid rgba(52, 152, 219, 0.3);
}

.severity-low {
    background: rgba(46, 204, 113, 0.15);
    color: var(--success-green);
    border: 1px solid rgba(46, 204, 113, 0.3);
}

/* Incident Type Tags */
.type-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    margin-right: 8px;
    margin-bottom: 8px;
}

.type-fire {
    background: rgba(243, 156, 18, 0.15);
    color: var(--warning-orange);
}

.type-flood {
    background: rgba(52, 152, 219, 0.15);
    color: var(--info-blue);
}

.type-landslide {
    background: rgba(155, 89, 182, 0.15);
    color: #9b59b6;
}

.type-accident {
    background: rgba(231, 76, 60, 0.15);
    color: var(--emergency-red);
}

.type-others {
    background: rgba(46, 204, 113, 0.15);
    color: var(--success-green);
}

/* Map Section */
.map-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--light-gray);
}

.section-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--primary-blue);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--secondary-teal);
}

.section-action {
    color: var(--secondary-teal);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.section-action:hover {
    color: var(--primary-blue);
    gap: 10px;
}

/* Map Container */
#citizenMap {
    height: 400px;
    width: 100%;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    background: var(--light-gray);
    overflow: hidden !important;
    position: relative;
    z-index: 0;
}

/* --- NEW: NOTICES SECTION STYLE --- */
.notices-section {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    border-left: 5px solid var(--warning-orange);
}

.notice-item {
    padding: 15px;
    background: #fff8e1; /* Light yellow bg for alerts */
    border-radius: 8px;
    margin-bottom: 10px;
    border: 1px solid #ffe0b2;
}

.notice-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}

.notice-title {
    font-weight: 700;
    color: #d35400;
    font-size: 1rem;
}

.notice-date {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.notice-body {
    color: #333;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Recent Incidents */
.recent-incidents {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
}

.incident-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.incident-item {
    display: flex;
    align-items: center;
    padding: 18px;
    border-bottom: 1px solid var(--light-gray);
    transition: all 0.3s ease;
    border-radius: 10px;
    margin-bottom: 8px;
}

.incident-item:hover {
    background: var(--light-gray);
    transform: translateX(5px);
}

.incident-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 20px;
    flex-shrink: 0;
    font-size: 1.2rem;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
}

.incident-details {
    flex: 1;
    min-width: 0;
}

.incident-title {
    font-weight: 600;
    margin-bottom: 8px;
    color: var(--text-dark);
    font-size: 1.05rem;
}

.incident-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    font-size: 0.85rem;
    color: var(--text-light);
}

.incident-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.incident-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    margin-left: 15px;
}

.status-pending {
    background: rgba(243, 156, 18, 0.15);
    color: var(--warning-orange);
    border: 1px solid rgba(243, 156, 18, 0.3);
}

.status-in-progress {
    background: rgba(52, 152, 219, 0.15);
    color: var(--info-blue);
    border: 1px solid rgba(52, 152, 219, 0.3);
}

.status-resolved {
    background: rgba(46, 204, 113, 0.15);
    color: var(--success-green);
    border: 1px solid rgba(46, 204, 113, 0.3);
}

.status-verified {
    background: rgba(155, 89, 182, 0.15);
    color: #9b59b6;
    border: 1px solid rgba(155, 89, 182, 0.3);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    #citizenMap {
        height: 350px;
    }
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .welcome-section {
        padding: 20px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .section-action {
        align-self: flex-start;
    }
    
    #citizenMap {
        height: 300px;
    }
    
    .incident-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .incident-icon {
        margin-right: 0;
    }
    
    .incident-status {
        margin-left: 0;
        align-self: flex-start;
    }
}

@media (max-width: 480px) {
    #citizenMap {
        height: 250px;
    }
    
    .card-value {
        font-size: 2.2rem;
    }
    
    .card-icon {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
    }
}
</style>

<div class="welcome-section">
    <h2>Welcome back, <?php echo htmlspecialchars($user['name'] ?? 'Citizen'); ?>!</h2>
    <p>You are logged in as <strong>Citizen</strong> | Email: <?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></p>
    <p style="margin-top: 10px; font-size: 0.9rem; opacity: 0.8;">
        <i class="fas fa-info-circle"></i> Your personal emergency dashboard
    </p>
</div>

<div class="dashboard-grid">
    <div class="quick-stat-card emergency">
        <div class="card-header">
            <h3 class="card-title">YOUR SOS ALERTS</h3>
            <div class="card-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="card-value"><?php echo $sos_data['active_count'] ?? 0; ?></div>
        <div class="card-subtitle">Your active emergency signals</div>
        <div class="card-details">
            <?php if (($sos_data['total_count'] ?? 0) > 0): ?>
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span><?php echo $sos_data['pending_count'] ?? 0; ?> older alerts</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-list"></i>
                    <span><?php echo $sos_data['total_count'] ?? 0; ?> total sent</span>
                </div>
            <?php else: ?>
                <div class="detail-item" style="background: rgba(46, 204, 113, 0.15); color: var(--success-green);">
                    <i class="fas fa-check-circle"></i>
                    <span>No SOS alerts sent yet</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="quick-stat-card info">
        <div class="card-header">
            <h3 class="card-title">YOUR INCIDENT REPORTS</h3>
            <div class="card-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
        <div class="card-value"><?php echo $total_incidents; ?></div>
        <div class="card-subtitle">Total incidents reported by you</div>
        <div class="card-details">
            <?php if ($total_incidents > 0): ?>
                <?php foreach ($incidents_by_type as $type => $count): ?>
                    <span class="type-tag type-<?php echo strtolower($type); ?>">
                        <i class="fas fa-<?php 
                            echo $type == 'Fire' ? 'fire' : 
                                 ($type == 'Flood' ? 'water' : 
                                 ($type == 'Landslide' ? 'mountain' : 
                                 ($type == 'Accident' ? 'car-crash' : 'exclamation-circle'))); 
                        ?>"></i>
                        <?php echo htmlspecialchars($type); ?>: <?php echo $count; ?>
                    </span>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="detail-item" style="background: rgba(52, 152, 219, 0.15); color: var(--info-blue);">
                    <i class="fas fa-info-circle"></i>
                    <span>No incidents reported yet</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="quick-stat-card teal">
        <div class="card-header">
            <h3 class="card-title">WEATHER & RISK</h3>
            <div class="card-icon">
                <i class="fas fa-cloud-sun"></i>
            </div>
        </div>
        <div class="card-value">Moderate</div>
        <div class="card-subtitle">Current area risk level</div>
        <div class="card-details">
            <div class="detail-item">
                <i class="fas fa-thermometer-half"></i>
                <span>Temp: 28°C</span>
            </div>
            <div class="detail-item">
                <i class="fas fa-cloud-rain"></i>
                <span>Rain: 60%</span>
            </div>
            <div class="detail-item">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Flood Risk: Medium</span>
            </div>
        </div>
    </div>
</div>

<div class="map-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-map-marked-alt"></i> PUBLIC INCIDENT MAP
        </h3>
        <a href="gis.php" class="section-action">
            Full Screen <i class="fas fa-expand"></i>
        </a>
    </div>
    
    <div id="citizenMap"></div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #666;">
                <div style="width: 12px; height: 12px; background-color: #e74c3c; border-radius: 50%;"></div>
                <span>Critical</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #666;">
                <div style="width: 12px; height: 12px; background-color: #f39c12; border-radius: 50%;"></div>
                <span>High</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #666;">
                <div style="width: 12px; height: 12px; background-color: #3498db; border-radius: 50%;"></div>
                <span>Medium</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #666;">
                <div style="width: 12px; height: 12px; background-color: #2ecc71; border-radius: 50%;"></div>
                <span>Low</span>
            </div>
        </div>
        <div style="font-size: 0.85rem; color: #666;">
            <i class="fas fa-info-circle"></i> Shows all public incidents
        </div>
    </div>
</div>

<?php if (!empty($notices)): ?>
<div class="notices-section">
    <div class="section-header" style="border-bottom: 2px solid #f39c12;">
        <h3 class="section-title" style="color: #d35400;">
            <i class="fas fa-bullhorn"></i> COMMUNITY NOTICES & EVENTS
        </h3>
    </div>
    
    <div class="notices-list">
        <?php foreach ($notices as $notice): ?>
        <div class="notice-item">
            <div class="notice-header">
                <span class="notice-title">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($notice['title']); ?>
                </span>
                <span class="notice-date">
                    <?php echo date('d M Y, h:i A', strtotime($notice['created_at'])); ?>
                </span>
            </div>
            <div class="notice-body">
                <?php echo nl2br(htmlspecialchars($notice['message'])); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<div class="recent-incidents">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-history"></i> YOUR RECENT REPORTS
        </h3>
        <?php if ($total_incidents > 0): ?>
        <a href="your-reports.php" class="section-action">
            View All <i class="fas fa-arrow-right"></i>
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($recent_incidents)): ?>
    <div style="text-align: center; padding: 40px; color: #666;">
        <i class="fas fa-clipboard" style="font-size: 2rem; color: var(--info-blue); margin-bottom: 15px; display: block;"></i>
        <p>You haven't reported any incidents yet.</p>
        <a href="?page=reports" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: var(--info-blue); color: white; border-radius: 5px; text-decoration: none; font-size: 0.9rem;">
            <i class="fas fa-plus"></i> Report Your First Incident
        </a>
    </div>
    <?php else: ?>
    <ul class="incident-list">
        <?php foreach ($recent_incidents as $incident): 
            // Get icon and color based on incident type
            $icon_class = '';
            $icon_color = '';
            $incident_type = $incident['incident_type'];
            
            switch ($incident_type) {
                case 'Fire':
                    $icon_class = 'fas fa-fire';
                    $icon_color = '#f39c12';
                    break;
                case 'Flood':
                    $icon_class = 'fas fa-water';
                    $icon_color = '#3498db';
                    break;
                case 'Landslide':
                    $icon_class = 'fas fa-mountain';
                    $icon_color = '#9b59b6';
                    break;
                case 'Accident':
                    $icon_class = 'fas fa-car-crash';
                    $icon_color = '#e74c3c';
                    break;
                default:
                    $icon_class = 'fas fa-exclamation-circle';
                    $icon_color = '#2ecc71';
            }
            
            // Format date
            $created_at = new DateTime($incident['created_at']);
            $now = new DateTime();
            $interval = $created_at->diff($now);
            
            $time_ago = '';
            if ($interval->d > 0) {
                $time_ago = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
            } elseif ($interval->h > 0) {
                $time_ago = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
            } else {
                $time_ago = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
            }
            
            // Severity color
            $severity_color = '';
            switch ($incident['severity']) {
                case 'Critical': $severity_color = '#e74c3c'; break;
                case 'High': $severity_color = '#f39c12'; break;
                case 'Medium': $severity_color = '#3498db'; break;
                case 'Low': $severity_color = '#2ecc71'; break;
                default: $severity_color = '#95a5a6';
            }
        ?>
        <li class="incident-item">
            <div class="incident-icon" style="background-color: <?php echo $icon_color; ?>;">
                <i class="<?php echo $icon_class; ?>"></i>
            </div>
            <div class="incident-details">
                <div class="incident-title">
                    <?php echo htmlspecialchars($incident_type); ?> Report
                    <span style="color: <?php echo $severity_color; ?>; font-size: 0.8rem; margin-left: 10px;">
                        <i class="fas fa-circle"></i> <?php echo htmlspecialchars($incident['severity']); ?>
                    </span>
                </div>
                <div class="incident-meta">
                    <span class="incident-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($incident['address'] ?? 'Location not specified'); ?>
                    </span>
                    <span>
                        <i class="far fa-clock"></i> <?php echo $time_ago; ?>
                    </span>
                    <span>
                        <i class="fas fa-hashtag"></i> ID: INC<?php echo $incident['id']; ?>
                    </span>
                </div>
            </div>
            <div class="incident-status status-<?php echo strtolower(str_replace(' ', '-', $incident['status'])); ?>">
                <?php echo htmlspecialchars($incident['status']); ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize Map for Citizen Dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Force override any inherited problematic styles
    const mapElement = document.getElementById('citizenMap');
    if (mapElement) {
        mapElement.style.overflow = 'hidden';
        mapElement.style.position = 'relative';
        mapElement.style.display = 'block';
    }
    
    // Initialize Map
    var map = L.map('citizenMap').setView([4.2105, 101.9758], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Add Incident Markers (ALL PUBLIC INCIDENTS)
    var incidents = <?php echo json_encode($mapData); ?>;
    var markers = [];

    incidents.forEach(inc => {
        // Color based on severity
        let color = '#2ecc71'; // Low - Green
        if (inc.severity === 'Medium') color = '#3498db'; // Medium - Blue
        if (inc.severity === 'High') color = '#f39c12'; // High - Orange
        if (inc.severity === 'Critical') color = '#e74c3c'; // Critical - Red

        // Format date
        let date = new Date(inc.created_at);
        let formattedDate = date.toLocaleDateString() + ' ' + 
                           date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        // Create marker
        var marker = L.circleMarker([inc.lat, inc.lng], {
            color: color,
            fillColor: color,
            fillOpacity: 0.7,
            radius: 8
        })
        .addTo(map)
        .bindPopup(`
            <div style="min-width: 250px; padding: 5px;">
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <div style="width: 12px; height: 12px; background-color: ${color}; border-radius: 50%; margin-right: 10px;"></div>
                    <strong style="font-size: 14px;">${inc.type}</strong>
                </div>
                <div style="color: ${color}; font-weight: bold; font-size: 12px; margin-bottom: 8px;">
                    ${inc.severity} Severity
                </div>
                <div style="font-size: 12px; color: #555; margin-bottom: 10px;">
                    ${inc.desc || 'No description available'}
                </div>
                <div style="font-size: 11px; color: #777;">
                    <div style="margin-bottom: 3px;">
                        <i class="fas fa-map-marker-alt"></i> ${inc.address || 'Location not specified'}
                    </div>
                    <div>
                        <i class="far fa-clock"></i> ${formattedDate}
                    </div>
                </div>
            </div>
        `);

        markers.push(marker);
    });

    // Smart Zoom
    if (markers.length > 0) {
        var group = L.featureGroup(markers);
        map.fitBounds(group.getBounds(), { padding: [50, 50] });
    }
});
</script>