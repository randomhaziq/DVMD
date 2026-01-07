// sos.js - Emergency SOS functionality
document.addEventListener('DOMContentLoaded', function () {
    console.log('SOS Emergency System Loaded');

    // DOM Elements
    const sosButton = document.getElementById('sosMainButton');
    const sosModal = document.getElementById('sosModal');
    const alertSentModal = document.getElementById('alertSentModal');
    const closeSosModalBtn = document.getElementById('closeSosModal');
    const cancelSosBtn = document.getElementById('cancelSos');
    const nextToConfirmBtn = document.getElementById('nextToConfirm');
    const goBackBtn = document.getElementById('goBack');
    const confirmSosNowBtn = document.getElementById('confirmSosNow');
    const locationText = document.getElementById('locationText');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const summaryType = document.getElementById('summaryType');
    const summaryLocation = document.getElementById('summaryLocation');
    const summaryInfo = document.getElementById('summaryInfo');
    const additionalInfo = document.getElementById('additionalInfo');
    const emergencyTypeSelect = document.getElementById('emergencyType');

    let locationInterval;
    let countdownInterval;
    let countdownValue = 5;

    // -------------------------
    // Utility Functions
    // -------------------------

    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const timeText = document.getElementById('timeText');
        const sentTime = document.getElementById('sentTime');
        if (timeText) timeText.textContent = timeString;
        if (sentTime) sentTime.textContent = timeString;
    }

    // -------------------------
    // Live Location
    // -------------------------
    function updateLocation() {
        if (!locationText) return;

        if (!navigator.geolocation) {
            locationText.textContent = 'Geolocation not supported';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude.toFixed(6);
                const lng = position.coords.longitude.toFixed(6);

                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                    .then(res => res.json())
                    .then(data => {
                        const address = data.display_name || `${lat}, ${lng}`;
                        locationText.textContent = address;
                        locationText.dataset.lat = lat;
                        locationText.dataset.lng = lng;
                    })
                    .catch(() => {
                        locationText.textContent = `${lat}, ${lng}`;
                        locationText.dataset.lat = lat;
                        locationText.dataset.lng = lng;
                    });
            },
            function (err) {
                switch (err.code) {
                    case err.PERMISSION_DENIED:
                        locationText.textContent = 'Location permission denied';
                        break;
                    case err.POSITION_UNAVAILABLE:
                        locationText.textContent = 'Location unavailable';
                        break;
                    case err.TIMEOUT:
                        locationText.textContent = 'Location request timed out';
                        break;
                    default:
                        locationText.textContent = 'Unable to detect location';
                }
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function startLocationUpdates() {
        updateLocation(); // immediately
        locationInterval = setInterval(updateLocation, 10000);
    }

    function stopLocationUpdates() {
        clearInterval(locationInterval);
    }

    // -------------------------
    // SOS Modal Control
    // -------------------------
    function openSosModal() {
        sosModal.style.display = 'flex';
        step1.style.display = 'block';
        step2.style.display = 'none';

        emergencyTypeSelect.value = '';
        additionalInfo.value = '';

        updateTime();

        if (isMobileDevice()) {
            setTimeout(() => {
                sosModal.scrollTop = 0;
                const modalContent = document.querySelector('.modal-content');
                if (modalContent) modalContent.scrollTop = 0;
            }, 100);
        }

        startLocationUpdates();
    }

    function closeSosModalFunc() {
        sosModal.style.display = 'none';
        clearInterval(countdownInterval);
        stopLocationUpdates();
    }

    // -------------------------
    // Countdown & Confirmation
    // -------------------------
    function startCountdown() {
        countdownValue = 5;
        countdownInterval = setInterval(() => {
            countdownValue--;
            document.getElementById('countdownNumber').textContent = countdownValue;
            document.getElementById('countdown').textContent = `Alert will send automatically in ${countdownValue} seconds...`;

            if (countdownValue <= 0) {
                clearInterval(countdownInterval);
                sendSOSAlert();
            }
        }, 1000);
    }

    // -------------------------
    // Send SOS Alert
    // -------------------------
    function sendSOSAlert() {
        closeSosModalFunc();

        const alertData = {
            type: emergencyTypeSelect.value,
            additional_info: additionalInfo.value,
            location: locationText.textContent,
            lat: locationText.dataset.lat || null,
            lng: locationText.dataset.lng || null
        };

        fetch('api/send_sos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(alertData)
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('alertId').textContent = data.success ? data.sos_id : 'SOS-' + Date.now();
            alertSentModal.style.display = 'flex';
        })
        .catch(() => {
            document.getElementById('alertId').textContent = 'SOS-' + Date.now();
            alertSentModal.style.display = 'flex';
        });
    }

    // -------------------------
    // Event Listeners
    // -------------------------
    if (sosButton) sosButton.addEventListener('click', openSosModal);
    if (closeSosModalBtn) closeSosModalBtn.addEventListener('click', closeSosModalFunc);
    if (cancelSosBtn) cancelSosBtn.addEventListener('click', closeSosModalFunc);

    if (nextToConfirmBtn) {
        nextToConfirmBtn.addEventListener('click', function () {
            if (!emergencyTypeSelect.value) {
                alert('Please select an emergency type.');
                return;
            }
            summaryType.textContent = emergencyTypeSelect.options[emergencyTypeSelect.selectedIndex].text;
            summaryLocation.textContent = locationText.textContent;
            summaryInfo.textContent = additionalInfo.value || 'None provided';
            step1.style.display = 'none';
            step2.style.display = 'block';
            startCountdown();
        });
    }

    if (goBackBtn) goBackBtn.addEventListener('click', function () {
        step2.style.display = 'none';
        step1.style.display = 'block';
        clearInterval(countdownInterval);
    });

    if (confirmSosNowBtn) confirmSosNowBtn.addEventListener('click', function () {
        clearInterval(countdownInterval);
        sendSOSAlert();
    });

    // Keyboard shortcut Alt+S
    document.addEventListener('keydown', function (e) {
        if (e.altKey && e.key.toLowerCase() === 's') {
            e.preventDefault();
            if (sosButton) sosButton.click();
        }
    });

    // Update time every second
    setInterval(updateTime, 1000);

    // Mobile improvements: scroll/focus handling
    if (isMobileDevice()) {
        const textarea = document.getElementById('additionalInfo');
        if (textarea) {
            textarea.addEventListener('focus', () => {
                setTimeout(() => { textarea.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 300);
            });
        }
    }
});
