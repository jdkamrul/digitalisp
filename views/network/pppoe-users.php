<?php // views/network/pppoe-users.php ?>
<div class="page-header fade-in" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 class="page-title">PPPoE Users</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-network-wired" style="color:var(--blue)"></i> Network <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> PPPoE Users</div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?= base_url('network/pppoe-profiles') ?>" class="btn btn-ghost"><i class="fa-solid fa-layer-group"></i> PPPoE Profiles</a>
    </div>
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

<div class="card fade-in" style="overflow:hidden;">
    <div style="padding:16px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="color:var(--text2);font-size:13px;">Search:</span>
            <input type="text" id="searchInput" class="form-input" style="width:250px;padding:6px 10px;" placeholder="Search customer, username..." onkeyup="filterTable()">
        </div>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>PPPoE Username</th>
                <th>Profile</th>
                <th>Static IP</th>
                <th>NAS</th>
                <th>Status</th>
                <th style="width:180px;">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if(empty($customers)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2);">No PPPoE users found</td></tr>
            <?php else: foreach($customers as $c): 
                $profile = $customerProfiles[$c['id']] ?? '';
                $staticIp = $customerStaticIp[$c['id']] ?? '';
            ?>
            <tr data-search="<?= strtolower(htmlspecialchars($c['full_name'].' '.$c['customer_code'].' '.$c['pppoe_username'])) ?>">
                <td>
                    <a href="<?= base_url('customers/view/'.$c['id']) ?>" style="font-weight:600;color:var(--blue);text-decoration:none;">
                        <?= htmlspecialchars($c['full_name']) ?>
                    </a>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($c['customer_code']) ?></div>
                </td>
                <td>
                    <code style="background:var(--bg3);padding:4px 8px;border-radius:4px;font-family:monospace;font-weight:600;">
                        <?= htmlspecialchars($c['pppoe_username']) ?>
                    </code>
                </td>
                <td>
                    <?php if(!empty($profile)): ?>
                    <span class="badge badge-purple"><?= htmlspecialchars($profile) ?></span>
                    <?php else: ?>
                    <span style="color:var(--text2);font-size:12px;">Default</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(!empty($staticIp)): ?>
                    <code style="background:var(--bg3);padding:3px 6px;border-radius:4px;font-size:12px;"><?= htmlspecialchars($staticIp) ?></code>
                    <?php elseif(!empty($c['static_ip'])): ?>
                    <code style="background:var(--bg3);padding:3px 6px;border-radius:4px;font-size:12px;"><?= htmlspecialchars($c['static_ip']) ?></code>
                    <?php else: ?>
                    <span style="color:var(--text2);font-size:12px;">Dynamic</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(!empty($c['nas_name'])): ?>
                    <span style="font-size:12px;"><?= htmlspecialchars($c['nas_name']) ?></span>
                    <?php else: ?>
                    <span style="color:var(--text2);font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($c['is_online']): ?>
                    <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(16,185,129,0.1);color:#10b981;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;">
                        <span style="width:6px;height:6px;border-radius:50%;background:#10b981;animation:pulse 2s infinite;"></span> Online
                    </span>
                    <?php else: ?>
                    <span style="display:inline-flex;align-items:center;gap:6px;background:var(--bg3);color:var(--text2);padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;">
                        Offline
                    </span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <button class="btn btn-blue btn-sm" onclick='openEditModal(<?= json_encode($c) ?>)' title="Edit"><i class="fa-solid fa-pen"></i></button>
                        <?php if($c['is_online']): ?>
                        <form action="<?= base_url('network/pppoe-users/kick/'.$c['id']) ?>" method="POST" style="display:inline;">
                            <button type="submit" class="btn btn-yellow btn-sm" title="Kick Session" onclick="return confirm('Disconnect this user?')"><i class="fa-solid fa-plug-circle-xmark"></i></button>
                        </form>
                        <?php endif; ?>
                        <form action="<?= base_url('network/pppoe-users/reset-password/'.$c['id']) ?>" method="POST" style="display:inline;">
                            <button type="submit" class="btn btn-ghost btn-sm" title="Reset Password" onclick="return confirm('Reset password to random?')"><i class="fa-solid fa-key"></i></button>
                        </form>
                        <button class="btn btn-ghost btn-sm" onclick='openCredentialsModal(<?= json_encode($c) ?>)' title="View Credentials"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-user-gear" style="color:var(--blue);margin-right:8px;"></i>Edit PPPoE User</div>
            <button class="icon-btn" onclick="document.getElementById('editModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editForm" method="POST">
            <div class="modal-body" style="display:grid;gap:16px;">
                <div style="background:var(--bg2);padding:12px;border-radius:8px;">
                    <strong><span id="edit_customer_name"></span></strong>
                    <div style="font-size:12px;color:var(--text2);" id="edit_username"></div>
                </div>
                <div>
                    <label class="form-label">New Password (leave blank to keep)</label>
                    <input type="text" name="pppoe_password" id="edit_password" class="form-input" placeholder="Enter new password">
                </div>
                <div>
                    <label class="form-label">Speed Profile</label>
                    <select name="profile" id="edit_profile" class="form-input">
                        <option value="">-- Default --</option>
                        <?php foreach($profiles as $p): ?>
                        <option value="<?= htmlspecialchars($p['groupname']) ?>"><?= htmlspecialchars($p['groupname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Static IP Address</label>
                    <input type="text" name="static_ip" id="edit_static_ip" class="form-input" placeholder="e.g. 172.25.100.50">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Credentials Modal -->
<div id="credentialsModal" class="modal-overlay">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-key" style="color:var(--blue);margin-right:8px;"></i>PPPoE Credentials</div>
            <button class="icon-btn" onclick="document.getElementById('credentialsModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:var(--bg2);padding:16px;border-radius:8px;text-align:center;">
                <div style="font-size:12px;color:var(--text2);margin-bottom:4px;">Username</div>
                <div style="font-family:monospace;font-size:18px;font-weight:700;color:var(--blue);" id="cred_username"></div>
            </div>
            <div style="background:var(--bg2);padding:16px;border-radius:8px;text-align:center;margin-top:12px;">
                <div style="font-size:12px;color:var(--text2);margin-bottom:4px;">Password</div>
                <div style="font-family:monospace;font-size:18px;font-weight:700;" id="cred_password"></div>
            </div>
            <div style="margin-top:12px;font-size:12px;color:var(--text2);text-align:center;">
                Connection: PPPoE | Service: anything
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('credentialsModal').classList.remove('open')">Close</button>
            <button class="btn btn-primary" onclick="copyCredentials()"><i class="fa-regular fa-copy"></i> Copy</button>
        </div>
    </div>
</div>

<script>
let currentCred = {};

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#tableBody tr').forEach(row => {
        row.style.display = row.getAttribute('data-search').includes(search) ? '' : 'none';
    });
}

function openEditModal(c) {
    document.getElementById('edit_customer_name').textContent = c.full_name;
    document.getElementById('edit_username').textContent = c.pppoe_username;
    document.getElementById('editForm').action = '<?= base_url('network/pppoe-users/update/') ?>' + c.id;
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_static_ip').value = c.static_ip || '';
    document.getElementById('editModal').classList.add('open');
}

function openCredentialsModal(c) {
    document.getElementById('cred_username').textContent = c.pppoe_username;
    document.getElementById('cred_password').textContent = c.pppoe_password || '(stored in system)';
    currentCred = { username: c.pppoe_username, password: c.pppoe_password };
    document.getElementById('credentialsModal').classList.add('open');
}

function copyCredentials() {
    const text = currentCred.username + '\n' + (currentCred.password || '');
    navigator.clipboard.writeText(text).then(() => {
        alert('Credentials copied!');
    });
}

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target === o) o.classList.remove('open'); });
});
</script>