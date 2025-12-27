<?php
// Get user role from session
$role = $_SESSION['user_role'] ?? 'citizen';

// Get current page from session (set in dashboard.php)
$currentPage = $_SESSION['current_page'] ?? 'dashboard';

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
            'url' => 'dashboard.php?page=reports',
            'icon' => 'fas fa-flag',
            'badge' => null
        ],
        [
            'title' => 'Profile',
            'url' => 'dashboard.php?page=profile',
            'icon' => 'fas fa-user',
            'badge' => null
        ]
    ];
} elseif ($role === 'ketua kampung') {
    $navItems = [
        [
            'title' => 'Dashboard',
            'url' => 'dashboard.php?page=dashboard',
            'icon' => 'fas fa-home',
            'badge' => null
        ],
        [
            'title' => 'Incident Management',
            'url' => 'dashboard.php?page=incidents',
            'icon' => 'fas fa-exclamation-triangle',
            'badge' => '5'
        ],
        [
            'title' => 'Reports',
            'url' => 'dashboard.php?page=reports',
            'icon' => 'fas fa-file-alt',
            'badge' => null
        ],
        [
            'title' => 'Villager Management',
            'url' => 'dashboard.php?page=villagers',
            'icon' => 'fas fa-users',
            'badge' => null
        ],
        [
            'title' => 'Profile',
            'url' => 'dashboard.php?page=profile',
            'icon' => 'fas fa-user',
            'badge' => null
        ]
    ];
} elseif ($role === 'district') {
    // Default for other roles
    $navItems = [
        [
            'title' => 'Dashboard',
            'url' => 'dashboard.php?page=dashboard',
            'icon' => 'fas fa-tachometer-alt',  // Better icon for dashboard
            'badge' => null
        ],
        [
            'title' => 'Manage Incidents',
            'url' => 'dashboard.php?page=incidents',  // Fixed to use dashboard.php
            'icon' => 'fas fa-exclamation-triangle',
            'badge' => null
        ],
        [
            'title' => 'Manage Reports',
            'url' => 'dashboard.php?page=reports',
            'icon' => 'fas fa-file-alt',
            'badge' => '2'
        ],
        [
            'title' => 'Manage Users',
            'url' => 'dashboard.php?page=users',
            'icon' => 'fas fa-users',
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