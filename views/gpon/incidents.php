<?php // views/gpon/incidents.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Fiber Incidents</h1><div class="page-breadcrumb"><i class="fa-solid fa-triangle-exclamation" style="color:var(--red)"></i> GPON</div></div>
    <button class="btn btn-primary" onclick="document.getElementById('addIncidentModal').classList.add('open')"><i class="fa-solid fa-plus"></i> Report Incident</button>
</div>

<div style="display:grid;gap:14px;" class="fade-in">
    <?php if(empty($incidents)): ?>
    <div class="card" style="padding:48px;text-align:center;color:var(--text2);">
        <i class="fa-solid fa-check-circle" style="font-size:40px;display:block;margin-bottom:12px;color:var(--green);opacity:.6;"></i>
        No active incidents. All systems operational!
    </div>
    <?php else: foreach($incidents as $i): $sev=['low'=>'badge-blue','medium'=>'badge-yellow','high'=>'badge-red','critical'=>'badge-red']; ?>
    <div class="card" style="padding:18px;border-left:4px solid <?= ['low'=>'var(--blue)','medium'=>'var(--yellow)','high'=>'var(--red)','critical'=>'#dc2626'][$i['severity']]??'var(--border)' ?>;">
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                    <div style="font-size:15px;font-weight:700;"><?= htmlspecialchars($i['title']) ?></div>
                    <span class="badge <?= $sev[$i['severity']]??'badge-gray' ?>"><?= ucfirst($i['severity']) ?></span>
                    <?php $isc=['open'=>'badge-red','investigating'=>'badge-yellow','resolved'=>'badge-green','closed'=>'badge-gray'];
                    echo '<span class="badge '.($isc[$i['status']]??'badge-gray').'">'.ucfirst($i['status']).'</span>'; ?>
                </div>
                <div style="font-size:13px;color:var(--text2);margin-bottom:8px;"><?= htmlspecialchars($i['description']??'') ?></div>
                <div style="display:flex;gap:16px;font-size:12px;color:var(--text2);">
                    <?php if($i['branch_name']): ?><span><i class="fa-solid fa-building" style="margin-right:4px;"></i><?= htmlspecialchars($i['branch_name']) ?></span><?php endif; ?>
                    <?php if($i['zone_name']): ?><span><i class="fa-solid fa-location-dot" style="margin-right:4px;"></i><?= htmlspecialchars($i['zone_name']) ?></span><?php endif; ?>
                    <?php if($i['affected_customers']): ?><span style="color:var(--red);font-weight:600;"><i class="fa-solid fa-users" style="margin-right:4px;"></i><?= $i['affected_customers'] ?> affected</span><?php endif; ?>
                    <span><i class="fa-solid fa-clock" style="margin-right:4px;"></i><?= date('d M Y H:i',strtotime($i['created_at'])) ?></span>
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
                <button class="btn btn-ghost btn-sm" onclick='updateIncident(<?= json_encode($i) ?>)' title="Update Status"><i class="fa-solid fa-pen"></i></button>
                <form method="POST" action="<?= base_url("gpon/incidents/delete/{$i['id']}") ?>" onsubmit="return confirm('Delete this incident?');" style="display:inline;">
                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<div class="modal-overlay" id="addIncidentModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-triangle-exclamation" style="color:var(--red);margin-right:8px;"></i>Report Fiber Incident</div>
            <button class="icon-btn" onclick="document.getElementById('addIncidentModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('gpon/incidents/store') ?>">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div><label class="form-label">Title</label><input type="text" name="title" class="form-input" placeholder="e.g. Fiber cut on Road 12" required></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Branch</label>
                        <select name="branch_id" class="form-input" required>
                            <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div><label class="form-label">Severity</label>
                        <select name="severity" class="form-input">
                            <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="critical">Critical</option>
                        </select>
                    </div>
                    <div><label class="form-label">Affected Customers</label><input type="number" name="affected_customers" class="form-input" value="0"></div>
                    <div><label class="form-label">Location</label><input type="text" name="location" class="form-input" placeholder="Street/area"></div>
                </div>
                <div><label class="form-label">Description</label><textarea name="description" class="form-input" rows="3" placeholder="Describe the issue..."></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addIncidentModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-triangle-exclamation"></i> Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Incident Modal -->
<div class="modal-overlay" id="updateIncidentModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--blue);margin-right:8px;"></i>Update Incident</div>
            <button class="icon-btn" onclick="document.getElementById('updateIncidentModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('gpon/incidents/update') ?>">
            <input type="hidden" name="id" id="upd_inc_id">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Status</label>
                        <select name="status" id="upd_inc_status" class="form-input">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div><label class="form-label">Severity</label>
                        <select name="severity" id="upd_inc_severity" class="form-input">
                            <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div><label class="form-label">Resolution Notes</label><textarea name="resolution_notes" id="upd_inc_notes" class="form-input" rows="3" placeholder="Describe how the issue was resolved..."></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('updateIncidentModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Incident</button>
            </div>
        </form>
    </div>
</div>
<script>
function updateIncident(i) {
    document.getElementById('upd_inc_id').value       = i.id;
    document.getElementById('upd_inc_status').value   = i.status;
    document.getElementById('upd_inc_severity').value = i.severity;
    document.getElementById('upd_inc_notes').value    = i.resolution_notes || '';
    document.getElementById('updateIncidentModal').classList.add('open');
}
</script>
