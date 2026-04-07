<?php // views/portal/billing/receipt-content.php ?>
<style>@media print { .no-print { display:none; } }</style>
<div class="max-w-lg mx-auto">
    <div class="no-print flex justify-between items-center mb-4">
        <a href="<?= base_url('portal/billing/payments') ?>" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 text-sm">
            <i class="fas fa-print mr-2"></i> Print
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Money Receipt</h2>
            <p class="text-gray-500 text-sm mt-1"><?= sanitize($portalSettings['portal_name'] ?? 'Customer Portal') ?></p>
        </div>

        <?php if (!empty($payment)): ?>
        <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 mb-6 text-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider">Receipt Number</p>
            <p class="text-xl font-mono font-bold text-gray-900 mt-1"><?= sanitize($payment['receipt_number'] ?? '—') ?></p>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm">Customer</span>
                <span class="font-semibold text-sm"><?= sanitize($payment['full_name'] ?? '—') ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm">Customer ID</span>
                <span class="font-mono text-sm"><?= sanitize($payment['customer_code'] ?? '—') ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm">Invoice</span>
                <span class="font-mono text-sm"><?= sanitize($payment['invoice_number'] ?? 'Advance') ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm">Payment Method</span>
                <span class="text-sm"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'] ?? '—')) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500 text-sm">Date</span>
                <span class="text-sm"><?= date('d M Y H:i', strtotime($payment['payment_date'] ?? 'now')) ?></span>
            </div>
            <div class="flex justify-between py-3 bg-green-50 rounded-lg px-3 mt-2">
                <span class="font-bold text-gray-900">Amount Paid</span>
                <span class="font-bold text-green-600 text-xl"><?= formatMoney($payment['amount'] ?? 0) ?></span>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-8 text-gray-500">Receipt not found.</div>
        <?php endif; ?>

        <div class="mt-6 text-center text-xs text-gray-400">
            Thank you for your payment. Please keep this receipt for your records.
        </div>
    </div>
</div>
