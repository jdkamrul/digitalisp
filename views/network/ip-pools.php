<?php // views/network/ip-pools.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">IP Pool Management</h1><div class="page-breadcrumb"><i class="fa-solid fa-network-wired" style="color:var(--blue)"></i> Network</div></div>
    <button class="btn btn-primary" onclick="document.getElementById('addPoolModal').classList.add('open')"><i class="fa-solid fa-plus"></i> Add Pool</button>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;" class="fade-in">
    <?php if(empty($pools)): ?>
    <div class="card" style="padding:40px;text-align:center;color:var(--text2);grid-column:1/-1;">
        <i class="fa-solid fa-network-wired" style="font-size:32px;display:block;margin-bottom:12px;"></i>
        No IP pools configured. Add your first pool.
    </div>
    <?php else: foreach($pools as $p): 
        $usedPct = $p['total_ips'] > 0 ? round(($p['used_count']/$p['total_ips'])*100) : 0;
        $barColor = $usedPct>85?'var(--red)':($usedPct>60?'var(--yellow)':'var(--green)');
    ?>
    <div class="card" style="padding:18px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
            <div>
                <div style="font-size:15px;font-weight:700;"><?= htmlspecialchars($p['name']) ?></div>
                <div style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($p['branch_name']??'—') ?></div>
            </div>
            <span class="badge <?= $p['is_active']?'badge-green':'badge-gray' ?>"><?= $p['is_active']?'Active':'Inactive' ?></span>
        </div>
        <div style="font-family:monospace;font-size:13px;margin-bottom:10px;color:var(--blue);"><?= htmlspecialchars($p['network_cidr']) ?></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;color:var(--text2);margin-bottom:12px;">
            <div>Gateway: <strong><?= htmlspecialchars($p['gateway']??'—') ?></strong></div>
            <div>DNS: <strong><?= htmlspecialchars($p['dns1']??'—') ?></strong></div>
            <div>Type: <strong><?= ucfirst($p['ip_type']) ?></strong></div>
            <div>Total IPs: <strong><?= number_format($p['total_ips']) ?></strong></div>
        </div>
        <div style="margin-bottom:6px;display:flex;justify-content:space-between;font-size:12px;">
            <span style="color:var(--text2);">Usage</span>
            <span style="font-weight:700;"><?= $p['used_count'] ?> / <?= $p['total_ips'] ?> (<?= $usedPct ?>%)</span>
        </div>
        <div style="background:var(--bg3);border-radius:6px;height:6px;overflow:hidden;">
            <div style="height:6px;width:<?= $usedPct ?>%;background:<?= $barColor ?>;border-radius:6px;transition:width .4s;"></div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<!-- Add Pool Modal -->
<div class="modal-overlay" id="addPoolModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-plus" style="color:var(--green);margin-right:8px;"></i>Add IP Pool</div>
            <button class="icon-btn" onclick="document.getElementById('addPoolModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('network/ip-pools/store') ?>">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div><label class="form-label">Pool Name</label><input type="text" name="name" class="form-input" placeholder="e.g. Dhaka-Core" required></div>
                <div><label class="form-label">Branch</label>
                    <select name="branch_id" class="form-input" required>
                        <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">CIDR</label><input type="text" name="network_cidr" class="form-input" placeholder="192.168.1.0/24" required></div>
                    <div><label class="form-label">Gateway</label><input type="text" name="gateway" class="form-input" placeholder="192.168.1.1"></div>
                    <div><label class="form-label">DNS 1</label><input type="text" name="dns1" class="form-input" value="8.8.8.8"></div>
                    <div><label class="form-label">DNS 2</label><input type="text" name="dns2" class="form-input" value="8.8.4.4"></div>
                    <div><label class="form-label">Type</label>
                        <select name="ip_type" class="form-input"><option value="private">Private</option><option value="public">Public</option><option value="cgnat">CGNAT</option></select>
                    </div>
                    <div><label class="form-label">Total IPs</label><input type="number" name="total_ips" class="form-input" value="254"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addPoolModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Pool</button>
            </div>
        </form>
    </div>
</div>
