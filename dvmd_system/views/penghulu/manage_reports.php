<?php
// views/penghulu/manage_reports.php

// 1. Connection & Session
require_once __DIR__ . '/../../api/dbconnect.php';
$user = $_SESSION['user_data'] ?? null;

// Security Check
if (!$user || empty($user['village'])) {
    echo "<div style='padding:20px; color:red;'>Error: No village assigned to your account.</div>";
    exit;
}

$my_village = $conn->real_escape_string($user['village']);
$msg = "";
$msg_type = "";

// ---------------------------------------------------------
// 2. HANDLE ACTIONS (Verify / Reject / Resolve)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['incident_id'])) {
    $id = intval($_POST['incident_id']);
    $action = $_POST['action'];
    $new_status = "";

    switch ($action) {
        case 'verify':
            $new_status = 'Verified';
            $success_text = "Incident #$id has been verified.";
            break;
        case 'reject':
            $new_status = 'Rejected';
            $success_text = "Incident #$id has been rejected/flagged as false.";
            break;
        case 'resolve':
            $new_status = 'Resolved';
            $success_text = "Incident #$id marked as resolved/closed.";
            break;
    }

    if ($new_status) {
        $stmt = $conn->prepare("UPDATE incidents SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);
        
        if ($stmt->execute()) {
            $msg = $success_text;
            $msg_type = "success";
        } else {
            $msg = "Error updating database.";
            $msg_type = "error";
        }
    }
}

// ---------------------------------------------------------
// 3. FETCH DATA (With Filters)
// ---------------------------------------------------------
$filter = $_GET['filter'] ?? 'All';

// Base Query: specific to village, excluding Announcements
$sql = "SELECT i.*, u.name as reporter_name, u.phone, u.village 
        FROM incidents i 
        JOIN users u ON i.reported_by = u.id 
        WHERE u.village = '$my_village' 
        AND i.incident_type != 'Announcement'";

// Apply Filter
if ($filter === 'Pending') {
    $sql .= " AND i.status = 'Pending'";
} elseif ($filter === 'Verified') {
    // Verified usually implies active/in-progress
    $sql .= " AND i.status IN ('Verified', 'In Progress')";
} elseif ($filter === 'Resolved') {
    $sql .= " AND i.status = 'Resolved'";
}

$sql .= " ORDER BY i.created_at DESC";
$result = $conn->query($sql);
?>

<style>
/* Consistent Palette */
:root {
    --primary: #1a5f7a;
    --secondary: #57c5b6;
    --red: #e74c3c;
    --orange: #f39c12;
    --green: #2ecc71;
    --gray: #95a5a6;
    --dark: #2c3e50;
}

.page-header {
    background: white; padding: 20px; border-radius: 10px; 
    box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px;
    display: flex; justify-content: space-between; align-items: center;
}

