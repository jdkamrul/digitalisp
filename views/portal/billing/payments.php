<?php
$currentPage = 'billing-payments';
$content = __DIR__ . '/content.php';

function renderPaymentsContent(): void {
    global $payments, $totalPages, $page, $pageTitle;
?>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment History</h3>
    </div>
    
    <?php if (empty($payments)): ?>
    <div class="p-12 text-center">
        <i class="fas fa-receipt text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400">No payment history found</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Receipt #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Invoice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($payments as $payment): ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        <?= sanitize($payment['receipt_number']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <?= sanitize($payment['invoice_number'] ?? 'Advance') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                        <?= formatMoney($payment['amount']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?= match($payment['payment_method']) { 'online' => 'bg-blue-100 text-blue-700', 'mobile_banking' => 'bg-pink-100 text-pink-700', 'bank_transfer' => 'bg-purple-100 text-purple-700', default => 'bg-gray-100 text-gray-700' } ?>">
                            <?= str_replace('_', ' ', ucfirst($payment['payment_method'])) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        <?= formatDate($payment['payment_date'], 'd M Y h:i A') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?= base_url('portal/billing/receipt/' . $payment['receipt_number']) ?>" class="text-primary-600 hover:text-primary-900">
                            <i class="fas fa-eye"></i> View
                        </a>
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
<?php } ?>
