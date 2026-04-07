<?php
global $payments, $totalPages, $page;
?>
<!-- Premium Payments Table -->
<div class="glass-card-dark rounded-2xl border border-dark-700/50 overflow-hidden">
    <div class="p-6 border-b border-dark-700/50">
        <h3 class="text-lg font-bold text-white">
            <i class="fas fa-history text-green-400 mr-2"></i> Payment History
        </h3>
    </div>
    
    <?php if (empty($payments)): ?>
    <div class="p-12 text-center">
        <div class="w-20 h-20 rounded-2xl bg-dark-800 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-receipt text-dark-500 text-4xl"></i>
        </div>
        <p class="text-dark-400">No payment history</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-800/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Receipt #</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Invoice</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Method</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-dark-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700/50">
                <?php foreach ($payments as $p): ?>
                <tr class="hover:bg-dark-800/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono text-primary-400"><?= sanitize($p['receipt_number']) ?></td>
                    <td class="px-6 py-4 text-sm text-dark-300"><?= sanitize($p['invoice_number'] ?? 'Advance') ?></td>
                    <td class="px-6 py-4 text-sm font-bold text-green-400"><?= formatMoney($p['amount']) ?></td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-3 py-1 text-xs rounded-full bg-dark-800 text-dark-300"><?= ucfirst(str_replace('_', ' ', $p['payment_method'])) ?></span>
                    </td>
                    <td class="px-6 py-4 text-sm text-dark-400"><?= formatDate($p['payment_date'], 'd M Y') ?></td>
                    <td class="px-6 py-4">
                        <a href="<?= base_url('portal/billing/receipt/' . $p['receipt_number']) ?>" class="text-primary-400 hover:text-primary-300 transition-colors">
                            <i class="fas fa-print"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
