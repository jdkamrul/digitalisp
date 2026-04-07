#!/usr/bin/env php
<?php
/**
 * Digital ISP ERP - Quick Setup Script
 * Sets up the system for first use
 */

echo "🚀 Digital ISP ERP - Quick Setup\n";
echo "================================\n\n";

define('BASE_PATH', __DIR__);

// Check if .env exists
if (!file_exists(__DIR__ . '/.env')) {
    echo "❌ .env file not found. Copy .env.example to .env and configure it.\n";
    exit(1);
}

echo "✅ Environment file found\n";

// Load database configuration
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "✅ Database connection established\n";

    // Check required tables
    $requiredTables = ['users', 'customers', 'packages', 'nas_devices', 'config_items'];
    $missingTables = [];

    foreach ($requiredTables as $table) {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = ?");
        $stmt->execute([$table]);
        if (!$stmt->fetchColumn()) {
            $missingTables[] = $table;
        }
    }

    if (!empty($missingTables)) {
        echo "📦 Creating missing tables...\n";
        // The database.php ensureSqliteSchema() will handle this
        $db = Database::getInstance(); // Re-initialize to trigger schema creation
    }

    // Check if admin user exists
    $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();

    if ($adminExists == 0) {
        echo "👤 Creating admin user...\n";

        // Create default role if it doesn't exist
        $roleExists = $pdo->query("SELECT COUNT(*) FROM roles WHERE name = 'admin'")->fetchColumn();
        if ($roleExists == 0) {
            $pdo->exec("INSERT INTO roles (name, display_name, description) VALUES ('admin', 'Administrator', 'Full system access')");
            $roleId = $pdo->lastInsertId();

            // Insert basic permissions
            $pdo->exec("INSERT INTO permissions (name, module, description) VALUES
                ('customers.view', 'customers', 'View customers'),
                ('billing.view', 'billing', 'View invoices'),
                ('users.manage', 'admin', 'Manage users'),
                ('settings.manage', 'admin', 'System settings')");

            // Link permissions to role
            $pdo->exec("INSERT INTO role_permissions (role_id, permission_id)
                SELECT $roleId, id FROM permissions");
        } else {
            $roleId = $pdo->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
        }

        // Create admin user
        $passwordHash = password_hash('admin', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (role_id, username, email, password_hash, full_name, is_active)
            VALUES (?, 'admin', 'admin@local.host', ?, 'System Administrator', 1)")
            ->execute([$roleId, $passwordHash]);

        echo "✅ Admin user created (username: admin, password: admin)\n";
    } else {
        echo "✅ Admin user already exists\n";
    }

    // Check config items
    $configCount = $pdo->query("SELECT COUNT(*) FROM config_items")->fetchColumn();
    if ($configCount == 0) {
        echo "⚙️  Setting up configuration items...\n";
        require_once __DIR__ . '/create_config_table.php';
        createConfigItemsTable();
        echo "✅ Configuration items created\n";
    } else {
        echo "✅ Configuration items: $configCount entries\n";
    }

    echo "\n🎉 Setup complete! Your Digital ISP ERP system is ready.\n\n";
    echo "🌐 Access the system:\n";
    echo "   - Local: http://localhost/ispd/public/\n";
    echo "   - PHP Server: php -S 127.0.0.1:8088 -t public\n\n";
    echo "👤 Login with: admin / admin\n\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}