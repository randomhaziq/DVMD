// sos.js - Emergency SOS functionality

document.addEventListener('DOMContentLoaded', function() {
    // Initialize SOS functionality
    console.log('SOS Emergency System Loaded');
    
    // Real-time location updates (simulated)
    function updateLocation() {
        // In a real app, this would use the Geolocation API
        const locations = [
            "Kampung Baru, Kuala Lumpur",
            "Taman Desa, Kuala Lumpur", 
            "Bangsar South, Kuala Lumpur",
            "Cheras, Kuala Lumpur"
        ];
        
        const randomLocation = locations[Math.floor(Math.random() * locations.length)];
        const locationElement = document.getElementById('locationText');
        
        if (locationElement && !locationElement.textContent.includes('Fetching')) {
            // Just for demo - show location is active
            locationElement.textContent = randomLocation;
        }
    }
    
    // Update location every 30 seconds (simulated)
    setInterval(updateLocation, 30000);
    
    // Add keyboard shortcut for SOS (Alt+S)
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            const sosButton = document.getElementById('sosMainButton');
            if (sosButton) {
                sosButton.click();
                console.log('SOS triggered via keyboard shortcut');
            }
        }
    });
    
    // Show keyboard shortcut hint
    console.log('Press Alt+S to trigger SOS alert');
});

// Add this to your existing JavaScript

// Function to check if device is mobile
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// Update modal opening for mobile
function openSosModal() {
    sosModal.style.display = 'flex';
    step1.style.display = 'block';
    step2.style.display = 'none';
    
    // Reset form
    document.getElementById('emergencyType').value = '';
    document.getElementById('additionalInfo').value = '';
    
    // Update time
    updateTime();
    
    // Scroll to top of modal on mobile
    if (isMobileDevice()) {
        setTimeout(() => {
            sosModal.scrollTop = 0;
            const modalContent = document.querySelector('.modal-content');
            if (modalContent) {
                modalContent.scrollTop = 0;
            }
        }, 100);
    }
}

// Add event listener for better mobile handling
document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...
    
    // Prevent default behavior when touching modal on iOS
    if (isMobileDevice()) {
        document.querySelectorAll('.modal-content').forEach(modal => {
            modal.addEventListener('touchmove', function(e) {
                e.stopPropagation();
            }, { passive: false });
        });
    }
    
    // Focus management for mobile
    const textarea = document.getElementById('additionalInfo');
    if (textarea && isMobileDevice()) {
        textarea.addEventListener('focus', function() {
            setTimeout(() => {
                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        });
    }
});