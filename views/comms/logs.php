<?php // views/comms/logs.php ?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">SMS Logs</h1>
        <div class="page-breadcrumb">
            <a href="<?= base_url('comms') ?>" style="color:var(--blue);text-decoration:none;">Communication Hub</a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i> Logs
        </div>
    </div>
    <div style="font-size:13px;color:var(--text2);"><?= number_format($total) ?> total records</div>
</div>

<!-- Filters -->
<div class="card fade-in" style="padding:14px 18px;margin-bottom:16px;">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" class="form-input" style="max-width:260px;"
               placeholder="Search phone or name..." value="<?= htmlspecialchars($search) ?>">
        <select name="status" class="form-input" style="max-width:160px;">
            <option value="">All Statuses</option>
            <?php foreach (['sent','failed','pending','delivered'] as $s): ?>
            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Filter</button>
        <a href="<?= base_url('comms/logs') ?>" class="btn btn-ghost btn-sm">Clear</a>
    </form>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Phone</th>
                <th>Customer</th>
                <th>Template</th>
                <th>Message</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
            <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text2);">No SMS logs found.</td></tr>
            <?php else: foreach ($logs as $log): ?>
            <tr>
                <td style="font-size:12px;white-space:nowrap;"><?= date('d M Y H:i', strtotime($log['sent_at'])) ?></td>
                <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($log['phone']) ?></td>
                <td style="font-size:12px;"><?= htmlspecialchars($log['full_name'] ?? '—') ?></td>
                <td style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($log['template_name'] ?? '—') ?></td>
                <td style="font-size:12px;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                    title="<?= htmlspecialchars($log['message']) ?>">
                    <?= htmlspecialchars(mb_substr($log['message'], 0, 60)) ?><?= mb_strlen($log['message']) > 60 ? '…' : '' ?>
                </td>
                <td>
                    <?php $sc=['sent'=>'badge-green','failed'=>'badge-red','pending'=>'badge-yellow','delivered'=>'badge-blue']; ?>
                    <span class="badge <?= $sc[$log['status']] ?? 'badge-gray' ?>" style="font-size:10px;"><?= ucfirst($log['status']) ?></span>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="padding:14px 18px;border-top:1px solid var(--border);display:flex;gap:6px;justify-content:center;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"
           class="btn btn-sm <?= $i===$page?'btn-primary':'btn-ghost' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
