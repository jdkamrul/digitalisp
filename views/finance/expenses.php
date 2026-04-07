<?php // views/finance/expenses.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Expenses</h1><div class="page-breadcrumb"><i class="fa-solid fa-receipt" style="color:var(--red)"></i> Finance</div></div>
    <button class="btn btn-primary" onclick="document.getElementById('addExpenseModal').classList.add('open')"><i class="fa-solid fa-plus"></i> Add Expense</button>
</div>

<!-- Filters + Summary -->
<div style="display:flex;gap:10px;align-items:center;margin-bottom:16px;" class="fade-in">
    <form method="GET">
        <input type="month" name="month" value="<?= htmlspecialchars($month) ?>" class="form-input" onchange="this.form.submit()">
    </form>
    <div class="card" style="padding:10px 18px;display:flex;gap:10px;align-items:center;">
        <span style="font-size:12px;color:var(--text2);">Total for period:</span>
        <span style="font-size:18px;font-weight:800;color:var(--red);">৳<?= number_format($total,2) ?></span>
    </div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Amount</th><th>Vendor</th><th>Method</th><th></th></tr></thead>
        <tbody>
            <?php if(empty($expenses)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2);">No expenses for this period.</td></tr>
            <?php else: foreach($expenses as $e): ?>
            <tr>
                <td style="font-size:12px;white-space:nowrap;"><?= date('d M Y',strtotime($e['expense_date'])) ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($e['title']) ?></td>
                <td><span class="badge badge-gray"><?= htmlspecialchars($e['category_name']??'—') ?></span></td>
                <td style="font-weight:700;color:var(--red);">৳<?= number_format($e['amount'],2) ?></td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($e['vendor']??'—') ?></td>
                <td style="font-size:12px;"><?= ucfirst(str_replace('_',' ',$e['payment_method'])) ?></td>
                <td>
                    <form method="POST" action="<?= base_url("finance/expenses/delete/{$e['id']}") ?>" onsubmit="return confirm('Delete this expense?');" style="display:inline;">
                        <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="addExpenseModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-receipt" style="color:var(--red);margin-right:8px;"></i>Add Expense</div>
            <button class="icon-btn" onclick="document.getElementById('addExpenseModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('finance/expenses/store') ?>">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div><label class="form-label">Title <span style="color:var(--red)">*</span></label><input type="text" name="title" class="form-input" required placeholder="e.g. Office Rent, Salary, Maintenance"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Amount (৳)</label><input type="number" name="amount" class="form-input" step="0.01" required></div>
                    <div><label class="form-label">Category</label>
                        <select name="category_id" class="form-input">
                            <option value="">Uncategorized</option>
                            <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div><label class="form-label">Date</label><input type="date" name="expense_date" class="form-input" value="<?= date('Y-m-d') ?>"></div>
                    <div><label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-input">
                            <option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>
                    <div style="grid-column:1/-1;"><label class="form-label">Vendor / Payee</label><input type="text" name="vendor" class="form-input" placeholder="Who paid to?"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addExpenseModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-plus"></i> Add Expense</button>
            </div>
        </form>
    </div>
</div>
