<?php // views/network/radius.php ?>
<div class="page-header fade-in" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 class="page-title">RADIUS AAA Users</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-satellite-dish" style="color:var(--blue)"></i> Network <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> RADIUS AAA</div>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="btn btn-outline" onclick="document.getElementById('filterModal').classList.add('open')"><i class="fa-solid fa-filter"></i> Filters</button>
        <button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa-solid fa-plus"></i> Add User</button>
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

<!-- Filter Status Bar -->
<div class="card fade-in" style="padding:12px 16px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:13px;color:var(--text2);">Search:</span>
            <input type="text" id="searchInput" class="form-input" style="width:200px;padding:6px 10px;font-size:13px;" placeholder="Username, customer..." onkeyup="filterUsers()">
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:13px;color:var(--text2);">Status:</span>
            <select id="statusFilter" class="form-input" style="width:120px;padding:6px 10px;font-size:13px;" onchange="filterUsers()">
                <option value="">All</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:13px;color:var(--text2);">Profile:</span>
            <select id="profileFilter" class="form-input" style="width:150px;padding:6px 10px;font-size:13px;" onchange="filterUsers()">
                <option value="">All Profiles</option>
                <?php foreach($profiles as $p): ?>
                <option value="<?= htmlspecialchars($p['groupname']) ?>"><?= htmlspecialchars($p['groupname']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div style="font-size:13px;color:var(--text2);">
        Showing: <span id="showingCount" style="font-weight:700;color:var(--text);"><?= count($users) ?></span> / <?= count($users) ?> users
    </div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table" id="radiusTable">
        <thead>
            <tr>
                <th>Username</th>
                <th>Authentication</th>
                <th>Customer</th>
                <th>Profile</th>
                <th>IP Address</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if(empty($users)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text2);">No RADIUS users found</td></tr>
            <?php else: foreach($users as $u): ?>
            <tr data-username="<?= strtolower(htmlspecialchars($u['username'])) ?>" data-status="<?= $u['is_online'] ? 'online' : 'offline' ?>" data-profile="<?= htmlspecialchars($u['profile'] ?? '') ?>">
                <td style="font-family:monospace;font-weight:700;color:var(--text);"><?= htmlspecialchars($u['username']) ?></td>
                <td>
                    <div style="font-size:11px;color:var(--text2);">Type: <?= htmlspecialchars($u['attribute']) ?></div>
                    <code style="background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:11px;"><?= htmlspecialchars($u['password']) ?></code>
                </td>
                <td>
                    <?php if(!empty($u['customer_id'])): ?>
                    <a href="<?= base_url("customers/view/{$u['customer_id']}") ?>" style="font-weight:600;color:var(--blue);text-decoration:none;"><?= htmlspecialchars($u['customer_name']) ?></a>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($u['customer_code']) ?></div>
                    <?php else: ?>
                    <span style="color:var(--text2);font-style:italic;">Not Linked</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(!empty($u['profile'])): ?>
                    <span class="badge badge-purple" style="font-family:monospace;"><?= htmlspecialchars($u['profile']) ?></span>
                    <?php else: ?>
                    <span style="color:var(--text2);font-size:12px;">Dynamic</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(!empty($u['ip_address'])): ?>
                    <code style="background:var(--bg3);padding:3px 6px;border-radius:4px;font-size:12px;"><?= htmlspecialchars($u['ip_address']) ?></code>
                    <?php else: ?>
                    <span style="color:var(--text2);font-size:12px;">Pool</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($u['is_online']): ?>
                    <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(16,185,129,0.1);color:#10b981;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;">
                        <span style="width:6px;height:6px;border-radius:50%;background:#10b981;animation:pulse 2s infinite;"></span> Online
                    </span>
                    <?php else: ?>
                    <span style="display:inline-flex;align-items:center;gap:6px;background:var(--bg3);color:var(--text2);padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;">
                        Offline
                    </span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;">
                    <?php if($u['is_online']): ?>
                    <form action="<?= base_url('network/radius/kick/' . urlencode($u['username'])) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Disconnect this active PPP session?');">
                        <button type="submit" class="btn btn-ghost btn-sm" title="Disconnect Session" style="color:var(--yellow);"><i class="fa-solid fa-plug-circle-xmark"></i></button>
                    </form>
                    <?php endif; ?>
                    <button class="btn btn-ghost btn-sm" title="Edit User" onclick='editUser(<?= json_encode($u) ?>)'><i class="fa-solid fa-pen"></i></button>
                    <form action="<?= base_url('network/radius/delete/' . urlencode($u['username'])) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to completely DELETE this RADIUS user? This will stop their internet access.');">
                        <button type="submit" class="btn btn-ghost btn-sm" title="Delete User" style="color:var(--red);"><i class="fa-solid fa-trash"></i></button>
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
            <div class="modal-title">Add RADIUS User</div>
            <button class="icon-btn" onclick="closeModal('addModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('network/radius/store') ?>">
            <div class="modal-body" style="display:grid;gap:16px;">
                <div>
                    <label class="form-label">PPPoE Username *</label>
                    <input type="text" name="username" class="form-input" required placeholder="john.doe">
                </div>
                <div>
                    <label class="form-label">Password *</label>
                    <input type="text" name="password" class="form-input" required placeholder="secret">
                </div>
                <div>
                    <label class="form-label">MikroTik Profile (Optional)</label>
                    <select name="profile" class="form-input">
                        <option value="">-- Dynamic Profile --</option>
                        <?php foreach($profiles as $p): ?>
                        <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size:11px;color:var(--text2);margin-top:4px;">Matches the exact profile name in Mikrotik.</div>
                </div>
                <div>
                    <label class="form-label">Static IP Address (Optional)</label>
                    <input type="text" name="ip_address" class="form-input" placeholder="e.g. 192.168.100.50">
                    <div style="font-size:11px;color:var(--text2);margin-top:4px;">Framed-IP-Address for reserved IPs.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Edit RADIUS User: <span id="edit_username_label" style="color:var(--blue);"></span></div>
            <button class="icon-btn" onclick="closeModal('editModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editForm" method="POST" action="">
            <div class="modal-body" style="display:grid;gap:16px;">
                <div>
                    <label class="form-label">New Password</label>
                    <input type="text" name="password" id="edit_password" class="form-input" placeholder="Leave blank to keep current">
                </div>
                <div>
                    <label class="form-label">MikroTik Profile</label>
                    <select name="profile" id="edit_profile" class="form-input">
                        <option value="">-- Dynamic Profile --</option>
                        <?php foreach($profiles as $p): ?>
                        <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Static IP Address</label>
                    <input type="text" name="ip_address" id="edit_ip_address" class="form-input" placeholder="e.g. 192.168.100.50">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update User</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function editUser(u) {
    document.getElementById('edit_username_label').textContent = u.username;
    document.getElementById('editForm').action = '<?= base_url("network/radius/update/") ?>' + encodeURIComponent(u.username);
    document.getElementById('edit_profile').value = u.profile || '';
    document.getElementById('edit_ip_address').value = u.ip_address || '';
    document.getElementById('edit_password').value = '';
    openModal('editModal');
}

function filterUsers() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const profile = document.getElementById('profileFilter').value;
    const rows = document.querySelectorAll('#tableBody tr');
    let visible = 0;
    
    rows.forEach(row => {
        const username = row.getAttribute('data-username') || '';
        const rowStatus = row.getAttribute('data-status') || '';
        const rowProfile = row.getAttribute('data-profile') || '';
        
        const matchSearch = username.includes(search);
        const matchStatus = !status || rowStatus === status;
        const matchProfile = !profile || rowProfile === profile;
        
        if (matchSearch && matchStatus && matchProfile) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('showingCount').textContent = visible;
}

document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if(e.target === o) o.classList.remove('open'); });
});
</script>
