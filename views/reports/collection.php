<?php // views/reports/collection.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Collection Report</h1><div class="page-breadcrumb"><i class="fa-solid fa-hand-holding-dollar" style="color:var(--blue)"></i> Reports</div></div>
    <form method="GET" style="display:flex;gap:8px;align-items:center;">
        <input type="date" name="date" class="form-input" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
    </form>
</div>

<div style="font-size:12px;color:var(--text2);margin-bottom:16px;" class="fade-in">
    Showing collections for <strong><?= date('l, d F Y',strtotime($date)) ?></strong>
    &nbsp;&nbsp;|&nbsp;&nbsp; Total Collected: <strong style="color:var(--green);">৳<?= number_format(array_sum(array_column($collections,'total')),2) ?></strong>
    &nbsp;|&nbsp; Transactions: <strong><?= array_sum(array_column($collections,'count')) ?></strong>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;margin-bottom:20px;" class="fade-in">
    <?php foreach($collections as $cl): ?>
    <div class="card" style="padding:18px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--blue),var(--purple));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                <?= strtoupper(substr($cl['collector']??'?',0,1)) ?>
            </div>
            <div>
                <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($cl['collector']??'Unknown') ?></div>
                <div style="font-size:11px;color:var(--text2);"><?= $cl['count'] ?> transactions</div>
            </div>
        </div>
        <div style="font-size:24px;font-weight:900;color:var(--green);">৳<?= number_format($cl['total'],0) ?></div>
    </div>
    <?php endforeach; if(empty($collections)): ?>
    <div class="card" style="padding:40px;text-align:center;color:var(--text2);grid-column:1/-1;">
        No collections recorded for this date.
    </div>
    <?php endif; ?>
</div>
