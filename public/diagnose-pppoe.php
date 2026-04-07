<?php
// Complete PPPoE Profile Diagnostic Tool
// Visit: http://localhost:8000/diagnose-pppoe.php

// Define required constants
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/database.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>PPPoE Profile Complete Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #007bff; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin: 15px 0; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-ok { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
        .status-error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        .status-warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        .step { background: #e7f3ff; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .code { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🔍 PPPoE Profile Complete Diagnostic</h1>
    
    <?php
    $db = Database::getInstance();
    
    echo "<div class='card'>";
    echo "<h2>Step 1: Check pppoe_profiles Table</h2>";
    
    // Check if table exists
    try {
        $tables = $db->fetchAll("SHOW TABLES LIKE 'pppoe_profiles'");
        if (!empty($tables)) {
            echo "<div class='status-ok'>✓ Table pppoe_profiles exists</div>";
            
            // Count profiles
            $count = $db->fetchOne("SELECT COUNT(*) as c FROM pppoe_profiles");
            echo "<p>Total profiles in database: <strong>{$count['c']}</strong></p>";
            
            if ($count['c'] > 0) {
                echo "<h4>Profiles in database:</h4>";
                $profiles = $db->fetchAll("SELECT * FROM pppoe_profiles ORDER BY name");
                echo "<table>";
                echo "<tr><th>ID</th><th>NAS ID</th><th>Name</th><th>Download</th><th>Upload</th><th>Status</th></tr>";
                foreach ($profiles as $profile) {
                    $status = $profile['is_active'] ? '✓ Active' : '✗ Inactive';
                    echo "<tr>";
                    echo "<td>{$profile['id']}</td>";
                    echo "<td>" . ($profile['nas_id'] ?? 'Global') . "</td>";
                    echo "<td>" . htmlspecialchars($profile['name']) . "</td>";
                    echo "<td>" . number_format($profile['speed_download']) . " Kbps</td>";
                    echo "<td>" . number_format($profile['speed_upload']) . " Kbps</td>";
                    echo "<td>$status</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='status-warning'>⚠ Table is empty! No profiles found.</div>";
                echo "<div class='step'>";
                echo "<strong>Action needed:</strong> Run this SQL to add sample profiles:<br>";
                echo "<pre>";
echo "INSERT INTO pppoe_profiles (name, speed_download, speed_upload, is_active) VALUES
('1Mbps', 1000, 500, 1),
('2Mbps', 2000, 1000, 1),
('3Mbps', 3000, 1500, 1),
('5Mbps', 5000, 2500, 1),
('10Mbps', 10000, 5000, 1);";
                echo "</pre>";
                echo "</div>";
            }
        } else {
            echo "<div class='status-error'>✗ Table pppoe_profiles does NOT exist!</div>";
            echo "<div class='step'>";
            echo "<strong>Action needed:</strong> Create the table first.<br>";
            echo "<pre>";
echo "CREATE TABLE IF NOT EXISTS pppoe_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nas_id INT NULL,
    name VARCHAR(100) NOT NULL,
    speed_download INT DEFAULT 0,
    speed_upload INT DEFAULT 0,
    description VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            echo "</pre>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='status-error'>✗ Error: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    
    echo "<div class='card'>";
    echo "<h2>Step 2: Check NAS Devices</h2>";
    
    $nasDevices = $db->fetchAll("SELECT * FROM nas_devices WHERE is_active = 1 ORDER BY id");
    
    if (empty($nasDevices)) {
        echo "<div class='status-warning'>⚠ No NAS devices configured</div>";
        echo "<p>You need to add at least one MikroTik/NAS device.</p>";
        echo "<p>Go to: Network → NAS Devices → Add New</p>";
    } else {
        echo "<div class='status-ok'>✓ Found " . count($nasDevices) . " active NAS device(s)</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>IP Address</th><th>Port</th><th>Username</th></tr>";
        foreach ($nasDevices as $nas) {
            echo "<tr>";
            echo "<td>{$nas['id']}</td>";
            echo "<td>" . htmlspecialchars($nas['name']) . "</td>";
            echo "<td>{$nas['ip_address']}</td>";
            echo "<td>{$nas['api_port']}</td>";
            echo "<td>{$nas['username']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    echo "<hr>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px;'>";
    echo "<h3>Quick Actions:</h3>";
    echo "<a href='http://digitalisp.xyz:8081/customers/create' target='_blank' style='display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;margin:5px;'>Test Customer Form</a>";
    echo "<a href='http://digitalisp.xyz:8081/network/nas' target='_blank' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;margin:5px;'>Manage NAS Devices</a>";
    echo "<a href='http://localhost/phpmyadmin' target='_blank' style='display:inline-block;padding:10px 20px;background:#6c757d;color:white;text-decoration:none;border-radius:5px;margin:5px;'>phpMyAdmin</a>";
    echo "</div>";
    
    echo "<p><em>Diagnostic completed at: " . date('Y-m-d H:i:s') . "</em></p>";
    ?>
</body>
</html>
