<?php
// Get user role and current page
$role = $_SESSION['user_role'] ?? 'citizen';
$currentPage = $_SESSION['current_page'] ?? 'dashboard';

$navItems = [];

// Navigation definitions (logical pages only)
if ($role === 'citizen') {
    $navItems = [
        ['title' => 'Dashboard', 'page' => 'dashboard', 'icon' => 'fas fa-home'],
        ['title' => 'SOS Alerts', 'page' => 'sos', 'icon' => 'fas fa-exclamation-triangle'],
        ['title' => 'Report Incident', 'page' => 'reports', 'icon' => 'fas fa-flag'],
        ['title' => 'Profile', 'page' => 'profile', 'icon' => 'fas fa-user'],
    ];
} elseif ($role === 'ketua_kampung' || $role === 'penghulu') {
    $navItems = [
        ['title' => 'Dashboard', 'page' => 'dashboard', 'icon' => 'fas fa-tachometer-alt'],
        ['title' => 'Incident Response', 'page' => 'manage_incidents', 'icon' => 'fas fa-map-marked-alt'],
        ['title' => 'Manage Reports', 'page' => 'manage_reports', 'icon' => 'fas fa-clipboard-check'],
        ['title' => 'Emergency Alerts', 'page' => 'emergency_alerts', 'icon' => 'fas fa-bullhorn'],
    ];
} elseif ($role === 'district') {
    $navItems = [
        ['title' => 'Dashboard', 'page' => 'dashboard', 'icon' => 'fas fa-tachometer-alt'],
        ['title' => 'District Incidents', 'page' => 'manage_incidents', 'icon' => 'fas fa-map'],
        ['title' => 'Report & Alert', 'page' => 'emergency_alerts', 'icon' => 'fas fa-bullhorn'],
        ['title' => 'User Management', 'page' => 'users', 'icon' => 'fas fa-users-cog'],
    ];
} elseif ($role === 'hq') {
    $navItems = [
        ['title' => 'HQ Dashboard', 'page' => 'dashboard', 'icon' => 'fas fa-globe-asia'],
        ['title' => 'National Incidents', 'page' => 'manage_incidents', 'icon' => 'fas fa-map-marked-alt'],
        ['title' => 'Broadcast Center', 'page' => 'broadcast_center', 'icon' => 'fas fa-tower-broadcast'],
        ['title' => 'System Audit Logs', 'page' => 'audit_logs', 'icon' => 'fas fa-history'],
        ['title' => 'Users Management', 'page' => 'users', 'icon' => 'fas fa-users-cog'],
    ];
}
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo"><small>DVMD</small></div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <nav class="menu">
        <ul>
            <?php foreach ($navItems as $item): 
                $isActive = ($currentPage === $item['page']);
                // Always encode URL and escape HTML
                $url = 'dashboard.php?page=' . urlencode($item['page']);
            ?>
                <li class="menu-item">
                    <a href="<?php echo htmlspecialchars($url); ?>"
                       class="menu-link <?php echo $isActive ? 'active' : ''; ?>">
                        <span class="menu-icon">
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                        </span>
                        <span class="menu-text">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>