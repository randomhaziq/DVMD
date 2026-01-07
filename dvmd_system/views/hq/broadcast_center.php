<?php
// views/kplb_hq/broadcast_center.php

// 1. Database Connection
$rootPath = $_SERVER['DOCUMENT_ROOT'] . '/dvmd_system';
require_once $rootPath . '/dbconnect.php';

$msg = "";
$msgType = "";

// 2. Handle POST (Create Broadcast)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_broadcast') {
    $title = trim($_POST['title']);
    $messageBody = trim($_POST['message']);
    $target = $_POST['target'];
    $admin_id = $_SESSION['user_data']['id'] ?? 0;

    if (!empty($title) && !empty($messageBody)) {
        $stmt = $conn->prepare("INSERT INTO broadcasts (title, message, target_audience, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $messageBody, $target, $admin_id);
        
        if ($stmt->execute()) {
            $msg = "Broadcast posted successfully!";
            $msgType = "success";
            if (function_exists('logAction')) {
                logAction($admin_id, 'HQ', 'BROADCAST', "Posted: $title");
            }
        } else {
            $msg = "Error posting broadcast.";
            $msgType = "error";
        }
    }
}

// 3. Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM broadcasts WHERE id = $id");
    $msg = "Message deleted.";
    $msgType = "success";
}

// 4. Fetch History
$history = $conn->query("SELECT * FROM broadcasts ORDER BY created_at DESC");
?>

<div class="broadcast-container" style="padding: 20px;">
    <h2><i class="fas fa-bullhorn"></i> Broadcast Center</h2>
    <p style="color: #666;">Send alerts, announcements, and warnings to all users.</p>

    <?php if ($msg): ?>
        <div style="padding: 10px; margin-bottom: 20px; border-radius: 5px; color: white; background: <?php echo $msgType=='success'?'#2ecc71':'#e74c3c'; ?>;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px;">
        
        <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); height: fit-content;">
            <h3 style="margin-top: 0; color: #2c3e50;">Compose Message</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_broadcast">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Title / Headline</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Heavy Rain Warning" required 
                           style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Message Details</label>
                    <textarea name="message" rows="5" class="form-control" placeholder="Enter full details here..." required
                              style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Target Audience</label>
                    <select name="target" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="All">All Users (Public)</option>
                        <option value="District">District Officers Only</option>
                        <option value="Raub">Residents of Raub</option>
                    </select>
                </div>

                <button type="submit" style="width: 100%; background: #2c3e50; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                    <i class="fas fa-paper-plane"></i> Publish Broadcast
                </button>
            </form>
        </div>

        <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #2c3e50;">Active Announcements</h3>
            
            <?php if ($history->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; text-align: left;">
                            <th style="padding: 10px;">Date</th>
                            <th style="padding: 10px;">Message</th>
                            <th style="padding: 10px;">Target</th>
                            <th style="padding: 10px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $history->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px; font-size: 0.9rem; color: #666; white-space: nowrap;">
                                <?php echo date('d M Y', strtotime($row['created_at'])); ?><br>
                                <?php echo date('h:i A', strtotime($row['created_at'])); ?>
                            </td>
                            <td style="padding: 12px;">
                                <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                <span style="font-size: 0.9rem; color: #555;"><?php echo htmlspecialchars($row['message']); ?></span>
                            </td>
                            <td style="padding: 12px;">
                                <span style="background: #e3f2fd; color: #1565c0; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">
                                    <?php echo htmlspecialchars($row['target_audience']); ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <a href="?page=broadcast_center&delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this message?')" style="color: #e74c3c;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #888; text-align: center;">No active broadcasts.</p>
            <?php endif; ?>
        </div>

    </div>
</div>