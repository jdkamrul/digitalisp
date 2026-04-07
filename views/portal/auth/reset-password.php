<?php
$settings = $settings ?? [];
$portalName = $settings['portal_name'] ?? 'Customer Portal';
$error   = $_SESSION['portal_error']   ?? null; unset($_SESSION['portal_error']);
$success = $_SESSION['portal_success'] ?? null; unset($_SESSION['portal_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= sanitize($portalName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{primary:{500:'#6366f1',600:'#4f46e5'}}}}}</script>
    <link rel="stylesheet" href="<?= base_url('assets/css/portal.css') ?>">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur rounded-2xl mb-4">
                <i class="fas fa-lock text-white text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white"><?= sanitize($portalName) ?></h1>
            <p class="text-white/80 mt-2">Enter OTP and new password</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Reset Password</h2>

            <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i><?= sanitize($error) ?>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg text-sm">
                <i class="fas fa-check-circle mr-2"></i><?= sanitize($success) ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('portal/reset-password') ?>" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">OTP Code</label>
                    <input type="text" name="otp" required maxlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 text-center text-2xl tracking-widest font-bold"
                        placeholder="000000" autofocus>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" name="new_password" required minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                        placeholder="Minimum 6 characters">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                        placeholder="Repeat new password">
                </div>
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-semibold rounded-lg hover:opacity-90 transition-opacity">
                    <i class="fas fa-check mr-2"></i> Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="<?= base_url('portal/forgot-password') ?>" class="text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left mr-1"></i> Request new OTP
                </a>
            </div>
        </div>
    </div>
</body>
</html>
