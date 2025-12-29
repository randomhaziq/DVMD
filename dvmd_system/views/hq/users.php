<?php
// views/kplb_hq/users.php

// 1. Connect to Database
require_once 'api/dbconnect.php';

$message = "";
$messageType = "";

// 2. Handle Form Submissions (Add User)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    
    // HARDCODED ROLE: Only allow adding 'district'
    $role = 'district'; 
    $district = $_POST['district'];
    
    // RESTORED: Capture the village input
    $village = $_POST['village'] ?? ''; 

    // Check if email exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Error: Email already exists!";
        $messageType = "error";
    } else {
        // Hash Password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert User
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role, district, village) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashed_password, $phone, $role, $district, $village);
        
        if ($stmt->execute()) {
            $message = "District Officer registered successfully!";
            $messageType = "success";
            // Log action if function exists
            if (function_exists('logAction')) {
                logAction($_SESSION['user_id'] ?? 0, 'KPLB HQ', 'CREATE_USER', "Created District Officer: $email");
            }
        } else {
            $message = "Database Error: " . $conn->error;
            $messageType = "error";
        }
        $stmt->close();
    }
    $check->close();
}

// 3. Handle Delete Request
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    // Prevent deleting yourself
    if ($id != ($_SESSION['user_id'] ?? 0)) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "User deleted successfully.";
            $messageType = "success";
            if (function_exists('logAction')) {
                logAction($_SESSION['user_id'] ?? 0, 'KPLB HQ', 'DELETE_USER', "Deleted user ID: $id");
            }
        } else {
            $message = "Error deleting user.";
            $messageType = "error";
        }
        $stmt->close();
    } else {
        $message = "You cannot delete your own account!";
        $messageType = "error";
    }
}

// 4. Fetch ONLY District Officers
// Filter query to only show role = 'district'
$sql = "SELECT * FROM users WHERE role = 'district' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<style>
    /* ... (Styles kept consistent) ... */
    .users-container { padding: 20px; }
    .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn-add { background-color: #2c3e50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
    .btn-add:hover { background-color: #1a252f; }
    .user-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .user-table th, .user-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
    .user-table th { background-color: #f8f9fa; color: #333; font-weight: 600; }
    .role-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500; text-transform: capitalize; }
    .role-district { background-color: #f3e5f5; color: #7b1fa2; }
    .add-user-form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; display: none; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
    .form-control { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    .btn-submit { background-color: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; }
    .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    .alert-success { background-color: #d4edda; color: #155724; }
    .alert-error { background-color: #f8d7da; color: #721c24; }
</style>

<div class="users-container">
    
    <div class="action-bar">
        <h2>District Officers Management</h2>
        <button class="btn-add" onclick="toggleForm()">
            <i class="fas fa-plus"></i> Add New Officer
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div id="addUserForm" class="add-user-form">
        <h3>Register New District Officer</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_user">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" class="form-control" value="District Officer" disabled style="background: #eee;">
                    <input type="hidden" name="role" value="district">
                </div>

                <div class="form-group">
                    <label>Assigned District</label>
                    <input type="text" name="district" class="form-control" placeholder="e.g. Raub" required>
                </div>

                <div class="form-group">
                    <label>Village</label>
                    <input type="text" name="village" class="form-control" placeholder="e.g. Kampung Ulu Gali" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Register District Officer</button>
        </form>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Assigned District</th>
                <th>Village</th> <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $counter = 1;
            
            if ($result->num_rows > 0): 
                while($row = $result->fetch_assoc()): 
            ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <span class="role-badge role-district">
                            District Officer
                        </span>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['district']); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['village'] ?? '-'); ?>
                    </td>
                    <td>
                        <a href="?page=users&delete_id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this officer?');" 
                           style="color: #e74c3c; text-decoration: none;">
                           <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No District Officers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleForm() {
        var form = document.getElementById('addUserForm');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>