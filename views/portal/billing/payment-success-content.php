<?php // views/portal/billing/payment-success-content.php ?>
<div class="max-w-lg mx-auto text-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check-circle text-green-500 text-4xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Payment Successful!</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Your payment has been processed successfully.</p>

        <?php if (!empty($payment)): ?>
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-left space-y-2 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Receipt #</span>
                <span class="font-mono font-semibold"><?= sanitize($payment['receipt_number'] ?? '—') ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Amount Paid</span>
                <span class="font-bold text-green-600"><?= formatMoney($payment['amount'] ?? 0) ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Date</span>
                <span><?= date('d M Y H:i', strtotime($payment['payment_date'] ?? 'now')) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex gap-3">
            <a href="<?= base_url('portal/billing/invoices') ?>" class="flex-1 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition font-semibold">
                <i class="fas fa-file-invoice mr-2"></i> View Invoices
            </a>
            <a href="<?= base_url('portal/dashboard') ?>" class="flex-1 py-3 border border-gray-300 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition font-semibold">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
        </div>
    </div>
</div>
