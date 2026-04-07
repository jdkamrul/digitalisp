<?php // views/billing/cashbook.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Billing Cashbook</h1><div class="page-breadcrumb"><i class="fa-solid fa-cash-register" style="color:var(--green)"></i> Billing</div></div>
    <form method="GET" style="display:flex;gap:8px;align-items:center;">
        <input type="month" name="month" class="form-input" value="<?= htmlspecialchars($month) ?>" onchange="this.form.submit()">
    </form>
</div>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px;" class="fade-in">
    <div class="card" style="padding:18px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Total Collected</div>
        <div style="font-size:28px;font-weight:900;color:var(--green);">৳<?= number_format(array_sum(array_column($entries,'amount')),2) ?></div>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Transactions</div>
        <div style="font-size:28px;font-weight:900;color:var(--blue);"><?= count($entries) ?></div>
    </div>
    <div class="card" style="padding:18px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Period</div>
        <div style="font-size:18px;font-weight:700;"><?= date('F Y',strtotime($month.'-01')) ?></div>
    </div>
</div>
<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Date</th><th>Receipt#</th><th>Customer</th><th>Package Month</th><th>Amount</th><th>Method</th><th></th></tr></thead>
        <tbody>
            <?php if(empty($entries)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2);">No payments recorded for <?= date('F Y',strtotime($month.'-01')) ?>.</td></tr>
            <?php else: foreach($entries as $e): ?>
            <tr>
                <td style="font-size:12px;white-space:nowrap;"><?= date('d M Y',strtotime($e['payment_date'])) ?></td>
                <td style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($e['receipt_number']??'—') ?></td>
                <td>
                    <div style="font-weight:500;"><?= htmlspecialchars($e['full_name']) ?></div>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($e['customer_code']) ?></div>
                </td>
                <td style="font-size:12px;color:var(--text2);"><?= $e['billing_month'] ? date('M Y',strtotime($e['billing_month'])) : '—' ?></td>
                <td style="font-size:16px;font-weight:800;color:var(--green);">৳<?= number_format($e['amount'],2) ?></td>
                <td>
                    <?php $mc=['cash'=>'💵','mobile_banking'=>'📱','bank_transfer'=>'🏦','online'=>'🌐'];
                    echo ($mc[$e['payment_method']]??'💳').' '.ucfirst(str_replace('_',' ',$e['payment_method'])); ?>
                </td>
                <td><a href="<?= base_url("billing/receipt/{$e['id']}") ?>" class="btn btn-ghost btn-sm" target="_blank"><i class="fa-solid fa-print"></i></a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
