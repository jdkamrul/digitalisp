<?php
global $customer, $usageData, $pageTitle;
?>
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="text-2xl font-bold mt-1 <?= ($usageData['online'] ?? false) ? 'text-green-600' : 'text-red-600' ?>">
                    <?= ($usageData['online'] ?? false) ? 'Online' : 'Offline' ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full <?= ($usageData['online'] ?? false) ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?> flex items-center justify-center">
                <i class="fas fa-wifi text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Download</p>
                <p class="text-2xl font-bold mt-1 text-blue-600">
                    <?= ($usageData['speed_download'] ?? 0) > 0 ? number_format($usageData['speed_download'], 1) . ' Mbps' : 'N/A' ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                <i class="fas fa-arrow-down text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Upload</p>
                <p class="text-2xl font-bold mt-1 text-green-600">
                    <?= ($usageData['speed_upload'] ?? 0) > 0 ? number_format($usageData['speed_upload'], 1) . ' Mbps' : 'N/A' ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                <i class="fas fa-arrow-up text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Session</p>
                <p class="text-lg font-bold mt-1 text-purple-600 truncate">
                    <?= sanitize($usageData['session_time'] ?? 'N/A') ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
                <i class="fas fa-clock text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Usage Details -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-bar text-primary-500 mr-2"></i> Today's Usage
        </h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <i class="fas fa-arrow-down text-blue-500 mb-2"></i>
                <p class="text-sm text-gray-500">Download</p>
                <p class="text-xl font-bold text-blue-600"><?= number_format($usageData['today_download'] ?? 0, 2) ?> GB</p>
            </div>
            <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <i class="fas fa-arrow-up text-green-500 mb-2"></i>
                <p class="text-sm text-gray-500">Upload</p>
                <p class="text-xl font-bold text-green-600"><?= number_format($usageData['today_upload'] ?? 0, 2) ?> GB</p>
            </div>
            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <i class="fas fa-database text-purple-500 mb-2"></i>
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-xl font-bold text-purple-600"><?= number_format($usageData['today_total'] ?? 0, 2) ?> GB</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-network-wired text-primary-500 mr-2"></i> Connection Details
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">IP Address</span>
                <span class="font-mono font-medium"><?= sanitize($usageData['ip_address'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Package</span>
                <span class="font-medium"><?= sanitize($customer['package_name'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Speed</span>
                <span class="font-medium"><?= sanitize(($customer['speed_download'] ?? 'N/A') . ' / ' . ($customer['speed_upload'] ?? 'N/A')) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">NAS Status</span>
                <span class="px-2 py-1 text-xs rounded-full <?= ($usageData['nas_status'] ?? '') === 'online' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= ucfirst($usageData['nas_status'] ?? 'Unknown') ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Package Info -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        <i class="fas fa-box text-primary-500 mr-2"></i> Your Package
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Download</p>
            <p class="text-2xl font-bold text-blue-600"><?= sanitize($customer['speed_download'] ?? 'N/A') ?></p>
        </div>
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Upload</p>
            <p class="text-2xl font-bold text-green-600"><?= sanitize($customer['speed_upload'] ?? 'N/A') ?></p>
        </div>
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Data Limit</p>
            <p class="text-2xl font-bold text-purple-600"><?= sanitize($customer['data_limit'] ?? 'Unlimited') ?></p>
        </div>
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Monthly Fee</p>
            <p class="text-2xl font-bold text-primary-600"><?= formatMoney($customer['monthly_charge'] ?? 0) ?></p>
        </div>
    </div>
</div>
