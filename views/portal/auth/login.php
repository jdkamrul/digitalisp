<?php
$settings = $settings ?? [];
$portalName = $settings['portal_name'] ?? 'SelfCare';
$isDark = true;
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= sanitize($portalName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e' },
                        accent: { 500:'#d946ef',600:'#c026d3' }
                    }
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
        .glass { background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .glow-btn { box-shadow: 0 0 30px rgba(14, 165, 233, 0.4); }
        .glow-btn:hover { box-shadow: 0 0 40px rgba(14, 165, 233, 0.6); }
        .gradient-text { background: linear-gradient(135deg, #0ea5e9, #d946ef); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 shadow-lg glow-primary mb-4">
                <i class="fas fa-bolt text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white"><?= sanitize($portalName) ?></h1>
            <p class="text-dark-400 mt-2">Your Premium SelfCare Portal</p>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-2xl shadow-2xl p-8">
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 text-red-400 rounded-xl flex items-center">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <?= sanitize($error) ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('portal/login') ?>" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Phone, Email, or Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-dark-500">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="identifier" required
                            class="w-full pl-12 pr-4 py-4 bg-dark-800/50 border border-dark-700 rounded-xl text-white placeholder-dark-500 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all"
                            placeholder="Enter your login ID">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-dark-500">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" required
                            class="w-full pl-12 pr-14 py-4 bg-dark-800/50 border border-dark-700 rounded-xl text-white placeholder-dark-500 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all"
                            placeholder="Enter your password">
                        <button type="button" onclick="togglePassword(this)" class="absolute inset-y-0 right-0 pr-4 flex items-center text-dark-500 hover:text-white transition-colors">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-dark-600 bg-dark-800 text-primary-500 focus:ring-primary-500">
                        <span class="ml-2 text-dark-400">Remember me</span>
                    </label>
                    <a href="<?= base_url('portal/forgot-password') ?>" class="text-primary-400 hover:text-primary-300 font-medium">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="w-full py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-primary-500/30 transition-all glow-btn">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
            </form>

            <?php if (($settings['allow_secret_question'] ?? '1') === '1'): ?>
            <div class="mt-6 text-center">
                <a href="<?= base_url('portal/forgot-password') ?>?method=secret" class="text-sm text-dark-500 hover:text-primary-400">
                    <i class="fas fa-key mr-1"></i> Reset password with security question
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Help -->
        <div class="mt-8 text-center">
            <p class="text-dark-500 text-sm">
                Need help? Call <a href="tel:<?= sanitize($settings['support_phone'] ?? '01700000000') ?>" class="text-primary-400 font-medium"><?= sanitize($settings['support_phone'] ?? '01700000000') ?></a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword(btn) {
            const input = btn.closest('.relative').querySelector('input');
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
