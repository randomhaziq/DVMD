// js/sidebar.js
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileToggle');
    const contentWrapper = document.querySelector('.content-wrapper');
    
    // Check if elements exist
    if (!sidebar || !contentWrapper) return;
    
    // Toggle sidebar collapse/expand
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        contentWrapper.classList.toggle('expanded');
        
        // Update toggle button icon
        if (sidebarToggle) {
            const icon = sidebarToggle.querySelector('i');
            if (icon) {
                if (sidebar.classList.contains('collapsed')) {
                    icon.className = 'fas fa-chevron-right';
                } else {
                    icon.className = 'fas fa-chevron-left';
                }
            }
        }
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
    
    // Toggle mobile sidebar
    function toggleMobileSidebar() {
        sidebar.classList.toggle('show');
    }
    
    // Check saved preference
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true' && sidebar && contentWrapper) {
        sidebar.classList.add('collapsed');
        contentWrapper.classList.add('expanded');
        if (sidebarToggle) {
            const icon = sidebarToggle.querySelector('i');
            if (icon) icon.className = 'fas fa-chevron-right';
        }
    }
    
    // Event listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleMobileSidebar);
    }
    
    // Close mobile sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const isMobile = window.innerWidth <= 992;
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnMobileToggle = mobileToggle && mobileToggle.contains(event.target);
        
        if (isMobile && !isClickInsideSidebar && !isClickOnMobileToggle && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });
    
    // Close mobile sidebar on menu link click
    const menuLinks = document.querySelectorAll('.menu-link');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                sidebar.classList.remove('show');
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('show');
        }
    });
});