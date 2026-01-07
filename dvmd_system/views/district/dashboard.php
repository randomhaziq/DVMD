<?php
// Get user data from session - District Officer has district in their profile
$user = $_SESSION['user_data'] ?? null;
$user_id = $user['id'] ?? 0;
$user_district = $user['district'] ?? 'Unknown District';

// Direct database connection
require_once __DIR__ . '/../../api/dbconnect.php';

// 1. DISTRICT-LEVEL STATISTICS (All incidents in the officer's district)
$district_stats_query = "SELECT 
    COUNT(DISTINCT i.id) as total_incidents,
    COUNT(DISTINCT CASE WHEN i.severity = 'Critical' THEN i.id END) as critical_count,
    COUNT(DISTINCT CASE WHEN i.severity = 'High' THEN i.id END) as high_count,
    COUNT(DISTINCT CASE WHEN i.severity = 'Medium' THEN i.id END) as medium_count,
    COUNT(DISTINCT CASE WHEN i.severity = 'Low' THEN i.id END) as low_count,
    COUNT(DISTINCT CASE WHEN i.status = 'Pending' THEN i.id END) as pending_count,
    COUNT(DISTINCT CASE WHEN i.status = 'In Progress' THEN i.id END) as inprogress_count,
    COUNT(DISTINCT CASE WHEN i.status = 'Resolved' THEN i.id END) as resolved_count,
    COUNT(DISTINCT s.id) as total_sos_alerts,
    COUNT(DISTINCT CASE WHEN TIMESTAMPDIFF(HOUR, s.created_at, NOW()) <= 24 THEN s.id END) as active_sos_alerts
    FROM users u
    LEFT JOIN incidents i ON u.id = i.reported_by
    LEFT JOIN sos_alerts s ON u.id = s.user_id
    WHERE u.district = '" . mysqli_real_escape_string($conn, $user_district) . "'";

$district_stats_result = mysqli_query($conn, $district_stats_query);
$district_data = mysqli_fetch_assoc($district_stats_result);

// 2. INCIDENTS BY TYPE IN DISTRICT
$incidents_by_type_query = "SELECT 
    i.incident_type,
    COUNT(i.id) as count
    FROM incidents i
    JOIN users u ON i.reported_by = u.id
    WHERE u.district = '" . mysqli_real_escape_string($conn, $user_district) . "'
    GROUP BY i.incident_type
    ORDER BY count DESC";

$incidents_by_type_result = mysqli_query($conn, $incidents_by_type_query);
$incidents_by_type = [];
$total_district_incidents = 0;

while ($row = mysqli_fetch_assoc($incidents_by_type_result)) {
    $incidents_by_type[$row['incident_type']] = $row['count'];
    $total_district_incidents += $row['count'];
}

// 3. TOP REPORTERS IN DISTRICT
$top_reporters_query = "SELECT 
    u.name,
    u.email,
    COUNT(i.id) as reports_count
    FROM incidents i
    JOIN users u ON i.reported_by = u.id
    WHERE u.district = '" . mysqli_real_escape_string($conn, $user_district) . "'
    GROUP BY u.id
    ORDER BY reports_count DESC
    LIMIT 5";

$top_reporters_result = mysqli_query($conn, $top_reporters_query);
$top_reporters = [];

while ($row = mysqli_fetch_assoc($top_reporters_result)) {
    $top_reporters[] = $row;
}

// 4. RECENT INCIDENTS IN DISTRICT
$recent_incidents_query = "SELECT 
    i.*,
    u.name as reporter_name,
    u.district as reporter_district
    FROM incidents i
    JOIN users u ON i.reported_by = u.id
    WHERE u.district = '" . mysqli_real_escape_string($conn, $user_district) . "'
    ORDER BY i.created_at DESC 
    LIMIT 6";

$recent_incidents_result = mysqli_query($conn, $recent_incidents_query);
$recent_incidents = [];

while ($row = mysqli_fetch_assoc($recent_incidents_result)) {
    $recent_incidents[] = $row;
}

// 5. DISTRICT MAP DATA (Only incidents in this district)
$district_map_query = "SELECT 
    i.lat, i.lng, i.incident_type, i.description, i.severity, i.created_at, i.address,
    u.name as reporter_name
    FROM incidents i
    JOIN users u ON i.reported_by = u.id
    WHERE u.district = '" . mysqli_real_escape_string($conn, $user_district) . "'
    AND i.lat IS NOT NULL AND i.lng IS NOT NULL 
    ORDER BY i.created_at DESC";

