<?php // views/gpon/olt-onus.php
$selectedOltId = (int)($_GET['olt_id'] ?? 0);
$searchVal     = htmlspecialchars($_GET['search'] ?? '');
?>
<div class="page-header fade-in">
    <div><h1 class="page-title">OLT ONUs List</h1><div class="page-breadcrumb"><i class="fa-solid fa-diagram-project" style="color:var(--blue)"></i> GPON / ONUs</div></div>
    <div style="display:flex;gap:8px;">
        <button class="btn btn-ghost" onclick="exportTable()"><i class="fa-solid fa-download"></i> Export</button>
    </div>
</div>

<!-- Filter bar -->
<div class="card fade-in" style="padding:14px 16px;margin-bottom:12px;">
    <form method="GET" action="<?= base_url('gpon/olts/onus') ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:6px;">
            <label style="font-size:13px;color:var(--text2);white-space:nowrap;">Filter OLT:</label>
            <select name="olt_id" class="form-input" style="width:200px;padding:6px 10px;" onchange="this.form.submit()">
                <option value="">All OLTs</option>
                <?php foreach($olts as $o): ?>
                <option value="<?= $o['id'] ?>" <?= $selectedOltId == $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            <label style="font-size:13px;color:var(--text2);">Search:</label>
            <input type="text" name="search" class="form-input" style="width:200px;padding:6px 10px;" placeholder="Serial / Name / MAC..." value="<?= $searchVal ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        <?php if($selectedOltId || $searchVal): ?>
        <a href="<?= base_url('gpon/olts/onus') ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
        <?php endif; ?>
        <?php if($selectedOltId): ?>
        <button type="button" class="btn btn-green btn-sm" onclick="syncOnusFromFilter(<?= $selectedOltId ?>)">
            <i class="fa-solid fa-rotate"></i> Sync from OLT
        </button>
        <?php endif; ?>
    </form>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table" id="onusTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Client Code</th>
                <th>UserName</th>
                <th>Client Name</th>
                <th>MacAddress</th>
                <th>IpAddress</th>
                <th>OLT Name</th>
                <th>Optical Power (dBm)</th>
                <th>OLT Port</th>
                <th>Onu Status</th>
                <th>Description</th>
                <th>Distance (m)</th>
                <th>Last Deregister Time</th>
                <th>Deregister Reason</th>
                <th>Last Synced</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($onus)): ?>
            <tr><td colspan="16" style="text-align:center;padding:40px;color:var(--text2);">
                <i class="fa-solid fa-router" style="font-size:32px;display:block;margin-bottom:12px;opacity:.4;"></i>
                No ONU devices found<?= $selectedOltId ? ' for this OLT' : '' ?>
            </td></tr>
            <?php else: $i=1; foreach($onus as $o): ?>
            <tr>
                <td style="font-weight:600;"><?= $i++ ?></td>
                <td><span style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($o['customer_code']??'—') ?></span></td>
                <td><span style="font-family:monospace;"><?= htmlspecialchars($o['customer_username']??'—') ?></span></td>
                <td>
                    <?php if($o['customer_id']): ?>
                    <a href="<?= base_url("customers/view/{$o['customer_id']}") ?>" style="color:var(--blue);text-decoration:none;font-weight:500;"><?= htmlspecialchars($o['customer_name']??'—') ?></a>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td><span style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($o['mac_address']??'—') ?></span></td>
                <td><span style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($o['ip_address']??'—') ?></span></td>
                <td><span style="font-weight:500;"><?= htmlspecialchars($o['olt_name']??'—') ?></span></td>
                <td>
                    <?php
                    $signal = $o['signal_level'];
                    $signalClass = 'badge-gray'; $signalText = '—';
                    if($signal !== null) {
                        $signalText = $signal.' dBm';
                        if($signal >= -25) $signalClass = 'badge-green';
                        elseif($signal >= -27) $signalClass = 'badge-yellow';
                        else $signalClass = 'badge-red';
                    }
                    ?>
                    <span class="badge <?= $signalClass ?>"><?= $signalText ?></span>
                </td>
                <td><span style="font-family:monospace;"><?= $o['olt_port']??'—' ?></span></td>
                <td>
                    <?php
                    $status = $o['status'] ?? 'stock';
                    $sc=['installed'=>'badge-green','active'=>'badge-green','online'=>'badge-green','offline'=>'badge-red','stock'=>'badge-yellow','faulty'=>'badge-red','returned'=>'badge-gray'];
                    echo '<span class="badge '.($sc[$status]??'badge-gray').'">'.ucfirst($status).'</span>';
                    ?>
                </td>
                <td style="font-size:11px;color:var(--text2);max-width:120px;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($o['description']??'') ?>">
                    <?= htmlspecialchars($o['description']??'—') ?>
                </td>
                <td><span style="font-family:monospace;font-size:11px;"><?= $o['distance_from_olt'] !== null ? number_format($o['distance_from_olt']).' m' : '—' ?></span></td>
                <td style="font-size:11px;color:var(--text2);"><?= $o['deregister_time'] ? date('d-M H:i', strtotime($o['deregister_time'])) : '—' ?></td>
                <td style="font-size:11px;color:var(--red);"><?= htmlspecialchars($o['deregister_reason']??'—') ?></td>
                <td style="font-size:11px;color:var(--text2);"><?= $o['last_synced_at'] ? date('d-M H:i', strtotime($o['last_synced_at'])) : '—' ?></td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <?php if($o['customer_id']): ?>
                        <a href="<?= base_url("customers/view/{$o['customer_id']}") ?>" class="btn btn-ghost btn-sm" title="View Customer"><i class="fa-solid fa-user"></i></a>
                        <?php endif; ?>
                        <button class="btn btn-ghost btn-sm" onclick='openEditOnuModal(<?= htmlspecialchars(json_encode($o)) ?>)' title="Edit"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-danger btn-sm" onclick="deleteOnu(<?= $o['id'] ?>, '<?= addslashes(htmlspecialchars($o['serial_number'])) ?>')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        <button class="btn btn-ghost btn-sm" onclick="viewSnapshot(<?= htmlspecialchars($o['id']) ?>)" title="Snapshot"><i class="fa-solid fa-camera"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Edit ONU Modal -->
