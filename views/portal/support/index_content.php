<?php
global $tickets, $status;
?>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b flex justify-between items-center">
        <h3 class="text-lg font-semibold">My Support Tickets</h3>
        <a href="<?= base_url('portal/support/create') ?>" class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600">
            <i class="fas fa-plus mr-2"></i> New Ticket
        </a>
    </div>
    <div class="p-4 border-b flex gap-2">
        <a href="<?= base_url('portal/support') ?>" class="px-3 py-1.5 text-sm rounded-full <?= !$status ? 'bg-primary-500 text-white' : 'bg-gray-100' ?>">All</a>
        <?php foreach (['open', 'in_progress', 'pending_customer', 'resolved', 'closed'] as $s): ?>
        <a href="?status=<?= $s ?>" class="px-3 py-1.5 text-sm rounded-full <?= $status === $s ? 'bg-primary-500 text-white' : 'bg-gray-100' ?>">
            <?= ucwords(str_replace('_', ' ', $s)) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php if (empty($tickets)): ?>
    <div class="p-12 text-center">
        <i class="fas fa-headset text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500">No tickets found</p>
    </div>
    <?php else: ?>
    <div class="divide-y">
        <?php foreach ($tickets as $t): ?>
        <a href="<?= base_url('portal/support/view/' . $t['id']) ?>" class="block p-4 hover:bg-gray-50">
            <div class="flex justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono text-sm text-gray-500"><?= sanitize($t['ticket_number']) ?></span>
                        <span class="px-2 py-0.5 text-xs rounded-full <?= $t['status'] === 'open' ? 'bg-red-100 text-red-700' : ($t['status'] === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700') ?>">
                            <?= ucwords(str_replace('_', ' ', $t['status'])) ?>
                        </span>
                    </div>
                    <h4 class="font-medium"><?= sanitize($t['subject']) ?></h4>
                </div>
                <div class="text-right text-sm text-gray-500">
                    <?= formatDate($t['created_at'], 'd M Y') ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
