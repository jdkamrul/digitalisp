<?php // views/reseller/list.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Resellers</h1><div class="page-breadcrumb"><i class="fa-solid fa-sitemap" style="color:var(--blue)"></i> <?= count($resellers) ?> resellers</div></div>
    <a href="<?= base_url('resellers/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Reseller</a>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Business</th><th>Contact</th><th>Branch</th><th>Customers</th><th>Balance</th><th>Commission</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <?php if(empty($resellers)): ?>
            <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--text2);">
                <i class="fa-solid fa-sitemap" style="font-size:36px;display:block;margin-bottom:12px;opacity:.3;"></i>
                No resellers yet. Add your first sub-reseller.
            </td></tr>
            <?php else: foreach($resellers as $r): ?>
            <tr>
                <td>
                    <div style="font-weight:700;"><?= htmlspecialchars($r['business_name']) ?></div>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($r['contact_person']) ?></div>
                </td>
                <td>
                    <div style="font-size:13px;"><?= htmlspecialchars($r['phone']) ?></div>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($r['email']??'') ?></div>
                </td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($r['branch_name']??'—') ?></td>
                <td style="font-size:16px;font-weight:700;color:var(--blue);"><?= number_format($r['customer_count']) ?></td>
                <td style="font-weight:700;<?= $r['balance']>0?'color:var(--green)':($r['balance']<0?'color:var(--red)':'') ?>">৳<?= number_format($r['balance'],2) ?></td>
                <td style="font-weight:600;"><?= $r['commission_rate'] ?>%</td>
                <td><span class="badge <?= $r['status']==='active'?'badge-green':'badge-gray' ?>"><?= ucfirst($r['status']) ?></span></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="<?= base_url("resellers/view/{$r['id']}") ?>" class="btn btn-ghost btn-sm" title="View"><i class="fa-solid fa-eye"></i></a>
                        <a href="<?= base_url("resellers/edit/{$r['id']}") ?>" class="btn btn-ghost btn-sm" title="Edit"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" action="<?= base_url("resellers/delete/{$r['id']}") ?>" onsubmit="return confirm('Deactivate this reseller?');" style="display:inline;">
                            <button type="submit" class="btn btn-danger btn-sm" title="Deactivate"><i class="fa-solid fa-ban"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
