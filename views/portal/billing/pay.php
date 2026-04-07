<?php
$currentPage = 'billing-pay';
$content = __DIR__ . '/content.php';

function renderPayContent(): void {
    global $invoice, $customer, $settings, $pageTitle;
    $pending = $_SESSION['pending_payment'] ?? null;
?>
<?php if ($pending): ?>
<!-- Payment Confirmation -->
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-circle text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Confirm Payment</h3>
        </div>

        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500 dark:text-gray-400">Amount</span>
                <span class="font-bold text-xl text-gray-900 dark:text-white"><?= formatMoney($pending['amount']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Method</span>
                <span class="text-gray-900 dark:text-white uppercase"><?= $pending['method'] ?></span>
            </div>
        </div>

        <form action="<?= base_url('portal/billing/confirm-payment') ?>" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <?= strtoupper($pending['method']) ?> Transaction ID / Reference
                </label>
                <input type="text" name="ref_number" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Enter transaction reference number">
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Please complete the payment via <?= ucfirst($pending['method']) ?> and enter the transaction ID.
                </p>
            </div>

            <div class="flex space-x-3">
                <a href="<?= base_url('portal/billing/pay-form/' . $invoice['id']) ?>" 
                   class="flex-1 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-center text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" class="flex-1 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium">
                    <i class="fas fa-check mr-2"></i> Confirm
                </button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<!-- Payment Form -->
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pay Invoice</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1"><?= sanitize($invoice['invoice_number']) ?></p>
        </div>

        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500 dark:text-gray-400">Total Amount</span>
                <span class="font-bold text-gray-900 dark:text-white"><?= formatMoney($invoice['total']) ?></span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-500 dark:text-gray-400">Already Paid</span>
                <span class="text-green-600"><?= formatMoney($invoice['paid_amount']) ?></span>
            </div>
            <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                <span class="text-gray-700 dark:text-gray-300 font-medium">Due Amount</span>
                <span class="font-bold text-xl text-red-600"><?= formatMoney($invoice['due_amount']) ?></span>
            </div>
        </div>

        <form action="<?= base_url('portal/billing/initiate-payment') ?>" method="POST">
            <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount to Pay</label>
                <input type="number" name="amount" value="<?= $invoice['due_amount'] ?>" step="0.01" min="1" max="<?= $invoice['due_amount'] ?>" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    <a href="#" onclick="document.querySelector('[name=amount]').value='<?= $invoice['due_amount'] ?>'; return false;" class="text-primary-600 hover:underline">Pay full amount</a>
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Payment Method</label>
                <div class="grid grid-cols-2 gap-3">
                    <?php if (($settings['enable_bkash_payment'] ?? '1') === '1'): ?>
                    <label class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                        <input type="radio" name="payment_method" value="bkash" checked class="mr-3">
                        <div class="flex items-center">
                            <span class="text-pink-600 font-bold text-lg mr-2">bKash</span>
                        </div>
                    </label>
                    <?php endif; ?>
                    
                    <?php if (($settings['enable_nagad_payment'] ?? '1') === '1'): ?>
                    <label class="flex items-center p-4 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                        <input type="radio" name="payment_method" value="nagad" class="mr-3">
                        <div class="flex items-center">
                            <span class="text-orange-600 font-bold text-lg mr-2">Nagad</span>
                        </div>
                    </label>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium">
                <i class="fas fa-credit-card mr-2"></i> Proceed to Pay
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                Need help? Call <a href="tel:<?= sanitize($settings['support_phone'] ?? '01700000000') ?>" class="text-primary-600"><?= sanitize($settings['support_phone'] ?? '01700000000') ?></a>
            </p>
        </div>
    </div>
</div>
<?php endif; ?>
<?php } ?>
