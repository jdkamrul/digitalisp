<?php // views/automation/logs.php ?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Automation Logs</h1>
        <div class="page-breadcrumb">
            <a href="<?= base_url('automation') ?>" style="color:var(--blue);text-decoration:none;">Automation</a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i> Logs
        </div>
    </div>
    <div style="font-size:13px;color:var(--text2);"><?= number_format($total) ?> total runs</div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr><th>Date/Time</th><th>Job</th><th>Status</th><th>Affected</th><th>Message</th></tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--text2);">No logs yet.</td></tr>
            <?php else: foreach ($logs as $log): ?>
            <tr>
                <td style="font-size:12px;white-space:nowrap;"><?= date('d M Y H:i', strtotime($log['run_at'])) ?></td>
                <td><span class="badge badge-blue" style="font-size:10px;"><?= htmlspecialchars(str_replace('_',' ',$log['job_type'])) ?></span></td>
                <td><span class="badge <?= $log['status']==='success'?'badge-green':($log['status']==='error'?'badge-red':'badge-gray') ?>" style="font-size:10px;"><?= ucfirst($log['status']) ?></span></td>
                <td style="font-weight:600;"><?= number_format($log['affected']) ?></td>
                <td style="font-size:12px;color:var(--text2);">
                    <?= htmlspecialchars($log['message']) ?>
                    <?php if ($log['details']): ?>
                    <div style="font-size:11px;color:var(--red);margin-top:2px;"><?= htmlspecialchars(mb_substr($log['details'],0,120)) ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
    <div style="padding:14px 18px;border-top:1px solid var(--border);display:flex;gap:6px;justify-content:center;">
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
        <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i===$page?'btn-primary':'btn-ghost' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
