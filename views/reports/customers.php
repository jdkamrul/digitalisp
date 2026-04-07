<?php // views/reports/customers.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Customer Growth</h1><div class="page-breadcrumb"><i class="fa-solid fa-users" style="color:var(--purple)"></i> Reports</div></div>
</div>
<div class="card fade-in" style="padding:20px;margin-bottom:16px;">
    <div style="font-size:14px;font-weight:700;margin-bottom:16px;">New Customers — Last 12 Months</div>
    <canvas id="growthChart" height="70"></canvas>
</div>
<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Month</th><th>New Customers</th><th>Growth Bar</th></tr></thead>
        <tbody>
            <?php $maxG = $growth ? max(array_column($growth,'new_customers')) : 1; foreach($growth as $g): 
                $pct = $maxG>0?round(($g['new_customers']/$maxG)*100):0; ?>
            <tr>
                <td style="font-weight:600;"><?= date('F Y',strtotime($g['month'].'-01')) ?></td>
                <td style="font-size:18px;font-weight:800;color:var(--blue);"><?= number_format($g['new_customers']) ?></td>
                <td><div style="background:var(--bg3);border-radius:4px;height:6px;"><div style="height:6px;width:<?=$pct?>%;background:var(--purple);border-radius:4px;"></div></div></td>
            </tr>
            <?php endforeach; if(empty($growth)): ?>
            <tr><td colspan="3" style="text-align:center;padding:32px;color:var(--text2);">No data available.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: {
        labels: [<?= implode(',', array_map(fn($g)=>"'".date('M Y',strtotime($g['month'].'-01'))."'", $growth)) ?>],
        datasets:[{ label:'New Customers', data:[<?= implode(',',array_column($growth,'new_customers')) ?>], fill:true, tension:.4,
            borderColor:'rgba(139,92,246,1)', backgroundColor:'rgba(139,92,246,0.1)', pointRadius:4 }]
    },
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{grid:{color:'rgba(255,255,255,0.05)',beginAtZero:true}},x:{grid:{display:false}}}}
});
</script>
