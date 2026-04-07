<?php
global $pageTitle;
?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-plus-circle mr-2 text-primary-500"></i> Create Support Ticket
            </h3>
        </div>
        <form action="<?= base_url('portal/support/create') ?>" method="POST" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select name="category" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="general">General Inquiry</option>
                        <option value="billing">Billing Issue</option>
                        <option value="technical">Technical Support</option>
                        <option value="complaint">Complaint</option>
                        <option value="new_connection">New Connection Request</option>
                        <option value="disconnection">Disconnection Request</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                    <select name="priority" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                <input type="text" name="subject" required maxlength="200" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Brief description of your issue">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="6" required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Please provide detailed information about your issue..."></textarea>
            </div>

            <div class="flex justify-between items-center pt-4">
                <a href="<?= base_url('portal/support') ?>" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 font-medium">
                    <i class="fas fa-paper-plane mr-2"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>