<div class="modal-overlay" id="editOnuModal">
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--blue);margin-right:8px;"></i>Edit ONU</div>
            <button class="icon-btn" onclick="document.getElementById('editOnuModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <input type="hidden" id="eo_id">
            <div style="grid-column:1/-1;"><label class="form-label">Serial Number</label><input type="text" id="eo_serial" class="form-input" style="font-family:monospace;"></div>
            <div><label class="form-label">Model</label><input type="text" id="eo_model" class="form-input"></div>
            <div><label class="form-label">Brand</label><input type="text" id="eo_brand" class="form-input"></div>
            <div><label class="form-label">MAC Address</label><input type="text" id="eo_mac" class="form-input" style="font-family:monospace;"></div>
            <div><label class="form-label">IP Address</label><input type="text" id="eo_ip" class="form-input" style="font-family:monospace;"></div>
            <div><label class="form-label">OLT Port</label><input type="text" id="eo_olt_port" class="form-input" style="font-family:monospace;"></div>
            <div><label class="form-label">Signal Level (dBm)</label><input type="number" id="eo_signal" class="form-input" step="0.01"></div>
            <div><label class="form-label">Status</label>
                <select id="eo_status" class="form-input">
                    <option value="stock">Stock</option>
                    <option value="installed">Installed</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="faulty">Faulty</option>
                    <option value="returned">Returned</option>
                </select>
            </div>
            <div style="grid-column:1/-1;"><label class="form-label">Description</label><input type="text" id="eo_description" class="form-input"></div>
            <div style="grid-column:1/-1;"><label class="form-label">Customer ID <span style="color:var(--text2);font-weight:400;">(leave blank to unlink)</span></label><input type="number" id="eo_customer_id" class="form-input" placeholder="Customer ID"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('editOnuModal').classList.remove('open')">Cancel</button>
            <button class="btn btn-primary" onclick="saveOnuEdit()"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
        </div>
    </div>
</div>

<!-- Snapshot Modal -->
<div class="modal-overlay" id="snapshotModal">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-camera" style="color:var(--blue);margin-right:8px;"></i>Previous Snapshot</div>
            <button class="icon-btn" onclick="document.getElementById('snapshotModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <pre id="snapshotContent" style="background:var(--bg2);padding:16px;border-radius:8px;font-size:12px;max-height:400px;overflow:auto;"></pre>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('snapshotModal').classList.remove('open')">Close</button>
        </div>
    </div>
</div>

<!-- Sync result panel -->
<div id="syncResultPanel" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;min-width:300px;">
    <div class="card" style="padding:16px;border-left:4px solid var(--green);box-shadow:0 4px 24px rgba(0,0,0,.18);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-weight:700;font-size:14px;" id="syncPanelTitle">Syncing...</span>
            <button class="icon-btn" onclick="document.getElementById('syncResultPanel').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="syncPanelBody" style="font-size:13px;"></div>
    </div>
</div>

<script>
const onusData = <?= json_encode(array_column($onus ?? [], 'previous_snapshot', 'id')) ?>;

