<?php
?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-6">
        <h3 class="text-lg font-semibold mb-4"><i class="fas fa-plus-circle mr-2 text-primary-500"></i> Create Support Ticket</h3>
        <form action="<?= base_url('portal/support/create') ?>" method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select name="category" required class="w-full px-4 py-2 border rounded-lg">
                        <option value="general">General Inquiry</option>
                        <option value="billing">Billing Issue</option>
                        <option value="technical">Technical Support</option>
                        <option value="complaint">Complaint</option>
                        <option value="new_connection">New Connection</option>
                        <option value="disconnection">Disconnection</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Priority</label>
                    <select name="priority" required class="w-full px-4 py-2 border rounded-lg">
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Subject</label>
                <input type="text" name="subject" required maxlength="200" class="w-full px-4 py-2 border rounded-lg" placeholder="Brief description">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="6" required class="w-full px-4 py-2 border rounded-lg" placeholder="Detailed information..."></textarea>
            </div>
            <div class="flex justify-between pt-4">
                <a href="<?= base_url('portal/support') ?>" class="text-gray-500 hover:text-gray-700"><i class="fas fa-arrow-left mr-2"></i>Back</a>
                <button type="submit" class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600"><i class="fas fa-paper-plane mr-2"></i>Submit</button>
            </div>
        </form>
    </div>
</div>
