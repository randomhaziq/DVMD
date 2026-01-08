<?php
session_start();

// ------------------------------
// AUTHENTICATION
// ------------------------------
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['user_role'] ?? 'citizen';
$page = $_GET['page'] ?? 'dashboard';

// ------------------------------
// URL VALIDATION
// Only allow lowercase letters and underscores
// ------------------------------
if (!preg_match('/^[a-z_]+$/', $page)) {
    http_response_code(400);
    exit('Invalid request');
}

// ------------------------------
// CENTRALIZED ROUTES
// Only allow include from these mappings
// ------------------------------
$routes = [
    'citizen' => [
        'dashboard' => 'views/citizen/dashboard.php',
        'sos'       => 'views/citizen/sos.php',
        'reports'   => 'views/citizen/reports.php',
        'profile'   => 'views/citizen/profile.php',
    ],
    'ketua_kampung' => [
        'dashboard'        => 'views/ketua_kampung/dashboard.php',
        'manage_incidents' => 'views/ketua_kampung/manage_incidents.php',
        'manage_reports'   => 'views/ketua_kampung/manage_reports.php',
        'emergency_alerts' => 'views/ketua_kampung/emergency_alerts.php',
    ],
    'penghulu' => [
        'dashboard'        => 'views/penghulu/dashboard.php',
        'manage_incidents' => 'views/penghulu/manage_incidents.php',
        'manage_reports'   => 'views/penghulu/manage_reports.php',
        'emergency_alerts' => 'views/penghulu/emergency_alerts.php',
    ],
    'district' => [
        'dashboard'        => 'views/district/dashboard.php',
        'manage_incidents' => 'views/district/manage_incidents.php',
        'manage_reports'   => 'views/district/manage_reports.php',
        'emergency_alerts' => 'views/district/emergency_alerts.php',
        'users'            => 'views/district/users.php',
    ],
    'hq' => [
        'dashboard'        => 'views/hq/dashboard.php',
        'manage_incidents' => 'views/hq/manage_incidents.php',
        'broadcast_center' => 'views/hq/broadcast_center.php',
        'audit_logs'       => 'views/hq/audit_logs.php',
        'users'            => 'views/hq/users.php',
    ]
];

// ------------------------------
// AUTHORIZATION
// ------------------------------
if (!isset($routes[$role][$page])) {
    http_response_code(403);
    exit('Access denied');
}

// ------------------------------
// PAGE DATA
// ------------------------------
$pageTitles = [
    'dashboard' => 'Dashboard',
    'reports' => 'Report Incident',
    'profile' => 'My Profile',
    'sos' => 'SOS Alerts',
    'manage_incidents' => 'Incident Management',
    'manage_reports' => 'Manage Reports',
    'emergency_alerts' => 'Emergency Alerts',
    'analytics' => 'Analytics',
    'audit_logs' => 'System Audit Logs',
    'users' => 'User Management',
    'broadcast_alerts' => 'Broadcast Center'
];

$pageTitle = $pageTitles[$page] ?? 'Dashboard';
$contentFile = $routes[$role][$page];

// ------------------------------
// STORE CURRENT PAGE (FOR SIDEBAR ACTIVE LINK)
// ------------------------------
$_SESSION['current_page'] = $page;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DVMD - <?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/sidebar.css">

    <?php if ($page === 'reports'): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <link rel="stylesheet" href="css/reports.css">
    <?php endif; ?>

    <?php if ($page === 'profile'): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <link rel="stylesheet" href="css/profile.css">
    <?php endif; ?>

    <script src="js/sidebar.js" defer></script>
</head>
<body>
<div class="wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <header class="dashboard-header">
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

            <div class="user-info">
                <?php $user = $_SESSION['user_data'] ?? []; ?>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 2)); ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></strong>
                    <div style="font-size: 0.9rem;">
                        <?php echo htmlspecialchars($user['role'] ?? 'Role'); ?>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>

        <main class="dashboard-content">
            <?php require_once __DIR__ . '/' . $contentFile; ?>

            <div class="dashboard-footer">
                <p>Digital Village Management Dashboard (DVMD)</p>
                <p style="font-size: 0.8rem;">
                    Last Updated: <?php echo date('d F Y'); ?> •
                    <span style="color: green;">Operational</span>
                </p>
            </div>
        </main>
    </div>
</div>

<?php if ($page === 'reports'): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php endif; ?>
</body>
</html>