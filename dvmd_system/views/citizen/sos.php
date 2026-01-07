<?php
// sos.php - Emergency SOS Alert Page for Citizens
?>
<!-- SOS Alert Page Styles -->
<link rel="stylesheet" href="css/sos.css">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<div class="sos-page-container">
    <div class="alert-header">
        <h2>Emergency SOS System</h2>
        <p class="alert-subtitle">In case of emergency, trigger the SOS button to send your location and alert
            information to emergency responders.</p>
    </div>

    <!-- Emergency Status Cards -->
    <div class="status-cards">
        <div class="status-card">
            <div class="status-icon location-active">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="status-info">
                <h3>Live Location</h3>
                <p>GPS tracking active</p>
            </div>
        </div>

        <div class="status-card">
            <div class="status-icon contacts-ready">
                <i class="fas fa-user-friends"></i>
            </div>
            <div class="status-info">
                <h3>Emergency Contacts</h3>
                <p><?php echo isset($_SESSION['emergency_contacts']) ? count($_SESSION['emergency_contacts']) : '3'; ?>
                    contacts</p>
            </div>
        </div>

        <div class="status-card">
            <div class="status-icon history-clean">
                <i class="fas fa-history"></i>
            </div>
            <div class="status-info">
                <h3>Alert History</h3>
                <p>No previous alerts</p>
            </div>
        </div>
    </div>

    <!-- SOS Trigger Area -->
    <div class="sos-trigger-area">
        <div class="sos-instructions">
            <div class="instruction-step">
                <div class="step-number">1</div>
                <p>Click the SOS button below</p>
            </div>
            <div class="instruction-step">
                <div class="step-number">2</div>
                <p>Enter emergency details</p>
            </div>
            <div class="instruction-step">
                <div class="step-number">3</div>
                <p>Confirm with 5-second countdown</p>
            </div>
        </div>

        <div class="sos-button-container" id="sosTriggerContainer">
            <div class="sos-pulse-ring"></div>
            <div class="sos-pulse-ring delay-1"></div>
            <button class="sos-main-button" id="sosMainButton">
                <div class="sos-button-inner">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="sos-text">SOS</span>
                </div>
                <div class="sos-button-label">EMERGENCY ALERT</div>
            </button>
        </div><br><br>

        <div class="sos-warning">
            <i class="fas fa-info-circle"></i>
            <p><strong>Warning:</strong> Only use this in genuine emergencies. False alerts may result in penalties.</p>
        </div>
    </div>

    <!-- Emergency Contacts Section -->
    <div class="contacts-section">
        <h3><i class="fas fa-address-book"></i> Emergency Contacts</h3>
        <div class="contacts-list">
            <?php
            $emergencyContacts = $_SESSION['emergency_contacts'] ?? [
                ['name' => 'Village Chief', 'phone' => '6012-345-6789', 'relation' => 'Authority'],
                ['name' => 'Family Member', 'phone' => '6013-456-7890', 'relation' => 'Family'],
                ['name' => 'Neighbor', 'phone' => '6014-567-8901', 'relation' => 'Neighbor']
            ];
            
            foreach ($emergencyContacts as $contact): ?>
            <div class="contact-card">
                <div class="contact-avatar">
                    <?php echo strtoupper(substr($contact['name'], 0, 2)); ?>
                </div>
                <div class="contact-details">
                    <h4><?php echo htmlspecialchars($contact['name']); ?></h4>
                    <p class="contact-relation"><?php echo htmlspecialchars($contact['relation']); ?></p>
                    <p class="contact-phone"><i class="fas fa-phone"></i>
                        <?php echo htmlspecialchars($contact['phone']); ?></p>
                </div>
                <button class="contact-call-btn"
                    data-phone="<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', $contact['phone'])); ?>">
                    <i class="fas fa-phone-alt"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Emergency Info -->
    <div class="emergency-info-section">
        <h3><i class="fas fa-history"></i> Emergency Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-ambulance"></i>
                </div>
                <div class="info-content">
                    <h4>Ambulance Number</h4>
                    <p>999 / 112</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-fire-extinguisher"></i>
                </div>
                <div class="info-content">
                    <h4>Fire & Rescue</h4>
                    <p>994 / 112</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="info-content">
                    <h4>Police</h4>
                    <p>999 / 112</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <div class="info-content">
                    <h4>Nearest Hospital</h4>
                    <p>Hospital <?php echo $_SESSION['user_data']['village'] ?? 'Local'; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SOS Modal -->
