<?php
require_once __DIR__ . '/../../api/dbconnect.php';

// Get user data from session
$user = $_SESSION['user_data'] ?? null;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $category = $_POST['category'] ?? '';
    $severity = $_POST['severity'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $affected_count = (int)($_POST['affected_count'] ?? 0);
    $latitude = (float)($_POST['latitude'] ?? 0);
    $longitude = (float)($_POST['longitude'] ?? 0);

    // Get user ID (or 0 if not logged in)
    $reported_by = $user['id'] ?? 0;
    $status = 'Pending';
    
    // Validation
    if (empty($category)) {
        $error = 'Please select an incident category';
    } elseif (empty($severity)) {
        $error = 'Please select a severity level';
    } elseif (empty($description)) {
        $error = 'Please provide a description';
    } elseif (strlen($description) < 10) {
        $error = 'Description must be at least 10 characters';
    } elseif ($affected_count < 0) {
        $error = 'Please enter a valid number of affected people';
    } elseif (empty($latitude) || empty($longitude)) {
        $error = 'Please select a location on the map';
    } else {
        // Prepare insert
        $stmt = $conn->prepare("
            INSERT INTO incidents (
                incident_type,
                description,
                affected,
                lat,
                lng,
                severity,
                status,
                reported_by,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        if (!$stmt) {
            $error = 'Prepare failed: ' . $conn->error;
        } else {
            // Bind parameters: s = string, i = integer, d = double/float
            $stmt->bind_param(
                "ssidsssi",
                $category,      // string
                $description,   // string
                $affected_count,// integer
                $latitude,      // double
                $longitude,     // double
                $severity,      // string
                $status,        // string
                $reported_by    // integer
            );

            if ($stmt->execute()) {
                $success = 'Incident report submitted successfully! Report ID: INC' . $stmt->insert_id;
                $_POST = []; // Clear form
            } else {
                $error = 'Execute failed: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<!-- JUST THE FORM CONTENT STARTS HERE - NO HTML/HEAD/BODY TAGS -->
<!-- Welcome message area -->
<div class="welcome-message">
    <h2>Report Emergency Incident</h2>
    <p>Fill out the form below to report an emergency incident in your area.</p>
    <?php if ($error || $success): ?>
    <div style="margin-top: 15px;">
        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div><?php echo htmlspecialchars($success); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Main Form Container -->
<div class="report-form-container">
    <div class="report-form-content">
        <form method="POST" id="incidentForm">
            <!-- Incident Category -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-list"></i> Incident Category <span class="required">*</span>
                </label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="category" value="Flood"
                            <?php echo ($_POST['category'] ?? '') === 'Flood' ? 'checked' : ''; ?> required>
                        <div class="radio-content">
                            <div class="radio-icon flood">
                                <i class="fas fa-water"></i>
                            </div>
                            <span class="radio-text">Flood</span>
                        </div>
                    </label>

                    <label class="radio-label">
                        <input type="radio" name="category" value="Fire"
                            <?php echo ($_POST['category'] ?? '') === 'Fire' ? 'checked' : ''; ?>>
                        <div class="radio-content">
                            <div class="radio-icon fire">
                                <i class="fas fa-fire"></i>
                            </div>
                            <span class="radio-text">Fire</span>
                        </div>
                    </label>

                    <label class="radio-label">
                        <input type="radio" name="category" value="Landslide"
                            <?php echo ($_POST['category'] ?? '') === 'Landslide' ? 'checked' : ''; ?>>
                        <div class="radio-content">
                            <div class="radio-icon landslide">
                                <i class="fas fa-mountain"></i>
                            </div>
                            <span class="radio-text">Landslide</span>
                        </div>
                    </label>

                    <label class="radio-label">
                        <input type="radio" name="category" value="Accident"
                            <?php echo ($_POST['category'] ?? '') === 'Accident' ? 'checked' : ''; ?>>
                        <div class="radio-content">
                            <div class="radio-icon accident">
                                <i class="fas fa-car-crash"></i>
                            </div>
                            <span class="radio-text">Accident</span>
                        </div>
                    </label>

                    <label class="radio-label">
                        <input type="radio" name="category" value="Others"
                            <?php echo ($_POST['category'] ?? '') === 'Others' ? 'checked' : ''; ?>>
                        <div class="radio-content">
                            <div class="radio-icon others">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <span class="radio-text">Others</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Severity Level -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-exclamation-triangle"></i> Severity Level <span class="required">*</span>
                </label>
                <select name="severity" class="form-control" required>
                    <option value="">-- Select Severity --</option>
                    <option value="Low" <?php echo ($_POST['severity'] ?? '') === 'Low' ? 'selected' : ''; ?>>Low
                    </option>
                    <option value="Medium" <?php echo ($_POST['severity'] ?? '') === 'Medium' ? 'selected' : ''; ?>>
                        Medium</option>
                    <option value="High" <?php echo ($_POST['severity'] ?? '') === 'High' ? 'selected' : ''; ?>>High
                    </option>
                    <option value="Critical" <?php echo ($_POST['severity'] ?? '') === 'Critical' ? 'selected' : ''; ?>>
                        Critical</option>
                </select>
            </div>


            <!-- Details (description) -->
            <div class="form-group">
                <label class="form-label" for="description">
                    <i class="fas fa-align-left"></i> Details (Description) <span class="required">*</span>
                </label>
                <textarea class="form-control" id="description" name="description"
                    placeholder="Describe the incident in detail. Include time, severity, and any immediate dangers..."
                    required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <div style="font-size: 13px; color: #666; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> Please provide as much detail as possible
                </div>
            </div>

            <!-- 3. How many are affected? -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-users"></i> How many are affected? <span class="required">*</span>
                </label>
                <div class="counter-input">
                    <button type="button" class="counter-btn" id="decreaseBtn">-</button>
                    <div class="counter-display" id="counterValue">0</div>
                    <button type="button" class="counter-btn" id="increaseBtn">+</button>
                </div>
                <input type="hidden" name="affected_count" id="affectedCount"
                    value="<?php echo htmlspecialchars($_POST['affected_count'] ?? '0'); ?>" required>
            </div>

            <!-- 4. Location (lat, lng) -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-map-marker-alt"></i> Location <span class="required">*</span>
                </label>
                <p style="color: #666; margin-bottom: 10px; font-size: 13px;">
                    Click on the map to mark the incident location
                </p>

                <div class="map-container">
                    <div id="map"></div>
                </div>

                <div class="location-display">
                    <div class="location-field">
                        <label>Latitude</label>
                        <div class="location-value" id="latDisplay">Not selected</div>
                        <input type="hidden" name="latitude" id="latitude"
                            value="<?php echo htmlspecialchars($_POST['latitude'] ?? ''); ?>" required>
                    </div>
                    <div class="location-field">
                        <label>Longitude</label>
                        <div class="location-value" id="lngDisplay">Not selected</div>
                        <input type="hidden" name="longitude" id="longitude"
                            value="<?php echo htmlspecialchars($_POST['longitude'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="form-footer">
                <button type="button" class="btn btn-cancel" onclick="window.history.back()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- INLINE SCRIPT (not separate file) -->
<script>
// Counter functionality
let counter = <?php echo (int)($_POST['affected_count'] ?? 0); ?>;
const counterValue = document.getElementById('counterValue');
const affectedCount = document.getElementById('affectedCount');

function updateCounter() {
    counterValue.textContent = counter;
    affectedCount.value = counter;
}

document.getElementById('increaseBtn').addEventListener('click', () => {
    counter++;
    updateCounter();
});

document.getElementById('decreaseBtn').addEventListener('click', () => {
    if (counter > 0) {
        counter--;
        updateCounter();
    }
});

// Initialize map
let map;
let marker;

// Default coordinates (Malaysia center)
const defaultLat = 4.2105;
const defaultLng = 101.9758;

// Initialize map
function initMap() {
    map = L.map('map').setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Click handler for map
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        // Update display
        document.getElementById('latDisplay').textContent = lat.toFixed(6);
        document.getElementById('lngDisplay').textContent = lng.toFixed(6);

        // Update hidden inputs
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        // Remove existing marker
        if (marker) {
            map.removeLayer(marker);
        }

        // Add new marker
        marker = L.marker([lat, lng]).addTo(map)
            .bindPopup('Incident Location<br>Lat: ' + lat.toFixed(6) + '<br>Lng: ' + lng.toFixed(6))
            .openPopup();
    });

    // If there's existing location data, show marker
    const existingLat = document.getElementById('latitude').value;
    const existingLng = document.getElementById('longitude').value;

    if (existingLat && existingLng) {
        const lat = parseFloat(existingLat);
        const lng = parseFloat(existingLng);

        document.getElementById('latDisplay').textContent = lat.toFixed(6);
        document.getElementById('lngDisplay').textContent = lng.toFixed(6);

        marker = L.marker([lat, lng]).addTo(map)
            .bindPopup('Incident Location<br>Lat: ' + lat.toFixed(6) + '<br>Lng: ' + lng.toFixed(6))
            .openPopup();

        map.setView([lat, lng], 15);
    }
}

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', initMap);

// Form validation
document.getElementById('incidentForm').addEventListener('submit', function(e) {
    const category = document.querySelector('input[name="category"]:checked');
    const description = document.getElementById('description').value.trim();
    const latitude = document.getElementById('latitude').value;

    if (!category) {
        e.preventDefault();
        alert('Please select an incident category');
        return;
    }

    if (description.length < 10) {
        e.preventDefault();
        alert('Description must be at least 10 characters');
        return;
    }

    if (!latitude) {
        e.preventDefault();
        alert('Please select a location on the map');
        return;
    }
});
</script>
<!-- END OF CONTENT - NO CLOSING HTML/BODY TAGS -->