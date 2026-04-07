<?php
// views/billing/invoices.php
?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Invoices</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-file-invoice-dollar" style="color:var(--blue)"></i> Billing Management</div>
    </div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-primary" onclick="document.getElementById('generateModal').classList.add('open')">
            <i class="fa-solid fa-bolt"></i> Generate Invoices
        </button>
        <button class="btn btn-ghost" onclick="exportInvoices()"><i class="fa-solid fa-download"></i> Export</button>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
<div class="card fade-in" style="padding:14px 18px;margin-bottom:16px;border-color:rgba(34,197,94,0.4);background:rgba(34,197,94,0.08);">
    <span style="color:var(--green);"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></span>
    <?php unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<!-- Summary Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;" class="fade-in">
    <div class="card stat-card" style="padding:16px;">
        <div class="stat-label">Total Invoices</div>
        <div class="stat-value" style="font-size:22px;"><?= number_format($summary['total']??0) ?></div>
    </div>
    <div class="card stat-card" style="padding:16px;">
        <div class="stat-label">Gross Amount</div>
        <div class="stat-value" style="font-size:22px;">৳<?= number_format($summary['gross']??0,0) ?></div>
    </div>
    <div class="card stat-card" style="padding:16px;">
        <div class="stat-label">Collected</div>
        <div class="stat-value" style="font-size:22px;color:var(--green);">৳<?= number_format($summary['paid_total']??0,0) ?></div>
    </div>
    <div class="card stat-card" style="padding:16px;">
        <div class="stat-label">Outstanding Due</div>
        <div class="stat-value" style="font-size:22px;color:var(--red);">৳<?= number_format($summary['due_total']??0,0) ?></div>
    </div>
</div>

<!-- Filters -->
<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;" class="fade-in">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search invoice / customer..." class="form-input" style="flex:1;min-width:200px;">
    <select name="status" class="form-input" style="width:140px;">
        <option value="">All Status</option>
        <?php foreach(['unpaid','partial','paid','waived','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="month" name="month" value="<?= htmlspecialchars($month) ?>" class="form-input" style="width:160px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
</form>

<!-- Invoice Table -->
<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Invoice #</th><th>Customer</th><th>Month</th>
                <th>Amount</th><th>Paid</th><th>Due</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($invoices)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text2);">
                <i class="fa-solid fa-file-slash" style="font-size:28px;display:block;margin-bottom:10px;"></i>
                No invoices found. Generate invoices to get started.
            </td></tr>
            <?php else: foreach($invoices as $inv): ?>
            <tr>
                <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                <td>
                    <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($inv['full_name']) ?></div>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($inv['customer_code']) ?></div>
                </td>
                <td style="font-size:13px;"><?= date('F Y', strtotime($inv['billing_month'])) ?>
                    <?php if($inv['is_prorata']): ?><span class="badge badge-yellow" style="font-size:9px;">Pro-rata</span><?php endif; ?>
                </td>
                <td style="font-weight:600;">৳<?= number_format($inv['total'],2) ?></td>
                <td style="color:var(--green);">৳<?= number_format($inv['paid_amount'],2) ?></td>
                <td style="font-weight:700;<?= $inv['due_amount']>0?'color:var(--red)':'' ?>">৳<?= number_format($inv['due_amount'],2) ?></td>
                <td><?php
                    $sc=['paid'=>'badge-green','unpaid'=>'badge-red','partial'=>'badge-yellow','waived'=>'badge-gray','cancelled'=>'badge-gray'];
                    echo '<span class="badge '.($sc[$inv['status']]??'badge-gray').'">'.ucfirst($inv['status']).'</span>';
                ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="<?= base_url("billing/invoice/{$inv['id']}") ?>" class="btn btn-ghost btn-sm" title="View"><i class="fa-solid fa-eye"></i></a>
                        <?php if(in_array($inv['status'],['unpaid','partial'])): ?>
                        <a href="<?= base_url("billing/pay/{$inv['id']}") ?>" class="btn btn-success btn-sm" title="Pay"><i class="fa-solid fa-money-bill"></i> Pay</a>
                        <?php endif; ?>
                        <a href="<?= base_url("billing/invoice/{$inv['id']}") ?>" class="btn btn-ghost btn-sm" title="Print"><i class="fa-solid fa-print"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Generate Invoices Modal -->
<div class="modal-overlay" id="generateModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-bolt" style="color:var(--blue);margin-right:8px;"></i>Generate Monthly Invoices</div>
            <button class="icon-btn" onclick="document.getElementById('generateModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('billing/generate') ?>">
            <div class="modal-body">
                <div style="background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);border-radius:10px;padding:12px;margin-bottom:16px;font-size:13px;color:var(--text2);">
                    <i class="fa-solid fa-info-circle" style="color:var(--blue);"></i>
                    This will generate invoices for all active customers for the selected billing month. Existing invoices will be skipped.
                </div>
                <div style="display:grid;gap:14px;">
                    <div>
                        <label class="form-label">Billing Month <span style="color:var(--red)">*</span></label>
                        <input type="month" name="billing_month" class="form-input" value="<?= date('Y-m') ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Branch (leave blank for all)</label>
                        <select name="branch_id" class="form-input">
                            <option value="">All Branches</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('generateModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-bolt"></i> Generate Now</button>
            </div>
        </form>
    </div>
</div>

<script>function exportInvoices() { alert('Export coming soon!'); }</script>