$district_map_result = mysqli_query($conn, $district_map_query);
$district_mapData = [];

while ($row = mysqli_fetch_assoc($district_map_result)) {
    $district_mapData[] = [
        'lat' => $row['lat'],
        'lng' => $row['lng'],
        'type' => $row['incident_type'],
        'desc' => $row['description'],
        'severity' => $row['severity'],
        'address' => $row['address'],
        'created_at' => $row['created_at'],
        'reporter' => $row['reporter_name']
    ];
}

// 6. WEEKLY TREND (Last 7 days)
$weekly_trend_query = "SELECT 
    DATE(i.created_at) as date,
    COUNT(i.id) as incident_count
    FROM incidents i
    JOIN users u ON i.reported_by = u.id
    WHERE u.district = '" . mysqli_real_escape_string($conn, $user_district) . "'
    AND i.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(i.created_at)
    ORDER BY date ASC";

$weekly_trend_result = mysqli_query($conn, $weekly_trend_query);
$weekly_trend = [];
$dates = [];
$counts = [];

while ($row = mysqli_fetch_assoc($weekly_trend_result)) {
    $weekly_trend[] = $row;
    $dates[] = date('D', strtotime($row['date'])); // Day name
    $counts[] = (int)$row['incident_count'];
}
?>

<!-- DISTRICT OFFICER DASHBOARD STYLES -->
<style>
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
    --officer-purple: #8e44ad;
}

/* Officer Welcome Section */
.welcome-section {
    background: linear-gradient(135deg, var(--officer-purple), #9b59b6);
    color: white;
    padding: 25px 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(142, 68, 173, 0.2);
    border-left: 5px solid #f39c12;
}

.welcome-section h2 {
    font-size: 1.8rem;
    margin-bottom: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.welcome-section h2 i {
    color: #f39c12;
}

.district-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-left: 10px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* District Dashboard Grid - 4 cards for district overview */
.district-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
    margin-bottom: 40px;
}

@media (max-width: 1200px) {
    .district-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .district-grid {
        grid-template-columns: 1fr;
    }
}

.district-stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border-top: 5px solid;
    position: relative;
    overflow: hidden;
}

.district-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.district-stat-card.critical {
    border-top-color: var(--emergency-red);
}

.district-stat-card.active {
    border-top-color: var(--info-blue);
}

.district-stat-card.resolved {
    border-top-color: var(--success-green);
}

.district-stat-card.monitoring {
    border-top-color: var(--officer-purple);
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

.critical .card-icon {
    background: linear-gradient(135deg, var(--emergency-red), #ff7979);
}

.active .card-icon {
    background: linear-gradient(135deg, var(--info-blue), #5dade2);
}

.resolved .card-icon {
    background: linear-gradient(135deg, var(--success-green), #58d68d);
}

.monitoring .card-icon {
    background: linear-gradient(135deg, var(--officer-purple), #af7ac5);
}

.card-value {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--text-dark);
    line-height: 1;
}

.card-trend {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-top: 5px;
}

.card-trend.positive {
    color: var(--success-green);
}

.card-trend.negative {
    color: var(--emergency-red);
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

/* District Map Section */
.district-map-section {
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
    color: var(--officer-purple);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--officer-purple);
}

.section-action {
    color: var(--officer-purple);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    padding: 8px 15px;
    border-radius: 5px;
    background: rgba(142, 68, 173, 0.1);
}

.section-action:hover {
    color: white;
    background: var(--officer-purple);
    gap: 10px;
}

#districtMap {
    height: 400px;
    width: 100%;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    background: var(--light-gray);
    overflow: hidden !important;
    position: relative;
    border: 1px solid #eee;
}

/* Top Reporters Section */
.top-reporters {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
}

.reporter-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.reporter-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid var(--light-gray);
    transition: all 0.3s ease;
    border-radius: 10px;
    margin-bottom: 8px;
}

.reporter-item:hover {
    background: var(--light-gray);
}

.reporter-rank {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--officer-purple);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
    flex-shrink: 0;
    font-size: 1rem;
}

.reporter-details {
    flex: 1;
    min-width: 0;
}

.reporter-name {
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-dark);
}

.reporter-email {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-bottom: 8px;
}

.reporter-stats {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 0.85rem;
}

.report-count {
    background: rgba(52, 152, 219, 0.1);
    color: var(--info-blue);
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 600;
}

