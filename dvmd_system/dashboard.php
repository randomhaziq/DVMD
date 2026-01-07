<?php
// dashboard.php - Simplified version
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['user_role'] ?? 'citizen';
$page = $_GET['page'] ?? 'dashboard';

// Convert role name to folder name
$roleFolder = strtolower(str_replace(' ', '_', $role));

// Define allowed pages
$allowedPages = [
    'citizen' => [
        'dashboard', 
        'sos', 
        'reports',
        'profile',
    ],
    'ketua_kampung' => [ // Handles Penghulu too if folder is named this, or add 'penghulu' key
        'dashboard', 
        'manage_incidents', 
        'manage_reports', 
        'emergency_alerts', 
    ],
    'penghulu' => [
        'dashboard', 
        'manage_incidents', 
        'manage_reports', 
        'emergency_alerts',
    ],
    'district' => [
        'dashboard', 
        'manage_incidents', 
        'manage_reports', 
        'emergency_alerts', 
        'users',
    ],
    'hq' => [
        'dashboard', 
        'manage_incidents',        
        'broadcast_center', 
        'audit_logs',       
        'users',
    ]
];

// Check if page is allowed
if (!in_array($page, $allowedPages[$roleFolder] ?? [])) {
    $page = 'dashboard';
}

// Set page title
$pageTitles = [
    'dashboard' => 'Dashboard',
    'reports' => 'Report Incident',
    'profile' => 'My Profile',
    'complaints' => 'My Complaints',
    'sos' => 'SOS Alerts',
    'incidents' => 'Incident Management',
    'villagers' => 'Villager Management',
    'villages' => 'Village Management',
    'overview' => 'System Overview'
];
$pageTitle = $pageTitles[$page] ?? 'Dashboard';

// Determine which file to include
$contentFile = "views/{$roleFolder}/{$page}.php";

// If file doesn't exist, fall back to dashboard
if (!file_exists($contentFile)) {
    $contentFile = "views/{$roleFolder}/dashboard.php";
    $pageTitle = 'Dashboard';
}

// Store current page in session for sidebar
$_SESSION['current_page'] = $page;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DVMD - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <?php if ($page === 'reports'): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/reports.css">
    <?php endif; ?>
    <?php if ($page === 'profile'): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/profile.css">
    <?php endif; ?>
    <script src="js/sidebar.js" defer></script>
</head>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <div class="content-wrapper">
            <header class="dashboard-header">
                <h1><?php echo $pageTitle; ?></h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        $user = $_SESSION['user_data'] ?? null;
                        echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); 
                        ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></strong>
                        <div style="font-size: 0.9rem; opacity: 0.9;">
                            <?php echo htmlspecialchars($user['role'] ?? 'Role'); ?>
                        </div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </header>

            <main class="dashboard-content">
                <?php 
                // Include the content file
                if (file_exists($contentFile)) {
                    include $contentFile;
                } else {
                    // Fallback
                    echo "<div class='welcome-message'><h2>Page Not Found</h2><p>The requested page is not available.</p></div>";
                }
                ?>

                <!-- Footer -->
                <div class="dashboard-footer">
                    <p>Digital Village Management Dashboard (DVMD) • Version 2.1 • Compliant with PDPA 2010 & MyGovCloud
                        Standards</p>
                    <p style="font-size: 0.8rem; margin-top: 5px;">Last Updated: <?php echo date('d F Y'); ?> • System
                        Status:
                        <span style="color: var(--success-color);">Operational</span>
                    </p>
                </div>
            </main>
        </div>
    </div>

    <!-- Leaflet JS for reports page -->
    <?php if ($page === 'reports'): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>
</body>

</html>