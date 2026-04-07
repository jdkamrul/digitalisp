-- ============================================================
-- PPPoE Profiles Table for Digital ISP ERP
-- Run this in phpMyAdmin to add profile support
-- ============================================================

USE digital_isp;

-- Create pppoe_profiles table if it doesn't exist
CREATE TABLE IF NOT EXISTS pppoe_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nas_id INT NULL,
    name VARCHAR(100) NOT NULL,
    speed_download INT DEFAULT 0,
    speed_upload INT DEFAULT 0,
    description VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nas_id) REFERENCES nas_devices(id) ON DELETE SET NULL,
    INDEX idx_nas_id (nas_id),
    INDEX idx_name (name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert common profile templates
-- These will be available for all NAS devices (nas_id IS NULL)
INSERT INTO pppoe_profiles (name, speed_download, speed_upload, description) VALUES
('1Mbps', 1000, 500, '1 Mbps Download / 500 Kbps Upload'),
('2Mbps', 2000, 1000, '2 Mbps Download / 1 Mbps Upload'),
('3Mbps', 3000, 1500, '3 Mbps Download / 1.5 Mbps Upload'),
('5Mbps', 5000, 2500, '5 Mbps Download / 2.5 Mbps Upload'),
('10Mbps', 10000, 5000, '10 Mbps Download / 5 Mbps Upload'),
('20Mbps', 20000, 10000, '20 Mbps Download / 10 Mbps Upload'),
('50Mbps', 50000, 25000, '50 Mbps Download / 25 Mbps Upload'),
('100Mbps', 100000, 50000, '100 Mbps Download / 50 Mbps Upload')
ON DUPLICATE KEY UPDATE name=name;

-- Verify insertion
SELECT '✓ PPPoE profiles table created and populated!' AS status;
SELECT COUNT(*) as total_profiles FROM pppoe_profiles;
SELECT name, CONCAT(speed_download, 'K / ', speed_upload, 'K') as speed FROM pppoe_profiles ORDER BY speed_download;
