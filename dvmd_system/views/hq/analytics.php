<?php
// 1. Database Connection
require_once 'api/dbconnect.php';

// 2. DATA FETCHING LOGIC

// A. Get Total Counts by Status
$sqlStatus = "SELECT status, COUNT(*) as count FROM incidents GROUP BY status";
$resStatus = $conn->query($sqlStatus);
$stats = ['Pending' => 0, 'In Progress' => 0, 'Resolved' => 0];
while ($row = $resStatus->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}
$totalIncidents = array_sum($stats);

// B. Get Counts by Type (Fire, Flood, etc.)
$sqlType = "SELECT incident_type, COUNT(*) as count FROM incidents GROUP BY incident_type ORDER BY count DESC";
$resType = $conn->query($sqlType);

// C. Get Counts by Severity
$sqlSeverity = "SELECT severity, COUNT(*) as count FROM incidents GROUP BY severity";
$resSeverity = $conn->query($sqlSeverity);

// D. Get "Hotspots" (Incidents per location/district)
// Assuming you store 'district' in incidents table. If not, we use the raw location description for now.
$sqlHotspot = "SELECT district, COUNT(*) as count FROM incidents GROUP BY district ORDER BY count DESC LIMIT 5";
// Note: If 'district' column doesn't exist in incidents, you might need to join with users table or use 'village'
// For this demo, I will assume a simple grouping or fallback.
?>

<style>
    /* ... your existing styles ... */

    /* PRINT STYLES: Only applies when printing */
    @media print {
        /* 1. HIDE EVERYTHING WE DON'T WANT */
        #sidebar, 
        .dashboard-header, 
        .no-print,
        .dashboard-footer {
            display: none !important;
        }

        /* 2. RESET LAYOUT TO FULL WIDTH */
        body, .wrapper, .content-wrapper, .analytics-container {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        /* 3. ENSURE COLORS PRINT (For your graph bars) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Optional: Add a clean border for the page */
        .analytics-container {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="analytics-container" style="padding: 20px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2><i class="fas fa-chart-line"></i> National Analytics Center</h2>
            <p style="color: #666;">System-wide emergency data overview.</p>
        </div>
        <button onclick="window.print()" class="no-print"
            style="padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center;">
            <h3 style="font-size: 2.5rem; color: #2c3e50; margin: 0;"><?php echo $totalIncidents; ?></h3>
            <span style="color: #888; font-weight: bold;">Total Incidents</span>
        </div>
        <div class="stat-card"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; border-bottom: 4px solid #f1c40f;">
            <h3 style="font-size: 2.5rem; color: #f1c40f; margin: 0;"><?php echo $stats['Pending'] ?? 0; ?></h3>
            <span style="color: #666;">New / Pending</span>
        </div>
        <div class="stat-card"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; border-bottom: 4px solid #3498db;">
            <h3 style="font-size: 2.5rem; color: #3498db; margin: 0;"><?php echo $stats['In Progress'] ?? 0; ?></h3>
            <span style="color: #666;">In Progress</span>
        </div>
        <div class="stat-card"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; border-bottom: 4px solid #27ae60;">
            <h3 style="font-size: 2.5rem; color: #27ae60; margin: 0;"><?php echo $stats['Resolved'] ?? 0; ?></h3>
            <span style="color: #666;">Resolved</span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">

        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h4 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0;">Incidents by Category</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f8f9fa; text-align: left;">
                    <th style="padding: 10px;">Incident Type</th>
                    <th style="padding: 10px;">Count</th>
                    <th style="padding: 10px;">%</th>
                </tr>
                <?php while ($row = $resType->fetch_assoc()):
                    $percent = $totalIncidents > 0 ? round(($row['count'] / $totalIncidents) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <?php echo htmlspecialchars($row['incident_type']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <strong><?php echo $row['count']; ?></strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <div
                                style="background: #eee; width: 50px; height: 6px; border-radius: 3px; display: inline-block; vertical-align: middle;">
                                <div
                                    style="background: #2c3e50; width: <?php echo $percent; ?>%; height: 100%; border-radius: 3px;">
                                </div>
                            </div>
                            <small><?php echo $percent; ?>%</small>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h4 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0;">Severity Analysis</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f8f9fa; text-align: left;">
                    <th style="padding: 10px;">Severity Level</th>
                    <th style="padding: 10px;">Count</th>
                </tr>
                <?php while ($row = $resSeverity->fetch_assoc()):
                    $color = '#999';
                    if ($row['severity'] == 'Critical')
                        $color = '#e74c3c';
                    if ($row['severity'] == 'High')
                        $color = '#e67e22';
                    if ($row['severity'] == 'Medium')
                        $color = '#f1c40f';
                    if ($row['severity'] == 'Low')
                        $color = '#27ae60';
                    ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <span style="color: <?php echo $color; ?>; font-weight: bold;">●
                                <?php echo htmlspecialchars($row['severity']); ?></span>
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <strong><?php echo $row['count']; ?></strong></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>
</div>