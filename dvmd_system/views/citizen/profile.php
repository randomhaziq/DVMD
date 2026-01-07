<?php
require_once __DIR__ . '/../../api/dbconnect.php';

// Get user data from session
$user = $_SESSION['user_data'] ?? null;
$error = '';
$success = '';

// Redirect if not logged in
if (!$user) {
    header('Location: /login.php');
    exit();
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -----------------------
    // Update profile info
    // -----------------------
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } elseif (empty($name)) {
            $error = 'Please enter your name';
        } else {
            // Check if email exists for another user
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            if (!$check_stmt) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $check_stmt->bind_param("si", $email, $user['id']);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error = 'Email already exists.';
            } else {
                $update_stmt = $conn->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                if (!$update_stmt) {
                    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
                $update_stmt->bind_param("sssi", $name, $email, $phone, $user['id']);

                if ($update_stmt->execute()) {
                    $_SESSION['user_data']['name'] = $name;
                    $_SESSION['user_data']['email'] = $email;
                    $_SESSION['user_data']['phone'] = $phone;
                    $user = $_SESSION['user_data'];
                    $success = 'Profile updated successfully!';
                } else {
                    $error = 'Failed to update profile: ' . $update_stmt->error;
                }
                $update_stmt->close();
            }
            $check_stmt->close();
        }
    }

    // -----------------------
    // Change password
    // -----------------------
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password)) {
            $error = 'Please enter your current password';
        } elseif (strlen($new_password) < 8) {
            $error = 'New password must be at least 8 characters';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Fetch current hashed password
            $stmt = $conn->prepare("SELECT `password` FROM users WHERE id = ?");
            if (!$stmt) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();

            // Verify current password
            if (!password_verify($current_password, $hashed_password)) {
                $error = 'Current password is incorrect';
            } else {
                // Hash new password
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in DB
                $update_stmt = $conn->prepare("
                    UPDATE users 
                    SET `password` = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                if (!$update_stmt) {
                    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }
                $update_stmt->bind_param("si", $new_hashed, $user['id']);

                if ($update_stmt->execute()) {
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Failed to change password: ' . $update_stmt->error;
                }
                $update_stmt->close();
            }
        }
    }
}
?>


<!-- JUST THE FORM CONTENT STARTS HERE - NO HTML/HEAD/BODY TAGS -->
<!-- Welcome message area -->
<div class="welcome-message">
    <h2>My Profile</h2>
    <p>Manage your account information and security settings.</p>

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

<!-- Main Profile Container -->
<div class="profile-container">
    <div class="profile-content">
        <div class="profile-sections">

            <!-- Profile Information Section -->
            <div class="profile-section">
                <div class="section-header">
                    <h3>Personal Information</h3>
                </div>

                <form method="POST" id="profileForm" class="profile-form">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Name <span class="required"></span>
                        </label>
                        <input type="text" name="name" class="form-control"
                            value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i> Email Address <span class="required"></span>
                        </label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                            placeholder="your.email@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i> Phone Number
                        </label>
                        <input type="tel" name="phone" class="form-control"
                            value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="0123456789">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user-tag"></i> User Role
                        </label>
                        <div class="role-display">
                            <?php 
                            $role = $user['role'] ?? 'citizen';
                            $role_names = [
                                'citizen' => 'Citizen',
                                'responder' => 'Emergency Responder',
                                'admin' => 'Administrator'
                            ];
                            echo htmlspecialchars($role_names[$role] ?? ucfirst($role));
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt"></i> Account Created
                        </label>
                        <div class="date-display">
                            <?php 
                            $created_at = $user['created_at'] ?? '';
                            if ($created_at) {
                                echo date('F j, Y', strtotime($created_at));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Section -->
            <div class="profile-section">
                <div class="section-header">
                    <h3>Change Password</h3>
                </div>

                <form method="POST" id="passwordForm" class="profile-form">
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i> Current Password <span class="required">*</span>
                        </label>
                        <div class="password-input">
                            <input type="password" name="current_password" id="currentPassword" class="form-control"
                                placeholder="Enter current password" required>
                            <button type="button" class="password-toggle" data-target="currentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i> New Password <span class="required">*</span>
                        </label>
                        <div class="password-input">
                            <input type="password" name="new_password" id="newPassword" class="form-control"
                                placeholder="Enter new password (min. 8 characters)" required>
                            <button type="button" class="password-toggle" data-target="newPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i> Confirm New Password <span class="required">*</span>
                        </label>
                        <div class="password-input">
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control"
                                placeholder="Confirm new password" required>
                            <button type="button" class="password-toggle" data-target="confirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-match" id="passwordMatch"></div>
                    </div>

                    <div class="password-requirements">
                        <p><strong>Password Requirements:</strong></p>
                        <ul>
                            <li><i class="fas fa-check"></i> At least 8 characters long</li>
                            <li><i class="fas fa-check"></i> Use a mix of letters, numbers, and symbols</li>
                            <li><i class="fas fa-check"></i> Avoid common words and patterns</li>
                        </ul>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- INLINE SCRIPT (not separate file) -->
<script>
// Password toggle functionality
document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});

// Password strength checker
const newPasswordInput = document.getElementById('newPassword');
const passwordStrength = document.getElementById('passwordStrength');
const confirmPasswordInput = document.getElementById('confirmPassword');
const passwordMatch = document.getElementById('passwordMatch');

function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = '';
    let color = '#e74c3c'; // Red

    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    switch (strength) {
        case 0:
        case 1:
            feedback = 'Very Weak';
            color = '#e74c3c';
            break;
        case 2:
            feedback = 'Weak';
            color = '#e67e22';
            break;
        case 3:
            feedback = 'Good';
            color = '#f1c40f';
            break;
        case 4:
            feedback = 'Strong';
            color = '#2ecc71';
            break;
    }

    return {
        strength,
        feedback,
        color
    };
}

newPasswordInput.addEventListener('input', function() {
    const password = this.value;
    if (password.length === 0) {
        passwordStrength.innerHTML = '';
        return;
    }

    const result = checkPasswordStrength(password);
    passwordStrength.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 100px; height: 5px; background: #eee; border-radius: 3px; overflow: hidden;">
                <div style="width: ${result.strength * 25}%; height: 100%; background: ${result.color};"></div>
            </div>
            <span style="color: ${result.color}; font-weight: 500;">${result.feedback}</span>
        </div>
    `;

    // Also check password match
    checkPasswordMatch();
});

// Password match checker
function checkPasswordMatch() {
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (confirmPassword.length === 0) {
        passwordMatch.innerHTML = '';
        return;
    }

    if (newPassword === confirmPassword) {
        passwordMatch.innerHTML = '<span style="color: #2ecc71;"><i class="fas fa-check"></i> Passwords match</span>';
    } else {
        passwordMatch.innerHTML =
            '<span style="color: #e74c3c;"><i class="fas fa-times"></i> Passwords do not match</span>';
    }
}

confirmPasswordInput.addEventListener('input', checkPasswordMatch);

// Form validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long');
        return;
    }

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match');
        return;
    }

    if (newPassword === currentPassword) {
        e.preventDefault();
        alert('New password must be different from current password');
        return;
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Add any initialization code here
}); <
!--END OF CONTENT - NO CLOSING HTML / BODY TAGS-- >