function viewSnapshot(id) {
    const content = onusData[id] || 'No snapshot available';
    let display = content;
    try { display = JSON.stringify(JSON.parse(content), null, 2); } catch(e) {}
    document.getElementById('snapshotContent').textContent = display;
    document.getElementById('snapshotModal').classList.add('open');
}

function openEditOnuModal(o) {
    document.getElementById('eo_id').value          = o.id;
    document.getElementById('eo_serial').value      = o.serial_number || '';
    document.getElementById('eo_model').value       = o.model || '';
    document.getElementById('eo_brand').value       = o.brand || '';
    document.getElementById('eo_mac').value         = o.mac_address || '';
    document.getElementById('eo_ip').value          = o.ip_address || '';
    document.getElementById('eo_olt_port').value    = o.olt_port || '';
    document.getElementById('eo_signal').value      = o.signal_level || '';
    document.getElementById('eo_status').value      = o.status || 'stock';
    document.getElementById('eo_description').value = o.description || '';
    document.getElementById('eo_customer_id').value = o.customer_id || '';
    document.getElementById('editOnuModal').classList.add('open');
}

async function saveOnuEdit() {
    const id = document.getElementById('eo_id').value;
    const body = new FormData();
    body.append('serial_number', document.getElementById('eo_serial').value);
    body.append('model',         document.getElementById('eo_model').value);
    body.append('brand',         document.getElementById('eo_brand').value);
    body.append('mac_address',   document.getElementById('eo_mac').value);
    body.append('ip_address',    document.getElementById('eo_ip').value);
    body.append('olt_port',      document.getElementById('eo_olt_port').value);
    body.append('signal_level',  document.getElementById('eo_signal').value);
    body.append('status',        document.getElementById('eo_status').value);
    body.append('description',   document.getElementById('eo_description').value);
    body.append('customer_id',   document.getElementById('eo_customer_id').value);

    try {
        const res  = await fetch(`<?= base_url('gpon/api/onus/update') ?>/${id}`, { method: 'POST', body });
        const data = await res.json();
        if (data.success) {
            document.getElementById('editOnuModal').classList.remove('open');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Update failed'));
        }
    } catch(e) {
        alert('Request failed: ' + e.message);
    }
}

async function deleteOnu(id, serial) {
    if (!confirm(`Delete ONU "${serial}"?\n\nThis action cannot be undone.`)) return;
    try {
        const res  = await fetch(`<?= base_url('gpon/api/onus/delete') ?>/${id}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Delete failed'));
        }
    } catch(e) {
        alert('Request failed: ' + e.message);
    }
}

async function syncOnusFromFilter(oltId) {
    const panel = document.getElementById('syncResultPanel');
    document.getElementById('syncPanelTitle').textContent = 'Syncing ONUs...';
    document.getElementById('syncPanelBody').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Fetching ONU list via SNMP...';
    panel.style.display = 'block';

    try {
        const res  = await fetch(`<?= base_url('gpon/api/snmp/sync') ?>/${oltId}`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            document.getElementById('syncPanelTitle').textContent = '✓ Sync Complete';
            document.getElementById('syncPanelBody').innerHTML = `
                <div style="color:var(--green);font-weight:600;">Synced ${data.synced} ONUs</div>
                <div style="color:var(--text2);margin-top:4px;">🆕 New: <strong>${data.new_count}</strong> &nbsp; 🔄 Updated: <strong>${data.updated_count}</strong></div>
            `;
            setTimeout(() => { panel.style.display = 'none'; location.reload(); }, 2500);
        } else {
            document.getElementById('syncPanelTitle').textContent = '✗ Sync Failed';
            document.getElementById('syncPanelBody').innerHTML = `<div style="color:var(--red);">${data.error || 'Unknown error'}</div>`;
        }
    } catch(e) {
        document.getElementById('syncPanelTitle').textContent = '✗ Error';
        document.getElementById('syncPanelBody').innerHTML = `<div style="color:var(--red);">${e.message}</div>`;
    }
}

function exportTable() {
    const table = document.getElementById('onusTable');
    const rows  = table.querySelectorAll('tr');
    let csv = [];
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = [];
        cols.forEach(col => { rowData.push('"' + col.innerText.replace(/"/g, '""') + '"'); });
        csv.push(rowData.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url; a.download = 'olt-onus-<?= date('Y-m-d') ?>.csv'; a.click();
    URL.revokeObjectURL(url);
}
</script>

<style>
.data-table th { white-space:nowrap; }
.data-table td { font-size:12px; }
.data-table th:nth-child(11), .data-table td:nth-child(11) { max-width:120px; }
</style>
