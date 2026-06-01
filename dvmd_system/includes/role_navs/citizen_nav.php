<?php
// Navigation for citizens - Updated for new routing system
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
        'badge' => '3'
    ],
    [
        'title' => 'Report Incident',
        'url' => 'dashboard.php?page=reports',
        'icon' => 'fas fa-flag',
        'badge' => null
    ],
    [
        'title' => 'My Complaints',
        'url' => 'dashboard.php?page=complaints',
        'icon' => 'fas fa-comment-medical',
        'badge' => '2'
    ],
    [
        'title' => 'Profile',
        'url' => 'dashboard.php?page=profile',
        'icon' => 'fas fa-user',
        'badge' => null
    ]
];
?>