<?php // views/comms/campaigns.php ?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">SMS Campaigns</h1>
        <div class="page-breadcrumb">
            <a href="<?= base_url('comms') ?>" style="color:var(--blue);text-decoration:none;">Communication Hub</a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i> Campaigns
        </div>
    </div>
    <a href="<?= base_url('comms/bulk') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> New Campaign
    </a>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Campaign</th>
                <th>Filter</th>
                <th>Recipients</th>
                <th>Sent</th>
                <th>Failed</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($campaigns)): ?>
            <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text2);">No campaigns yet. <a href="<?= base_url('comms/bulk') ?>" style="color:var(--blue);">Send your first one.</a></td></tr>
            <?php else: foreach ($campaigns as $c): ?>
            <tr>
                <td>
                    <div style="font-weight:600;"><?= htmlspecialchars($c['name']) ?></div>
                    <div style="font-size:11px;color:var(--text2);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                         title="<?= htmlspecialchars($c['message']) ?>">
                        <?= htmlspecialchars(mb_substr($c['message'], 0, 60)) ?>…
                    </div>
                </td>
                <td style="font-size:12px;">
                    <span class="badge badge-blue" style="font-size:10px;"><?= ucfirst($c['filter_type']) ?></span>
                    <?php if ($c['filter_value']): ?>
                    <span style="color:var(--text2);font-size:11px;"> <?= htmlspecialchars($c['filter_value']) ?></span>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600;"><?= number_format($c['total_recipients']) ?></td>
                <td style="color:var(--green);font-weight:600;"><?= number_format($c['sent_count']) ?></td>
                <td style="color:<?= $c['failed_count']>0?'var(--red)':'var(--text2)' ?>;font-weight:600;"><?= number_format($c['failed_count']) ?></td>
                <td>
                    <?php $cs=['completed'=>'badge-green','sending'=>'badge-blue','failed'=>'badge-red','draft'=>'badge-gray']; ?>
                    <span class="badge <?= $cs[$c['status']] ?? 'badge-gray' ?>" style="font-size:10px;"><?= ucfirst($c['status']) ?></span>
                </td>
                <td style="font-size:12px;color:var(--text2);"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