/* Recent District Incidents */
.recent-district-incidents {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
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

.incident-reporter {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-bottom: 8px;
}

.incident-reporter i {
    color: var(--officer-purple);
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

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.action-button {
    flex: 1;
    padding: 15px;
    border-radius: 10px;
    text-decoration: none;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
    text-align: center;
}

.action-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    color: white;
}

.action-button.manage {
    background: linear-gradient(135deg, var(--officer-purple), #9b59b6);
}

.action-button.dispatch {
    background: linear-gradient(135deg, var(--info-blue), #5dade2);
}

.action-button.reports {
    background: linear-gradient(135deg, var(--success-green), #58d68d);
}

/* Weekly Trend Chart */
.trend-chart {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
}

.chart-container {
    height: 200px;
    width: 100%;
    margin-top: 20px;
}

.chart-placeholder {
    height: 100%;
    display: flex;
    align-items: flex-end;
    gap: 10px;
    padding: 20px;
    background: var(--light-gray);
    border-radius: 10px;
}

.chart-bar {
    flex: 1;
    background: var(--officer-purple);
    border-radius: 5px 5px 0 0;
    position: relative;
    min-height: 10px;
}

.chart-bar-label {
    position: absolute;
    bottom: -25px;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 0.8rem;
    color: var(--text-light);
}

.chart-bar-value {
    position: absolute;
    top: -25px;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--officer-purple);
}
</style>

<!-- DISTRICT OFFICER DASHBOARD CONTENT -->

<!-- Welcome Section -->
<div class="welcome-section">
    <h2>District Command Dashboard</h2>
    <p>Welcome back, <strong><?php echo htmlspecialchars($user['name'] ?? 'Officer'); ?></strong> | District Officer of
        <?php echo htmlspecialchars($user_district); ?></p>
    <p style="margin-top: 10px; font-size: 0.9rem; opacity: 0.9;">
        <i class="fas fa-chart-line"></i> Monitoring <?php echo $total_district_incidents; ?> incidents across your
        district
    </p>
</div>

<!-- District Overview Grid - 4 CARDS -->
<div class="district-grid">
    <!-- Card 1: Critical Incidents -->
    <div class="district-stat-card critical">
        <div class="card-header">
            <h3 class="card-title">CRITICAL INCIDENTS</h3>
            <div class="card-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="card-value"><?php echo $district_data['critical_count'] ?? 0; ?></div>
        <div class="card-subtitle">Require immediate attention</div>
        <div class="card-details">
            <div class="detail-item">
                <i class="fas fa-fire" style="color: #e74c3c;"></i>
                <span>High: <?php echo $district_data['high_count'] ?? 0; ?></span>
            </div>
            <div class="detail-item">
                <i class="fas fa-clock" style="color: #f39c12;"></i>
                <span>Pending: <?php echo $district_data['pending_count'] ?? 0; ?></span>
            </div>
        </div>
    </div>

    <!-- Card 2: Active Incidents -->
    <div class="district-stat-card active">
        <div class="card-header">
            <h3 class="card-title">ACTIVE INCIDENTS</h3>
            <div class="card-icon">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
        <div class="card-value"><?php echo $district_data['inprogress_count'] ?? 0; ?></div>
        <div class="card-subtitle">Currently being handled</div>
        <div class="card-trend">
            <i class="fas fa-arrow-up"></i> 12% from last week
        </div>
        <div class="card-details">
            <div class="detail-item">
                <i class="fas fa-users" style="color: #3498db;"></i>
                <span><?php echo count($district_mapData); ?> locations</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Resolved Cases -->
    <div class="district-stat-card resolved">
        <div class="card-header">
            <h3 class="card-title">RESOLVED CASES</h3>
            <div class="card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="card-value"><?php echo $district_data['resolved_count'] ?? 0; ?></div>
        <div class="card-subtitle">Successfully handled</div>
        <div class="card-details">
            <div class="detail-item">
                <i class="fas fa-chart-bar" style="color: #27ae60;"></i>
                <span><?php echo $total_district_incidents > 0 ? round(($district_data['resolved_count'] / $total_district_incidents) * 100) : 0; ?>%
                    success rate</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Active SOS Alerts -->
    <div class="district-stat-card monitoring">
        <div class="card-header">
            <h3 class="card-title">ACTIVE SOS ALERTS</h3>
            <div class="card-icon">
                <i class="fas fa-life-ring"></i>
            </div>
        </div>
        <div class="card-value"><?php echo $district_data['active_sos_alerts'] ?? 0; ?></div>
        <div class="card-subtitle">Emergency signals in district</div>
        <div class="card-details">
            <div class="detail-item">
                <i class="fas fa-bell" style="color: #8e44ad;"></i>
                <span><?php echo $district_data['total_sos_alerts'] ?? 0; ?> total alerts</span>
            </div>
        </div>
    </div>
</div>

<!-- District Map Section -->
<div class="district-map-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-map"></i> DISTRICT INCIDENT MAP
        </h3>
        <a href="district-map.php?district=<?php echo urlencode($user_district); ?>" class="section-action">
            <i class="fas fa-expand"></i> Full Screen View
        </a>
    </div>

    <!-- District Map Container -->
    <div id="districtMap"></div>

    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
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
            <i class="fas fa-info-circle"></i> Showing incidents in <?php echo htmlspecialchars($user_district); ?> only
        </div>
    </div>
</div>

<!-- Two Column Layout for Recent Incidents and Top Reporters -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Recent District Incidents -->
    <div class="recent-district-incidents">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-history"></i> RECENT DISTRICT INCIDENTS
            </h3>
            <?php if ($total_district_incidents > 0): ?>
            <a href="manage-incidents.php?district=<?php echo urlencode($user_district); ?>" class="section-action">
                <i class="fas fa-list"></i> Manage All
            </a>
            <?php endif; ?>
        </div>

        <?php if (empty($recent_incidents)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-clipboard"
                style="font-size: 2rem; color: var(--officer-purple); margin-bottom: 15px; display: block;"></i>
            <p>No incidents reported in <?php echo htmlspecialchars($user_district); ?> yet.</p>
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
                        <?php echo htmlspecialchars($incident_type); ?>
                        <span style="color: <?php echo $severity_color; ?>; font-size: 0.8rem; margin-left: 10px;">
                            <i class="fas fa-circle"></i> <?php echo htmlspecialchars($incident['severity']); ?>
                        </span>
                    </div>
                    <div class="incident-reporter">
                        <i class="fas fa-user"></i> Reported by:
                        <?php echo htmlspecialchars($incident['reporter_name']); ?>
                    </div>
                    <div class="incident-meta">
                        <span class="incident-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($incident['address'] ?? 'Location not specified'); ?>
                        </span>
                        <span>
                            <i class="far fa-clock"></i> <?php echo $time_ago; ?>
                        </span>
                    </div>
                </div>
                <div
                    class="incident-status status-<?php echo strtolower(str_replace(' ', '-', $incident['status'])); ?>">
                    <?php echo htmlspecialchars($incident['status']); ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <!-- Top Reporters -->
    <div class="top-reporters">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-medal"></i> TOP REPORTERS
            </h3>
        </div>

        <?php if (empty($top_reporters)): ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-users"
                style="font-size: 2rem; color: var(--officer-purple); margin-bottom: 15px; display: block;"></i>
            <p>No reports yet in <?php echo htmlspecialchars($user_district); ?>.</p>
        </div>
        <?php else: ?>
        <ul class="reporter-list">
            <?php foreach ($top_reporters as $index => $reporter): ?>
            <li class="reporter-item">
                <div class="reporter-rank" style="background: <?php echo $index < 3 ? '#e74c3c' : '#3498db'; ?>;">
                    <?php echo $index + 1; ?>
                </div>
                <div class="reporter-details">
                    <div class="reporter-name"><?php echo htmlspecialchars($reporter['name']); ?></div>
                    <div class="reporter-email"><?php echo htmlspecialchars($reporter['email']); ?></div>
                    <div class="reporter-stats">
                        <span class="report-count">
                            <i class="fas fa-clipboard-list"></i> <?php echo $reporter['reports_count']; ?> reports
                        </span>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <!-- Incident Types Breakdown -->
        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee;">
            <h4 style="font-size: 0.95rem; color: var(--text-dark); margin-bottom: 15px;">
                <i class="fas fa-chart-pie"></i> Incident Types Breakdown
            </h4>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <?php foreach ($incidents_by_type as $type => $count): 
                    $percentage = $total_district_incidents > 0 ? round(($count / $total_district_incidents) * 100) : 0;
                ?>
                <div style="flex: 1; min-width: 120px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span
                            style="font-size: 0.85rem; color: var(--text-dark);"><?php echo htmlspecialchars($type); ?></span>
                        <span
                            style="font-size: 0.85rem; font-weight: 600; color: var(--officer-purple);"><?php echo $percentage; ?>%</span>
                    </div>
                    <div style="height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
                        <div
                            style="width: <?php echo $percentage; ?>%; height: 100%; background: var(--officer-purple); border-radius: 4px;">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Buttons -->
<div class