<?php // views/network/nas.php ?>
<style>
/* ── Page Layout ── */
.nas-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.nas-title { display:flex; align-items:center; gap:10px; }
.nas-title h1 { font-size:20px; font-weight:700; color:var(--text); margin:0; }
.nas-title span { font-size:13px; color:var(--text2); font-weight:400; }
.nas-breadcrumb { font-size:12px; color:var(--text2); display:flex; align-items:center; gap:6px; }
.nas-breadcrumb a { color:var(--text2); text-decoration:none; }

/* ── Add Button ── */
.btn-add-server {
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: #fff; border: none; padding: 9px 18px;
    border-radius: 20px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; gap: 7px;
    box-shadow: 0 2px 8px rgba(37,99,235,0.35); transition: all .2s;
}
.btn-add-server:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.45); }
.btn-add-server i { color: #fbbf24; }

/* ── Card ── */
.nas-card { background:var(--card-bg); border:1px solid var(--border); border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden; }

/* ── Table Controls ── */
.table-controls { display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-bottom:1px solid var(--border); }
.table-controls label { font-size:13px; color:var(--text2); }
.table-controls select, .table-search {
    border:1px solid var(--border); background:var(--bg2); color:var(--text);
    padding:5px 10px; border-radius:5px; font-size:13px; outline:none;
}
.table-search { margin-left:6px; width:200px; }
.table-search:focus { border-color:#3b82f6; }

/* ── Table ── */
.nas-table { width:100%; border-collapse:collapse; text-align:center; font-size:13px; }
.nas-table thead tr { background:linear-gradient(90deg,#1e3a8a,#1e40af); }
.nas-table th { color:#fff; padding:13px 14px; font-weight:600; font-size:12px; letter-spacing:.4px; border:1px solid rgba(255,255,255,0.1); }
.nas-table td { padding:12px 14px; border:1px solid var(--border); color:var(--text); vertical-align:middle; }
.nas-table tbody tr:nth-child(even) td { background:var(--bg2); }
.nas-table tbody tr:hover td { background:rgba(59,130,246,0.05); }

/* ── Server Name Cell ── */
.server-name { font-weight:700; font-size:13px; color:var(--text); }
.server-status-tag { font-size:11px; font-weight:500; margin-top:3px; display:block; }
.status-connected { color:#16a34a; }
.status-disconnected { color:#dc2626; }
.status-unknown { color:#f59e0b; }

/* ── Toggle ── */
.toggle-switch { position:relative; display:inline-block; width:40px; height:22px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider {
    position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0;
    background:#cbd5e1; transition:.3s; border-radius:22px;
}
.toggle-slider:before {
    position:absolute; content:""; height:16px; width:16px; left:3px; bottom:3px;
    background:#fff; transition:.3s; border-radius:50%;
    box-shadow: 0 1px 3px rgba(0,0,0,.3);
}
.toggle-switch input:checked + .toggle-slider { background:#3b82f6; }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(18px); }

/* ── Action Buttons ── */
.nas-action-wrap { display:flex; align-items:center; justify-content:center; gap:6px; }
.nas-btn { border:none; background:none; cursor:pointer; padding:6px 8px; border-radius:6px; font-size:13px; transition:all .15s; }
.nas-btn.refresh { color:#3b82f6; } .nas-btn.refresh:hover { background:rgba(59,130,246,.12); }
.nas-btn.edit    { color:#16a34a; } .nas-btn.edit:hover    { background:rgba(22,163,74,.12); }
.nas-btn.reconnect { color:#8b5cf6; } .nas-btn.reconnect:hover { background:rgba(139,92,246,.12); }
.nas-btn.delete  { color:#dc2626; } .nas-btn.delete:hover  { background:rgba(220,38,38,.12); }

/* ── Pagination ── */
.pagination-wrap { display:flex; justify-content:space-between; align-items:center; padding:14px 18px; font-size:13px; color:var(--text2); }
.pagination { list-style:none; padding:0; margin:0; display:flex; gap:4px; }
.pagination li a, .pagination li span {
    display:block; padding:5px 11px; border:1px solid var(--border); border-radius:5px;
    color:var(--text2); text-decoration:none; font-size:12px;
}
.pagination li.active span { background:#3b82f6; color:#fff; border-color:#3b82f6; }

/* ── Badge ── */
.version-badge { background:rgba(59,130,246,.12); color:#3b82f6; border:1px solid rgba(59,130,246,.2); font-size:11px; font-weight:600; padding:2px 8px; border-radius:4px; }

/* ── Modal Overlay ── */
.nas-modal-overlay {
    position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,.55); z-index:9998; display:none;
    align-items:center; justify-content:center;
}
.nas-modal-overlay.active { display:flex; }
.nas-modal {
    background:var(--card-bg); border-radius:10px;
    width:520px; max-width:96vw; max-height:92vh; overflow-y:auto;
    box-shadow:0 20px 60px rgba(0,0,0,.3); animation:slideInUp .2s ease;
}
@keyframes slideInUp { from { transform:translateY(30px); opacity:0; } to { transform:none; opacity:1; } }

/* ── Modal Header ── */
.nas-modal-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 22px; border-bottom:1px solid var(--border);
}
.nas-modal-header h3 { font-size:16px; font-weight:700; color:var(--text); margin:0; }
.nas-modal-close { background:none; border:none; color:var(--text2); font-size:18px; cursor:pointer; padding:4px 8px; border-radius:4px; }
.nas-modal-close:hover { background:var(--bg2); }

/* ── Modal Body / Form ── */
.nas-modal-body { padding:22px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
.form-row.full { grid-template-columns:1fr; }
.form-group { display:flex; flex-direction:column; gap:4px; }
.form-group label { font-size:12px; font-weight:600; color:var(--text2); text-transform:uppercase; letter-spacing:.5px; }
.form-group label .req { color:#dc2626; }
.form-input {
    width:100%; padding:9px 12px; border:1px solid var(--border);
    border-radius:6px; background:var(--bg2); color:var(--text);
    font-size:13px; outline:none; box-sizing:border-box; transition:border .15s;
}
.form-input:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
.form-input[type="password"] { letter-spacing:2px; }
.form-select { appearance:none; background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"); background-repeat:no-repeat; background-position:right 10px center; background-size:14px; padding-right:30px; }

/* ── Modal Footer ── */
.nas-modal-footer { display:flex; align-items:center; justify-content:flex-end; gap:10px; padding:16px 22px; border-top:1px solid var(--border); }
.btn-modal-cancel { background:var(--bg2); border:1px solid var(--border); color:var(--text); padding:8px 20px; border-radius:6px; font-size:13px; cursor:pointer; }
.btn-modal-save { background:linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; border:none; padding:9px 24px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; }
.btn-modal-save:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(37,99,235,.4); }
.btn-modal-save:disabled { opacity:.6; cursor:not-allowed; transform:none; }

/* ── Test Status ── */
.test-result { margin:10px 22px 0; padding:10px 14px; border-radius:6px; font-size:13px; display:none; }
.test-result.ok  { background:#dcfce7; border:1px solid #86efac; color:#16a34a; }
.test-result.err { background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; }
.test-result.loading { background:var(--bg2); border:1px solid var(--border); color:var(--text2); }

/* ── Empty ── */
.nas-empty { text-align:center; padding:50px 0; color:var(--text2); }
.nas-empty i { font-size:48px; opacity:.25; display:block; margin-bottom:12px; }
</style>

<?php
// Flash messages
$flashSuccess = $_SESSION['success'] ?? null; unset($_SESSION['success']);
$flashError   = $_SESSION['error']   ?? null; unset($_SESSION['error']);
?>

<div class="nas-header fade-in">
    <div class="nas-title">
        <i class="fa-solid fa-server" style="font-size:22px;color:#3b82f6;"></i>
        <div>
            <h1>Mikrotik Server <span>All Mikrotik Servers</span></h1>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:14px;">
        <div class="nas-breadcrumb"><a href="#">System</a> <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i> Server</div>
        <button class="btn-add-server" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Server
        </button>
    </div>
</div>

<?php if ($flashSuccess): ?>
<div class="alert" style="background:#dcfce7;border:1px solid #86efac;color:#16a34a;padding:10px 16px;border-radius:6px;margin-bottom:14px;font-size:13px;display:flex;align-items:center;gap:8px;" id="flashMsg">
    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flashSuccess) ?>
    <button onclick="document.getElementById('flashMsg').remove()" style="background:none;border:none;cursor:pointer;margin-left:auto;font-size:14px;color:#16a34a;">×</button>
</div>
<?php endif; ?>
<?php if ($flashError): ?>
<div class="alert" style="background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;padding:10px 16px;border-radius:6px;margin-bottom:14px;font-size:13px;display:flex;align-items:center;gap:8px;" id="flashMsg">
    <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($flashError) ?>
    <button onclick="document.getElementById('flashMsg').remove()" style="background:none;border:none;cursor:pointer;margin-left:auto;font-size:14px;color:#dc2626;">×</button>
</div>
<?php endif; ?>

<div class="nas-card fade-in fade-in-delay-1">
    <div class="table-controls">
        <label>SHOW
            <select id="perPage" onchange="filterTable()">
                <option>10</option><option>25</option><option>50</option>
            </select>
            ENTRIES
        </label>
        <label>SEARCH: <input type="text" class="table-search" id="nasSearch" oninput="filterTable()" placeholder="Search..."></label>
    </div>

    <table class="nas-table" id="nasTable">
        <thead>
            <tr>
                <th style="width:60px;">Serial</th>
                <th>ServerName</th>
                <th>Server IP</th>
                <th>API Port</th>
                <th>Username</th>
                <th>Version</th>
                <th>Timeout</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($nasDevices)): ?>
            <tr>
                <td colspan="9">
                    <div class="nas-empty">
                        <i class="fa-solid fa-server"></i>
                        No MikroTik servers configured.<br>
                        <small>Click <b>+ Server</b> to add your first server.</small>
                    </div>
                </td>
            </tr>
            <?php else: ?>
            <?php $i = 1; foreach ($nasDevices as $n): ?>
            <tr id="row-<?= $n['id'] ?>">
                <td><?= $i++ ?></td>
                <td>
                    <div class="server-name"><?= htmlspecialchars($n['name']) ?></div>
                    <?php
                        $cs = $n['connection_status'] ?? null;
                        if ($cs === null)   { $cls = 'status-unknown';       $label = 'Not Tested'; }
                        elseif ($cs)        { $cls = 'status-connected';     $label = 'Mikrotik Connected'; }
                        else                { $cls = 'status-disconnected';  $label = 'Disconnected'; }
                    ?>
                    <span class="server-status-tag <?= $cls ?>" id="statusTag-<?= $n['id'] ?>">
                        <i class="fa-solid fa-circle" style="font-size:7px;"></i> <?= $label ?>
                    </span>
                </td>
                <td style="font-family:monospace;font-weight:600;"><?= htmlspecialchars($n['ip_address']) ?></td>
                <td><code style="font-size:12px;"><?= (int)($n['api_port'] ?? 8728) ?></code></td>
                <td><?= htmlspecialchars($n['username'] ?? 'admin') ?></td>
                <td><span class="version-badge"><?= htmlspecialchars($n['mikrotik_version'] ?? 'v2') ?></span></td>
                <td><?= (int)($n['timeout'] ?? 10) ?> sec.</td>
                <td>
                    <label class="toggle-switch" title="<?= $n['is_active'] ? 'Active — click to disable' : 'Inactive — click to enable' ?>">
                        <input type="checkbox" id="toggle-<?= $n['id'] ?>"
                               <?= $n['is_active'] ? 'checked' : '' ?>
                               onchange="toggleServer(<?= $n['id'] ?>, this)">
                        <span class="toggle-slider"></span>
                    </label>
                </td>
                <td>
                    <div class="nas-action-wrap">
                        <button class="nas-btn refresh" title="Test Connection" onclick="testConnection(<?= $n['id'] ?>, this)">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                        <button class="nas-btn edit" title="Edit Server" onclick="openEditModal(<?= $n['id'] ?>)">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="nas-btn reconnect" title="Test Connection Details" onclick="openTestModal(<?= $n['id'] ?>, '<?= htmlspecialchars($n['name']) ?>')">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                        <a href="<?= base_url('network/mikrotik-radius/'.$n['id']) ?>" class="nas-btn" style="color:#8b5cf6;display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;background:rgba(139,92,246,0.1);border-radius:6px;text-decoration:none;" title="RADIUS Config">
                            <i class="fa-solid fa-satellite-dish"></i>
                        </a>
                        <button class="nas-btn delete" title="Delete Server" onclick="deleteServer(<?= $n['id'] ?>, '<?= htmlspecialchars(addslashes($n['name'])) ?>')">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrap">
        <span id="showingText">Showing <?= count($nasDevices) ?> of <?= count($nasDevices) ?> entries</span>
        <ul class="pagination">
            <li class="disabled"><span>Previous</span></li>
            <li class="active"><span>1</span></li>
            <li class="disabled"><span>Next</span></li>
        </ul>
    </div>
</div>

<!-- ══ ADD SERVER MODAL ══ -->
<div class="nas-modal-overlay" id="addModal">
    <div class="nas-modal">
        <div class="nas-modal-header">
            <h3><i class="fa-solid fa-server" style="color:#3b82f6;margin-right:8px;"></i>Add New Server</h3>
            <button class="nas-modal-close" onclick="closeModal('addModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="addForm" method="POST" action="<?= base_url('network/nas/store') ?>">
            <div class="nas-modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>SERVER NAME <span class="req">*</span></label>
                        <input type="text" name="name" class="form-input" placeholder="e.g. RM-1009" required>
                    </div>
                    <div class="form-group">
                        <label>SERVER IP <span class="req">*</span></label>
                        <input type="text" name="ip_address" class="form-input" placeholder="e.g. 103.109.96.166" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>USER NAME <span class="req">*</span></label>
                        <input type="text" name="username" class="form-input" placeholder="admin" value="admin" required>
                    </div>
                    <div class="form-group">
                        <label>PASSWORD <span class="req">*</span></label>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>API PORT <span class="req">*</span></label>
                        <input type="number" name="api_port" class="form-input" placeholder="8728" value="8728" required>
                    </div>
                    <div class="form-group">
                        <label>MIKROTIK VERSION</label>
                        <select name="mikrotik_version" class="form-input form-select">
                            <option value="v2">Version greater 6.43 or older 7.0</option>
                            <option value="v3">Version 7.1+</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>API REQUEST TIMEOUT</label>
                        <input type="number" name="timeout" class="form-input" placeholder="10" value="10">
                    </div>
                    <div class="form-group">
                        <label>BRANCH</label>
                        <select name="branch_id" class="form-input form-select">
                            <option value="">— Select Branch —</option>
                            <?php foreach ($branches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label>SHARED SECRET (optional)</label>
                        <input type="text" name="secret" class="form-input" placeholder="RADIUS shared secret (optional)">
                    </div>
                </div>
            </div>
            <div id="addTestResult" class="test-result"></div>
            <div class="nas-modal-footer">
                <button type="button" id="addTestBtn" class="btn-modal-cancel" onclick="testBeforeSave('addForm')">
                    <i class="fa-solid fa-plug-circle-check"></i> Test Connection
                </button>
                <button type="button" class="btn-modal-cancel" onclick="closeModal('addModal')">Close</button>
                <button type="submit" class="btn-modal-save">
                    <i class="fa-solid fa-floppy-disk"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══ EDIT SERVER MODAL ══ -->
<div class="nas-modal-overlay" id="editModal">
    <div class="nas-modal">
        <div class="nas-modal-header">
            <h3><i class="fa-solid fa-pen-to-square" style="color:#16a34a;margin-right:8px;"></i>Edit Server</h3>
            <button class="nas-modal-close" onclick="closeModal('editModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editForm" method="POST" action="">
            <input type="hidden" name="_nasId" id="editNasId">
            <div class="nas-modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>SERVER NAME <span class="req">*</span></label>
                        <input type="text" name="name" id="editName" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>SERVER IP <span class="req">*</span></label>
                        <input type="text" name="ip_address" id="editIp" class="form-input" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>USER NAME <span class="req">*</span></label>
                        <input type="text" name="username" id="editUser" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>PASSWORD <span class="req">*</span></label>
                        <input type="password" name="password" id="editPass" class="form-input" placeholder="Leave blank to keep current">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>API PORT <span class="req">*</span></label>
                        <input type="number" name="api_port" id="editPort" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>MIKROTIK VERSION</label>
                        <select name="mikrotik_version" id="editVersion" class="form-input form-select">
                            <option value="v2">Version greater 6.43 or older 7.0</option>
                            <option value="v3">Version 7.1+</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>API REQUEST TIMEOUT</label>
                        <input type="number" name="timeout" id="editTimeout" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>BRANCH</label>
                        <select name="branch_id" id="editBranch" class="form-input form-select">
                            <option value="">— Select Branch —</option>
                            <?php foreach ($branches as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label>SHARED SECRET</label>
                        <input type="text" name="secret" id="editSecret" class="form-input">
                    </div>
                </div>
            </div>
            <div id="editTestResult" class="test-result"></div>
            <div class="nas-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="testEditConnection()">
                    <i class="fa-solid fa-plug-circle-check"></i> Test
                </button>
                <button type="button" class="btn-modal-cancel" onclick="closeModal('editModal')">Cancel</button>
                <button type="button" class="btn-modal-save" onclick="submitEdit()">
                    <i class="fa-solid fa-floppy-disk"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══ TEST DETAIL MODAL ══ -->
<div class="nas-modal-overlay" id="testModal">
    <div class="nas-modal" style="max-width:440px;">
        <div class="nas-modal-header">
            <h3 id="testModalTitle"><i class="fa-solid fa-plug" style="color:#8b5cf6;margin-right:8px;"></i>Connection Test</h3>
            <button class="nas-modal-close" onclick="closeModal('testModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="nas-modal-body" id="testModalBody" style="min-height:120px;">
            <div style="text-align:center;padding:30px;color:var(--text2);">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size:28px;margin-bottom:10px;display:block;"></i>
                Testing connection...
            </div>
        </div>
        <div class="nas-modal-footer">
            <button class="btn-modal-cancel" onclick="closeModal('testModal')">Close</button>
        </div>
    </div>
</div>

<script>
const BASE = '<?= base_url('') ?>';

/* ── Utilities ── */
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

/* Close modals by clicking overlay */
document.querySelectorAll('.nas-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('active'); });
});

/* ── Open Add Modal ── */
function openAddModal() {
    document.getElementById('addForm').reset();
    document.getElementById('addTestResult').style.display = 'none';
    openModal('addModal');
}

/* ── Open Edit Modal ── */
async function openEditModal(id) {
    openModal('editModal');
    document.getElementById('editTestResult').style.display = 'none';
    try {
        const res  = await fetch(`${BASE}network/nas/get/${id}`);
        const json = await res.json();
        if (!json.success) { alert('Failed to load server data.'); closeModal('editModal'); return; }
        const d = json.data;
        document.getElementById('editNasId').value   = d.id;
        document.getElementById('editName').value    = d.name;
        document.getElementById('editIp').value      = d.ip_address;
        document.getElementById('editUser').value    = d.username;
        document.getElementById('editPass').value    = '';
        document.getElementById('editPort').value    = d.api_port;
        document.getElementById('editTimeout').value = d.timeout || 10;
        document.getElementById('editSecret').value  = d.secret || '';
        const ver = document.getElementById('editVersion');
        ver.value = d.mikrotik_version || 'v2';
        const br  = document.getElementById('editBranch');
        if (br) br.value = d.branch_id || '';
        document.getElementById('editForm').action = `${BASE}network/nas/update/${d.id}`;
    } catch (e) {
        alert('Error loading server data.'); closeModal('editModal');
    }
}

/* ── Submit Edit via AJAX ── */
async function submitEdit() {
    const form    = document.getElementById('editForm');
    const nasId   = document.getElementById('editNasId').value;
    const formData = new FormData(form);
    const btn     = form.querySelector('.btn-modal-save');
    btn.disabled  = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    try {
        const res  = await fetch(`${BASE}network/nas/update/${nasId}`, {
            method: 'POST', body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        showResult('editTestResult', true, 'Server updated successfully!');
        setTimeout(() => { closeModal('editModal'); location.reload(); }, 900);
    } catch (e) {
        showResult('editTestResult', false, 'Update failed. Please try again.');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Update';
    }
}

/* ── Test Connection (Table Row Button) ── */
async function testConnection(id, btn) {
    const icon = btn.querySelector('i');
    icon.className = 'fa-solid fa-circle-notch fa-spin';
    btn.disabled = true;

    const tag = document.getElementById(`statusTag-${id}`);

    try {
        const res  = await fetch(`${BASE}network/nas/test/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
        });
        const json = await res.json();

        if (json.success) {
            tag.className = 'server-status-tag status-connected';
            tag.innerHTML = '<i class="fa-solid fa-circle" style="font-size:7px;"></i> Mikrotik Connected';
            showToast('Connected to ' + (json.info?.['board-name'] || 'MikroTik'), 'success');
        } else {
            tag.className = 'server-status-tag status-disconnected';
            tag.innerHTML = '<i class="fa-solid fa-circle" style="font-size:7px;"></i> Disconnected';
            showToast('Connection failed', 'error');
        }
    } catch (e) {
        tag.className = 'server-status-tag status-disconnected';
        tag.innerHTML = '<i class="fa-solid fa-circle" style="font-size:7px;"></i> Error';
        showToast('Connection error', 'error');
    }

    icon.className = 'fa-solid fa-arrows-rotate';
    btn.disabled = false;
}

/* ── Open Test Detail Modal ── */
async function openTestModal(id, name) {
    document.getElementById('testModalTitle').innerHTML = `<i class="fa-solid fa-plug" style="color:#8b5cf6;margin-right:8px;"></i>Test: ${name}`;
    document.getElementById('testModalBody').innerHTML  = `
        <div style="text-align:center;padding:30px;color:var(--text2);">
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size:28px;margin-bottom:10px;display:block;"></i>
            Testing connection...
        </div>`;
    openModal('testModal');

    try {
        const res  = await fetch(`${BASE}network/nas/test/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (json.success && json.info && Object.keys(json.info).length) {
            const info   = json.info;
            const rows   = Object.entries(info).map(([k,v]) =>
                `<tr><td style="padding:6px 10px;font-weight:600;color:var(--text2);font-size:12px;text-transform:uppercase;">${k}</td>
                      <td style="padding:6px 10px;color:var(--text);font-family:monospace;">${v}</td></tr>`
            ).join('');
            document.getElementById('testModalBody').innerHTML = `
                <div style="background:#dcfce7;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:13px;font-weight:600;">
                    <i class="fa-solid fa-circle-check"></i> Connection Successful
                </div>
                <table style="width:100%;border-collapse:collapse;">${rows}</table>`;
        } else if (json.success) {
            document.getElementById('testModalBody').innerHTML = `
                <div style="background:#dcfce7;border:1px solid #86efac;color:#16a34a;padding:10px 14px;border-radius:6px;font-size:13px;font-weight:600;">
                    <i class="fa-solid fa-circle-check"></i> Connected successfully (no system info returned)
                </div>`;
        } else {
            document.getElementById('testModalBody').innerHTML = `
                <div style="background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;padding:10px 14px;border-radius:6px;font-size:13px;font-weight:600;">
                    <i class="fa-solid fa-circle-xmark"></i> Connection failed. Check IP, port, credentials and ensure the MikroTik API is enabled.
                </div>`;
        }
    } catch (e) {
        document.getElementById('testModalBody').innerHTML = `<div style="color:#dc2626;padding:20px;">Error: ${e.message}</div>`;
    }
}

/* ── Toggle Active ── */
async function toggleServer(id, checkbox) {
    try {
        const res  = await fetch(`${BASE}network/nas/toggle/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (!json.success) { checkbox.checked = !checkbox.checked; showToast('Failed to update status', 'error'); }
        else { showToast(json.is_active ? 'Server enabled' : 'Server disabled', json.is_active ? 'success' : 'info'); }
    } catch (e) {
        checkbox.checked = !checkbox.checked;
        showToast('Network error', 'error');
    }
}

/* ── Delete ── */
async function deleteServer(id, name) {
    if (!confirm(`Are you sure you want to delete server "${name}"?\n\nThis action cannot be undone.`)) return;
    try {
        const res  = await fetch(`${BASE}network/nas/delete/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (json.success) {
            const row = document.getElementById(`row-${id}`);
            if (row) { row.style.opacity = '0'; row.style.transition = 'opacity .3s'; setTimeout(() => row.remove(), 320); }
            showToast('Server deleted', 'success');
        } else {
            showToast('Delete failed', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    }
}

/* ── Test Before Add Save ── */
async function testBeforeSave(formId) {
    const resultDiv = document.getElementById('addTestResult');
    showResult('addTestResult', null, '<i class="fa-solid fa-circle-notch fa-spin"></i> Testing connection...', true);

    const form = document.getElementById(formId);
    const ip   = form.querySelector('[name="ip_address"]').value;
    const user = form.querySelector('[name="username"]').value;
    const pass = form.querySelector('[name="password"]').value;
    const port = form.querySelector('[name="api_port"]').value;

    if (!ip || !user) { showResult('addTestResult', false, 'Please fill in IP and credentials first.'); return; }

    // We test by creating a temp server entry to use existing endpoint
    showResult('addTestResult', null, 'Testing requires saving first. Proceed with Save and then use the Refresh button on the row.', true);
}

/* ── Test Edit Connection ── */
async function testEditConnection() {
    const id = document.getElementById('editNasId').value;
    if (!id) return;
    showResult('editTestResult', null, '<i class="fa-solid fa-circle-notch fa-spin"></i> Testing...', true);
    try {
        const res  = await fetch(`${BASE}network/nas/test/${id}`, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'} });
        const json = await res.json();
        if (json.success) showResult('editTestResult', true, '<i class="fa-solid fa-circle-check"></i> Connected! Board: ' + (json.info?.['board-name'] || 'MikroTik') + ', RouterOS: ' + (json.info?.['version'] || ''));
        else showResult('editTestResult', false, '<i class="fa-solid fa-circle-xmark"></i> Connection failed. Check IP/Port/Credentials.');
    } catch (e) {
        showResult('editTestResult', false, 'Network error: ' + e.message);
    }
}

/* ── Show Result Banner ── */
function showResult(divId, success, msg, loading = false) {
    const d = document.getElementById(divId);
    d.style.display = 'block';
    d.className = 'test-result ' + (loading ? 'loading' : (success ? 'ok' : 'err'));
    d.innerHTML = msg;
}

/* ── Toast ── */
function showToast(msg, type = 'success') {
    const colors = { success:'#16a34a', error:'#dc2626', info:'#3b82f6' };
    const icons  = { success:'fa-circle-check', error:'fa-circle-xmark', info:'fa-circle-info' };
    const toast  = document.createElement('div');
    toast.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:#fff;color:${colors[type]};
        border:1px solid ${colors[type]}33;padding:12px 18px;border-radius:8px;font-size:13px;font-weight:600;
        box-shadow:0 4px 20px rgba(0,0,0,.15);display:flex;align-items:center;gap:8px;transition:opacity .3s;`;
    toast.innerHTML = `<i class="fa-solid ${icons[type]}"></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}

/* ── Client-side Search ── */
function filterTable() {
    const q    = document.getElementById('nasSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#nasTable tbody tr');
    let   show = 0;
    rows.forEach(row => {
        const match = row.textContent.toLowerCase().includes(q);
        row.style.display = match ? '' : 'none';
        if (match) show++;
    });
    document.getElementById('showingText').textContent = `Showing ${show} entries`;
}
</script>
