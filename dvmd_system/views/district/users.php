<?php
// views/district/users.php

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
    $role = $_POST['role'];
    $district = $_POST['district'];
    $village = $_POST['village'];

    // SECURITY CHECK: Ensure District Officer cannot create HQ or other District Officers
    $allowed_roles = ['citizen', 'ketua_kampung', 'penghulu'];
    
    if (!in_array($role, $allowed_roles)) {
        $message = "Error: You are not authorized to create this role.";
        $messageType = "error";
    } else {
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
                $message = "User registered successfully!";
                $messageType = "success";
                if (function_exists('logAction')) {
                    logAction($_SESSION['user_id'] ?? 0, 'District', 'CREATE_USER', "Created $role: $email");
                }
            } else {
                $message = "Database Error: " . $conn->error;
                $messageType = "error";
            }
            $stmt->close();
        }
        $check->close();
    }
}

// 3. Handle Delete Request
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    // Security: Check the role of the user being deleted first
    // You shouldn't be able to delete an HQ user by accident via URL hacking
    $checkRole = $conn->query("SELECT role FROM users WHERE id = $id")->fetch_assoc();
    
    if ($checkRole && in_array($checkRole['role'], ['citizen', 'ketua_kampung', 'penghulu'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "User deleted successfully.";
            $messageType = "success";
            if (function_exists('logAction')) {
                logAction($_SESSION['user_id'] ?? 0, 'District', 'DELETE_USER', "Deleted user ID: $id");
            }
        } else {
            $message = "Error deleting user.";
            $messageType = "error";
        }
        $stmt->close();
    } else {
        $message = "Error: You can only delete Citizens, Penghulu, or Ketua Kampung.";
        $messageType = "error";
    }
}

// 4. Fetch Users List (FILTERED)
// We use WHERE IN (...) to only get the specific roles allowed for District view
$sql = "SELECT * FROM users 
        WHERE role IN ('citizen', 'ketua_kampung', 'penghulu') 
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<style>
    /* ... (Styles remain the same) ... */
    .users-container { padding: 20px; }
    .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .btn-add { background-color: #2c3e50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
    .btn-add:hover { background-color: #1a252f; }
    .user-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .user-table th, .user-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
    .user-table th { background-color: #f8f9fa; color: #333; font-weight: 600; }
    .role-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500; text-transform: capitalize; }
    .role-citizen { background-color: #e3f2fd; color: #1565c0; }
    .role-ketua_kampung { background-color: #e8f5e9; color: #2e7d32; }
    .role-penghulu { background-color: #fff3e0; color: #ef6c00; }
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
        <h2>Village & Community Management</h2>
        <button class="btn-add" onclick="toggleForm()">
            <i class="fas fa-plus"></i> Add New User
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div id="addUserForm" class="add-user-form">
        <h3>Register New User</h3>
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
                    <select name="role" class="form-control" required>
                        <option value="citizen">Citizen</option>
                        <option value="ketua_kampung">Ketua Kampung</option>
                        <option value="penghulu">Penghulu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>District</label>
                    <input type="text" name="district" class="form-control" placeholder="e.g. Raub">
                </div>
                <div class="form-group">
                    <label>Village</label>
                    <input type="text" name="village" class="form-control" placeholder="e.g. Kampung Gali" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Register User</button>
        </form>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>No.</th> <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Counter for sequential numbering
            $counter = 1;

            if ($result->num_rows > 0): 
                while($row = $result->fetch_assoc()): 
            ?>
                <tr>
                    <td><?php echo $counter++; ?></td> <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <span class="role-badge role-<?php echo strtolower(str_replace(' ', '_', $row['role'])); ?>">
                            <?php echo htmlspecialchars($row['role']); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                            echo htmlspecialchars($row['district']); 
                            if($row['village']) echo ", " . htmlspecialchars($row['village']); 
                        ?>
                    </td>
                    <td>
                        <a href="?page=users&delete_id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this user?');" 
                           style="color: #e74c3c; text-decoration: none;">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No users found in this category.</td>
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