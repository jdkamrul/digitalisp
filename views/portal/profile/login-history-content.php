<?php // views/portal/profile/login-history-content.php ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-history text-primary-500 mr-2"></i> Login History
            </h3>
        </div>
        <?php if (empty($loginHistory)): ?>
        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
            <i class="fas fa-history text-4xl mb-3 opacity-30"></i>
            <p>No login history available.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($loginHistory as $log): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-400"><?= sanitize($log['ip_address'] ?? '—') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400"><?= ucfirst($log['login_method'] ?? '—') ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full <?= $log['status'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                <?= ucfirst($log['status'] ?? '—') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <div class="mt-4">
        <a href="<?= base_url('portal/profile') ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Profile
        </a>
    </div>
</div>
