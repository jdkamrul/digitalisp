<?php // views/network/mac-filters.php ?>
<style>
.kpi-row { display:flex; gap:12px; margin-bottom:16px; }
.kpi-card { flex:1; background:var(--bg2); padding:16px; border-radius:8px; border:1px solid var(--border); }
.kpi-card .label { font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:0.5px; }
.kpi-card .value { font-size:28px; font-weight:700; color:var(--text); margin-top:4px; }
</style>

<div class="page-header fade-in" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 class="page-title">MAC Filters</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-network-wired" style="color:var(--blue)"></i> Network <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> MAC Filters</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa-solid fa-plus"></i> Add Filter</button>
</div>

<?php if(isset($_SESSION['success'])): ?>
<div style="background:rgba(16,185,129,0.1); border:1px solid var(--green); color:var(--green); padding:12px; border-radius:8px; margin-bottom:16px; font-weight:600;">
    <i class="fa-solid fa-check-circle" style="margin-right:8px;"></i> <?= $_SESSION['success'] ?>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div style="background:rgba(239,68,68,0.1); border:1px solid var(--red); color:var(--red); padding:12px; border-radius:8px; margin-bottom:16px; font-weight:600;">
    <i class="fa-solid fa-triangle-exclamation" style="margin-right:8px;"></i> <?= $_SESSION['error'] ?>
</div>
<?php unset($_SESSION['error']); endif; ?>

<div class="kpi-row fade-in">
    <div class="kpi-card">
        <div class="label">Total Filters</div>
        <div class="value"><?= count($filters) ?></div>
    </div>
    <div class="kpi-card">
        <div class="label">Allowed</div>
        <div class="value"><?= count(array_filter($filters, fn($f) => $f['action'] === 'allow' && $f['is_active'])) ?></div>
    </div>
    <div class="kpi-card">
        <div class="label">Blocked</div>
        <div class="value"><?= count(array_filter($filters, fn($f) => $f['action'] === 'deny' && $f['is_active'])) ?></div>
    </div>
    <div class="kpi-card">
        <div class="label">Inactive</div>
        <div class="value"><?= count(array_filter($filters, fn($f) => !$f['is_active'])) ?></div>
    </div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th>MAC Address</th>
                <th>Action</th>
                <th>Customer</th>
                <th>NAS</th>
                <th>Reason</th>
                <th>Expires</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($filters)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text2);">No MAC filters found</td></tr>
            <?php else: foreach($filters as $f): ?>
            <tr>
                <td><code style="background:var(--bg3);padding:4px 8px;border-radius:4px;font-size:12px;"><?= htmlspecialchars($f['mac_address']) ?></code></td>
                <td>
                    <?php if($f['action'] === 'allow'): ?>
                    <span class="badge badge-green"><i class="fa-solid fa-check"></i> Allow</span>
                    <?php else: ?>
                    <span class="badge badge-red"><i class="fa-solid fa-ban"></i> Deny</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(!empty($f['customer_id'])): ?>
                    <a href="<?= base_url("customers/view/{$f['customer_id']}") ?>" style="font-weight:600;color:var(--blue);text-decoration:none;"><?= htmlspecialchars($f['customer_name']) ?></a>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($f['customer_code']) ?></div>
                    <?php else: ?>
                    <span style="color:var(--text2);">—</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($f['nas_name'] ?? 'All') ?></td>
                <td><?= htmlspecialchars($f['reason'] ?: '—') ?></td>
                <td><?= $f['expires_at'] ? date('d M Y', strtotime($f['expires_at'])) : 'Never' ?></td>
                <td>
                    <?php if($f['is_active']): ?>
                    <span class="badge badge-green">Active</span>
                    <?php else: ?>
                    <span class="badge badge-gray">Inactive</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;">
                    <form action="<?= base_url('network/mac-filters/toggle/' . $f['id']) ?>" method="POST" style="display:inline;">
                        <button type="submit" class="btn btn-ghost btn-sm" title="<?= $f['is_active'] ? 'Disable' : 'Enable' ?>">
                            <i class="fa-solid fa-toggle-<?= $f['is_active'] ? 'on' : 'off' ?>"></i>
                        </button>
                    </form>
                    <form action="<?= base_url('network/mac-filters/delete/' . $f['id']) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Delete this MAC filter?');">
                        <button type="submit" class="btn btn-ghost btn-sm" title="Delete" style="color:var(--red);"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Add MAC Filter</div>
            <button class="icon-btn" onclick="closeModal('addModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('network/mac-filters/store') ?>">
            <div class="modal-body" style="display:grid;gap:14px;">
                <div>
                    <label class="form-label">MAC Address *</label>
                    <input type="text" name="mac_address" class="form-input" required placeholder="AA:BB:CC:DD:EE:FF" style="font-family:monospace;">
                </div>
                <div>
                    <label class="form-label">Action *</label>
                    <select name="action" class="form-input">
                        <option value="allow">Allow (Whitelist)</option>
                        <option value="deny">Deny (Blacklist)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Customer (Optional)</label>
                    <select name="customer_id" class="form-input">
                        <option value="">— None —</option>
                        <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['full_name'] . ' (' . $c['customer_code'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">NAS Server</label>
                    <select name="nas_id" class="form-input">
                        <option value="">All NAS</option>
                        <?php foreach($nasDevices as $n): ?>
                        <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['name'] . ' (' . $n['ip_address'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Reason</label>
                    <input type="text" name="reason" class="form-input" placeholder="Why this filter exists">
                </div>
                <div>
                    <label class="form-label">Expires At (Optional)</label>
                    <input type="datetime-local" name="expires_at" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target === o) o.classList.remove('open'); });
});
</script>
