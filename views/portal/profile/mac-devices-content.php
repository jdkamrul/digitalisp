<?php // views/portal/profile/mac-devices-content.php ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-laptop text-primary-500 mr-2"></i> Registered Devices
            </h3>
            <span class="text-sm text-gray-500"><?= count($devices ?? []) ?> / <?= $maxDevices ?? 5 ?> devices</span>
        </div>

        <?php if (empty($devices)): ?>
        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
            <i class="fas fa-laptop text-4xl mb-3 opacity-30"></i>
            <p>No devices registered yet.</p>
        </div>
        <?php else: ?>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php foreach ($devices as $device): ?>
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div>
                        <p class="font-mono font-semibold text-gray-900 dark:text-white"><?= sanitize($device['mac_address']) ?></p>
                        <p class="text-xs text-gray-500"><?= $device['last_seen'] ? 'Last seen: ' . date('d M Y H:i', strtotime($device['last_seen'])) : 'Never seen' ?></p>
                    </div>
                </div>
                <form method="POST" action="<?= base_url('portal/profile/mac-devices/remove') ?>" onsubmit="return confirm('Remove this device?');">
                    <input type="hidden" name="mac_address" value="<?= sanitize($device['mac_address']) ?>">
                    <button type="submit" class="text-red-500 hover:text-red-700 p-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($canAdd ?? true): ?>
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Add New Device</h4>
            <form method="POST" action="<?= base_url('portal/profile/mac-devices/add') ?>" class="flex gap-3">
                <input type="text" name="mac_address" required
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white font-mono"
                    placeholder="AA:BB:CC:DD:EE:FF">
                <button type="submit" class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition">
                    <i class="fas fa-plus mr-1"></i> Add
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <div class="mt-4">
        <a href="<?= base_url('portal/profile') ?>" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Profile
        </a>
    </div>
</div>
