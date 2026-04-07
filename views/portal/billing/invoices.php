<?php
global $invoices, $totalPages, $page, $pageTitle;
?>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice History</h3>
        <a href="<?= base_url('portal/billing/payments') ?>" class="text-sm text-primary-600 hover:underline">
            <i class="fas fa-history mr-1"></i> Payment History
        </a>
    </div>
    
    <?php if (empty($invoices)): ?>
    <div class="p-12 text-center">
        <i class="fas fa-file-invoice text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400">No invoices found</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Billing Month</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paid</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($invoices as $invoice): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        <?= sanitize($invoice['invoice_number']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <?= formatDate($invoice['billing_month'], 'M Y') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        <?= formatMoney($invoice['total']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                        <?= formatMoney($invoice['paid_amount']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm <?= $invoice['due_amount'] > 0 ? 'text-red-600 font-medium' : 'text-gray-500' ?>">
                        <?= formatMoney($invoice['due_amount']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php
                        $statusClass = match($invoice['status']) {
                            'paid' => 'bg-green-100 text-green-800',
                            'partial' => 'bg-yellow-100 text-yellow-800',
                            'unpaid' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        ?>
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?>">
                            <?= ucfirst($invoice['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <?= formatDate($invoice['due_date'], 'd M Y') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?= base_url('portal/billing/invoice/' . $invoice['id']) ?>" class="text-primary-600 hover:text-primary-900 mr-3">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <?php if ($invoice['status'] !== 'paid'): ?>
                        <a href="<?= base_url('portal/billing/pay-form/' . $invoice['id']) ?>" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-credit-card"></i> Pay
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-center">
        <nav class="flex space-x-2">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" 
               class="px-4 py-2 text-sm rounded-lg <?= $i === $page ? 'bg-primary-500 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
