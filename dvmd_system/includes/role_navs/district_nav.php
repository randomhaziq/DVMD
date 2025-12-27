<?php

$navItems = [
    [
        'title' => 'Dashboard',
        'url' => 'dashboard.php',
        'icon' => 'fas fa-tachometer-alt',  // Better icon for dashboard
        'badge' => null
    ],
    [
        'title' => 'Manage Incidents',
        'url' => 'incidents.php',  // Changed to incidents.php (more appropriate)
        'icon' => 'fas fa-exclamation-triangle',  // Better for incidents
        'badge' => null
    ],
    [
        'title' => 'Manage Reports',
        'url' => 'reports.php',  // Changed to reports.php
        'icon' => 'fas fa-file-alt',  // Better for reports
        'badge' => '2'
    ],
    [
        'title' => 'Manage Users',
        'url' => 'users.php',  // Changed to users.php (for user management)
        'icon' => 'fas fa-users',  // Better for user management
        'badge' => null
    ],
];
?>