<?php // views/portal/profile/change-password-content.php ?>
<div class="max-w-lg mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <i class="fas fa-key text-primary-500 mr-2"></i> Change Password
        </h3>
        <form method="POST" action="<?= base_url('portal/profile/change-password') ?>" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                <input type="password" name="current_password" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Enter current password">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                <input type="password" name="new_password" required minlength="6"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Minimum 6 characters">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6"
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Repeat new password">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-3 bg-primary-500 text-white font-semibold rounded-lg hover:bg-primary-600 transition">
                    <i class="fas fa-save mr-2"></i> Update Password
                </button>
                <a href="<?= base_url('portal/profile') ?>" class="px-6 py-3 border border-gray-300 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
