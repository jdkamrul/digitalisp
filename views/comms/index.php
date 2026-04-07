<?php // views/comms/index.php ?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Communication Hub</h1>
        <div class="page-breadcrumb">SMS & Notifications</div>
    </div>
    <div style="display:flex;gap:8px;">
        <form method="POST" action="<?= base_url('comms/due-reminders') ?>" style="display:inline;">
            <button type="submit" class="btn btn-ghost" onclick="return confirm('Send due reminders to all overdue customers?')">
                <i class="fa-solid fa-bell"></i> Send Due Reminders
            </button>
        </form>
        <a href="<?= base_url('comms/bulk') ?>" class="btn btn-primary">
            <i class="fa-solid fa-paper-plane"></i> New Bulk SMS
        </a>
    </div>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;" class="fade-in">
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(37,99,235,0.1);color:var(--blue);"><i class="fa-solid fa-paper-plane"></i></div>
        <div class="stat-value"><?= number_format($stats['total_sent']) ?></div>
        <div class="stat-label">Total Sent</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(22,163,74,0.1);color:var(--green);"><i class="fa-solid fa-check-circle"></i></div>
        <div class="stat-value"><?= number_format($stats['today_sent']) ?></div>
        <div class="stat-label">Sent Today</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(220,38,38,0.1);color:var(--red);"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="stat-value"><?= number_format($stats['total_failed']) ?></div>
        <div class="stat-label">Failed</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:rgba(124,58,237,0.1);color:var(--purple);"><i class="fa-solid fa-bullhorn"></i></div>
        <div class="stat-value"><?= number_format($stats['campaigns']) ?></div>
        <div class="stat-label">Campaigns</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="fade-in">
    <!-- Recent Logs -->
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:14px;font-weight:700;">Recent SMS</div>
            <a href="<?= base_url('comms/logs') ?>" style="font-size:12px;color:var(--blue);text-decoration:none;">View all</a>
        </div>
        <table class="data-table">
            <thead><tr><th>Phone</th><th>Customer</th><th>Status</th><th>Time</th></tr></thead>
            <tbody>
                <?php if (empty($recentLogs)): ?>
                <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--text2);">No SMS sent yet.</td></tr>
                <?php else: foreach ($recentLogs as $log): ?>
                <tr>
                    <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($log['phone']) ?></td>
                    <td style="font-size:12px;"><?= htmlspecialchars($log['full_name'] ?? '—') ?></td>
                    <td>
                        <?php $sc=['sent'=>'badge-green','failed'=>'badge-red','pending'=>'badge-yellow','delivered'=>'badge-blue']; ?>
                        <span class="badge <?= $sc[$log['status']] ?? 'badge-gray' ?>" style="font-size:10px;"><?= ucfirst($log['status']) ?></span>
                    </td>
                    <td style="font-size:11px;color:var(--text2);"><?= date('d M H:i', strtotime($log['sent_at'])) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Campaigns -->
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:14px;font-weight:700;">Recent Campaigns</div>
            <a href="<?= base_url('comms/campaigns') ?>" style="font-size:12px;color:var(--blue);text-decoration:none;">View all</a>
        </div>
        <?php if (empty($recentCampaigns)): ?>
        <div style="text-align:center;padding:24px;color:var(--text2);font-size:13px;">No campaigns yet.</div>
        <?php else: foreach ($recentCampaigns as $c): ?>
        <div style="padding:12px 18px;border-bottom:1px solid var(--border);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:13px;font-weight:600;"><?= htmlspecialchars($c['name']) ?></div>
                    <div style="font-size:11px;color:var(--text2);">
                        <?= $c['sent_count'] ?>/<?= $c['total_recipients'] ?> sent
                        &middot; <?= date('d M Y', strtotime($c['created_at'])) ?>
                    </div>
                </div>
                <?php $cs=['completed'=>'badge-green','sending'=>'badge-blue','failed'=>'badge-red','draft'=>'badge-gray']; ?>
                <span class="badge <?= $cs[$c['status']] ?? 'badge-gray' ?>" style="font-size:10px;"><?= ucfirst($c['status']) ?></span>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
