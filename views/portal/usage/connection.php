<?php // views/portal/usage/connection.php ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-network-wired text-blue-500 mr-2"></i> Connection Status
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500">Status</span>
                <span class="font-semibold <?= ($customer['status'] ?? '') === 'active' ? 'text-green-600' : 'text-red-600' ?>">
                    <?= ucfirst($customer['status'] ?? 'Unknown') ?>
                </span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500">PPPoE Username</span>
                <span class="font-mono font-semibold"><?= sanitize($customer['pppoe_username'] ?? '—') ?></span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500">Package</span>
                <span class="font-semibold"><?= sanitize($customer['package_name'] ?? '—') ?></span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-gray-500">Speed</span>
                <span class="font-semibold"><?= sanitize($customer['speed_download'] ?? '—') ?> ↓ / <?= sanitize($customer['speed_upload'] ?? '—') ?> ↑</span>
            </div>
        </div>
        <div class="mt-6">
            <a href="<?= base_url('portal/usage') ?>" class="text-primary-600 hover:underline text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Usage
            </a>
        </div>
    </div>
</div>
