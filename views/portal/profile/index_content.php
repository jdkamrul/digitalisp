<?php
global $customer;
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold mb-4"><i class="fas fa-user mr-2 text-primary-500"></i> Profile Information</h3>
            <form action="<?= base_url('portal/profile/update') ?>" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Customer ID</label>
                        <input type="text" value="<?= sanitize($customer['customer_code'] ?? '') ?>" disabled class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Full Name</label>
                        <input type="text" value="<?= sanitize($customer['full_name'] ?? '') ?>" disabled class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone</label>
                        <input type="tel" name="phone" value="<?= sanitize($customer['phone'] ?? '') ?>" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" value="<?= sanitize($customer['email'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">PPPoE Username</label>
                        <input type="text" value="<?= sanitize($customer['pppoe_username'] ?? '') ?>" disabled class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-2 border rounded-lg"><?= sanitize($customer['address'] ?? '') ?></textarea>
                </div>
                <div class="pt-4 border-t">
                    <h4 class="font-medium mb-3">Billing Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Company Name</label>
                            <input type="text" name="billing_company_name" value="<?= sanitize($customer['billing_company_name'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">VAT/Tax ID</label>
                            <input type="text" name="billing_vat_reg" value="<?= sanitize($customer['billing_vat_reg'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg">
                        </div>
                    </div>
                </div>
                <button type="submit" class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600"><i class="fas fa-save mr-2"></i>Save Changes</button>
            </form>
        </div>
    </div>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold mb-4"><i class="fas fa-shield-alt mr-2 text-primary-500"></i> Security</h3>
            <div class="space-y-3">
                <a href="<?= base_url('portal/profile/change-password') ?>" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-key w-8 text-gray-500"></i>
                    <span class="font-medium">Change Password</span>
                </a>
                <a href="<?= base_url('portal/profile/mac-devices') ?>" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-laptop w-8 text-gray-500"></i>
                    <span class="font-medium">Device Management</span>
                </a>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold mb-4"><i class="fas fa-chart-pie mr-2 text-primary-500"></i> Account Summary</h3>
            <div class="space-y-3">
                <div class="flex justify-between"><span class="text-gray-500">Package</span><span class="font-medium"><?= sanitize($customer['package_name'] ?? 'N/A') ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Monthly</span><span class="font-medium"><?= formatMoney($customer['monthly_charge'] ?? 0) ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Due</span><span class="font-medium <?= ($customer['due_amount'] ?? 0) > 0 ? 'text-red-600' : 'text-green-600' ?>"><?= formatMoney($customer['due_amount'] ?? 0) ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="px-2 py-1 text-xs rounded-full <?= ($customer['status'] ?? '') === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= ucfirst($customer['status'] ?? 'N/A') ?></span></div>
            </div>
        </div>
    </div>
</div>
