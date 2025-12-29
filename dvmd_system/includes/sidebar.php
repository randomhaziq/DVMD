<?php
// Get user role from session
$role = $_SESSION['user_role'] ?? 'citizen';

// Get current page from session (set in dashboard.php)
$currentPage = $_SESSION['current_page'] ?? 'dashboard';

$navItems = []; // Add this line at the top 

// Define navigation based on role
if ($role === 'citizen') {
    $navItems = [
        [
            'title' => 'Dashboard',
            'url' => 'dashboard.php?page=dashboard',
            'icon' => 'fas fa-home',
            'badge' => null
        ],
        [
            'title' => 'SOS Alerts',
            'url' => 'dashboard.php?page=sos',
            'icon' => 'fas fa-exclamation-triangle',
            'badge' => null
        ],
        [
            'title' => 'Report Incident',
            'url' => 'dashboard.php?page=submit_reports',
            'icon' => 'fas fa-flag',
            'badge' => null
        ],
    ];
} elseif ($role === 'ketua_kampung' || $role === 'penghulu') {
    $navItems = [
        [
            'title' => 'Dashboard', 
            'url' => 'dashboard.php?page=dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'badge' => null
        ],
        [
            // COMBINED: View Incidents + Assign Resources here
            'title' => 'Incident Response', 
            'url' => 'dashboard.php?page=manage_incidents',
            'icon' => 'fas fa-map-marked-alt',
            'badge' => null 
        ],
        [
            'title' => 'Manage Reports', 
            'url' => 'dashboard.php?page=manage_reports',
            'icon' => 'fas fa-clipboard-check', 
            'badge' => '3' 
        ],
        [
            'title' => 'Emergency Alerts', 
            'url' => 'dashboard.php?page=emergency_alerts',
            'icon' => 'fas fa-bullhorn',
            'badge' => null
        ],
    ];
} elseif ($role === 'district') {
    // Default for other roles
    $navItems = [
        [
            'title' => 'Dashboard',
            'url' => 'dashboard.php?page=dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'badge' => null
        ],
        [
            // COMBINED: District-wide incidents + Resource Allocation (hana try tengok sini)
            'title' => 'District Incidents',
            'url' => 'dashboard.php?page=manage_incidents', 
            'icon' => 'fas fa-map',
            'badge' => null
        ],
        [
            // "emergency alert + submit report to HQ"
            'title' => 'Report & Alert',
            'url' => 'dashboard.php?page=emergency_alerts',
            'icon' => 'fas fa-bullhorn',
            'badge' => null
        ],
        [
            // "admin (daftar citizen, penghulu/ ketua kampung)"
            'title' => 'User Management',
            'url' => 'dashboard.php?page=users',
            'icon' => 'fas fa-users-cog',
            'badge' => null
        ],
    ];
} elseif ($role === 'hq') {
    // Default for other roles
    $navItems = [
        [
            'title' => 'HQ Dashboard',
            'url' => 'dashboard.php?page=dashboard',
            'icon' => 'fas fa-globe-asia', // Globe icon for National level
            'badge' => null
        ],
        [
            // "national wide incident (map + details)" + Resource Allocation
            'title' => 'National Incidents',
            'url' => 'dashboard.php?page=manage_incidents',
            'icon' => 'fas fa-map-marked-alt',
            'badge' => null
        ],
        [
            // "view reports and analytics"
            'title' => 'Reports & Analytics',
            'url' => 'dashboard.php?page=analytics', // Separate page for HQ analytics
            'icon' => 'fas fa-chart-line',
            'badge' => null
        ],
        [
            // "send broadcast message" & "emergency alert"
            'title' => 'Broadcast Center',
            'url' => 'dashboard.php?page=broadcast_alerts',
            'icon' => 'fas fa-tower-broadcast',
            'badge' => null
        ],
        [
            // "access audit logs"
            'title' => 'System Audit Logs',
            'url' => 'dashboard.php?page=audit_logs',
            'icon' => 'fas fa-history',
            'badge' => null
        ],
        [
            // Manage users (district only)
            'title' => 'Users Management',
            'url' => 'dashboard.php?page=users',
            'icon' => 'fas fa-users-cog',
            'badge' => null
        ],
    ];
}

?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <small>DVMD</small>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <nav class="menu">
        <ul>
            <?php foreach ($navItems as $item): 
                // Extract page parameter from URL
                $urlParts = parse_url($item['url']);
                parse_str($urlParts['query'] ?? '', $queryParams);
                $itemPage = $queryParams['page'] ?? '';
                
                // Check if current page matches
                $isActive = ($currentPage === $itemPage);
            ?>
            <li class="menu-item">
                <a href="<?php echo htmlspecialchars($item['url']); ?>"
                    class="menu-link <?php echo $isActive ? 'active' : ''; ?>">
                    <span class="menu-icon">
                        <i class="<?php echo $item['icon']; ?>"></i>
                    </span>
                    <span class="menu-text"><?php echo $item['title']; ?></span>
                    <?php if ($item['badge']): ?>
                    <span class="menu-badge"><?php echo $item['badge']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>