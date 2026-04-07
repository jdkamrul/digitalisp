<?php // views/portal/profile/notifications-content.php ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-bell text-primary-500 mr-2"></i> Notifications
            </h3>
            <?php if (!empty($notifications)): ?>
            <form method="POST" action="<?= base_url('portal/profile/notifications/read') ?>">
                <input type="hidden" name="mark_all" value="1">
                <button type="submit" class="text-sm text-primary-600 hover:underline">Mark all as read</button>
            </form>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
            <i class="fas fa-bell-slash text-4xl mb-3 opacity-30"></i>
            <p>No notifications yet.</p>
        </div>
        <?php else: ?>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($notifications as $n): ?>
            <div class="p-4 flex items-start gap-3 <?= !$n['is_read'] ? 'bg-blue-50 dark:bg-blue-900/10' : '' ?>">
                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-bell text-xs"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?= sanitize($n['title']) ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= sanitize($n['message'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></p>
                </div>
                <?php if (!$n['is_read']): ?>
                <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
