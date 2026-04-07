<?php
global $customer, $usageData;
$isOnline = $usageData['online'] ?? false;
?>
<!-- Usage Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Status Card -->
    <div class="relative overflow-hidden rounded-2xl <?= $isOnline ? 'bg-gradient-to-br from-green-500 to-emerald-600' : 'bg-gradient-to-br from-red-500 to-rose-600' ?> p-5 shadow-lg status-card-usage">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <p class="text-white/80 text-sm font-medium">Status</p>
                <p class="text-3xl font-bold text-white mt-1" id="status-text">
                    <?= $isOnline ? 'Online' : 'Offline' ?>
                </p>
                <p class="text-white/70 text-xs mt-2" id="session-time"><?= sanitize($usageData['session_time'] ?? 'N/A') ?></p>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center" id="status-icon">
                <i class="fas fa-wifi text-3xl text-white"></i>
            </div>
        </div>
    </div>

    <!-- Download Speed -->
    <div class="glass-card-dark rounded-2xl p-5 border border-dark-700/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-dark-400 text-sm font-medium">Download</p>
                <p class="text-3xl font-bold text-primary-400 mt-1" id="download-speed">
                    <?= ($usageData['speed_download'] ?? 0) > 0 ? number_format($usageData['speed_download'], 1) . ' Mbps' : 'N/A' ?>
                </p>
                <p class="text-dark-500 text-xs mt-2">Current Speed</p>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-primary-500/20 flex items-center justify-center">
                <i class="fas fa-arrow-down text-3xl text-primary-400"></i>
            </div>
        </div>
    </div>

    <!-- Upload Speed -->
    <div class="glass-card-dark rounded-2xl p-5 border border-dark-700/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-dark-400 text-sm font-medium">Upload</p>
                <p class="text-3xl font-bold text-accent-400 mt-1" id="upload-speed">
                    <?= ($usageData['speed_upload'] ?? 0) > 0 ? number_format($usageData['speed_upload'], 1) . ' Mbps' : 'N/A' ?>
                </p>
                <p class="text-dark-500 text-xs mt-2">Current Speed</p>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-accent-500/20 flex items-center justify-center">
                <i class="fas fa-arrow-up text-3xl text-accent-400"></i>
            </div>
        </div>
    </div>

    <!-- Session Time -->
    <div class="glass-card-dark rounded-2xl p-5 border border-dark-700/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-dark-400 text-sm font-medium">Session Time</p>
                <p class="text-2xl font-bold text-white mt-1">
                    <?= sanitize($usageData['session_time'] ?? 'N/A') ?>
                </p>
                <p class="text-dark-500 text-xs mt-2">Connected Since</p>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-yellow-500/20 flex items-center justify-center">
                <i class="fas fa-clock text-3xl text-yellow-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Usage & Connection Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Today's Usage -->
    <div class="glass-card-dark rounded-2xl p-6 border border-dark-700/50">
        <h3 class="text-lg font-bold text-white mb-5">
            <i class="fas fa-chart-pie text-primary-400 mr-2"></i> Data Usage
        </h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-dark-800/50 rounded-xl border border-dark-700/50">
                <i class="fas fa-arrow-down text-primary-400 text-xl mb-2"></i>
                <p class="text-sm text-dark-400">Download</p>
                <p class="text-xl font-bold text-primary-400" id="today-download"><?= number_format($usageData['today_download'] ?? 0, 2) ?> GB</p>
            </div>
            <div class="text-center p-4 bg-dark-800/50 rounded-xl border border-dark-700/50">
                <i class="fas fa-arrow-up text-accent-400 text-xl mb-2"></i>
                <p class="text-sm text-dark-400">Upload</p>
                <p class="text-xl font-bold text-accent-400" id="today-upload"><?= number_format($usageData['today_upload'] ?? 0, 2) ?> GB</p>
            </div>
            <div class="text-center p-4 bg-dark-800/50 rounded-xl border border-dark-700/50">
                <i class="fas fa-database text-green-400 text-xl mb-2"></i>
                <p class="text-sm text-dark-400">Total</p>
                <p class="text-xl font-bold text-green-400" id="today-total"><?= number_format($usageData['today_total'] ?? 0, 2) ?> GB</p>
            </div>
        </div>
        
        <!-- Usage Bar -->
        <div class="mt-5">
            <div class="flex justify-between text-sm mb-2">
                <span class="text-dark-400">Usage Progress</span>
                <span class="text-white font-medium"><?= number_format($usageData['today_total'] ?? 0, 2) ?> GB</span>
            </div>
            <div class="h-3 bg-dark-800 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-primary-500 to-accent-500 rounded-full" style="width: <?= min(($usageData['today_total'] ?? 0) / 100 * 100, 100) ?>%"></div>
            </div>
            <p class="text-xs text-dark-500 mt-2">Unlimited data package</p>
        </div>
    </div>

    <!-- Connection Details -->
    <div class="glass-card-dark rounded-2xl p-6 border border-dark-700/50">
        <h3 class="text-lg font-bold text-white mb-5">
            <i class="fas fa-network-wired text-primary-400 mr-2"></i> Connection Details
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-3 px-4 bg-dark-800/50 rounded-xl">
                <span class="text-dark-400"><i class="fas fa-globe mr-2 text-dark-500"></i>IP Address</span>
                <span class="font-mono text-primary-400" id="ip-address"><?= sanitize($usageData['ip_address'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-3 px-4 bg-dark-800/50 rounded-xl">
                <span class="text-dark-400"><i class="fas fa-box mr-2 text-dark-500"></i>Package</span>
                <span class="text-white font-medium"><?= sanitize($customer['package_name'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-3 px-4 bg-dark-800/50 rounded-xl">
                <span class="text-dark-400"><i class="fas fa-tachometer-alt mr-2 text-dark-500"></i>Speed</span>
                <span class="text-white font-medium"><?= sanitize(($customer['speed_download'] ?? 'N/A') . ' / ' . ($customer['speed_upload'] ?? 'N/A')) ?></span>
            </div>
            <div class="flex justify-between items-center py-3 px-4 bg-dark-800/50 rounded-xl">
                <span class="text-dark-400"><i class="fas fa-server mr-2 text-dark-500"></i>NAS Server</span>
                <span class="px-3 py-1 text-xs rounded-full <?= ($usageData['nas_status'] ?? '') === 'online' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' ?>" id="nas-status">
                    <?= ucfirst($usageData['nas_status'] ?? 'Unknown') ?>
                </span>
            </div>
            <div class="flex justify-between items-center py-3 px-4 bg-dark-800/50 rounded-xl">
                <span class="text-dark-400"><i class="fas fa-wifi mr-2 text-dark-500"></i>Connection Type</span>
                <span class="text-white">PPPoE</span>
            </div>
        </div>
    </div>
</div>

<!-- Package Info -->
<div class="mt-6 glass-card-dark rounded-2xl p-6 border border-dark-700/50">
    <h3 class="text-lg font-bold text-white mb-5">
        <i class="fas fa-gift text-accent-400 mr-2"></i> My Package
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-dark-800/50 rounded-xl border border-dark-700/50">
            <p class="text-sm text-dark-400 mb-1">Download</p>
            <p class="text-2xl font-bold text-primary-400"><?= sanitize($customer['speed_download'] ?? 'N/A') ?></p>
            <p class="text-xs text-dark-500">Mbps</p>
        </div>
        <div class="text-center p-4 bg-dark-800/50 rounded-xl border border-dark-700/50">
            <p class="text-sm text-dark-400 mb-1">Upload</p>
            <p class="text-2xl font-bold text-accent-400"><?= sanitize($customer['speed_upload'] ?? 'N/A') ?></p>
            <p class="text-xs text-dark-500">Mbps</p>
        </div>
        <div class="text-center p-4 bg-dark-800/50 rounded-xl border border-dark-700/50">
            <p class="text-sm text-dark-400 mb-1">Data Limit</p>
            <p class="text-2xl font-bold text-green-400"><?= sanitize($customer['data_limit'] ?? 'Unlimited') ?></p>
            <p class="text-xs text-dark-500">GB/Month</p>
        </div>
        <div class="text-center p-4 bg-dark-800/50 rounded-xl border border-dark-700/50">
            <p class="text-sm text-dark-400 mb-1">Monthly Fee</p>
            <p class="text-2xl font-bold text-white"><?= formatMoney($customer['monthly_charge'] ?? 0) ?></p>
            <p class="text-xs text-dark-500">BDT</p>
        </div>
    </div>
</div>

<script>
const isOnline = <?= $isOnline ? 'true' : 'false' ?>;
function updateStatusUI(online) {
    const statusText = document.getElementById('status-text');
    const statusIcon = document.getElementById('status-icon');
    const statusCard = document.querySelector('.status-card-usage');
    if (online) {
        statusText.textContent = 'Online';
        statusText.className = 'text-3xl font-bold text-white';
        statusIcon.className = 'w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center';
        if(statusCard) {
            statusCard.classList.remove('from-red-500', 'to-rose-600');
            statusCard.classList.add('from-green-500', 'to-emerald-600');
        }
    } else {
        statusText.textContent = 'Offline';
        statusText.className = 'text-3xl font-bold text-white';
        statusIcon.className = 'w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center';
        if(statusCard) {
            statusCard.classList.remove('from-green-500', 'to-emerald-600');
            statusCard.classList.add('from-red-500', 'to-rose-600');
        }
    }
}
function updateUI(data) {
    if (data.online !== undefined) {
        updateStatusUI(data.online);
    }
    if (data.speed_download !== undefined) {
        const el = document.getElementById('download-speed');
        el.textContent = data.speed_download > 0 ? data.speed_download.toFixed(1) + ' Mbps' : 'N/A';
    }
    if (data.speed_upload !== undefined) {
        const el = document.getElementById('upload-speed');
        el.textContent = data.speed_upload > 0 ? data.speed_upload.toFixed(1) + ' Mbps' : 'N/A';
    }
    if (data.session_time !== undefined) {
        document.getElementById('session-time').textContent = data.session_time;
    }
    if (data.data_used_today !== undefined) {
        const gb = (data.data_used_today || 0).toFixed(2);
        document.getElementById('today-total').textContent = gb + ' GB';
    }
    if (data.data_tx_gb !== undefined) {
        document.getElementById('today-download').textContent = data.data_tx_gb.toFixed(2) + ' GB';
    }
    if (data.data_rx_gb !== undefined) {
        document.getElementById('today-upload').textContent = data.data_rx_gb.toFixed(2) + ' GB';
    }
    if (data.ip_address !== undefined && data.ip_address !== 'N/A') {
        document.getElementById('ip-address').textContent = data.ip_address;
    }
}
function fetchLiveData() {
    fetch('<?= base_url("portal/api/live") ?>', {
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(data => {
        updateUI(data);
    })
    .catch(err => console.error('Live data error:', err));
}
if (isOnline) {
    updateStatusUI(true);
    setInterval(fetchLiveData, 5000);
} else {
    updateStatusUI(false);
    setInterval(fetchLiveData, 10000);
}
</script>
