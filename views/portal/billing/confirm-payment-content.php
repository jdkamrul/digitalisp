<?php // views/portal/billing/confirm-payment-content.php ?>
<div class="max-w-lg mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <i class="fas fa-credit-card text-primary-500 mr-2"></i> Confirm Payment
        </h3>
        <?php if (!empty($paymentData)): ?>
        <div class="space-y-3 mb-6">
            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500">Invoice</span>
                <span class="font-semibold"><?= sanitize($paymentData['invoice_number'] ?? '—') ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                <span class="text-gray-500">Amount</span>
                <span class="font-bold text-green-600 text-xl"><?= formatMoney($paymentData['amount'] ?? 0) ?></span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-gray-500">Method</span>
                <span class="font-semibold"><?= sanitize($paymentData['method'] ?? '—') ?></span>
            </div>
        </div>
        <form method="POST" action="<?= base_url('portal/billing/confirm-payment') ?>">
            <input type="hidden" name="payment_data" value="<?= htmlspecialchars(json_encode($paymentData)) ?>">
            <button type="submit" class="w-full py-3 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition">
                <i class="fas fa-check mr-2"></i> Confirm Payment
            </button>
        </form>
        <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <p>No payment data found.</p>
            <a href="<?= base_url('portal/billing/invoices') ?>" class="text-primary-600 hover:underline mt-2 block">Back to Invoices</a>
        </div>
        <?php endif; ?>
    </div>
</div>
