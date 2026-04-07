<?php
global $invoices, $totalPages, $page;
?>
<!-- Premium Invoices Table -->
<div class="glass-card-dark rounded-2xl border border-dark-700/50 overflow-hidden">
    <div class="p-6 border-b border-dark-700/50 flex justify-between items-center">
        <h3 class="text-lg font-bold text-white">
            <i class="fas fa-file-invoice-dollar text-primary-400 mr-2"></i> Invoice History
        </h3>
        <a href="<?= base_url('portal/billing/payments') ?>" class="text-sm text-primary-400 hover:text-primary-300">
            <i class="fas fa-history mr-1"></i> Payment History
        </a>
    </div>
    
    <?php if (empty($invoices)): ?>
    <div class="p-12 text-center">
        <div class="w-20 h-20 rounded-2xl bg-dark-800 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-file-invoice text-dark-500 text-4xl"></i>
        </div>
        <p class="text-dark-400">No invoices found</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-800/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Invoice #</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Month</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Total</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Paid</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Due</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700/50">
                <?php foreach ($invoices as $inv): ?>
                <tr class="hover:bg-dark-800/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono text-primary-400"><?= sanitize($inv['invoice_number']) ?></td>
                    <td class="px-6 py-4 text-sm text-dark-300"><?= formatDate($inv['billing_month'], 'M Y') ?></td>
                    <td class="px-6 py-4 text-sm font-semibold text-white"><?= formatMoney($inv['total']) ?></td>
                    <td class="px-6 py-4 text-sm text-green-400"><?= formatMoney($inv['paid_amount']) ?></td>
                    <td class="px-6 py-4 text-sm <?= $inv['due_amount'] > 0 ? 'text-red-400 font-semibold' : 'text-dark-400' ?>">
                        <?= formatMoney($inv['due_amount']) ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 text-xs rounded-full <?= $inv['status'] === 'paid' ? 'bg-green-500/20 text-green-400' : ($inv['status'] === 'partial' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') ?>">
                            <?= ucfirst($inv['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <a href="<?= base_url('portal/billing/invoice/' . $inv['id']) ?>" class="text-dark-400 hover:text-primary-400 transition-colors">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($inv['status'] !== 'paid'): ?>
                            <a href="<?= base_url('portal/billing/pay-form/' . $inv['id']) ?>" class="text-green-400 hover:text-green-300 transition-colors">
                                <i class="fas fa-credit-card"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
