<?php
global $customer, $currentBill, $unpaidInvoices, $recentPayments, $openTickets, $usageData;
$isOnline = $usageData['online'] ?? false;
?>

<!-- Premium Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Connection Status - Gradient Card -->
    <div class="relative overflow-hidden rounded-2xl <?= $isOnline ? 'bg-gradient-to-br from-green-500 to-emerald-600' : 'bg-gradient-to-br from-red-500 to-rose-600' ?> p-5 shadow-lg <?= $isOnline ? 'shadow-green-500/30' : 'shadow-red-500/30' ?> status-card">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <p class="text-white/80 text-sm font-medium">Connection Status</p>
                <p class="text-2xl font-bold text-white mt-1" id="conn-status-text">
                    <?= $isOnline ? 'Online' : 'Offline' ?>
                </p>
                <p class="text-white/70 text-xs mt-2">
                    <i class="fas fa-network-wired mr-1"></i> 
                    <span id="conn-ip"><?= sanitize($usageData['ip_address'] ?? 'N/A') ?></span>
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center">
                <i class="fas fa-wifi text-2xl text-white"></i>
            </div>
        </div>
        <?php if($isOnline): ?>
        <div class="mt-3 flex items-center">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse mr-2"></span>
            <span class="text-white/80 text-xs">Session: <?= sanitize($usageData['session_time'] ?? 'N/A') ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Current Bill -->
    <div class="glass-card-dark rounded-2xl p-5 border border-dark-700/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-dark-400 text-sm font-medium">Current Bill</p>
                <p class="text-2xl font-bold text-white mt-1">
                    <?= formatMoney($currentBill['due_amount'] ?? $customer['due_amount'] ?? 0) ?>
                </p>
                <?php if (!empty($currentBill)): ?>
                <p class="text-xs <?= ($currentBill['days_until_due'] ?? 0) < 0 ? 'text-red-400' : 'text-dark-400' ?> mt-2">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Due: <?= formatDate($currentBill['due_date'] ?? '', 'd M Y') ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-yellow-500/20 flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-2xl text-yellow-400"></i>
            </div>
        </div>
        <?php if (($customer['due_amount'] ?? 0) > 0): ?>
        <a href="<?= base_url('portal/billing/pay-form/' . ($currentBill['id'] ?? '')) ?>" class="mt-3 flex items-center justify-center w-full py-2 bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-xl font-medium text-sm hover:shadow-lg transition-all">
            <i class="fas fa-credit-card mr-2"></i> Pay Now
        </a>
        <?php else: ?>
        <p class="mt-3 text-xs text-green-400"><i class="fas fa-check-circle mr-1"></i> All bills paid</p>
        <?php endif; ?>
    </div>

    <!-- Package Info -->
    <div class="glass-card-dark rounded-2xl p-5 border border-dark-700/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-dark-400 text-sm font-medium">My Package</p>
                <p class="text-xl font-bold text-white mt-1 truncate"><?= sanitize($customer['package_name'] ?? 'N/A') ?></p>
                <p class="text-dark-400 text-xs mt-2">
                    <span class="text-primary-400"><i class="fas fa-arrow-down mr-1"></i><?= sanitize($customer['speed_download'] ?? 'N/A') ?></span>
                    <span class="mx-2">|</span>
                    <span class="text-accent-400"><i class="fas fa-arrow-up mr-1"></i><?= sanitize($customer['speed_upload'] ?? 'N/A') ?></span>
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-primary-500/20 flex items-center justify-center">
                <i class="fas fa-rocket text-2xl text-primary-400"></i>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs">
            <span class="text-dark-400">Monthly</span>
            <span class="text-white font-semibold"><?= formatMoney($customer['monthly_charge'] ?? 0) ?></span>
        </div>
    </div>

    <!-- Data Usage -->
    <div class="glass-card-dark rounded-2xl p-5 border border-dark-700/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-dark-400 text-sm font-medium">Data Usage</p>
                <p class="text-2xl font-bold text-white mt-1" id="dash-total">
                    <?= number_format($usageData['today_total'] ?? 0, 2) ?> <span class="text-lg text-dark-400">GB</span>
                </p>
                <p class="text-dark-400 text-xs mt-2">
                    <i class="fas fa-chart-pie mr-1"></i> Session Total
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-accent-500/20 flex items-center justify-center">
                <i class="fas fa-database text-2xl text-accent-400"></i>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <div class="flex-1 text-center">
                <p class="text-xs text-dark-400">Download</p>
                <p class="text-sm font-semibold text-primary-400" id="dash-download"><?= number_format($usageData['today_download'] ?? 0, 1) ?> GB</p>
            </div>
            <div class="flex-1 text-center">
                <p class="text-xs text-dark-400">Upload</p>
                <p class="text-sm font-semibold text-accent-400" id="dash-upload"><?= number_format($usageData['today_upload'] ?? 0, 1) ?> GB</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Quick Actions -->
    <div class="glass-card-dark rounded-2xl p-6 border border-dark-700/50">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fas fa-bolt text-yellow-400 mr-2"></i> Quick Actions
        </h3>
        <div class="space-y-3">
            <?php if (($customer['due_amount'] ?? 0) > 0): ?>
            <a href="<?= base_url('portal/billing/pay-form/' . ($currentBill['id'] ?? '')) ?>" 
               class="flex items-center p-4 bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-xl border border-green-500/30 hover:border-green-500/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-green-500/30 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-credit-card text-green-400 text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-white">Pay Bill</p>
                    <p class="text-xs text-dark-400">Due: <?= formatMoney($customer['due_amount'] ?? 0) ?></p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-dark-500 group-hover:text-white transition-colors"></i>
            </a>
            <?php endif; ?>
            
            <a href="<?= base_url('portal/support/create') ?>" 
               class="flex items-center p-4 bg-gradient-to-r from-blue-500/20 to-primary-500/20 rounded-xl border border-blue-500/30 hover:border-blue-500/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-blue-500/30 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-plus text-blue-400 text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-white">New Ticket</p>
                    <p class="text-xs text-dark-400">Report an issue</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-dark-500 group-hover:text-white transition-colors"></i>
            </a>

            <a href="<?= base_url('portal/profile') ?>" 
               class="flex items-center p-4 bg-gradient-to-r from-purple-500/20 to-accent-500/20 rounded-xl border border-purple-500/30 hover:border-purple-500/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-purple-500/30 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-user text-purple-400 text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-white">My Profile</p>
                    <p class="text-xs text-dark-400">Manage account</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-dark-500 group-hover:text-white transition-colors"></i>
            </a>

            <a href="<?= base_url('portal/usage') ?>" 
               class="flex items-center p-4 bg-gradient-to-r from-accent-500/20 to-pink-500/20 rounded-xl border border-accent-500/30 hover:border-accent-500/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-accent-500/30 flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-line text-accent-400 text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-white">Usage Details</p>
                    <p class="text-xs text-dark-400">View statistics</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-dark-500 group-hover:text-white transition-colors"></i>
            </a>
        </div>
    </div>

    <!-- Account Details -->
    <div class="glass-card-dark rounded-2xl p-6 border border-dark-700/50">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fas fa-id-card text-primary-400 mr-2"></i> Account Details
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-dark-700/50">
                <span class="text-dark-400"><i class="fas fa-id-badge mr-2 text-dark-500"></i>Customer ID</span>
                <span class="font-mono text-white"><?= sanitize($customer['customer_code'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-dark-700/50">
                <span class="text-dark-400"><i class="fas fa-user mr-2 text-dark-500"></i>Name</span>
                <span class="font-medium text-white"><?= sanitize($customer['full_name'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-dark-700/50">
                <span class="text-dark-400"><i class="fas fa-phone mr-2 text-dark-500"></i>Phone</span>
                <span class="font-mono text-white"><?= sanitize($customer['phone'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-dark-700/50">
                <span class="text-dark-400"><i class="fas fa-wifi mr-2 text-dark-500"></i>PPPoE ID</span>
                <span class="font-mono text-primary-400"><?= sanitize($customer['pppoe_username'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-dark-400"><i class="fas fa-calendar-check mr-2 text-dark-500"></i>Expiry</span>
                <span class="text-white"><?= formatDate($customer['expiry_date'] ?? '', 'd M Y') ?></span>
            </div>
        </div>
    </div>

    <!-- Recent Payments / Live Stats -->
    <div class="glass-card-dark rounded-2xl p-6 border border-dark-700/50">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-white">
                <i class="fas fa-history text-green-400 mr-2"></i> Recent Payments
            </h3>
            <a href="<?= base_url('portal/billing/payments') ?>" class="text-xs text-primary-400 hover:text-primary-300">View all</a>
        </div>
        <?php if (empty($recentPayments)): ?>
        <div class="text-center py-8">
            <div class="w-16 h-16 rounded-full bg-dark-800 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-receipt text-3xl text-dark-500"></i>
            </div>
            <p class="text-dark-400">No payment history</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach (array_slice($recentPayments, 0, 3) as $payment): ?>
            <div class="flex items-center p-3 bg-dark-800/50 rounded-xl">
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center mr-3">
                    <i class="fas fa-check text-green-400"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-white"><?= formatMoney($payment['amount']) ?></p>
                    <p class="text-xs text-dark-400"><?= formatDate($payment['payment_date'], 'd M Y') ?></p>
                </div>
                <span class="text-xs text-green-400 bg-green-500/10 px-2 py-1 rounded-lg">Paid</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Live Status Banner -->
<?php if($isOnline): ?>
<div class="mt-6 glass-card border border-primary-500/30 rounded-2xl p-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="w-3 h-3 rounded-full bg-green-500 status-dot mr-3"></div>
            <span class="text-white font-medium">You're connected</span>
            <span class="text-dark-400 mx-2">•</span>
            <span class="text-dark-400 text-sm">Last updated: <span id="last-update">Just now</span></span>
        </div>
        <a href="<?= base_url('portal/usage') ?>" class="text-sm text-primary-400 hover:text-primary-300">
            <i class="fas fa-chart-area mr-1"></i> Detailed Usage
        </a>
    </div>
</div>
<?php else: ?>
<div class="mt-6 glass-card border border-red-500/30 rounded-2xl p-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="w-3 h-3 rounded-full bg-red-500 mr-3"></div>
            <span class="text-white font-medium">You're offline</span>
            <span class="text-dark-400 mx-2">•</span>
            <span class="text-dark-400 text-sm">Reconnect your PPPoE to go online</span>
        </div>
        <a href="<?= base_url('portal/support/create') ?>" class="text-sm text-red-400 hover:text-red-300">
            <i class="fas fa-life-ring mr-1"></i> Get Help
        </a>
    </div>
</div>
<?php endif; ?>

<script>
const isOnlineDash = <?= $isOnline ? 'true' : 'false' ?>;
function updateDashStatusUI(online) {
    const statusEl = document.getElementById('dash-status');
    const connText = document.getElementById('conn-status-text');
    const connIcon = document.getElementById('conn-status-icon');
    const statusCard = document.querySelector('.status-card');
    if (online) {
        if(statusEl) {
            statusEl.textContent = 'Online';
            statusEl.className = 'text-2xl font-bold text-green-400';
        }
        if(connText) connText.textContent = 'Active';
        if(statusCard) {
            statusCard.classList.remove('from-red-500', 'to-rose-600', 'shadow-red-500/30');
            statusCard.classList.add('from-green-500', 'to-emerald-600', 'shadow-green-500/30');
        }
    } else {
        if(statusCard) {
            statusCard.classList.remove('from-green-500', 'to-emerald-600', 'shadow-green-500/30');
            statusCard.classList.add('from-red-500', 'to-rose-600', 'shadow-red-500/30');
        }
    }
}
function updateDashUI(data) {
    if (data.online !== undefined) {
        updateDashStatusUI(data.online);
    }
    if (data.data_tx_gb !== undefined) {
        document.getElementById('dash-download').textContent = data.data_tx_gb.toFixed(1) + ' GB';
    }
    if (data.data_rx_gb !== undefined) {
        document.getElementById('dash-upload').textContent = data.data_rx_gb.toFixed(1) + ' GB';
    }
    if (data.data_used_today !== undefined) {
        document.getElementById('dash-total').innerHTML = data.data_used_today.toFixed(2) + ' <span class="text-lg text-dark-400">GB</span>';
    }
    if (data.ip_address !== undefined && data.ip_address !== 'N/A') {
        document.getElementById('conn-ip').textContent = data.ip_address;
    }
    const lastUpdate = document.getElementById('last-update');
    if(lastUpdate) lastUpdate.textContent = new Date().toLocaleTimeString();
}
function fetchDashLiveData() {
    fetch('<?= base_url("portal/api/live") ?>', {
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        updateDashUI(data);
    })
    .catch(err => console.error('Live data error:', err));
}
if (isOnlineDash) {
    updateDashStatusUI(true);
    setInterval(fetchDashLiveData, 5000);
} else {
    updateDashStatusUI(false);
    setInterval(fetchDashLiveData, 10000);
}
</script>
