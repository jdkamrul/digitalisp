<?php // views/comms/templates.php ?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">SMS Templates</h1>
        <div class="page-breadcrumb">
            <a href="<?= base_url('comms') ?>" style="color:var(--blue);text-decoration:none;">Communication Hub</a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i> Templates
        </div>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addTemplateModal').classList.add('open')">
        <i class="fa-solid fa-plus"></i> New Template
    </button>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Event Type</th><th>Message (BN)</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (empty($templates)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--text2);">No templates yet.</td></tr>
            <?php else: foreach ($templates as $t): ?>
            <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($t['name']) ?></td>
                <td><span class="badge badge-blue" style="font-size:10px;"><?= htmlspecialchars($t['event_type']) ?></span></td>
                <td style="font-size:12px;color:var(--text2);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                    title="<?= htmlspecialchars($t['message_bn']) ?>">
                    <?= htmlspecialchars(mb_substr($t['message_bn'] ?? '', 0, 80)) ?>…
                </td>
                <td><span class="badge <?= $t['is_active']?'badge-green':'badge-gray' ?>" style="font-size:10px;"><?= $t['is_active']?'Active':'Inactive' ?></span></td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <button class="icon-btn btn-sm" onclick='openEditTemplate(<?= json_encode($t) ?>)' title="Edit">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <form method="POST" action="<?= base_url('comms/templates/delete/'.$t['id']) ?>" style="display:inline;"
                              onsubmit="return confirm('Delete this template?')">
                            <button type="submit" class="icon-btn btn-sm" style="color:var(--red);" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Variables reference -->
<div class="card fade-in" style="padding:18px;margin-top:16px;">
    <div style="font-size:13px;font-weight:700;margin-bottom:10px;">Available Variables</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;font-size:12px;">
        <?php foreach (['{name}'=>'Customer name','{phone}'=>'Phone number','{amount}'=>'Due amount','{month}'=>'Billing month','{due_date}'=>'Due date','{receipt}'=>'Receipt number','{package}'=>'Package name'] as $var=>$desc): ?>
        <div style="background:var(--bg3);border:1px solid var(--border);border-radius:6px;padding:6px 10px;">
            <code style="color:var(--blue);"><?= $var ?></code>
            <span style="color:var(--text2);margin-left:6px;"><?= $desc ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addTemplateModal">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-file-lines" style="color:var(--blue);margin-right:8px;"></i>New Template</div>
            <button class="icon-btn" onclick="document.getElementById('addTemplateModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('comms/templates/store') ?>">
            <div class="modal-body" style="display:grid;gap:14px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div><label class="form-label">Template Name</label><input type="text" name="name" class="form-input" required></div>
                    <div><label class="form-label">Event Type</label>
                        <select name="event_type" class="form-input">
                            <?php foreach (['bill_generated','payment_received','due_reminder','suspension','reconnection','welcome','otp','custom'] as $e): ?>
                            <option value="<?= $e ?>"><?= ucfirst(str_replace('_',' ',$e)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div><label class="form-label">Message (Bangla) <span style="color:var(--red)">*</span></label>
                    <textarea name="message_bn" class="form-input" rows="4" required placeholder="বাংলায় বার্তা লিখুন..."></textarea></div>
                <div><label class="form-label">Message (English)</label>
                    <textarea name="message_en" class="form-input" rows="3" placeholder="English message (optional)"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addTemplateModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Template</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editTemplateModal">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-edit" style="color:var(--blue);margin-right:8px;"></i>Edit Template</div>
            <button class="icon-btn" onclick="document.getElementById('editTemplateModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('comms/templates/update') ?>">
            <input type="hidden" name="id" id="edit_tpl_id">
            <div class="modal-body" style="display:grid;gap:14px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div><label class="form-label">Template Name</label><input type="text" name="name" id="edit_tpl_name" class="form-input" required></div>
                    <div><label class="form-label">Event Type</label>
                        <select name="event_type" id="edit_tpl_event" class="form-input">
                            <?php foreach (['bill_generated','payment_received','due_reminder','suspension','reconnection','welcome','otp','custom'] as $e): ?>
                            <option value="<?= $e ?>"><?= ucfirst(str_replace('_',' ',$e)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div><label class="form-label">Message (Bangla)</label>
                    <textarea name="message_bn" id="edit_tpl_bn" class="form-input" rows="4"></textarea></div>
                <div><label class="form-label">Message (English)</label>
                    <textarea name="message_en" id="edit_tpl_en" class="form-input" rows="3"></textarea></div>
                <div><label class="form-label">Status</label>
                    <select name="is_active" id="edit_tpl_status" class="form-input">
                        <option value="1">Active</option><option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editTemplateModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditTemplate(t) {
    document.getElementById('editTemplateModal').classList.add('open');
    document.getElementById('edit_tpl_id').value    = t.id;
    document.getElementById('edit_tpl_name').value  = t.name;
    document.getElementById('edit_tpl_event').value = t.event_type;
    document.getElementById('edit_tpl_bn').value    = t.message_bn || '';
    document.getElementById('edit_tpl_en').value    = t.message_en || '';
    document.getElementById('edit_tpl_status').value = t.is_active;
}
</script>
