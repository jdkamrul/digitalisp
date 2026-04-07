<?php
/**
 * Digital ISP ERP - Web Installer
 * Run this to install/setup the application.
 * Access: http://localhost:8088/install.php
 */

define('BASE_PATH', __DIR__);
define('INSTALL_PATH', __DIR__ . '/install');
define('CONFIG_PATH', __DIR__ . '/config');
define('DATABASE_PATH', __DIR__ . '/database');

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

function saveEnv($data) {
    $content = '';
    foreach ($data as $key => $value) {
        $content .= "$key=$value\n";
    }
    return file_put_contents(BASE_PATH . '/.env', $content);
}

function initDatabase($dbPath, $schemaPath) {
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("PRAGMA foreign_keys = ON");
    $sql = file_get_contents($schemaPath);
    $db->exec($sql);
    return true;
}

function createAdminUser($dbPath, $username, $password, $email) {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (name, username, email, password, role, status, created_at) 
               VALUES ('Administrator', '$username', '$email', '$passwordHash', 'admin', 'active', datetime('now'))");
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 2) {
        $appName = $_POST['app_name'] ?? 'Digital ISP ERP';
        $appUrl = $_POST['app_url'] ?? 'http://localhost:8088';
        $timezone = $_POST['timezone'] ?? 'Asia/Dhaka';
        
        $envData = [
            'APP_NAME' => $appName,
            'APP_URL' => $appUrl,
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'true',
            'APP_TIMEZONE' => $timezone,
            'APP_KEY' => 'base64:' . base64_encode(random_bytes(32)),
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => 'database/digital-isp.sqlite',
            'JWT_SECRET' => base64_encode(random_bytes(32)),
            'JWT_EXPIRY' => '86400',
            'RADIUS_DB_CONNECTION' => 'mysql',
            'RADIUS_DB_HOST' => '127.0.0.1',
            'RADIUS_DB_PORT' => '3306',
            'RADIUS_DB_DATABASE' => 'radius',
            'RADIUS_DB_USERNAME' => 'root',
            'RADIUS_DB_PASSWORD' => '',
        ];
        
        if (saveEnv($envData)) {
            header('Location: ?step=3');
            exit;
        } else {
            $error = 'Failed to save configuration file.';
        }
    }
    
    if ($step == 3) {
        $dbPath = DATABASE_PATH . '/digital-isp.sqlite';
        $schemaPath = DATABASE_PATH . '/sqlite_schema.sql';
        
        if (!file_exists($schemaPath)) {
            $error = 'Schema file not found: ' . $schemaPath;
        } else {
            try {
                initDatabase($dbPath, $schemaPath);
                header('Location: ?step=4');
                exit;
            } catch (Exception $e) {
                $error = 'Database initialization failed: ' . $e->getMessage();
            }
        }
    }
    
    if ($step == 4) {
        $dbPath = DATABASE_PATH . '/digital-isp.sqlite';
        $username = $_POST['username'] ?? 'admin';
        $password = $_POST['password'] ?? '';
        $email = $_POST['email'] ?? 'admin@example.com';
        
        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            try {
                createAdminUser($dbPath, $username, $password, $email);
                header('Location: ?step=5');
                exit;
            } catch (Exception $e) {
                $error = 'Failed to create admin user: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Digital ISP ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; }
        .glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); }
        .step-active { background: linear-gradient(135deg, #3b82f6, #8b5cf6); }
        .step-inactive { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body class="text-white">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 mb-4">
                    <i class="fa-solid fa-server text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Digital ISP ERP</h1>
                <p class="text-slate-400 mt-2">Installation Wizard</p>
            </div>

            <div class="glass rounded-2xl p-8">
                <div class="flex items-center justify-center gap-4 mb-8">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold <?= $step >= $i ? 'step-active' : 'step-inactive' ?>">
                                <?= $i ?>
                            </div>
                            <?php if ($i < 5): ?>
                                <div class="w-8 h-0.5 <?= $step > $i ? 'bg-blue-500' : 'bg-slate-700' ?>"></div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <h2 class="text-xl font-semibold mb-6">
                    <?php
                    $titles = ['', 'Welcome', 'Configuration', 'Database', 'Admin Account', 'Complete'];
                    echo $titles[$step] ?? 'Step ' . $step;
                    ?>
                </h2>

                <?php if ($error): ?>
                    <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($step == 1): ?>
                    <div class="space-y-4">
                        <p class="text-slate-300">Welcome to Digital ISP ERP installation. This wizard will guide you through the setup process.</p>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-slate-800/50 p-4 rounded-lg">
                                <i class="fa-solid fa-check text-green-400 mr-2"></i>PHP 8.1+
                            </div>
                            <div class="bg-slate-800/50 p-4 rounded-lg">
                                <i class="fa-solid fa-check text-green-400 mr-2"></i>SQLite/MySQL
                            </div>
                            <div class="bg-slate-800/50 p-4 rounded-lg">
                                <i class="fa-solid fa-check text-green-400 mr-2"></i>SNMP Extension
                            </div>
                            <div class="bg-slate-800/50 p-4 rounded-lg">
                                <i class="fa-solid fa-check text-green-400 mr-2"></i>PDO Extension
                            </div>
                        </div>
                        <a href="?step=2" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg font-medium hover:opacity-90 transition">
                            Get Started <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($step == 2): ?>
                    <form method="POST">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-slate-400 mb-2">Application Name</label>
                                <input type="text" name="app_name" value="Digital ISP ERP" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm text-slate-400 mb-2">Application URL</label>
                                <input type="text" name="app_url" value="http://localhost:8088" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm text-slate-400 mb-2">Timezone</label>
                                <select name="timezone" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:outline-none">
                                    <option value="Asia/Dhaka">Asia/Dhaka (GMT+6)</option>
                                    <option value="Asia/Kolkata">Asia/Kolkata (GMT+5:30)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 flex gap-4">
                            <a href="?step=1" class="px-6 py-3 bg-slate-700 rounded-lg font-medium hover:bg-slate-600 transition">Back</a>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg font-medium hover:opacity-90 transition">Next Step</button>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if ($step == 3): ?>
                    <div class="space-y-4">
                        <div class="bg-slate-800/50 p-4 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-slate-400">Database Type</span>
                                <span class="text-white">SQLite</span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-slate-400">Database File</span>
                                <span class="text-white font-mono text-sm">database/digital-isp.sqlite</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Schema</span>
                                <span class="text-green-400"><i class="fa-solid fa-check"></i> Found</span>
                            </div>
                        </div>
                        <form method="POST">
                            <div class="mt-6 flex gap-4">
                                <a href="?step=2" class="px-6 py-3 bg-slate-700 rounded-lg font-medium hover:bg-slate-600 transition">Back</a>
                                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg font-medium hover:opacity-90 transition">Initialize Database</button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if ($step == 4): ?>
                    <form method="POST">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-slate-400 mb-2">Admin Username</label>
                                <input type="text" name="username" value="admin" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm text-slate-400 mb-2">Admin Email</label>
                                <input type="email" name="email" value="admin@example.com" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm text-slate-400 mb-2">Password (min 6 chars)</label>
                                <input type="password" name="password" value="Admin@1234" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="mt-6 flex gap-4">
                            <a href="?step=3" class="px-6 py-3 bg-slate-700 rounded-lg font-medium hover:bg-slate-600 transition">Back</a>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg font-medium hover:opacity-90 transition">Create Admin</button>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if ($step == 5): ?>
                    <div class="text-center space-y-6">
                        <div class="w-20 h-20 mx-auto rounded-full bg-green-500/20 flex items-center justify-center">
                            <i class="fa-solid fa-check text-4xl text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-white">Installation Complete!</h3>
                            <p class="text-slate-400 mt-2">Your Digital ISP ERP is ready to use.</p>
                        </div>
                        <div class="bg-slate-800/50 p-4 rounded-lg text-left">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-slate-400">Login URL</span>
                                <span class="text-white font-mono"><?= $_ENV['APP_URL'] ?? 'http://localhost:8088' ?></span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-slate-400">Username</span>
                                <span class="text-white">admin</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Password</span>
                                <span class="text-white">Admin@1234</span>
                            </div>
                        </div>
                        <a href="/" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg font-medium hover:opacity-90 transition">
                            <i class="fa-solid fa-sign-in-alt mr-2"></i>Go to Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-6 text-slate-500 text-sm">
                Digital ISP ERP &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>
</body>
</html>