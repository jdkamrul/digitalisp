<?php
global $invoice, $customer, $settings;
$pending = $_SESSION['pending_payment'] ?? null;
?>
<div class="max-w-md mx-auto">
    <?php if ($pending): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-circle text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold">Confirm Payment</h3>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500">Amount</span>
                <span class="font-bold text-xl"><?= formatMoney($pending['amount']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Method</span>
                <span class="uppercase"><?= $pending['method'] ?></span>
            </div>
        </div>
        <form action="<?= base_url('portal/billing/confirm-payment') ?>" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1"><?= strtoupper($pending['method']) ?> Transaction ID</label>
                <input type="text" name="ref_number" required class="w-full px-4 py-2 border rounded-lg" placeholder="Enter transaction reference">
            </div>
            <div class="flex gap-3">
                <a href="<?= base_url('portal/billing/pay-form/' . $invoice['id']) ?>" class="flex-1 py-3 border rounded-lg text-center hover:bg-gray-50">Cancel</a>
                <button type="submit" class="flex-1 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600">Confirm</button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold">Pay Invoice</h3>
            <p class="text-gray-500"><?= sanitize($invoice['invoice_number']) ?></p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500">Total</span>
                <span class="font-bold"><?= formatMoney($invoice['total']) ?></span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-500">Paid</span>
                <span class="text-green-600"><?= formatMoney($invoice['paid_amount']) ?></span>
            </div>
            <div class="flex justify-between pt-2 border-t">
                <span class="font-medium">Due</span>
                <span class="font-bold text-xl text-red-600"><?= formatMoney($invoice['due_amount']) ?></span>
            </div>
        </div>
        <form action="<?= base_url('portal/billing/initiate-payment') ?>" method="POST">
            <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Amount</label>
                <input type="number" name="amount" value="<?= $invoice['due_amount'] ?>" step="0.01" min="1" max="<?= $invoice['due_amount'] ?>" required class="w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Payment Method</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="bkash" checked class="mr-3">
                        <span class="text-pink-600 font-bold">bKash</span>
                    </label>
                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="nagad" class="mr-3">
                        <span class="text-orange-600 font-bold">Nagad</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="w-full py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium">
                <i class="fas fa-credit-card mr-2"></i> Proceed to Pay
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>
