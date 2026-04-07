<?php
// Sync MikroTik Profiles Tool
// Visit: http://localhost:8000/sync-mikrotik-profiles.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sync MikroTik Profiles</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #007bff; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin: 15px 0; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-ok { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
        .status-error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        .status-warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .progress { background: #e9ecef; border-radius: 5px; overflow: hidden; margin: 15px 0; }
        .progress-bar { background: #007bff; height: 30px; transition: width 0.3s; text-align: center; color: white; line-height: 30px; }
    </style>
</head>
<body>
    <h1>🔄 Sync MikroTik PPPoE Profiles</h1>
    
    <?php
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../app/Services/MikroTikService.php';
    
    $db = Database::getInstance();
    
    // Get all active NAS devices
    $nasDevices = $db->fetchAll("SELECT * FROM nas_devices WHERE is_active = 1 ORDER BY id");
    
    echo "<div class='card'>";
    echo "<h2>📡 Connected NAS Devices</h2>";
    
    if (empty($nasDevices)) {
        echo "<div class='status-error'>";
        echo "✗ No NAS devices found in database!<br>";
        echo "You need to add at least one MikroTik router first.";
        echo "</div>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>";
        echo "<li>Go to Network → NAS Devices</li>";
        echo "<li>Add your MikroTik router with:
            <ul>
                <li>Name (e.g., Main-MikroTik)</li>
                <li>IP Address (e.g., 192.168.88.1)</li>
                <li>API Port (default: 8728)</li>
                <li>Username (must have API access)</li>
                <li>Password</li>
            </ul>
        </li>";
        echo "<li>Save and return here to sync</li>";
        echo "</ol>";
    } else {
        echo "<p>Found " . count($nasDevices) . " NAS device(s)</p>";
        
        foreach ($nasDevices as $index => $nas) {
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
            echo "<h3>NAS #" . ($index + 1) . ": " . htmlspecialchars($nas['name']) . "</h3>";
            echo "<p><strong>IP:</strong> {$nas['ip_address']}:<strong>{$nas['api_port']}</strong></p>";
            echo "<p><strong>Username:</strong> {$nas['username']}</p>";
            
            // Try to connect
            $mikrotik = new MikroTikService([
                'ip'       => $nas['ip_address'],
                'port'     => $nas['api_port'] ?? 8728,
                'username' => $nas['username'],
                'password' => $nas['password'],
                'timeout'  => 5,
            ]);
            
            if ($mikrotik->connect()) {
                echo "<div class='status-ok'>✓ Connected successfully!</div>";
                
                // Get profiles from MikroTik
                $profiles = $mikrotik->getProfiles();
                
                if (!empty($profiles)) {
                    echo "<div class='status-ok'>✓ Found " . count($profiles) . " profile(s) on MikroTik</div>";
                    
                    // Display profiles from MikroTik
                    echo "<h4>Profiles on MikroTik:</h4>";
                    echo "<table>";
                    echo "<tr><th>Name</th><th>Rate Up</th><th>Rate Down</th><th>Max Sessions</th></tr>";
                    foreach ($profiles as $profile) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($profile['name']) . "</td>";
                        echo "<td>" . ($profile['rate-upload'] ?? 'N/A') . "</td>";
                        echo "<td>" . ($profile['rate-download'] ?? 'N/A') . "</td>";
                        echo "<td>" . ($profile['session-limit'] ?? 'N/A') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    // Sync to database
                    echo "<h4>Syncing to Database...</h4>";
                    $synced = 0;
                    $updated = 0;
                    $errors = 0;
                    
                    foreach ($profiles as $profile) {
                        $profileName = $profile['name'];
                        
                        // Check if profile exists in DB
                        $existing = $db->fetchOne(
                            "SELECT id FROM pppoe_profiles WHERE name = ? AND (nas_id = ? OR nas_id IS NULL)",
                            [$profileName, $nas['id']]
                        );
                        
                        if ($existing) {
                            // Update existing profile
                            $db->update('pppoe_profiles', [
                                'speed_upload' => $this->parseSpeed($profile['rate-upload'] ?? ''),
                                'speed_download' => $this->parseSpeed($profile['rate-download'] ?? ''),
                                'description' => 'Synced from MikroTik',
                                'updated_at' => date('Y-m-d H:i:s'),
                            ], 'id=?', [$existing['id']]);
                            $updated++;
                        } else {
                            // Insert new profile
                            $db->insert('pppoe_profiles', [
                                'nas_id' => $nas['id'],
                                'name' => $profileName,
                                'speed_upload' => $this->parseSpeed($profile['rate-upload'] ?? ''),
                                'speed_download' => $this->parseSpeed($profile['rate-download'] ?? ''),
                                'description' => 'Synced from MikroTik',
                                'is_active' => 1,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                            $synced++;
                        }
                    }
                    
                    echo "<div class='status-ok'>";
                    echo "✓ Sync completed!<br>";
                    echo "New profiles added: <strong>$synced</strong><br>";
                    echo "Existing profiles updated: <strong>$updated</strong>";
                    echo "</div>";
                    
                } else {
                    echo "<div class='status-warning'>⚠ No profiles found on MikroTik</div>";
                }
                
            } else {
                echo "<div class='status-error'>";
                echo "✗ Failed to connect to MikroTik<br>";
                echo "Please check:
                    <ul>
                        <li>MikroTik IP address and port</li>
                        <li>Username and password</li>
                        <li>MikroTik is reachable</li>
                        <li>API is enabled on MikroTik (IP > Services > api)</li>
                    </ul>
                ";
                echo "</div>";
            }
            
            echo "</div>";
        }
    }
    
    // Show current database profiles
    echo "<div class='card'>";
    echo "<h2>💾 Current Profiles in Database</h2>";
    
    $dbProfiles = $db->fetchAll("SELECT * FROM pppoe_profiles ORDER BY nas_id, name");
    
    if (!empty($dbProfiles)) {
        echo "<p>Found " . count($dbProfiles) . " profile(s) in database</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>NAS ID</th><th>Name</th><th>Download</th><th>Upload</th><th>Status</th><th>Source</th></tr>";
        foreach ($dbProfiles as $profile) {
            $source = $profile['nas_id'] ? "NAS #{$profile['nas_id']}" : 'Global';
            $status = $profile['is_active'] ? '✓ Active' : '✗ Inactive';
            echo "<tr>";
            echo "<td>{$profile['id']}</td>";
            echo "<td>" . ($profile['nas_id'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($profile['name']) . "</td>";
            echo "<td>" . number_format($profile['speed_download']) . " Kbps</td>";
            echo "<td>" . number_format($profile['speed_upload']) . " Kbps</td>";
            echo "<td>$status</td>";
            echo "<td>$source</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='status-warning'>⚠ No profiles in database yet</div>";
    }
    echo "</div>";
    
    // Helper function to parse speed
    function parseSpeed($speedStr) {
        if (empty($speedStr)) return 0;
        
        // Parse formats like "1M", "512K", "1G", etc.
        preg_match('/(\d+)([KMG]?)/', strtoupper($speedStr), $matches);
        if (count($matches) < 2) return 0;
        
        $value = (int)$matches[1];
        $unit = $matches[2] ?? '';
        
        switch ($unit) {
            case 'G': return $value * 1000000; // Gbps to Kbps
            case 'M': return $value * 1000;    // Mbps to Kbps
            case 'K': return $value;           // Kbps
            default: return $value;            // Assume bps, convert to Kbps
        }
    }
    
    echo "<hr>";
    echo "<div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;'>";
    echo "<h3>Quick Actions:</h3>";
    echo "<a href='http://digitalisp.xyz:8081/network/nas' class='btn' target='_blank'>Manage NAS Devices</a>";
    echo "<a href='http://digitalisp.xyz:8081/settings#packages' class='btn' target='_blank'>View Packages</a>";
    echo "<a href='http://localhost:8000/test_profiles.php' class='btn' target='_blank'>Test Profiles</a>";
    echo "<a href='http://localhost/phpmyadmin' class='btn' target='_blank'>phpMyAdmin</a>";
    echo "</div>";
    
    echo "<p><em>Sync completed at: " . date('Y-m-d H:i:s') . "</em></p>";
    ?>
</body>
</html>
