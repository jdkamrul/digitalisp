<?php
$currentPage = 'billing-invoice-view';
$content = __DIR__ . '/content.php';

function renderInvoiceContent(): void {
    global $invoice, $payments, $customer, $pageTitle;
?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Invoice</h3>
                <p class="text-gray-500 dark:text-gray-400"><?= sanitize($invoice['invoice_number']) ?></p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 text-sm font-medium rounded-full <?= match($invoice['status']) { 'paid' => 'bg-green-100 text-green-700', 'partial' => 'bg-yellow-100 text-yellow-700', default => 'bg-red-100 text-red-700' } ?>">
                    <?= ucfirst($invoice['status']) ?>
                </span>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="p-6">
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h4 class="text-sm text-gray-500 dark:text-gray-400 mb-1">Bill For</h4>
                    <p class="font-medium text-gray-900 dark:text-white"><?= sanitize($customer['full_name']) ?></p>
                    <p class="text-sm text-gray-500"><?= sanitize($customer['address']) ?></p>
                    <?php if (!empty($customer['billing_company_name'])): ?>
                    <p class="text-sm"><?= sanitize($customer['billing_company_name']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <h4 class="text-sm text-gray-500 dark:text-gray-400 mb-1">Billing Period</h4>
                    <p class="font-medium text-gray-900 dark:text-white"><?= formatDate($invoice['billing_month'], 'F Y') ?></p>
                    <p class="text-sm text-gray-500">Due: <?= formatDate($invoice['due_date'], 'd M Y') ?></p>
                </div>
            </div>

            <!-- Charges Table -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden mb-6">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Description</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">
                                <?= sanitize($invoice['package_name'] ?? 'Internet Service') ?>
                                <br><span class="text-sm text-gray-500"><?= formatDate($invoice['billing_month'], 'F Y') ?></span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white"><?= formatMoney($invoice['amount']) ?></td>
                        </tr>
                        <?php if ($invoice['discount'] > 0): ?>
                        <tr>
                            <td class="px-4 py-3 text-green-600">Discount</td>
                            <td class="px-4 py-3 text-right text-green-600">-<?= formatMoney($invoice['discount']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($invoice['vat'] > 0): ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">VAT</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white"><?= formatMoney($invoice['vat']) ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-900 dark:text-white">Total</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white"><?= formatMoney($invoice['total']) ?></th>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-left text-green-600">Paid</td>
                            <td class="px-4 py-2 text-right text-green-600"><?= formatMoney($invoice['paid_amount']) ?></td>
                        </tr>
                        <tr class="border-t-2 border-gray-300">
                            <td class="px-4 py-2 text-left font-bold text-lg text-gray-900 dark:text-white">Balance Due</td>
                            <td class="px-4 py-2 text-right font-bold text-lg <?= $invoice['due_amount'] > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                <?= formatMoney($invoice['due_amount']) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Payment History -->
            <?php if (!empty($payments)): ?>
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment History</h4>
                <div class="space-y-2">
                    <?php foreach ($payments as $payment): ?>
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white"><?= formatMoney($payment['amount']) ?></p>
                            <p class="text-xs text-gray-500"><?= formatDate($payment['payment_date'], 'd M Y h:i A') ?></p>
                        </div>
                        <span class="text-xs text-green-600"><?= $payment['receipt_number'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <a href="<?= base_url('portal/billing/invoices') ?>" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                <div class="space-x-3">
                    <?php if ($invoice['status'] !== 'paid'): ?>
                    <a href="<?= base_url('portal/billing/pay-form/' . $invoice['id']) ?>" 
                       class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium inline-flex items-center">
                        <i class="fas fa-credit-card mr-2"></i> Pay Now
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
