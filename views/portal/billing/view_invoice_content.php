<?php
global $invoice, $payments, $customer;
?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold">Invoice</h3>
                <p class="text-gray-500"><?= sanitize($invoice['invoice_number']) ?></p>
            </div>
            <span class="px-3 py-1 text-sm rounded-full <?= $invoice['status'] === 'paid' ? 'bg-green-100 text-green-700' : ($invoice['status'] === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                <?= ucfirst($invoice['status']) ?>
            </span>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h4 class="text-sm text-gray-500 mb-1">Bill For</h4>
                    <p class="font-medium"><?= sanitize($customer['full_name']) ?></p>
                    <p class="text-sm text-gray-500"><?= sanitize($customer['address']) ?></p>
                </div>
                <div class="text-right">
                    <h4 class="text-sm text-gray-500 mb-1">Billing Period</h4>
                    <p class="font-medium"><?= formatDate($invoice['billing_month'], 'F Y') ?></p>
                    <p class="text-sm text-gray-500">Due: <?= formatDate($invoice['due_date'], 'd M Y') ?></p>
                </div>
            </div>
            <table class="w-full border mb-6">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs">Description</th>
                        <th class="px-4 py-2 text-right text-xs">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 py-3"><?= sanitize($invoice['package_name'] ?? 'Internet Service') ?></td>
                        <td class="px-4 py-3 text-right"><?= formatMoney($invoice['amount']) ?></td>
                    </tr>
                    <?php if ($invoice['discount'] > 0): ?>
                    <tr><td class="px-4 py-2 text-green-600">Discount</td><td class="px-4 py-2 text-right">-<?= formatMoney($invoice['discount']) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($invoice['vat'] > 0): ?>
                    <tr><td class="px-4 py-2">VAT</td><td class="px-4 py-2 text-right"><?= formatMoney($invoice['vat']) ?></td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr><th class="px-4 py-2 text-left">Total</th><th class="px-4 py-2 text-right"><?= formatMoney($invoice['total']) ?></th></tr>
                    <tr><td class="px-4 py-2 text-green-600">Paid</td><td class="px-4 py-2 text-right"><?= formatMoney($invoice['paid_amount']) ?></td></tr>
                    <tr class="border-t-2"><td class="px-4 py-2 font-bold text-lg">Balance Due</td><td class="px-4 py-2 text-right font-bold text-lg <?= $invoice['due_amount'] > 0 ? 'text-red-600' : 'text-green-600' ?>"><?= formatMoney($invoice['due_amount']) ?></td></tr>
                </tfoot>
            </table>
            <?php if (!empty($payments)): ?>
            <h4 class="font-medium mb-3">Payment History</h4>
            <div class="space-y-2 mb-6">
                <?php foreach ($payments as $p): ?>
                <div class="flex justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium"><?= formatMoney($p['amount']) ?></p>
                        <p class="text-xs text-gray-500"><?= formatDate($p['payment_date'], 'd M Y h:i A') ?></p>
                    </div>
                    <span class="text-xs text-green-600"><?= $p['receipt_number'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="flex justify-between">
                <a href="<?= base_url('portal/billing/invoices') ?>" class="text-gray-500 hover:text-gray-700"><i class="fas fa-arrow-left mr-2"></i>Back</a>
                <?php if ($invoice['status'] !== 'paid'): ?>
                <a href="<?= base_url('portal/billing/pay-form/' . $invoice['id']) ?>" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Pay Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
