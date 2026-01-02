<?php
// Get user data from session
$user = $_SESSION['user_data'] ?? null;
?>

<!-- JUST THE DASHBOARD CONTENT STARTS HERE -->
<div class="welcome-message">
    <h2>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</h2>
    <p>You are logged in as KPLB HQ</p>
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
            <h3>3</h3>
            <p>Active • 4 Pending</p>
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
            <h3>9</h3>
            <p>Fire: 1 • Flood: 2 • Landslide: 6</p>
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">HOUSEHOLD ASSISTANCE</h3>
            <div class="card-icon success">
                <i class="fas fa-home"></i>
            </div>
        </div>
        <div class="card-content">
            <h3>75%</h3>
            <p>Allocated/Required</p>
        </div>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="dashboard-grid">
    <!-- Left Column -->
    <div class="left-column">
        <!-- GIS Map Visualization -->
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">GIS MAP VISUALIZATION</h3>
                <a href="gis.php" class="section-action">Full Screen <i class="fas fa-expand"></i></a>
            </div>
            <div class="map-container">
                [Placeholder: Interactive GIS Map with incident locations marked]
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
            </div>
        </div>

        <!-- Recent Incidents Section -->
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
                            <span class="incident-location"><i class="fas fa-map-marker-alt"></i> Kg.
                                Merdeka</span>
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
                            <span class="incident-location"><i class="fas fa-map-marker-alt"></i> Kg.
                                Luna</span>
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
                            <span class="incident-location"><i class="fas fa-map-marker-alt"></i> Kg.
                                Kona</span>
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
                            <span class="incident-location"><i class="fas fa-map-marker-alt"></i> Kg.
                                Luna</span>
                            <span><i class="far fa-clock"></i> 1 day ago</span>
                        </div>
                    </div>
                    <div class="incident-status status-verified">Verifying</div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Right Column -->
    <div class="right-column">
        <!-- Complaint Tracking -->
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">COMPLAINT TRACKING</h3>
                <a href="complaints.php" class="section-action">Manage</a>
            </div>
            <div class="complaint-item">
                <div class="complaint-info">
                    <h4>Water supply disruption</h4>
                    <p>Kg. Merdeka • Reported 2 days ago</p>
                </div>
                <div class="complaint-status status-pending">Pending</div>
            </div>
            <div class="complaint-item">
                <div class="complaint-info">
                    <h4>Fallen tree blocking road</h4>
                    <p>Kg. Bahagia • Reported 1 day ago</p>
                </div>
                <div class="complaint-status status-resolved">Resolved</div>
            </div>
            <div class="progress-container">
                <div class="progress-label">
                    <span>Resolution Rate</span>
                    <span>65%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 65%;"></div>
                </div>
            </div>
        </div>

        <!-- Community Notices & Events -->
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">COMMUNITY NOTICES & EVENTS</h3>
                <a href="broadcast.php" class="section-action">Post New</a>
            </div>
            <div class="notice-item">
                <h4>River Water Level Rising</h4>
                <p>Warning issued for villages near Sungai Serdang</p>
            </div>
            <div class="notice-item">
                <h4>Disaster Preparedness Workshop</h4>
                <p>15 Nov 2023 • Community Hall, Kg. Bahagia</p>
            </div>
            <div class="notice-item">
                <h4>Vaccination Drive</h4>
                <p>20-22 Nov 2023 • All village clinics</p>
            </div>
        </div>

        <!-- Household Assistance Tracking -->
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">HOUSEHOLD ASSISTANCE TRACKING</h3>
                <a href="household.php" class="section-action">Details</a>
            </div>
            <div class="progress-container">
                <div class="progress-label">
                    <span>Allocation Progress</span>
                    <span>75%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 75%;"></div>
                </div>
            </div>
            <p style="font-size: 0.85rem; color: var(--gray-color); margin-top: 10px;">
                <i class="fas fa-info-circle"></i> 150 of 200 households have received assistance
            </p>
        </div>
    </div>
</div>
<!-- END OF CONTENT - NO CLOSING HTML/BODY TAGS -->