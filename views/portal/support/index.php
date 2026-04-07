<?php
global $tickets, $status, $pageTitle;
?>
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Support Tickets</h3>
        <a href="<?= base_url('portal/support/create') ?>" class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600">
            <i class="fas fa-plus mr-2"></i> New Ticket
        </a>
    </div>

    <!-- Status Filter -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex space-x-2 overflow-x-auto">
        <a href="<?= base_url('portal/support') ?>" class="px-3 py-1.5 text-sm rounded-full <?= !$status ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' ?>">
            All
        </a>
        <?php foreach (['open', 'in_progress', 'pending_customer', 'resolved', 'closed'] as $s): ?>
        <a href="?status=<?= $s ?>" class="px-3 py-1.5 text-sm rounded-full <?= $status === $s ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' ?>">
            <?= ucwords(str_replace('_', ' ', $s)) ?>
        </a>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($tickets)): ?>
    <div class="p-12 text-center">
        <i class="fas fa-headset text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400 mb-4">No tickets found</p>
        <a href="<?= base_url('portal/support/create') ?>" class="text-primary-600 hover:underline">Create your first ticket</a>
    </div>
    <?php else: ?>
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <?php foreach ($tickets as $ticket): ?>
        <a href="<?= base_url('portal/support/view/' . $ticket['id']) ?>" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                        <span class="font-mono text-sm text-gray-500"><?= sanitize($ticket['ticket_number']) ?></span>
                        <?php
                        $statusClass = match($ticket['status']) {
                            'open' => 'bg-red-100 text-red-700',
                            'in_progress' => 'bg-yellow-100 text-yellow-700',
                            'pending_customer' => 'bg-orange-100 text-orange-700',
                            'resolved' => 'bg-green-100 text-green-700',
                            'closed' => 'bg-gray-100 text-gray-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                        ?>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full <?= $statusClass ?>">
                            <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
                        </span>
                        <?php if (in_array($ticket['priority'], ['urgent', 'high'])): ?>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">
                            <?= ucfirst($ticket['priority']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="font-medium text-gray-900 dark:text-white"><?= sanitize($ticket['subject']) ?></h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2"><?= sanitize(substr($ticket['description'], 0, 150)) ?>...</p>
                </div>
                <div class="ml-4 text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= formatDate($ticket['created_at'], 'd M Y') ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?= formatDate($ticket['created_at'], 'h:i A') ?></p>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
