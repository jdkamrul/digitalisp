<?php
global $customer, $pageTitle;
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Info -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-user mr-2 text-primary-500"></i> Profile Information
                </h3>
            </div>
            <form action="<?= base_url('portal/profile/update') ?>" method="POST" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer ID</label>
                        <input type="text" value="<?= sanitize($customer['customer_code'] ?? '') ?>" disabled
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                        <input type="text" value="<?= sanitize($customer['full_name'] ?? '') ?>" disabled
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                        <input type="tel" name="phone" value="<?= sanitize($customer['phone'] ?? '') ?>" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" name="email" value="<?= sanitize($customer['email'] ?? '') ?>"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PPPoE Username</label>
                        <input type="text" value="<?= sanitize($customer['pppoe_username'] ?? '') ?>" disabled
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea name="address" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"><?= sanitize($customer['address'] ?? '') ?></textarea>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Billing Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name</label>
                            <input type="text" name="billing_company_name" value="<?= sanitize($customer['billing_company_name'] ?? '') ?>"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VAT/Tax ID</label>
                            <input type="text" name="billing_vat_reg" value="<?= sanitize($customer['billing_vat_reg'] ?? '') ?>"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 font-medium">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-shield-alt mr-2 text-primary-500"></i> Security
            </h3>
            <div class="space-y-3">
                <a href="<?= base_url('portal/profile/change-password') ?>" 
                   class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-key w-8 text-gray-500"></i>
                    <span class="font-medium text-gray-900 dark:text-white">Change Password</span>
                </a>
                <a href="<?= base_url('portal/profile/mac-devices') ?>" 
                   class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-laptop w-8 text-gray-500"></i>
                    <span class="font-medium text-gray-900 dark:text-white">Device Management</span>
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-chart-pie mr-2 text-primary-500"></i> Account Summary
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Package</span>
                    <span class="font-medium"><?= sanitize($customer['package_name'] ?? 'N/A') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Monthly</span>
                    <span class="font-medium"><?= formatMoney($customer['monthly_charge'] ?? 0) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Due Balance</span>
                    <span class="font-medium <?= ($customer['due_amount'] ?? 0) > 0 ? 'text-red-600' : 'text-green-600' ?>">
                        <?= formatMoney($customer['due_amount'] ?? 0) ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="px-2 py-1 text-xs rounded-full <?= ($customer['status'] ?? '') === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= ucfirst($customer['status'] ?? 'N/A') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
