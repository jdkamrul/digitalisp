<?php // views/network/pppoe-profiles.php ?>
<div class="page-header fade-in" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 class="page-title">PPPoE Profiles</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-network-wired" style="color:var(--blue)"></i> Network <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> PPPoE Profiles</div>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')"><i class="fa-solid fa-plus"></i> Add Profile</button>
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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if(empty($profiles)): ?>
    <div class="col-span-full card" style="text-align:center;padding:40px;color:var(--text2);">
        <i class="fa-solid fa-layer-group" style="font-size:32px;display:block;margin-bottom:12px;opacity:.4;"></i>
        No PPPoE profiles configured.
    </div>
    <?php else: foreach($profiles as $p): ?>
    <div class="card fade-in" style="padding:16px;">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px;">
            <div>
                <h3 style="font-weight:700;font-size:16px;color:var(--blue);"><?= htmlspecialchars($p['name']) ?></h3>
                <span style="font-size:12px;color:var(--text2);"><?= $p['nas_name'] ?? 'All NAS' ?></span>
            </div>
            <div style="display:flex;gap:4px;">
                <button class="btn btn-blue btn-sm" onclick='openEditModal(<?= json_encode($p) ?>)'><i class="fa-solid fa-pen"></i></button>
                <form action="<?= base_url('network/pppoe-profiles/sync/'.$p['id']) ?>" method="POST">
                    <button type="submit" class="btn btn-green btn-sm" title="Sync to MikroTik"><i class="fa-solid fa-sync"></i></button>
                </form>
                <form action="<?= base_url('network/pppoe-profiles/delete/'.$p['id']) ?>" method="POST" onsubmit="return confirm('Delete this profile?')">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
        
        <div style="background:var(--bg2);border-radius:8px;padding:12px;font-size:12px;">
            <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);">
                <span style="color:var(--text2);">Local Address</span>
                <span style="font-weight:600;"><?= $p['local_address'] ?: '—' ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);">
                <span style="color:var(--text2);">Remote Address</span>
                <span style="font-weight:600;"><?= $p['remote_address'] ?: '—' ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);">
                <span style="color:var(--text2);">DNS Server</span>
                <span style="font-weight:600;"><?= $p['dns_server'] ?: '—' ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);">
                <span style="color:var(--text2);">Session Timeout</span>
                <span style="font-weight:600;"><?= $p['session_timeout'] ? $p['session_timeout'].'s' : '—' ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);">
                <span style="color:var(--text2);">Idle Timeout</span>
                <span style="font-weight:600;"><?= $p['idle_timeout'] ? $p['idle_timeout'].'s' : '—' ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:4px 0;">
                <span style="color:var(--text2);">Rate Limit</span>
                <span style="font-weight:600;font-family:monospace;"><?= $p['rate_limit'] ?: '—' ?></span>
            </div>
        </div>
        
        <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;">
            <?php if($p['is_synced']): ?>
            <span class="badge badge-green"><i class="fa-solid fa-check"></i> Synced</span>
            <?php else: ?>
            <span class="badge badge-gray">Not synced</span>
            <?php endif; ?>
            <?php if($p['last_synced']): ?>
            <span style="font-size:10px;color:var(--text2);"><?= date('d-M H:i', strtotime($p['last_synced'])) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-plus" style="color:var(--blue);margin-right:8px;"></i>Add PPPoE Profile</div>
            <button class="icon-btn" onclick="document.getElementById('addModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('network/pppoe-profiles/store') ?>">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div><label class="form-label">Profile Name *</label><input type="text" name="name" class="form-input" required placeholder="e.g. 10Mbps-Unlimited"></div>
                <div><label class="form-label">NAS (Optional)</label>
                    <select name="nas_id" class="form-input">
                        <option value="">-- All NAS --</option>
                        <?php foreach($nasDevices as $n): ?>
                        <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['name']) ?> (<?= $n['ip_address'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Local Address</label><input type="text" name="local_address" class="form-input" placeholder="e.g. 172.25.100.1"></div>
                    <div><label class="form-label">Remote Address Pool</label><input type="text" name="remote_address" class="form-input" placeholder="e.g. 172.25.100.10-50"></div>
                </div>
                <div><label class="form-label">DNS Server</label><input type="text" name="dns_server" class="form-input" placeholder="e.g. 8.8.8.8,8.8.4.4"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Session Timeout (sec)</label><input type="number" name="session_timeout" class="form-input" value="0" placeholder="0 = unlimited"></div>
                    <div><label class="form-label">Idle Timeout (sec)</label><input type="number" name="idle_timeout" class="form-input" value="0" placeholder="0 = unlimited"></div>
                </div>
                <div><label class="form-label">Rate Limit</label><input type="text" name="rate_limit" class="form-input" placeholder="e.g. 10M/10M"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Profile</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--blue);margin-right:8px;"></i>Edit PPPoE Profile</div>
            <button class="icon-btn" onclick="document.getElementById('editModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editForm" method="POST">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div><label class="form-label">Profile Name *</label><input type="text" name="name" id="edit_name" class="form-input" required></div>
                <div><label class="form-label">NAS</label>
                    <select name="nas_id" id="edit_nas_id" class="form-input">
                        <option value="">-- All NAS --</option>
                        <?php foreach($nasDevices as $n): ?>
                        <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Local Address</label><input type="text" name="local_address" id="edit_local_address" class="form-input"></div>
                    <div><label class="form-label">Remote Address Pool</label><input type="text" name="remote_address" id="edit_remote_address" class="form-input"></div>
                </div>
                <div><label class="form-label">DNS Server</label><input type="text" name="dns_server" id="edit_dns_server" class="form-input"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Session Timeout</label><input type="number" name="session_timeout" id="edit_session_timeout" class="form-input"></div>
                    <div><label class="form-label">Idle Timeout</label><input type="number" name="idle_timeout" id="edit_idle_timeout" class="form-input"></div>
                </div>
                <div><label class="form-label">Rate Limit</label><input type="text" name="rate_limit" id="edit_rate_limit" class="form-input"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(p) {
    document.getElementById('editForm').action = '<?= base_url('network/pppoe-profiles/update/') ?>' + p.id;
    document.getElementById('edit_name').value = p.name || '';
    document.getElementById('edit_nas_id').value = p.nas_id || '';
    document.getElementById('edit_local_address').value = p.local_address || '';
    document.getElementById('edit_remote_address').value = p.remote_address || '';
    document.getElementById('edit_dns_server').value = p.dns_server || '';
    document.getElementById('edit_session_timeout').value = p.session_timeout || 0;
    document.getElementById('edit_idle_timeout').value = p.idle_timeout || 0;
    document.getElementById('edit_rate_limit').value = p.rate_limit || '';
    document.getElementById('editModal').classList.add('open');
}
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target === o) o.classList.remove('open'); });
});
</script>

<style>
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: 1fr; }
@media (min-width: 768px) { .md\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .lg\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); } }
.col-span-full { grid-column: 1 / -1; }
</style>