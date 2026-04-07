<?php // views/finance/cashbook.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Cashbook</h1><div class="page-breadcrumb"><i class="fa-solid fa-book" style="color:var(--green)"></i> Finance</div></div>
    <div style="display:flex;gap:10px;align-items:center;">
        <form method="GET" style="display:flex;gap:8px;">
            <input type="date" name="date" class="form-input" value="<?= htmlspecialchars($date) ?>" style="width:160px;" onchange="this.form.submit()">
        </form>
        <form method="POST" action="<?= base_url('finance/daily-close') ?>" onsubmit="return confirm('Close today\'s book?')">
            <input type="hidden" name="date" value="<?= $date ?>">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-lock"></i> Close Day</button>
        </form>
    </div>
</div>

<!-- Summary -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;" class="fade-in">
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);margin-bottom:6px;">Total Credits (Income)</div>
        <div style="font-size:28px;font-weight:900;color:var(--green);">৳<?= number_format($credit,2) ?></div>
    </div>
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);margin-bottom:6px;">Total Debits (Expense)</div>
        <div style="font-size:28px;font-weight:900;color:var(--red);">৳<?= number_format($debit,2) ?></div>
    </div>
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);margin-bottom:6px;">Net Balance</div>
        <div style="font-size:28px;font-weight:900;<?= ($credit-$debit)>=0?'color:var(--green)':'color:var(--red)' ?>">৳<?= number_format($credit-$debit,2) ?></div>
    </div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Time</th><th>Category</th><th>Description</th>
                <th style="text-align:right;color:var(--green);">Credit ↑</th>
                <th style="text-align:right;color:var(--red);">Debit ↓</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($entries)): ?>
            <tr><td colspan="6" style="text-align:center;padding:48px;color:var(--text2);">
                <i class="fa-solid fa-book-open-reader" style="font-size:32px;display:block;margin-bottom:12px;opacity:.3;"></i>
                No entries for <?= date('d F Y',strtotime($date)) ?>
            </td></tr>
            <?php $runBal=0; else: $runBal=0; foreach($entries as $e): 
                $runBal += $e['entry_type']==='credit' ? $e['amount'] : -$e['amount'];
            ?>
            <tr style="border-left:3px solid <?= $e['entry_type']==='credit'?'var(--green)':'var(--red)' ?>;">
                <td style="font-size:11px;color:var(--text2);white-space:nowrap;"><?= date('H:i:s',strtotime($e['created_at'])) ?></td>
                <td><span class="badge badge-gray" style="font-size:10px;"><?= ucfirst(str_replace('_',' ',$e['entry_category'])) ?></span></td>
                <td style="font-size:13px;"><?= htmlspecialchars($e['description']) ?></td>
                <td style="text-align:right;font-weight:700;color:var(--green);">
                    <?= $e['entry_type']==='credit' ? '৳'.number_format($e['amount'],2) : '—' ?>
                </td>
                <td style="text-align:right;font-weight:700;color:var(--red);">
                    <?= $e['entry_type']==='debit' ? '৳'.number_format($e['amount'],2) : '—' ?>
                </td>
                <td style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($e['reference_type']??'') ?> #<?= $e['reference_id']??'' ?></td>
            </tr>
            <?php endforeach; endif; ?>
            <?php if(!empty($entries)): ?>
            <tr style="background:var(--bg3);">
                <td colspan="3" style="font-weight:700;font-size:13px;">TOTAL</td>
                <td style="text-align:right;font-weight:700;font-size:14px;color:var(--green);">৳<?= number_format($credit,2) ?></td>
                <td style="text-align:right;font-weight:700;font-size:14px;color:var(--red);">৳<?= number_format($debit,2) ?></td>
                <td></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
