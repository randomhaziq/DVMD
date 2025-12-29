<?php
// 1. Connect to Database
require_once 'api/dbconnect.php';

// 2. Fetch Logs (Newest first)
// We join with the users table to get the name of the person who performed the action
$sql = "SELECT logs.*, users.name as actor_name 
        FROM audit_logs logs 
        LEFT JOIN users ON logs.user_id = users.id 
        ORDER BY logs.created_at DESC 
        LIMIT 50"; // Limit to last 50 actions for performance

$result = $conn->query($sql);
?>

<div class="audit-container" style="padding: 20px;">
    <div class="audit-header" style="margin-bottom: 20px;">
        <h2><i class="fas fa-shield-alt"></i> System Audit Logs</h2>
        <p style="color: #666;">Tracking the last 50 system activities for PDPA compliance.</p>
    </div>

    <style>
        .log-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            font-size: 0.9rem;
        }
        .log-table th, .log-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .log-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #444;
        }
        .log-table tr:hover { background-color: #fcfcfc; }
        
        /* Badges for Action Types */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .bg-login { background-color: #e3f2fd; color: #0d47a1; } /* Blue */
        .bg-create { background-color: #e8f5e9; color: #1b5e20; } /* Green */
        .bg-delete { background-color: #ffebee; color: #b71c1c; } /* Red */
        .bg-update { background-color: #fff3e0; color: #e65100; } /* Orange */
        .bg-default { background-color: #f5f5f5; color: #616161; } /* Grey */
    </style>

    <table class="log-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>Actor</th>
                <th>Role</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    // Determine badge color
                    $badgeClass = 'bg-default';
                    if (strpos($row['action_type'], 'LOGIN') !== false) $badgeClass = 'bg-login';
                    if (strpos($row['action_type'], 'CREATE') !== false) $badgeClass = 'bg-create';
                    if (strpos($row['action_type'], 'DELETE') !== false) $badgeClass = 'bg-delete';
                ?>
                <tr>
                    <td style="white-space: nowrap; color: #666;">
                        <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['actor_name'] ?? 'Unknown'); ?></strong>
                    </td>
                    <td>
                        <small style="color: #888;"><?php echo htmlspecialchars($row['role']); ?></small>
                    </td>
                    <td>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <?php echo htmlspecialchars($row['action_type']); ?>
                        </span>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($row['details']); ?>
                    </td>
                    <td style="font-family: monospace; color: #666;">
                        <?php echo htmlspecialchars($row['ip_address']); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #888;">
                        No logs found yet. Perform some actions to see them here.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>