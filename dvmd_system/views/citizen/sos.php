<?php
// sos.php - Emergency SOS Alert Page for Citizens
?>
<!-- SOS Alert Page Styles -->
<link rel="stylesheet" href="css/sos.css">
<script src="js/sos.js" defer></script>

<div class="sos-page-container">
    <div class="alert-header">
        <h2><i class="fas fa-exclamation-triangle"></i> Emergency SOS System</h2>
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
        </div>

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
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>EMERGENCY DETAILS</h3>
                <p class="mobile-friendly-text">Please provide details about your emergency. You'll have 5 seconds to
                    cancel after confirming.</p>

                <div class="alert-info">
                    <div class="info-row">
                        <span class="info-label">Your Location:</span>
                        <span class="info-value"
                            id="locationText"><?php echo htmlspecialchars($_SESSION['user_data']['village'] ?? 'Fetching location...'); ?></span>
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
                <div class="alert-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
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
            <div class="alert-sent-icon">
                <i class="fas fa-check-circle"></i>
            </div>
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

<script>
// SOS functionality
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const sosButton = document.getElementById('sosMainButton');
    const sosModal = document.getElementById('sosModal');
    const alertSentModal = document.getElementById('alertSentModal');
    const closeSosModal = document.getElementById('closeSosModal');
    const cancelSos = document.getElementById('cancelSos');
    const nextToConfirm = document.getElementById('nextToConfirm');
    const goBack = document.getElementById('goBack');
    const confirmSosNow = document.getElementById('confirmSosNow');
    const closeAlertModal = document.getElementById('closeAlertModal');
    const closeAlertBtn = document.getElementById('closeAlertBtn');
    const callEmergency = document.getElementById('callEmergency');
    const countdownEl = document.getElementById('countdown');
    const countdownNumber = document.getElementById('countdownNumber');
    const locationText = document.getElementById('locationText');
    const timeText = document.getElementById('timeText');
    const alertId = document.getElementById('alertId');
    const sentTime = document.getElementById('sentTime');

    // Step elements
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const summaryType = document.getElementById('summaryType');
    const summaryLocation = document.getElementById('summaryLocation');
    const summaryInfo = document.getElementById('summaryInfo');

    // Call buttons
    const callButtons = document.querySelectorAll('.contact-call-btn');

    // Variables
    let countdownInterval;
    let countdownValue = 5;

    // Update time in modal
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        if (timeText) timeText.textContent = timeString;
        if (sentTime) sentTime.textContent = timeString;
    }

    // Open SOS Modal (shows step 1)
    function openSosModal() {
        sosModal.style.display = 'flex';
        step1.style.display = 'block';
        step2.style.display = 'none';

        // Reset form
        document.getElementById('emergencyType').value = '';
        document.getElementById('additionalInfo').value = '';

        // Update time
        updateTime();
    }

    // Close SOS Modal
    function closeSosModalFunc() {
        sosModal.style.display = 'none';
        clearInterval(countdownInterval);
    }

    // Next to confirmation step
    nextToConfirm.addEventListener('click', function() {
        const emergencyType = document.getElementById('emergencyType').value;
        const additionalInfo = document.getElementById('additionalInfo').value;

        // Validate
        if (!emergencyType) {
            alert('Please select an emergency type.');
            return;
        }

        // Update summary
        const typeText = {
            'medical': 'Medical Emergency',
            'accident': 'Accident',
            'crime': 'Crime in Progress',
            'fire': 'Fire',
            'natural': 'Natural Disaster',
            'other': 'Other Emergency'
        } [emergencyType] || 'Unknown';

        summaryType.textContent = typeText;
        summaryLocation.textContent = locationText.textContent;
        summaryInfo.textContent = additionalInfo || 'None provided';

        // Show step 2
        step1.style.display = 'none';
        step2.style.display = 'block';

        // Start 5-second countdown
        startCountdown();
    });

    // Go back to step 1
    goBack.addEventListener('click', function() {
        step2.style.display = 'none';
        step1.style.display = 'block';
        clearInterval(countdownInterval);
    });

    // Start countdown
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

    // Confirm SOS now (manual send)
    confirmSosNow.addEventListener('click', function() {
        clearInterval(countdownInterval);
        sendSOSAlert();
    });

    //sendSOSAlert()
    function sendSOSAlert() {
        closeSosModalFunc();

        // Get form values
        const emergencyType = document.getElementById('emergencyType').value;
        const additionalInfo = document.getElementById('additionalInfo').value;
        const location = document.getElementById('locationText').textContent;

        // Show loading state
        showLoading(true);

        // Prepare data to send
        const alertData = {
            type: emergencyType,
            additional_info: additionalInfo,
            location: location
            // Note: user_id is handled by PHP session
        };

        // Send to server
        fetch('api/send_sos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(alertData)
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);

                if (data.success) {
                    // Update alert ID in success modal
                    document.getElementById('alertId').textContent = data.sos_id;

                    // Show success modal
                    setTimeout(() => {
                        showLoading(false);
                        alertSentModal.style.display = 'flex';
                    }, 500);

                } else {
                    // Show error message
                    showLoading(false);
                    alert('Error: ' + (data.error || 'Failed to save SOS alert'));
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                showLoading(false);

                // Show fallback modal with temporary ID
                const tempId = 'SOS-' + Date.now();
                document.getElementById('alertId').textContent = tempId;

                alert('Network error. Alert was created with ID: ' + tempId +
                    '. Please contact emergency services directly if needed.');

                setTimeout(() => {
                    alertSentModal.style.display = 'flex';
                }, 500);
            });
    }

    // Helper function to show/hide loading
    function showLoading(isLoading) {
        const confirmBtn = document.getElementById('confirmSosNow');
        const countdownEl = document.getElementById('countdown');

        if (isLoading) {
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SAVING...';
            confirmBtn.disabled = true;
            if (countdownEl) {
                countdownEl.textContent = 'Saving alert to database...';
            }
        } else {
            confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> SEND NOW';
            confirmBtn.disabled = false;
        }
    }

    // Close Alert Modal
    function closeAlertModalFunc() {
        alertSentModal.style.display = 'none';
    }

    // Call emergency number
    function callEmergencyFunc() {
        alert("In a real implementation, this would call 999 (Malaysian emergency number).");
        closeAlertModalFunc();
    }

    // Call contact
    function callContact(phoneNumber) {
        alert(`Calling ${phoneNumber}... (In a real app, this would initiate a phone call)`);
        // In real app: window.location.href = `tel:${phoneNumber}`;
    }

    // Event Listeners
    sosButton.addEventListener('click', openSosModal);
    closeSosModal.addEventListener('click', closeSosModalFunc);
    cancelSos.addEventListener('click', closeSosModalFunc);

    closeAlertModal.addEventListener('click', closeAlertModalFunc);
    closeAlertBtn.addEventListener('click', closeAlertModalFunc);
    callEmergency.addEventListener('click', callEmergencyFunc);

    // Contact call buttons
    callButtons.forEach(button => {
        button.addEventListener('click', function() {
            const phoneNumber = this.getAttribute('data-phone');
            callContact(phoneNumber);
        });
    });

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === sosModal) {
            closeSosModalFunc();
        }
        if (e.target === alertSentModal) {
            closeAlertModalFunc();
        }
    });

    // Update time every second
    setInterval(updateTime, 1000);

    // Add keyboard shortcut for SOS (Alt+S)
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key === 's' && !sosModal.style.display === 'flex') {
            e.preventDefault();
            sosButton.click();
        }
    });
});
</script>