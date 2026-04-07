<?php
global $ticket, $replies, $pageTitle;
?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded"><?= sanitize($ticket['ticket_number']) ?></span>
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
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?= $statusClass ?>">
                            <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
                        </span>
                        <?php if ($ticket['priority'] !== 'normal'): ?>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                            <?= ucfirst($ticket['priority']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white"><?= sanitize($ticket['subject']) ?></h3>
                </div>
                <?php if ($ticket['status'] !== 'closed'): ?>
                <form action="<?= base_url('portal/support/close') ?>" method="POST" onsubmit="return confirm('Close this ticket?')">
                    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                    <button type="submit" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Replies -->
        <div class="p-6 max-h-[500px] overflow-y-auto">
            <div class="space-y-4">
                <?php foreach ($replies as $reply): ?>
                <div class="<?= ($reply['customer_id'] ?? null) ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-gray-700 ml-0 md:ml-8' ?> rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full <?= ($reply['customer_id'] ?? null) ? 'bg-blue-500' : 'bg-gray-500' ?> flex items-center justify-center text-white font-semibold mr-2">
                                <?= strtoupper(substr(($reply['customer_name'] ?? $reply['staff_name'] ?? 'S')[0], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    <?= sanitize($reply['customer_name'] ?? $reply['staff_name'] ?? 'Staff') ?>
                                    <span class="text-xs text-gray-500 ml-2"><?= ($reply['customer_id'] ?? null) ? 'You' : 'Support' ?></span>
                                </p>
                                <p class="text-xs text-gray-500"><?= formatDate($reply['created_at'], 'd M Y h:i A') ?></p>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap"><?= nl2br(sanitize($reply['message'])) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Reply Form -->
        <?php if ($ticket['status'] !== 'closed'): ?>
        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            <form action="<?= base_url('portal/support/reply') ?>" method="POST">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reply</label>
                    <textarea name="message" rows="4" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Type your reply..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 font-medium">
                        <i class="fas fa-reply mr-2"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <a href="<?= base_url('portal/support') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
            <i class="fas fa-arrow-left mr-2"></i> Back to Tickets
        </a>
    </div>
</div>
