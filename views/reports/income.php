<?php // views/reports/income.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Income Report</h1><div class="page-breadcrumb"><i class="fa-solid fa-chart-line" style="color:var(--green)"></i> Reports</div></div>
    <form method="GET" style="display:flex;gap:8px;">
        <input type="date" name="from" class="form-input" value="<?= htmlspecialchars($from) ?>">
        <input type="date" name="to" class="form-input" value="<?= htmlspecialchars($to) ?>">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
    </form>
</div>

<!-- Summary -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;" class="fade-in">
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Total Income</div>
        <div style="font-size:32px;font-weight:900;color:var(--green);">৳<?= number_format($totalIncome,2) ?></div>
        <div style="font-size:11px;color:var(--text2);margin-top:4px;"><?= date('d M',strtotime($from)) ?> – <?= date('d M Y',strtotime($to)) ?></div>
    </div>
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Transactions</div>
        <div style="font-size:32px;font-weight:900;color:var(--blue);"><?= array_sum(array_column($daily,'count')) ?></div>
    </div>
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Daily Average</div>
        <div style="font-size:32px;font-weight:900;color:var(--purple);">৳<?= count($daily)>0?number_format($totalIncome/count($daily),0):'0' ?></div>
    </div>
</div>

<!-- Chart -->
<div class="card fade-in" style="padding:20px;margin-bottom:16px;">
    <div style="font-size:14px;font-weight:700;margin-bottom:16px;">Daily Collection Chart</div>
    <canvas id="incomeChart" height="80"></canvas>
</div>

<!-- Table -->
<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Date</th><th>Transactions</th><th>Total Collected</th><th>Daily Bar</th></tr></thead>
        <tbody>
            <?php $maxDay = $daily ? max(array_column($daily,'total')) : 1; foreach($daily as $d): 
                $pct = $maxDay > 0 ? round(($d['total']/$maxDay)*100) : 0;
            ?>
            <tr>
                <td style="font-weight:600;"><?= date('d M Y (l)',strtotime($d['day'])) ?></td>
                <td><?= number_format($d['count']) ?></td>
                <td style="font-weight:700;color:var(--green);">৳<?= number_format($d['total'],2) ?></td>
                <td><div style="background:var(--bg3);border-radius:4px;height:6px;overflow:hidden;"><div style="height:6px;width:<?= $pct ?>%;background:var(--green);border-radius:4px;"></div></div></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($daily)): ?>
            <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text2);">No income records for this period.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('incomeChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?= implode(',', array_map(fn($d)=>"'".date('d M',strtotime($d['day']))."'", $daily)) ?>],
        datasets: [{
            label: 'Daily Income (৳)',
            data: [<?= implode(',', array_column($daily,'total')) ?>],
            backgroundColor: 'rgba(34,197,94,0.6)',
            borderColor: 'rgba(34,197,94,1)',
            borderWidth: 1, borderRadius: 6
        }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{ y:{grid:{color:'rgba(255,255,255,0.05)'}}, x:{grid:{display:false}} } }
});
</script>
