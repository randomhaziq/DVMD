<?php
// views/kplb_hq/analytics.php

// 1. database
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

// B. Get Counts by Type
$sqlType = "SELECT incident_type, COUNT(*) as count FROM incidents GROUP BY incident_type ORDER BY count DESC";
$resType = $conn->query($sqlType);

// C. Get Counts by Severity
$sqlSeverity = "SELECT severity, COUNT(*) as count FROM incidents GROUP BY severity";
$resSeverity = $conn->query($sqlSeverity);

// D. Get Total SOS Alerts
$sqlSOS = "SELECT COUNT(*) as total FROM sos_alerts";
$resSOS = $conn->query($sqlSOS);
$totalSOS = $resSOS->fetch_assoc()['total'] ?? 0;

// E. Get SOS Alerts by Type
$sqlSOSTypes = "SELECT emergency_type, COUNT(*) as count FROM sos_alerts GROUP BY emergency_type ORDER BY count DESC";
$resSOSTypes = $conn->query($sqlSOSTypes);

// F. Get SOS Alerts by VILLAGE (New)
// We JOIN with the users table to get the village name of the person who pressed SOS
$sqlSOSVillage = "SELECT u.village, COUNT(*) as count 
                  FROM sos_alerts s 
                  JOIN users u ON s.user_id = u.id 
                  GROUP BY u.village 
                  ORDER BY count DESC LIMIT 5";
$resSOSVillage = $conn->query($sqlSOSVillage);
?>

<style>
    /* PRINT STYLES */
    @media print {

        #sidebar,
        .dashboard-header,
        .no-print,
        .dashboard-footer {
            display: none !important;
        }

        body,
        .wrapper,
        .content-wrapper,
        .analytics-container {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .analytics-container {
            border: none !important;
            box-shadow: none !important;
        }
    }

    /* Responsive Grid for Top Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    /* Responsive Grid for Middle Tables */
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    /* SOS Section Styling */
    .sos-container {
        background-color: #ffebee;
        border: 1px solid #ffcdd2;
        border-radius: 10px;
        padding: 25px;
        /* Removed text-align:center to allow grid to stretch */
    }

    .sos-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        /* Two equal columns */
        gap: 20px;
        margin-top: 20px;
    }

    .sos-card-header {
        background: #ef5350;
        color: white;
        padding: 12px 20px;
    }

    /* Ensure table rows align */
    .sos-table td {
        padding: 12px 20px;
        border-bottom: 1px solid #eee;
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

    <div class="stats-grid">
        <div class="stat-card"
            style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; border-bottom: 4px solid #2c3e50;">
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

    <div class="tables-grid">
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h4 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0;">Incidents by Category</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f8f9fa; text-align: left;">
                    <th style="padding: 10px;">Type</th>
                    <th style="padding: 10px;">Count</th>
                    <th style="padding: 10px;">%</th>
                </tr>
                <?php
                $resType->data_seek(0);
                while ($row = $resType->fetch_assoc()):
                    $percent = $totalIncidents > 0 ? round(($row['count'] / $totalIncidents) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <?php echo htmlspecialchars($row['incident_type']); ?>
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <strong><?php echo $row['count']; ?></strong>
                        </td>
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
                    <th style="padding: 10px;">Severity</th>
                    <th style="padding: 10px;">Count</th>
                </tr>
                <?php while ($row = $resSeverity->fetch_assoc()):
                    $color = match ($row['severity']) { 'Critical' => '#e74c3c', 'High' => '#e67e22', 'Medium' => '#f1c40f', default => '#27ae60'};
                    ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <span style="color: <?php echo $color; ?>; font-weight: bold;">●
                                <?php echo htmlspecialchars($row['severity']); ?></span>
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <strong><?php echo $row['count']; ?></strong>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <div class="sos-container">

        <div
            style="width: 100%; min-width: 300px; background: white; padding: 15px 50px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #d32f2f; display: inline-block; text-align: center; box-sizing: border-box;">
            <h2 style="margin: 0; font-size: 3rem; color: #c62828;"><?php echo $totalSOS; ?></h2>
            <div style="color: #b71c1c; font-weight: bold; font-size: 1.1rem; margin-top: 5px;">
                <i class="fas fa-tower-broadcast"></i> Total SOS Alerts
            </div>
        </div>

        <div class="sos-grid">

            <div
                style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <div class="sos-card-header">
                    <i class="fas fa-notes-medical"></i> SOS Emergency Type
                </div>
                <table style="width: 100%; border-collapse: collapse;" class="sos-table">
                    <?php if ($resSOSTypes->num_rows > 0): ?>
                        <?php while ($row = $resSOSTypes->fetch_assoc()): ?>
                            <tr>
                                <td style="text-transform: capitalize;">
                                    <strong><?php echo htmlspecialchars($row['emergency_type']); ?></strong>
                                </td>
                                <td style="text-align: right;">
                                    <span
                                        style="background: #ffebee; color: #c62828; padding: 4px 10px; border-radius: 12px; font-weight: bold;">
                                        <?php echo $row['count']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; color: #888;">No data available.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div
                style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <div class="sos-card-header">
                    <i class="fas fa-map-marker-alt"></i> Affected Village
                </div>
                <table style="width: 100%; border-collapse: collapse;" class="sos-table">
                    <?php if ($resSOSVillage && $resSOSVillage->num_rows > 0): ?>
                        <?php while ($row = $resSOSVillage->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($row['village'] ? $row['village'] : 'Unknown Village'); ?>
                                </td>
                                <td style="text-align: right;">
                                    <span
                                        style="background: #e3f2fd; color: #1565c0; padding: 4px 10px; border-radius: 12px; font-weight: bold;">
                                        <?php echo $row['count']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; color: #888;">No village data yet.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

        </div>
    </div>
</div>