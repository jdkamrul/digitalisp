<?php
global $customer, $currentBill, $unpaidInvoices, $recentPayments, $openTickets, $usageData, $pageTitle;
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Connection Status -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Connection</p>
                <p class="text-2xl font-bold mt-1 <?= ($customer['status'] ?? '') === 'active' ? 'text-green-600' : 'text-red-600' ?>">
                    <?= ucfirst($customer['status'] ?? 'Unknown') ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full <?= ($customer['status'] ?? '') === 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?> flex items-center justify-center">
                <i class="fas fa-wifi text-xl"></i>
            </div>
        </div>
        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            <i class="fas fa-network-wired mr-1"></i> <?= sanitize($usageData['session_ip'] ?? 'N/A') ?>
        </div>
    </div>

    <!-- Current Bill -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Current Bill</p>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">
                    <?= formatMoney($currentBill['due_amount'] ?? $customer['due_amount'] ?? 0) ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar text-xl"></i>
            </div>
        </div>
        <?php if (!empty($currentBill)): ?>
        <div class="mt-3 text-xs <?= ($currentBill['days_until_due'] ?? 0) < 0 ? 'text-red-600' : 'text-gray-500' ?>">
            <i class="fas fa-calendar mr-1"></i> 
            Due: <?= formatDate($currentBill['due_date'] ?? '', 'd M Y') ?>
            <?php if (($currentBill['days_until_due'] ?? 0) < 0): ?>
                (<?= abs($currentBill['days_until_due']) ?> days overdue)
            <?php elseif (($currentBill['days_until_due'] ?? 0) <= 5): ?>
                (<?= $currentBill['days_until_due'] ?> days left)
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Monthly Package -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Package</p>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">
                    <?= sanitize($customer['package_name'] ?? 'N/A') ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                <i class="fas fa-tachometer-alt text-xl"></i>
            </div>
        </div>
        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            <i class="fas fa-arrow-down mr-1"></i><?= sanitize($customer['speed_download'] ?? 'N/A') ?> | 
            <i class="fas fa-arrow-up mr-1"></i><?= sanitize($customer['speed_upload'] ?? 'N/A') ?>
        </div>
    </div>

    <!-- Support Tickets -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Open Tickets</p>
                <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">
                    <?= $openTickets[0]['count'] ?? 0 ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
                <i class="fas fa-headset text-xl"></i>
            </div>
        </div>
        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            <a href="<?= base_url('portal/support/create') ?>" class="text-primary-600 hover:underline">
                <i class="fas fa-plus mr-1"></i> Create new ticket
            </a>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
        </h3>
        <div class="space-y-3">
            <?php if (($customer['due_amount'] ?? 0) > 0): ?>
            <a href="<?= base_url('portal/billing/pay-form/' . ($currentBill['id'] ?? '')) ?>" 
               class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition">
                <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center mr-3">
                    <i class="fas fa-credit-card text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Pay Bill</p>
                    <p class="text-xs text-gray-500">Pay <?= formatMoney($customer['due_amount'] ?? 0) ?></p>
                </div>
            </a>
            <?php endif; ?>
            
            <a href="<?= base_url('portal/support/create') ?>" 
               class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center mr-3">
                    <i class="fas fa-plus text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">New Support Ticket</p>
                    <p class="text-xs text-gray-500">Report an issue</p>
                </div>
            </a>

            <a href="<?= base_url('portal/profile') ?>" 
               class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition">
                <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center mr-3">
                    <i class="fas fa-user text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Update Profile</p>
                    <p class="text-xs text-gray-500">Manage your account</p>
                </div>
            </a>

            <a href="<?= base_url('portal/usage') ?>" 
               class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center mr-3">
                    <i class="fas fa-chart-bar text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">View Usage</p>
                    <p class="text-xs text-gray-500">Check bandwidth usage</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Account Details -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-user-circle text-primary-500 mr-2"></i> Account Details
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">Customer ID</span>
                <span class="font-medium text-gray-900 dark:text-white"><?= sanitize($customer['customer_code'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">Name</span>
                <span class="font-medium text-gray-900 dark:text-white"><?= sanitize($customer['full_name'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">Phone</span>
                <span class="font-medium text-gray-900 dark:text-white"><?= sanitize($customer['phone'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">PPPoE Username</span>
                <span class="font-medium text-gray-900 dark:text-white"><?= sanitize($customer['pppoe_username'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500 dark:text-gray-400">Monthly Charge</span>
                <span class="font-medium text-gray-900 dark:text-white"><?= formatMoney($customer['monthly_charge'] ?? 0) ?></span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-500 dark:text-gray-400">Advance Balance</span>
                <span class="font-medium text-green-600"><?= formatMoney($customer['advance_balance'] ?? 0) ?></span>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-receipt text-green-500 mr-2"></i> Recent Payments
            </h3>
            <a href="<?= base_url('portal/billing/payments') ?>" class="text-sm text-primary-600 hover:underline">View all</a>
        </div>
        <?php if (empty($recentPayments)): ?>
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <i class="fas fa-inbox text-4xl mb-3"></i>
            <p>No payment history</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($recentPayments as $payment): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-3">
                        <i class="fas fa-check text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?= formatMoney($payment['amount']) ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= formatDate($payment['payment_date'], 'd M Y') ?></p>
                    </div>
                </div>
                <a href="<?= base_url('portal/billing/receipt/' . $payment['receipt_number']) ?>" class="text-primary-600 hover:text-primary-700">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Usage Summary -->
<div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            <i class="fas fa-chart-area text-blue-500 mr-2"></i> Today's Usage
        </h3>
        <a href="<?= base_url('portal/usage') ?>" class="text-sm text-primary-600 hover:underline">Details</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Download</p>
            <p class="text-2xl font-bold text-blue-600"><?= number_format($usageData['today_download'] ?? 0, 2) ?> GB</p>
        </div>
        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Upload</p>
            <p class="text-2xl font-bold text-green-600"><?= number_format($usageData['today_upload'] ?? 0, 2) ?> GB</p>
        </div>
        <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
            <p class="text-2xl font-bold text-purple-600"><?= number_format($usageData['today_total'] ?? 0, 2) ?> GB</p>
        </div>
        <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
            <p class="text-2xl font-bold <?= ($usageData['online'] ?? false) ? 'text-green-600' : 'text-red-600' ?>">
                <?= ($usageData['online'] ?? false) ? 'Online' : 'Offline' ?>
            </p>
        </div>
    </div>
</div>