<div class="modal" id="sosModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-exclamation-triangle"></i> SOS EMERGENCY</h3>
            <button class="modal-close" id="closeSosModal">&times;</button>
        </div>
        <div class="modal-body">
            <!-- STEP 1: Emergency Details Form -->
            <div id="step1">
                <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h3>EMERGENCY DETAILS</h3>
                <p class="mobile-friendly-text">Please provide details about your emergency. You'll have 5 seconds to
                    cancel after confirming.</p>

                <div class="alert-info">
                    <div class="info-row">
                        <span class="info-label">Your Location:</span>
                        <span class="info-value" id="locationText">Detecting live location...</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Citizen:</span>
                        <span
                            class="info-value"><?php echo htmlspecialchars($_SESSION['user_data']['name'] ?? 'User'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Time:</span>
                        <span class="info-value" id="timeText"><?php echo date('H:i:s'); ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="emergencyType"><i class="fas fa-first-aid"></i> Emergency Type *</label>
                    <select id="emergencyType" class="form-control" required>
                        <option value="">Select emergency type</option>
                        <option value="medical">Medical Emergency</option>
                        <option value="accident">Accident</option>
                        <option value="crime">Crime in Progress</option>
                        <option value="fire">Fire</option>
                        <option value="natural">Natural Disaster</option>
                        <option value="other">Other Emergency</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="additionalInfo"><i class="fas fa-comment"></i> Additional Information</label>
                    <textarea id="additionalInfo" class="form-control" rows="3"
                        placeholder="Briefly describe the emergency, number of people involved, injuries, etc."></textarea>
                    <small class="form-text">Optional but helpful for responders</small>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelSos">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="nextToConfirm">
                        <i class="fas fa-arrow-right"></i> NEXT
                    </button>
                </div>
            </div>

            <!-- STEP 2: Confirmation with Countdown -->
            <div id="step2" style="display: none;">
                <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h3>CONFIRM SOS ALERT</h3>
                <p class="mobile-friendly-text"><strong>Alert will be sent in:</strong></p>

                <div class="countdown-display">
                    <div class="countdown-number" id="countdownNumber">5</div>
                    <div class="countdown-label">SECONDS</div>
                </div>

                <div class="countdown" id="countdown">Alert will send automatically in 5 seconds...</div>

                <div class="alert-summary">
                    <h4>Alert Summary:</h4>
                    <div class="summary-item">
                        <span>Emergency Type:</span>
                        <strong id="summaryType"></strong>
                    </div>
                    <div class="summary-item">
                        <span>Location:</span>
                        <strong id="summaryLocation"></strong>
                    </div>
                    <div class="summary-item">
                        <span>Additional Info:</span>
                        <strong id="summaryInfo" class="summary-info-text"></strong>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="goBack">
                        <i class="fas fa-arrow-left"></i> GO BACK
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmSosNow">
                        <i class="fas fa-paper-plane"></i> SEND NOW
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Sent Modal -->
<div class="modal" id="alertSentModal">
    <div class="modal-content">
        <div class="modal-header success">
            <h3 class="modal-title"><i class="fas fa-check-circle"></i> ALERT SENT</h3>
            <button class="modal-close" id="closeAlertModal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="alert-sent-icon"><i class="fas fa-check-circle"></i></div>
            <h3>SOS ALERT SENT SUCCESSFULLY</h3>
            <p>Your emergency alert has been sent to authorities and emergency contacts.</p>

            <div class="alert-details">
                <div class="detail-item">
                    <span class="detail-label">Alert ID:</span>
                    <span class="detail-value" id="alertId">SOS-<?php echo rand(100000, 999999); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Response ETA:</span>
                    <span class="detail-value">8-12 minutes</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Contacts Notified:</span>
                    <span
                        class="detail-value"><?php echo isset($_SESSION['emergency_contacts']) ? count($_SESSION['emergency_contacts']) : '3'; ?>
                        people</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Time Sent:</span>
                    <span class="detail-value" id="sentTime"><?php echo date('H:i:s'); ?></span>
                </div>
            </div>

            <div class="emergency-actions">
                <button type="button" class="btn btn-secondary" id="closeAlertBtn">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-danger" id="callEmergency">
                    <i class="fas fa-phone-alt"></i> CALL 999 NOW
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SOS Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const sosButton = document.getElementById('sosMainButton');
    const sosModal = document.getElementById('sosModal');
    const alertSentModal = document.getElementById('alertSentModal');
    const closeSosModalBtn = document.getElementById('closeSosModal');
    const cancelSosBtn = document.getElementById('cancelSos');
    const nextToConfirmBtn = document.getElementById('nextToConfirm');
    const goBackBtn = document.getElementById('goBack');
    const confirmSosNowBtn = document.getElementById('confirmSosNow');
    const closeAlertModalBtn = document.getElementById('closeAlertModal');
    const closeAlertBtn = document.getElementById('closeAlertBtn');
    const callEmergencyBtn = document.getElementById('callEmergency');
    const countdownEl = document.getElementById('countdown');
    const countdownNumber = document.getElementById('countdownNumber');
    const locationText = document.getElementById('locationText');
    const timeText = document.getElementById('timeText');
    const alertId = document.getElementById('alertId');
    const sentTime = document.getElementById('sentTime');

    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const summaryType = document.getElementById('summaryType');
    const summaryLocation = document.getElementById('summaryLocation');
    const summaryInfo = document.getElementById('summaryInfo');
    const callButtons = document.querySelectorAll('.contact-call-btn');

    let countdownInterval, countdownValue = 5;
    let locationInterval;

    // Helpers
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        timeText.textContent = timeString;
        sentTime.textContent = timeString;
    }

    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    // Live location
    function updateLocation() {
        if (!navigator.geolocation) {
            locationText.textContent = 'Geolocation not supported';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
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
            function(err) {
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
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    // Open modal
    function openSosModal() {
        sosModal.style.display = 'flex';
        step1.style.display = 'block';
        step2.style.display = 'none';
        document.getElementById('emergencyType').value = '';
        document.getElementById('additionalInfo').value = '';
        updateTime();
        updateLocation();
        locationInterval = setInterval(updateLocation, 10000);

        // Mobile scroll
        if (isMobileDevice()) {
            setTimeout(() => {
                sosModal.scrollTop = 0;
                const modalContent = document.querySelector('.modal-content');
                if (modalContent) modalContent.scrollTop = 0;
            }, 100);
        }
    }

    function closeSosModalFunc() {
        sosModal.style.display = 'none';
        clearInterval(countdownInterval);
        clearInterval(locationInterval);
    }

    function startCountdown() {
        countdownValue = 5;
        countdownNumber.textContent = countdownValue;
        countdownEl.textContent = `Alert will send automatically in ${countdownValue} seconds...`;

        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            countdownValue--;
            countdownNumber.textContent = countdownValue;
            countdownEl.textContent = `Alert will send automatically in ${countdownValue} seconds...`;

            if (countdownValue <= 0) {
                clearInterval(countdownInterval);
                sendSOSAlert();
            }
        }, 1000);
    }

    function sendSOSAlert() {
        closeSosModalFunc();

        const alertData = {
            type: document.getElementById('emergencyType').value,
            additional_info: document.getElementById('additionalInfo').value,
            location: locationText.textContent,
            lat: locationText.dataset.lat || null,
            lng: locationText.dataset.lng || null
        };

        showLoading(true);

        fetch('api/send_sos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(alertData)
            })
            .then(res => res.ok ? res.json() : Promise.reject(res.status))
            .then(data => {
                if (data.success) {
                    alertId.textContent = data.sos_id;
                    setTimeout(() => {
                        showLoading(false);
                        alertSentModal.style.display = 'flex';
                    }, 500);
                } else {
                    showLoading(false);
                    alert('Error: ' + (data.error || 'Failed to save SOS alert'));
                }
            })
            .catch(err => {
                console.error('Network error', err);
                showLoading(false);
                const tempId = 'SOS-' + Date.now();
                alertId.textContent = tempId;
                alert('Network error. Alert created with ID: ' + tempId +
                    '. Please contact emergency services directly.');
                setTimeout(() => {
                    alertSentModal.style.display = 'flex';
                }, 500);
            });
    }

    function showLoading(isLoading) {
        if (isLoading) {
            confirmSosNowBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SAVING...';
            confirmSosNowBtn.disabled = true;
            countdownEl.textContent = 'Saving alert to database...';
        } else {
            confirmSosNowBtn.innerHTML = '<i class="fas fa-paper-plane"></i> SEND NOW';
            confirmSosNowBtn.disabled = false;
        }
    }

    function closeAlertModalFunc() {
        alertSentModal.style.display = 'none';
    }

    function callEmergencyFunc() {
        alert("Calling 999...");
        closeAlertModalFunc();
    }

    function callContact(phone) {
        alert(`Calling ${phone}...`);
    }

    // Event listeners
    sosButton.addEventListener('click', openSosModal);
    closeSosModalBtn.addEventListener('click', closeSosModalFunc);
    cancelSosBtn.addEventListener('click', closeSosModalFunc);
    nextToConfirmBtn.addEventListener('click', () => {
        const type = document.getElementById('emergencyType').value;
        if (!type) {
            alert('Select emergency type');
            return;
        }
        summaryType.textContent = {
            'medical': 'Medical Emergency',
            'accident': 'Accident',
            'crime': 'Crime in Progress',
            'fire': 'Fire',
            'natural': 'Natural Disaster',
            'other': 'Other Emergency'
        } [type] || 'Unknown';
        summaryLocation.textContent = locationText.textContent;
        summaryInfo.textContent = document.getElementById('additionalInfo').value || 'None provided';
        step1.style.display = 'none';
        step2.style.display = 'block';
        startCountdown();
    });
    goBackBtn.addEventListener('click', () => {
        step2.style.display = 'none';
        step1.style.display = 'block';
        clearInterval(countdownInterval);
    });
    confirmSosNowBtn.addEventListener('click', () => {
        clearInterval(countdownInterval);
        sendSOSAlert();
    });
    closeAlertModalBtn.addEventListener('click', closeAlertModalFunc);
    closeAlertBtn.addEventListener('click', closeAlertModalFunc);
    callEmergencyBtn.addEventListener('click', callEmergencyFunc);
    callButtons.forEach(btn => btn.addEventListener('click', () => callContact(btn.dataset.phone)));
    window.addEventListener('click', (e) => {
        if (e.target === sosModal) closeSosModalFunc();
        if (e.target === alertSentModal) closeAlertModalFunc();
    });
    setInterval(updateTime, 1000);
    document.addEventListener('keydown', (e) => {
        if (e.altKey && e.key === 's' && sosModal.style.display !== 'flex') {
            e.preventDefault();
            sosButton.click();
        }
    });
});
</script>