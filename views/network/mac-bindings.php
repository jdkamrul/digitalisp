<?php // views/network/mac-bindings.php ?>
<style>
.kpi-row { display:flex; gap:12px; margin-bottom:16px; }
.kpi-card { flex:1; background:var(--bg2); padding:16px; border-radius:8px; border:1px solid var(--border); }
.kpi-card .label { font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:0.5px; }
.kpi-card .value { font-size:28px; font-weight:700; color:var(--text); margin-top:4px; }
</style>

<div class="page-header fade-in" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 class="page-title">MAC Bindings & CallerID</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-network-wired" style="color:var(--blue)"></i> Network <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> MAC Bindings</div>
    </div>
    <button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa-solid fa-plus"></i> Add Binding</button>
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
        <div class="label">Total Bindings</div>
        <div class="value"><?= count($bindings) ?></div>
    </div>
    <div class="kpi-card">
        <div class="label">Active</div>
        <div class="value"><?= count(array_filter($bindings, fn($b) => $b['is_active'])) ?></div>
    </div>
    <div class="kpi-card">
        <div class="label">Allowed</div>
        <div class="value"><?= count(array_filter($bindings, fn($b) => $b['is_allowed'])) ?></div>
    </div>
    <div class="kpi-card">
        <div class="label">Blocked</div>
        <div class="value"><?= count(array_filter($bindings, fn($b) => !$b['is_allowed'])) ?></div>
    </div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>MAC Address</th>
                <th>Caller ID</th>
                <th>Customer</th>
                <th>NAS</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($bindings)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2);">No MAC bindings found</td></tr>
            <?php else: foreach($bindings as $b): ?>
            <tr>
                <td style="font-family:monospace;font-weight:700;"><?= htmlspecialchars($b['username'] ?: '—') ?></td>
                <td><code style="background:var(--bg3);padding:4px 8px;border-radius:4px;font-size:12px;"><?= htmlspecialchars($b['mac_address']) ?></code></td>
                <td><code style="background:var(--bg3);padding:4px 8px;border-radius:4px;font-size:12px;"><?= htmlspecialchars($b['caller_id'] ?: '—') ?></code></td>
                <td>
                    <?php if(!empty($b['customer_id'])): ?>
                    <a href="<?= base_url("customers/view/{$b['customer_id']}") ?>" style="font-weight:600;color:var(--blue);text-decoration:none;"><?= htmlspecialchars($b['customer_name']) ?></a>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($b['customer_code']) ?></div>
                    <?php else: ?>
                    <span style="color:var(--text2);">—</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($b['nas_name'] ?? '—') ?></td>
                <td>
                    <?php if($b['is_active'] && $b['is_allowed']): ?>
                    <span class="badge badge-green">Allowed</span>
                    <?php elseif($b['is_active'] && !$b['is_allowed']): ?>
                    <span class="badge badge-red">Blocked</span>
                    <?php else: ?>
                    <span class="badge badge-gray">Disabled</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;">
                    <button class="btn btn-ghost btn-sm" title="Edit" onclick='editBinding(<?= json_encode($b) ?>)'><i class="fa-solid fa-pen"></i></button>
                    <form action="<?= base_url('network/mac-bindings/toggle/' . $b['id']) ?>" method="POST" style="display:inline;">
                        <button type="submit" class="btn btn-ghost btn-sm" title="<?= $b['is_active'] ? 'Disable' : 'Enable' ?>">
                            <i class="fa-solid fa-toggle-<?= $b['is_active'] ? 'on' : 'off' ?>"></i>
                        </button>
                    </form>
                    <form action="<?= base_url('network/mac-bindings/delete/' . $b['id']) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Delete this MAC binding?');">
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
            <div class="modal-title">Add MAC Binding</div>
            <button class="icon-btn" onclick="closeModal('addModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('network/mac-bindings/store') ?>">
            <div class="modal-body" style="display:grid;gap:14px;">
                <div>
                    <label class="form-label">PPPoE Username *</label>
                    <input type="text" name="username" class="form-input" required placeholder="username">
                </div>
                <div>
                    <label class="form-label">MAC Address *</label>
                    <input type="text" name="mac_address" class="form-input" required placeholder="AA:BB:CC:DD:EE:FF" style="font-family:monospace;">
                </div>
                <div>
                    <label class="form-label">Caller ID (Optional)</label>
                    <input type="text" name="caller_id" class="form-input" placeholder="Calling Station ID">
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
                    <label class="form-checkbox" style="margin-top:8px;">
                        <input type="checkbox" name="is_allowed" checked>
                        <span>Allow this MAC</span>
                    </label>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-input" placeholder="Notes">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Edit MAC Binding</div>
            <button class="icon-btn" onclick="closeModal('editModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editForm" method="POST" action="">
            <div class="modal-body" style="display:grid;gap:14px;">
                <div>
                    <label class="form-label">PPPoE Username *</label>
                    <input type="text" name="username" id="edit_username" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">MAC Address</label>
                    <input type="text" name="mac_address" id="edit_mac" class="form-input" style="font-family:monospace;">
                </div>
                <div>
                    <label class="form-label">Caller ID</label>
                    <input type="text" name="caller_id" id="edit_caller_id" class="form-input">
                </div>
                <div>
                    <label class="form-label">Customer</label>
                    <select name="customer_id" id="edit_customer_id" class="form-input">
                        <option value="">— None —</option>
                        <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['full_name'] . ' (' . $c['customer_code'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">NAS Server</label>
                    <select name="nas_id" id="edit_nas_id" class="form-input">
                        <option value="">All NAS</option>
                        <?php foreach($nasDevices as $n): ?>
                        <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['name'] . ' (' . $n['ip_address'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-checkbox" style="margin-top:8px;">
                        <input type="checkbox" name="is_allowed" id="edit_is_allowed">
                        <span>Allow this MAC</span>
                    </label>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <input type="text" name="description" id="edit_description" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function editBinding(b) {
    document.getElementById('editForm').action = '<?= base_url("network/mac-bindings/update/") ?>' + b.id;
    document.getElementById('edit_username').value = b.username || '';
    document.getElementById('edit_mac').value = b.mac_address || '';
    document.getElementById('edit_caller_id').value = b.caller_id || '';
    document.getElementById('edit_customer_id').value = b.customer_id || '';
    document.getElementById('edit_nas_id').value = b.nas_id || '';
    document.getElementById('edit_is_allowed').checked = b.is_allowed == 1;
    document.getElementById('edit_description').value = b.description || '';
    openModal('editModal');
}
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target === o) o.classList.remove('open'); });
});
</script>
