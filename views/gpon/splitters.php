<?php // views/gpon/splitters.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Splitters</h1><div class="page-breadcrumb"><i class="fa-solid fa-code-fork" style="color:var(--purple)"></i> GPON</div></div>
    <button class="btn btn-primary" onclick="document.getElementById('addSplitterModal').classList.add('open')"><i class="fa-solid fa-plus"></i> Add Splitter</button>
</div>
<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Name</th><th>OLT</th><th>Ratio</th><th>OLT Port</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if(empty($splitters)): ?>
            <tr><td colspan="7" style="text-align:center;padding:36px;color:var(--text2);">No splitters configured.</td></tr>
            <?php else: foreach($splitters as $s): ?>
            <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($s['name']) ?></td>
                <td style="color:var(--text2);"><?= htmlspecialchars($s['olt_name']??'—') ?></td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($s['ratio']) ?></span></td>
                <td style="font-family:monospace;"><?= $s['olt_port']??'—' ?></td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($s['location']??'—') ?></td>
                <td><span class="badge <?= $s['is_active']?'badge-green':'badge-gray' ?>"><?= $s['is_active']?'Active':'Inactive' ?></span></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button class="btn btn-ghost btn-sm" onclick='editSplitter(<?= json_encode($s) ?>)' title="Edit"><i class="fa-solid fa-pen"></i></button>
                        <form method="POST" action="<?= base_url("gpon/splitters/delete/{$s['id']}") ?>" onsubmit="return confirm('Delete this splitter?');" style="display:inline;">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addSplitterModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-code-fork" style="color:var(--purple);margin-right:8px;"></i>Add Splitter</div>
            <button class="icon-btn" onclick="document.getElementById('addSplitterModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('gpon/splitters/store') ?>">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="grid-column:1/-1;"><label class="form-label">Splitter Name <span style="color:var(--red)">*</span></label><input type="text" name="name" class="form-input" placeholder="SP-01" required></div>
                <div><label class="form-label">OLT</label>
                    <select name="olt_id" class="form-input">
                        <option value="">None</option>
                        <?php foreach($olts as $o): ?><option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Ratio</label>
                    <select name="ratio" class="form-input"><option value="1:2">1:2</option><option value="1:4">1:4</option><option value="1:8" selected>1:8</option><option value="1:16">1:16</option><option value="1:32">1:32</option></select>
                </div>
                <div><label class="form-label">OLT Port</label><input type="number" name="olt_port" class="form-input" placeholder="Port number"></div>
                <div><label class="form-label">Location</label><input type="text" name="location" class="form-input" placeholder="Pole / junction box"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addSplitterModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Splitter</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editSplitterModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--blue);margin-right:8px;"></i>Edit Splitter</div>
            <button class="icon-btn" onclick="document.getElementById('editSplitterModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('gpon/splitters/update') ?>">
            <input type="hidden" name="id" id="edit_sp_id">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="grid-column:1/-1;"><label class="form-label">Splitter Name</label><input type="text" name="name" id="edit_sp_name" class="form-input" required></div>
                <div><label class="form-label">OLT</label>
                    <select name="olt_id" id="edit_sp_olt" class="form-input">
                        <option value="">None</option>
                        <?php foreach($olts as $o): ?><option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Ratio</label>
                    <select name="ratio" id="edit_sp_ratio" class="form-input">
                        <?php foreach(['1:2','1:4','1:8','1:16','1:32'] as $r): ?><option value="<?= $r ?>"><?= $r ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">OLT Port</label><input type="number" name="olt_port" id="edit_sp_port" class="form-input"></div>
                <div><label class="form-label">Location</label><input type="text" name="location" id="edit_sp_loc" class="form-input"></div>
                <div><label class="form-label">Status</label>
                    <select name="is_active" id="edit_sp_active" class="form-input">
                        <option value="1">Active</option><option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editSplitterModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Splitter</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSplitter(s) {
    document.getElementById('edit_sp_id').value    = s.id;
    document.getElementById('edit_sp_name').value  = s.name;
    document.getElementById('edit_sp_olt').value   = s.olt_id || '';
    document.getElementById('edit_sp_ratio').value = s.ratio;
    document.getElementById('edit_sp_port').value  = s.olt_port || '';
    document.getElementById('edit_sp_loc').value   = s.location || '';
    document.getElementById('edit_sp_active').value= s.is_active;
    document.getElementById('editSplitterModal').classList.add('open');
}
</script>
