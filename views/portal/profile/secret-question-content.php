<?php // views/portal/profile/secret-question-content.php ?>
<div class="max-w-lg mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
            <i class="fas fa-shield-alt text-primary-500 mr-2"></i> Security Question
        </h3>
        <form method="POST" action="<?= base_url('portal/profile/secret-question') ?>" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Security Question</label>
                <select name="secret_question" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select a question</option>
                    <option value="What is your mother's maiden name?" <?= ($customer['secret_question'] ?? '') === "What is your mother's maiden name?" ? 'selected' : '' ?>>What is your mother's maiden name?</option>
                    <option value="What was the name of your first pet?" <?= ($customer['secret_question'] ?? '') === "What was the name of your first pet?" ? 'selected' : '' ?>>What was the name of your first pet?</option>
                    <option value="What city were you born in?" <?= ($customer['secret_question'] ?? '') === "What city were you born in?" ? 'selected' : '' ?>>What city were you born in?</option>
                    <option value="What is your favorite food?" <?= ($customer['secret_question'] ?? '') === "What is your favorite food?" ? 'selected' : '' ?>>What is your favorite food?</option>
                    <option value="What was the name of your primary school?" <?= ($customer['secret_question'] ?? '') === "What was the name of your primary school?" ? 'selected' : '' ?>>What was the name of your primary school?</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Answer</label>
                <input type="text" name="secret_answer" required
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Enter your answer">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-3 bg-primary-500 text-white font-semibold rounded-lg hover:bg-primary-600 transition">
                    <i class="fas fa-save mr-2"></i> Save Security Question
                </button>
                <a href="<?= base_url('portal/profile') ?>" class="px-6 py-3 border border-gray-300 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
