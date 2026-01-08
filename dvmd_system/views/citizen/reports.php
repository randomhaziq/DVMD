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
    $address = trim($_POST['address'] ?? '');

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
    } elseif ($affected_count < 1) {
        $error = 'Please specify at least one affected person';
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
                address,
                severity,
                status,
                reported_by,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        if (!$stmt) {
            $error = 'Prepare failed: ' . $conn->error;
        } else {
            // Bind parameters: s = string, i = integer, d = double/float
            $stmt->bind_param(
                "ssiddsssi",
                $category,      
                $description,   
                $affected_count,
                $latitude,      
                $longitude,     
                $address,       
                $severity,      
                $status,        
                $reported_by    
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

            <!-- Location (lat, lng + editable address) -->
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
                    <!-- Hidden latitude and longitude inputs -->
                    <input type="hidden" name="latitude" id="latitude" required>
                    <input type="hidden" name="longitude" id="longitude" required>

                    <!-- Optional: a simple status for user feedback -->
                    <div class="location-status" id="locationStatus">
                        No location selected
                    </div>
                    <div class="location-field">
                        <label>Address</label>
                        <input type="text" name="address" id="address" placeholder="Click on map to get address"
                            value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                        <div style="font-size: 12px; color: #666; margin-top: 3px;">
                            You can edit the address for more detail
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Footer -->
            <div class="form-footer">
                <button type="button" class="btn btn-cancel" onclick="window.history.back()">
                    <i class="fas fa-times"></i> Cancel
                </button>

                <button type="submit" class="btn btn-submit"
                    style="background: #1a5f7a !important; color: white !important;">
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
let map, marker;
const defaultLat = 4.2105,
    defaultLng = 101.9758;

function initMap() {
    setTimeout(() => {
        map = L.map('map').setView([defaultLat, defaultLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        map.invalidateSize();

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const addressInput = document.getElementById('address');
        const locationStatus = document.getElementById('locationStatus');

        function setMarker(lat, lng) {
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng]).addTo(map);

            // 1. VISUAL FEEDBACK: Tell user we are working
            addressInput.value = "Fetching address...";
            locationStatus.textContent = "Locating...";
            addressInput.disabled = true; // Prevent typing while fetching

            // 2. FETCH: Use Backticks (`) for variables, not single quotes (')
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Nominatim API Error: " + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    // 3. SUCCESS: Update Input
                    const address = data.display_name || "Address not found";
                    
                    marker.bindPopup('<b>Incident Location</b><br>' + address).openPopup();
                    addressInput.value = address;
                    locationStatus.textContent = "Location found";
                    
                    // Debugging log (Check F12 console if it fails)
                    console.log("Address found:", address);
                })
                .catch(err => {
                    // 4. ERROR: Handle failure gracefully
                    console.error("Geocoding failed:", err);
                    marker.bindPopup('<b>Incident Location</b><br>Address lookup failed').openPopup();
                    
                    addressInput.value = ""; // Clear it so user can type manually
                    addressInput.placeholder = "Could not auto-load address. Please type manually.";
                    locationStatus.textContent = "Manual entry required";
                })
                .finally(() => {
                    // Re-enable input regardless of success/fail
                    addressInput.disabled = false;
                });
        }

        // Click on map
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            latInput.value = lat;
            lngInput.value = lng;
            locationStatus.textContent = "Location selected on map";

            setMarker(lat, lng);
        });

        // Existing coordinates (for editing)
        if (latInput.value && lngInput.value) {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            locationStatus.textContent = "Location selected on map";
            setMarker(lat, lng);
            map.setView([lat, lng], 15);
        }

        // Address search
        addressInput.addEventListener('blur', function() {
            if (this.value.trim() && (!latInput.value || !lngInput.value)) {
                findCoordinatesFromAddress(this.value);
            }
        });

        function findCoordinatesFromAddress(address) {
            if (!address.trim()) return;
            const originalValue = addressInput.value;
            addressInput.value = "Searching location...";
            addressInput.disabled = true;

            fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`
                )
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        latInput.value = lat;
                        lngInput.value = lng;
                        setMarker(lat, lng);
                        map.setView([lat, lng], 15);
                        addressInput.value = data[0].display_name || originalValue;
                        document.getElementById('locationStatus').textContent =
                            "Location found from address";
                    }
                    addressInput.disabled = false;
                })
                .catch(err => {
                    console.error(err);
                    addressInput.value = originalValue;
                    addressInput.disabled = false;
                });
        }
    }, 100);
}

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