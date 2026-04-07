<?php // views/reports/due.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Due Report</h1><div class="page-breadcrumb"><i class="fa-solid fa-circle-exclamation" style="color:var(--red)"></i> Reports</div></div>
    <button class="btn btn-ghost" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
</div>

<!-- Summary -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;" class="fade-in">
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Customers with Due</div>
        <div style="font-size:32px;font-weight:900;color:var(--red);"><?= number_format(count($dueCustomers)) ?></div>
    </div>
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Total Outstanding</div>
        <div style="font-size:32px;font-weight:900;color:var(--red);">৳<?= number_format($totalDue,2) ?></div>
    </div>
    <div class="card" style="padding:20px;text-align:center;">
        <div style="font-size:12px;color:var(--text2);">Average Due</div>
        <div style="font-size:32px;font-weight:900;color:var(--yellow);">৳<?= count($dueCustomers)>0?number_format($totalDue/count($dueCustomers),0):'0' ?></div>
    </div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Package</th><th>Zone</th><th>Due Amount</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <?php if(empty($dueCustomers)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--green);">
                <i class="fa-solid fa-check-circle" style="font-size:32px;display:block;margin-bottom:10px;"></i>
                Excellent! No outstanding dues.
            </td></tr>
            <?php else: foreach($dueCustomers as $i=>$c): ?>
            <tr>
                <td style="color:var(--text2);"><?= $i+1 ?></td>
                <td>
                    <div style="font-weight:600;"><?= htmlspecialchars($c['full_name']) ?></div>
                    <div style="font-size:11px;font-family:monospace;color:var(--text2);"><?= htmlspecialchars($c['customer_code']) ?></div>
                </td>
                <td><?= htmlspecialchars($c['phone']) ?></td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($c['package_name']??'—') ?></td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($c['zone_name']??'—') ?></td>
                <td style="font-size:16px;font-weight:800;color:var(--red);">৳<?= number_format($c['due_amount'],0) ?></td>
                <td><span class="badge <?= $c['status']==='active'?'badge-green':'badge-red' ?>"><?= ucfirst($c['status']) ?></span></td>
                <td><a href="<?= base_url("customers/view/".($c['id']??'')) ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-eye"></i></a></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