/* Filter Tabs */
.filter-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
.tab-btn {
    padding: 8px 15px; border-radius: 20px; text-decoration: none; 
    font-size: 0.9rem; font-weight: 500; transition: all 0.3s;
    border: 1px solid transparent; color: #555; background: #e9ecef;
}
.tab-btn:hover { background: #dee2e6; }
.tab-btn.active {
    background: var(--primary); color: white; box-shadow: 0 2px 5px rgba(26, 95, 122, 0.3);
}

/* Table Styles */
.report-table-container {
    background: white; border-radius: 10px; overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.report-table { width: 100%; border-collapse: collapse; }
.report-table th { 
    background: #f8f9fa; text-align: left; padding: 15px; 
    color: var(--dark); border-bottom: 2px solid #eee; font-size: 0.9rem;
}
.report-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
.report-table tr:hover { background-color: #fcfcfc; }

/* Status Badges */
.badge { padding: 5px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
.badge-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
.badge-verified { background: #cce5ff; color: #004085; border: 1px solid #b8daff; }
.badge-resolved { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.badge-rejected { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

/* Action Buttons */
.btn-action {
    border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer;
    font-size: 0.8rem; margin-right: 5px; transition: 0.2s; color: white;
}
.btn-verify { background: var(--secondary); }
.btn-verify:hover { background: #46b0a2; }
.btn-reject { background: var(--red); }
.btn-reject:hover { background: #c0392b; }
.btn-resolve { background: var(--green); }
.btn-resolve:hover { background: #27ae60; }

@media (max-width: 768px) {
    .report-table thead { display: none; }
    .report-table, .report-table tbody, .report-table tr, .report-table td { display: block; width: 100%; }
    .report-table tr { margin-bottom: 15px; border: 1px solid #eee; border-radius: 10px; padding: 10px; }
    .report-table td { padding: 5px 0; text-align: right; border: none; }
    .report-table td::before { content: attr(data-label); float: left; font-weight: bold; color: #666; }
}
</style>

<div style="padding: 20px;">
    
    <div class="page-header">
        <div>
            <h2 style="margin:0; color: var(--primary);">Manage Incidents</h2>
            <p style="margin:5px 0 0; color:#666; font-size: 0.9rem;">Review and update incident reports for <strong><?php echo htmlspecialchars($my_village); ?></strong>.</p>
        </div>
        <div style="text-align: right;">
            <strong style="font-size: 1.5rem; color: var(--primary);"><?php echo $result->num_rows; ?></strong>
            <div style="font-size: 0.8rem; color: #888;">Records Found</div>
        </div>
    </div>

    <?php if ($msg): ?>
        <div style="padding: 15px; border-radius: 5px; margin-bottom: 20px; background: <?php echo $msg_type=='success'?'#d4edda':'#f8d7da'; ?>; color: <?php echo $msg_type=='success'?'#155724':'#721c24'; ?>;">
            <i class="fas <?php echo $msg_type=='success'?'fa-check-circle':'fa-exclamation-circle'; ?>"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="filter-tabs">
        <a href="?page=manage_reports&filter=All" class="tab-btn <?php echo $filter=='All'?'active':''; ?>">All Reports</a>
        <a href="?page=manage_reports&filter=Pending" class="tab-btn <?php echo $filter=='Pending'?'active':''; ?>">Pending</a>
        <a href="?page=manage_reports&filter=Verified" class="tab-btn <?php echo $filter=='Verified'?'active':''; ?>">Verified</a>
        <a href="?page=manage_reports&filter=Resolved" class="tab-btn <?php echo $filter=='Resolved'?'active':''; ?>">Resolved</a>
    </div>

    <div class="report-table-container">
        <?php if ($result->num_rows > 0): ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date & ID</th>
                        <th>Incident Type</th>
                        <th>Details & Location</th>
                        <th>Reported By</th>
                        <th>Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): 
                        // Status Badge Logic
                        $sClass = match($row['status']) {
                            'Pending' => 'badge-pending',
                            'Verified', 'In Progress' => 'badge-verified',
                            'Resolved' => 'badge-resolved',
                            'Rejected' => 'badge-rejected',
                            default => 'badge-pending'
                        };
                    ?>
                    <tr>
                        <td data-label="Date">
                            <strong><?php echo date('d M Y', strtotime($row['created_at'])); ?></strong><br>
                            <small style="color:#888;"><?php echo date('H:i', strtotime($row['created_at'])); ?></small><br>
                            <span style="font-size:0.8rem; color: var(--primary);">#INC<?php echo $row['id']; ?></span>
                        </td>
                        <td data-label="Type">
                            <strong style="color: var(--dark);"><?php echo htmlspecialchars($row['incident_type']); ?></strong><br>
                            <small style="color:<?php echo $row['severity']=='Critical'?'red':'orange'; ?>;">
                                ● <?php echo htmlspecialchars($row['severity']); ?>
                            </small>
                        </td>
                        <td data-label="Details">
                            <div style="max-width: 250px;">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </div>
                            <small style="display:block; margin-top:5px; color:#666;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['address']); ?>
                            </small>
                        </td>
                        <td data-label="Reporter">
                            <?php echo htmlspecialchars($row['reporter_name']); ?><br>
                            <a href="tel:<?php echo htmlspecialchars($row['phone']); ?>" style="color: var(--primary); font-size: 0.85rem; text-decoration: none;">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['phone']); ?>
                            </a>
                        </td>
                        <td data-label="Status">
                            <span class="badge <?php echo $sClass; ?>"><?php echo $row['status']; ?></span>
                        </td>
                        <td data-label="Actions">
                            <form method="POST">
                                <input type="hidden" name="incident_id" value="<?php echo $row['id']; ?>">
                                
                                <?php if ($row['status'] === 'Pending'): ?>
                                    <button type="submit" name="action" value="verify" class="btn-action btn-verify" title="Verify this is real">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn-action btn-reject" title="Reject as false" onclick="return confirm('Are you sure you want to reject this report?');">
                                        <i class="fas fa-times"></i>
                                    </button>
                                
                                <?php elseif ($row['status'] === 'Verified' || $row['status'] === 'In Progress'): ?>
                                    <button type="submit" name="action" value="resolve" class="btn-action btn-resolve" title="Mark as solved" onclick="return confirm('Mark this incident as Resolved?');">
                                        <i class="fas fa-check-double"></i> Mark Solved
                                    </button>
                                
                                <?php else: ?>
                                    <span style="color:#aaa; font-size:0.9rem;">No actions available</span>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 40px; text-align: center; color: #999;">
                <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
                <p>No records found for this filter.</p>
            </div>
        <?php endif; ?>
    </div>
</div>