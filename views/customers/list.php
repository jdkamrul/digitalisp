<?php
// views/customers/list.php
?>
<style>
.filter-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:20px; }
.status-dot { display:inline-block; width:7px; height:7px; border-radius:50%; margin-right:5px; }
.client-avatar { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:12px; flex-shrink:0; }
.action-btn { width:28px; height:28px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition:all 0.2s; }
.action-btn:hover { transform:scale(1.1); }
.btn-suspend { background:rgba(239,68,68,0.1); color:var(--text2); }
.btn-suspend:hover { background:var(--red); color:#fff; }
.btn-reconnect { background:rgba(34,197,94,0.1); color:var(--text2); }
.btn-reconnect:hover { background:var(--green); color:#fff; }
</style>

<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Client List</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-users" style="color:var(--blue)"></i> Client <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> Client List</div>
    </div>
    <div style="display:flex;gap:10px;">
        <button class="btn btn-ghost" onclick="openModal('importModal')"><i class="fa-solid fa-upload"></i> Import</button>
        <div class="dropdown">
            <button class="btn btn-ghost" onclick="toggleDropdown('exportMenu')"><i class="fa-solid fa-download"></i> Export <i class="fa-solid fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></button>
            <div class="dropdown-menu" id="exportMenu">
                <a href="javascript:void(0)" onclick="exportCSV()" class="dropdown-item"><i class="fa-solid fa-file-csv"></i> Excel / CSV</a>
                <a href="javascript:void(0)" onclick="exportPDF()" class="dropdown-item"><i class="fa-solid fa-file-pdf"></i> PDF Report</a>
            </div>
        </div>
        <a href="<?= base_url('customers/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Customer</a>
    </div>
</div>

<!-- Filters -->
<form method="GET" class="filter-bar card fade-in" style="padding:14px 16px;">
    <div style="flex:1;min-width:200px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search by name, phone, ID, MAC..." class="form-input">
    </div>
    <select name="status" class="form-input" style="width:140px;">
        <option value="">All Status</option>
        <?php foreach(['active','suspended','pending','cancelled','deleted'] as $s): ?>
        <option value="<?= $s ?>" <?= $status===$s?'selected':''; ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="package_id" class="form-input" style="width:180px;">
        <option value="">All Packages</option>
        <?php foreach($packages as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $package_id===$p['id']?'selected':''; ?>><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="zone_id" class="form-input" style="width:160px;">
        <option value="">All Zones</option>
        <?php foreach($zones as $z): ?>
        <option value="<?= $z['id'] ?>" <?= $zone_id===$z['id']?'selected':''; ?>><?= htmlspecialchars($z['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
    <a href="<?= base_url('customers') ?>" class="btn btn-ghost"><i class="fa-solid fa-xmark"></i> Clear</a>
</form>

<!-- Table -->
<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table" id="customerTable">
        <thead>
            <tr>
                <th style="width:40px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                <th>Client</th>
                <th>Contact</th>
                <th>Package</th>
                <th>Zone</th>
                <th>Expiry</th>
                <th>MAC</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
            <tr>
                <td colspan="9" style="text-align:center;padding:48px;color:var(--text2);">
                    <i class="fa-solid fa-users-slash" style="font-size:32px;display:block;margin-bottom:12px;"></i>
                    No customers found. <a href="<?= base_url('customers/create') ?>" style="color:var(--blue);">Add the first customer</a>
                </td>
            </tr>
            <?php else: foreach ($customers as $c): ?>
            <tr>
                <td><input type="checkbox" class="rowCheck" value="<?= $c['id'] ?>"></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="client-avatar" style="background:linear-gradient(135deg,var(--blue),var(--purple));">
                            <?= strtoupper(substr($c['full_name'],0,1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($c['full_name']) ?></div>
                            <div style="font-size:11px;color:var(--text2);font-family:monospace;"><?= htmlspecialchars($c['customer_code']) ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-size:13px;"><?= htmlspecialchars($c['phone']) ?></div>
                    <?php if ($c['pppoe_username']): ?>
                    <div style="font-size:11px;color:var(--text2);font-family:monospace;"><?= htmlspecialchars($c['pppoe_username']) ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($c['package_name']): ?>
                    <div style="font-size:13px;font-weight:500;"><?= htmlspecialchars($c['package_name']) ?></div>
                    <div style="font-size:11px;color:var(--blue);"><?= $c['speed_download'] ?>/<?= $c['speed_upload'] ?? $c['speed_download'] ?></div>
                    <?php else: ?>
                    <span style="color:var(--text2);">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;"><?= htmlspecialchars($c['zone_name'] ?? '—') ?></td>
                <td>
                    <?php if (!empty($c['expiration'])): ?>
                    <div style="font-size:12px;<?= strtotime($c['expiration']) < time() ? 'color:var(--red);font-weight:600;' : '' ?>">
                        <?= date('d M Y', strtotime($c['expiration'])) ?>
                    </div>
                    <?php elseif (!empty($c['mikrotik_profile'])): ?>
                    <span style="color:var(--text2);font-size:12px;">Auto</span>
                    <?php else: ?>
                    <span style="color:var(--text2);font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($c['mac_address'])): ?>
                    <code style="background:var(--bg3);padding:3px 6px;border-radius:4px;font-size:11px;"><?= htmlspecialchars($c['mac_address']) ?></code>
                    <?php else: ?>
                    <span style="color:var(--text2);font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $sc = ['active'=>'badge-green','suspended'=>'badge-red','pending'=>'badge-yellow','cancelled'=>'badge-gray','deleted'=>'badge-gray'];
                    $statusColors = ['active'=>'#22c55e','suspended'=>'#ef4444','pending'=>'#f59e0b','cancelled'=>'#6b7280','deleted'=>'#6b7280'];
                    echo '<span class="badge '.($sc[$c['status']]?:'badge-gray').'">';
                    echo '<span class="status-dot" style="background:'.($statusColors[$c['status']]??'#6b7280').'"></span>';
                    echo ucfirst($c['status']).'</span>';
                    ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        <a href="<?= base_url("customers/view/{$c['id']}") ?>" class="btn btn-ghost btn-sm" title="View"><i class="fa-solid fa-eye"></i></a>
                        <a href="<?= base_url("customers/edit/{$c['id']}") ?>" class="btn btn-ghost btn-sm" title="Edit"><i class="fa-solid fa-pen"></i></a>
                        <?php if ($c['status'] === 'active'): ?>
                        <button onclick="confirmSuspend(<?= $c['id'] ?>, '<?= htmlspecialchars($c['full_name']) ?>')" class="action-btn btn-suspend" title="Suspend"><i class="fa-solid fa-ban"></i></button>
                        <?php elseif ($c['status'] === 'suspended'): ?>
                        <button onclick="confirmReconnect(<?= $c['id'] ?>, '<?= htmlspecialchars($c['full_name']) ?>')" class="action-btn btn-reconnect" title="Reconnect"><i class="fa-solid fa-rotate"></i></button>
                        <?php endif; ?>
                        <?php if ($c['status'] !== 'deleted'): ?>
                        <button onclick="confirmDelete(<?= $c['id'] ?>, '<?= htmlspecialchars($c['full_name']) ?>')" class="action-btn" style="color:#dc2626;" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:between;align-items:center;margin-top:16px;gap:12px;flex-wrap:wrap;">
    <div style="font-size:13px;color:var(--text2);">
        Showing <?= min($offset+1, $total) ?>–<?= min($offset+$limit, $total) ?> of <?= number_format($total) ?>
    </div>
    <div style="display:flex;gap:6px;margin-left:auto;">
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
        <?php if ($i==1 || $i==$totalPages || abs($i-$page)<=2): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"
           style="width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;
                  <?= $i===$page ? 'background:var(--blue);color:#fff;' : 'background:var(--bg3);color:var(--text2);' ?>">
            <?= $i ?>
        </a>
        <?php elseif (abs($i-$page)==3): ?>
        <span style="color:var(--text2);display:inline-flex;align-items:center;padding:0 4px;">…</span>
        <?php endif; ?>
        <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<!-- Suspend Modal -->
<div class="modal-overlay" id="suspendModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-ban" style="color:var(--red);margin-right:8px;"></i>Suspend Customer</div>
            <button class="icon-btn" onclick="closeModal('suspendModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="suspendForm">
            <div class="modal-body">
                <p style="color:var(--text2);font-size:13px;margin-bottom:16px;">Are you sure you want to suspend <strong id="suspendName"></strong>?</p>
                <label class="form-label">Reason for suspension</label>
                <select name="reason" class="form-input">
                    <option>Non-payment</option>
                    <option>Customer request</option>
                    <option>Network abuse</option>
                    <option>Other</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('suspendModal')">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Suspend</button>
            </div>
        </form>
    </div>
</div>

<!-- Reconnect Modal -->
<div class="modal-overlay" id="reconnectModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-rotate" style="color:var(--green);margin-right:8px;"></i>Reconnect Customer</div>
            <button class="icon-btn" onclick="closeModal('reconnectModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="reconnectForm">
            <div class="modal-body">
                <p style="color:var(--text2);font-size:13px;">Reconnect <strong id="reconnectName"></strong>? Ensure payment is cleared before reconnecting.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('reconnectModal')">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-rotate"></i> Reconnect</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div class="modal-overlay" id="importModal">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-upload" style="color:var(--blue);margin-right:8px;"></i>Import Customers</div>
            <button class="icon-btn" onclick="closeModal('importModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('customers/import') ?>" enctype="multipart/form-data">
            <div class="modal-body">
                <!-- Demo download strip -->
                <div style="background:linear-gradient(135deg,rgba(59,130,246,0.08),rgba(139,92,246,0.08));border:1px solid rgba(59,130,246,0.2);border-radius:10px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
                    <div>
                        <div style="font-size:12px;font-weight:600;color:var(--blue);margin-bottom:2px;"><i class="fa-solid fa-file-excel" style="margin-right:5px;"></i>Download Template</div>
                        <div style="font-size:11px;color:var(--text2);">10 demo rows · 15 columns · field guide included</div>
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0;">
                        <a href="<?= base_url('customers/download-template?type=xlsx') ?>" class="btn btn-ghost btn-sm" style="font-size:11px;padding:5px 10px;" title="Download Excel template">
                            <i class="fa-solid fa-file-excel" style="color:#1d6f42;"></i> XLSX
                        </a>
                        <a href="<?= base_url('customers/download-template?type=csv') ?>" class="btn btn-ghost btn-sm" style="font-size:11px;padding:5px 10px;" title="Download CSV template">
                            <i class="fa-solid fa-file-csv" style="color:#217346;"></i> CSV
                        </a>
                    </div>
                </div>

                <p style="color:var(--text2);font-size:13px;margin-bottom:16px;">
                    Upload a CSV or Excel file. Required columns:<br>
                    <code style="display:block;background:var(--bg3);padding:6px;margin-top:8px;border-radius:4px;font-size:11px;">full_name, phone, address, package_name, zone_name</code>
                </p>
                <div style="margin-bottom:12px;">
                    <label class="form-label">Select CSV / Excel File</label>
                    <input type="file" name="csv_file" accept=".csv,.xlsx,.xls" class="form-input" required>
                </div>
                <div style="font-size:11px; color:var(--text2); background:var(--bg3); padding:8px; border-radius:4px;">
                    <i class="fa-solid fa-info-circle"></i> Package and Zone names must match existing ones in the system.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('importModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Start Import</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmSuspend(id, name) {
    document.getElementById('suspendName').textContent = name;
    document.getElementById('suspendForm').action = '<?= base_url('customers/suspend/') ?>' + id;
    document.getElementById('suspendModal').classList.add('open');
}
function confirmReconnect(id, name) {
    document.getElementById('reconnectName').textContent = name;
    document.getElementById('reconnectForm').action = '<?= base_url('customers/reconnect/') ?>' + id;
    document.getElementById('reconnectModal').classList.add('open');
}
function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete customer "${name}"?\n\nThis will mark the customer as deleted but preserve their data.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('customers/delete/') ?>' + id;
        document.body.appendChild(form);
        form.submit();
    }
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function toggleAll(el) { document.querySelectorAll('.rowCheck').forEach(c => c.checked = el.checked); }
function exportCSV() {
    window.location = '<?= base_url('customers') ?>?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>';
}
function exportPDF() {
    window.location = '<?= base_url('customers') ?>?<?= http_build_query(array_merge($_GET, ['export' => 'pdf'])) ?>';
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
</script>
