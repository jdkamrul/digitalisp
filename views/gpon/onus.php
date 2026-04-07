<?php // views/gpon/onus.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">ONU / CPE Devices</h1><div class="page-breadcrumb"><i class="fa-solid fa-router" style="color:var(--green)"></i> GPON</div></div>
    <button class="btn btn-primary" onclick="document.getElementById('addOnuModal').classList.add('open')"><i class="fa-solid fa-plus"></i> Add ONU</button>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Serial Number</th><th>Model/Brand</th><th>OLT</th><th>Splitter</th><th>Signal</th><th>Customer</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if(empty($onus)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text2);">
                <i class="fa-solid fa-router" style="font-size:32px;display:block;margin-bottom:12px;opacity:.4;"></i>No ONU devices found
            </td></tr>
            <?php else: foreach($onus as $o): ?>
            <tr>
                <td style="font-family:monospace;font-weight:700;font-size:13px;"><?= htmlspecialchars($o['serial_number']) ?></td>
                <td>
                    <div style="font-weight:500;"><?= htmlspecialchars($o['brand']??'—') ?> <?= htmlspecialchars($o['model']??'') ?></div>
                    <div style="font-size:11px;color:var(--text2);"><?= ucfirst($o['onu_type']??'') ?></div>
                </td>
                <td style="font-size:12px;color:var(--blue);font-weight:500;"><?= htmlspecialchars($o['olt_name']??'—') ?></td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($o['splitter_name']??'—') ?></td>
                <td><?php
                    $sig = $o['signal_level'];
                    if ($sig !== null) {
                        $sc = $sig >= -25 ? 'badge-green' : ($sig >= -27 ? 'badge-yellow' : 'badge-red');
                        echo '<span class="badge '.$sc.'">'.$sig.' dBm</span>';
                    } else { echo '<span style="color:var(--text2);">—</span>'; }
                ?></td>
                <td>
                    <?php if($o['customer_name']): ?>
                    <a href="<?= base_url("customers/view/{$o['customer_id']}") ?>" style="color:var(--blue);text-decoration:none;font-weight:500;"><?= htmlspecialchars($o['customer_name']) ?></a>
                    <div style="font-size:11px;color:var(--text2);"><?= htmlspecialchars($o['customer_code']) ?></div>
                    <?php else: ?><span class="badge badge-yellow">Stock</span><?php endif; ?>
                </td>
                <td><?php
                    $sc=['online'=>'badge-green','installed'=>'badge-green','active'=>'badge-green','offline'=>'badge-red','stock'=>'badge-yellow','faulty'=>'badge-red','returned'=>'badge-gray'];
                    echo '<span class="badge '.($sc[$o['status']]??'badge-gray').'">'.ucfirst($o['status']).'</span>';
                ?></td>
                <td><div style="display:flex;gap:6px;">
                    <a href="<?= base_url("customers/view/".($o['customer_id']??'')) ?>" class="btn btn-ghost btn-sm" <?= !$o['customer_id']?'style="pointer-events:none;opacity:.4"':'' ?> title="Customer"><i class="fa-solid fa-user"></i></a>
                    <button class="btn btn-ghost btn-sm" onclick='editOnu(<?= json_encode($o) ?>)' title="Edit"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="<?= base_url("gpon/onus/delete/{$o['id']}") ?>" onsubmit="return confirm('Delete this ONU?');" style="display:inline;">
                        <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="addOnuModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-router" style="color:var(--green);margin-right:8px;"></i>Add ONU Device</div>
            <button class="icon-btn" onclick="document.getElementById('addOnuModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('gpon/onus/store') ?>">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="grid-column:1/-1;"><label class="form-label">Serial Number <span style="color:var(--red)">*</span></label><input type="text" name="serial_number" class="form-input" style="font-family:monospace;" placeholder="HWTC1234ABCD" required></div>
                <div><label class="form-label">Brand</label><input type="text" name="brand" class="form-input" placeholder="Huawei / TP-Link"></div>
                <div><label class="form-label">Model</label><input type="text" name="model" class="form-input" placeholder="HG8145V5"></div>
                <div><label class="form-label">MAC Address</label><input type="text" name="mac_address" class="form-input" style="font-family:monospace;" placeholder="AA:BB:CC:DD:EE:FF"></div>
                <div><label class="form-label">Type</label>
                    <select name="onu_type" class="form-input"><option value="indoor">Indoor</option><option value="outdoor">Outdoor</option><option value="enterprise">Enterprise</option></select>
                </div>
                <div><label class="form-label">OLT</label>
                    <select name="olt_id" class="form-input">
                        <option value="">None</option>
                        <?php foreach($olts ?? [] as $olt): ?><option value="<?= $olt['id'] ?>"><?= htmlspecialchars($olt['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Splitter</label>
                    <select name="splitter_id" class="form-input">
                        <option value="">None</option>
                        <?php foreach($splitters as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Purchase Price (৳)</label><input type="number" name="purchase_price" class="form-input" step="0.01"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addOnuModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add ONU</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit ONU Modal -->
<div class="modal-overlay" id="editOnuModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--blue);margin-right:8px;"></i>Edit ONU</div>
            <button class="icon-btn" onclick="document.getElementById('editOnuModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('gpon/onus/update') ?>">
            <input type="hidden" name="id" id="edit_onu_id">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="grid-column:1/-1;"><label class="form-label">Serial Number</label><input type="text" name="serial_number" id="edit_onu_serial" class="form-input" style="font-family:monospace;" required></div>
                <div><label class="form-label">Brand</label><input type="text" name="brand" id="edit_onu_brand" class="form-input"></div>
                <div><label class="form-label">Model</label><input type="text" name="model" id="edit_onu_model" class="form-input"></div>
                <div><label class="form-label">MAC Address</label><input type="text" name="mac_address" id="edit_onu_mac" class="form-input" style="font-family:monospace;"></div>
                <div><label class="form-label">Type</label>
                    <select name="onu_type" id="edit_onu_type" class="form-input">
                        <option value="indoor">Indoor</option><option value="outdoor">Outdoor</option><option value="enterprise">Enterprise</option>
                    </select>
                </div>
                <div><label class="form-label">Splitter</label>
                    <select name="splitter_id" id="edit_onu_splitter" class="form-input">
                        <option value="">None</option>
                        <?php foreach($splitters as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Status</label>
                    <select name="status" id="edit_onu_status" class="form-input">
                        <option value="stock">Stock</option><option value="installed">Installed</option><option value="faulty">Faulty</option><option value="returned">Returned</option>
                    </select>
                </div>
                <div><label class="form-label">Purchase Price (৳)</label><input type="number" name="purchase_price" id="edit_onu_price" class="form-input" step="0.01"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editOnuModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update ONU</button>
            </div>
        </form>
    </div>
</div>
<script>
function editOnu(o) {
    document.getElementById('edit_onu_id').value      = o.id;
    document.getElementById('edit_onu_serial').value  = o.serial_number;
    document.getElementById('edit_onu_brand').value   = o.brand || '';
    document.getElementById('edit_onu_model').value   = o.model || '';
    document.getElementById('edit_onu_mac').value     = o.mac_address || '';
    document.getElementById('edit_onu_type').value    = o.onu_type || 'indoor';
    document.getElementById('edit_onu_splitter').value= o.splitter_id || '';
    document.getElementById('edit_onu_status').value  = o.status || 'stock';
    document.getElementById('edit_onu_price').value   = o.purchase_price || '';
    document.getElementById('editOnuModal').classList.add('open');
}
</script>
