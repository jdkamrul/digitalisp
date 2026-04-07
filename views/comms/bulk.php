<?php // views/comms/bulk.php ?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Bulk SMS</h1>
        <div class="page-breadcrumb">
            <a href="<?= base_url('comms') ?>" style="color:var(--blue);text-decoration:none;">Communication Hub</a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i> Bulk SMS
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;" class="fade-in">
    <!-- Form -->
    <div class="card" style="padding:24px;">
        <form method="POST" action="<?= base_url('comms/bulk/send') ?>" id="bulkForm">
            <div style="display:grid;gap:16px;">
                <div>
                    <label class="form-label">Campaign Name</label>
                    <input type="text" name="campaign_name" class="form-input"
                           value="Campaign <?= date('d M Y H:i') ?>" required>
                </div>

                <div>
                    <label class="form-label">Filter Recipients By</label>
                    <select name="filter_type" id="filterType" class="form-input" onchange="onFilterChange()">
                        <option value="all">All Active Customers</option>
                        <option value="due">Customers with Due Invoices</option>
                        <option value="zone">By Zone</option>
                        <option value="package">By Package</option>
                        <option value="branch">By Branch</option>
                        <option value="status">By Status</option>
                    </select>
                </div>

                <!-- Dynamic filter value -->
                <div id="filterValueWrap" style="display:none;">
                    <label class="form-label" id="filterValueLabel">Select Value</label>
                    <select name="filter_value" id="filterValue" class="form-input">
                        <option value="">— Select —</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">
                        Message
                        <span style="float:right;font-size:11px;color:var(--text2);" id="charCount">0 / 160</span>
                    </label>
                    <textarea name="message" id="messageText" class="form-input" rows="5"
                              placeholder="Type your message here..." maxlength="480" required
                              oninput="updateCharCount(this)"></textarea>
                    <div style="font-size:11px;color:var(--text2);margin-top:4px;">
                        Available variables: <code>{name}</code> <code>{phone}</code> <code>{amount}</code> <code>{due_date}</code>
                    </div>
                </div>

                <!-- Template loader -->
                <?php if (!empty($templates)): ?>
                <div>
                    <label class="form-label">Load from Template (optional)</label>
                    <select class="form-input" onchange="loadTemplate(this)">
                        <option value="">— Select template —</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= htmlspecialchars($t['message_bn']) ?>">
                            <?= htmlspecialchars($t['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid var(--border);">
                    <div id="recipientPreview" style="font-size:13px;color:var(--text2);">
                        <i class="fa-solid fa-users"></i> <span id="recipientCount">—</span> recipients
                    </div>
                    <button type="submit" class="btn btn-primary" id="sendBtn">
                        <i class="fa-solid fa-paper-plane"></i> Send Campaign
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Sidebar info -->
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="padding:20px;">
            <div style="font-size:13px;font-weight:700;margin-bottom:14px;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;">Quick Actions</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <form method="POST" action="<?= base_url('comms/due-reminders') ?>">
                    <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:flex-start;"
                            onclick="return confirm('Send due reminders to all overdue customers?')">
                        <i class="fa-solid fa-bell" style="color:var(--yellow);"></i> Send Due Reminders
                    </button>
                </form>
                <a href="<?= base_url('comms/templates') ?>" class="btn btn-ghost" style="justify-content:flex-start;">
                    <i class="fa-solid fa-file-lines" style="color:var(--blue);"></i> Manage Templates
                </a>
                <a href="<?= base_url('comms/logs') ?>" class="btn btn-ghost" style="justify-content:flex-start;">
                    <i class="fa-solid fa-list" style="color:var(--purple);"></i> View SMS Logs
                </a>
            </div>
        </div>

        <div class="card" style="padding:20px;">
            <div style="font-size:13px;font-weight:700;margin-bottom:12px;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;">Tips</div>
            <div style="font-size:12px;color:var(--text2);display:grid;gap:8px;">
                <div><i class="fa-solid fa-circle-info" style="color:var(--blue);margin-right:6px;"></i>160 chars = 1 SMS credit. Bangla uses more.</div>
                <div><i class="fa-solid fa-circle-info" style="color:var(--blue);margin-right:6px;"></i>Use <code>{name}</code> to personalise messages.</div>
                <div><i class="fa-solid fa-circle-info" style="color:var(--blue);margin-right:6px;"></i>Only active customers with a phone number receive SMS.</div>
            </div>
        </div>
    </div>
</div>

<script>
const zonesData    = <?= json_encode(array_map(fn($z) => ['id'=>$z['id'],'name'=>$z['name']], $zones)) ?>;
const packagesData = <?= json_encode(array_map(fn($p) => ['id'=>$p['id'],'name'=>$p['name']], $packages)) ?>;
const branchesData = <?= json_encode(array_map(fn($b) => ['id'=>$b['id'],'name'=>$b['name']], $branches)) ?>;
const statusData   = [
    {id:'active',name:'Active'},{id:'suspended',name:'Suspended'},
    {id:'pending',name:'Pending'},{id:'cancelled',name:'Cancelled'}
];

function onFilterChange() {
    const type = document.getElementById('filterType').value;
    const wrap = document.getElementById('filterValueWrap');
    const sel  = document.getElementById('filterValue');
    const lbl  = document.getElementById('filterValueLabel');

    let data = null;
    if (type === 'zone')    { data = zonesData;    lbl.textContent = 'Select Zone'; }
    if (type === 'package') { data = packagesData; lbl.textContent = 'Select Package'; }
    if (type === 'branch')  { data = branchesData; lbl.textContent = 'Select Branch'; }
    if (type === 'status')  { data = statusData;   lbl.textContent = 'Select Status'; }

    if (data) {
        wrap.style.display = 'block';
        sel.innerHTML = '<option value="">— Select —</option>' +
            data.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
    } else {
        wrap.style.display = 'none';
    }
    previewCount();
}

function previewCount() {
    const type  = document.getElementById('filterType').value;
    const value = document.getElementById('filterValue')?.value || '';
    fetch(`<?= base_url('comms/preview-recipients') ?>?filter_type=${type}&filter_value=${encodeURIComponent(value)}`)
        .then(r => r.json())
        .then(d => { document.getElementById('recipientCount').textContent = d.count.toLocaleString(); });
}

document.getElementById('filterValue')?.addEventListener('change', previewCount);
document.getElementById('filterType').addEventListener('change', previewCount);

function updateCharCount(el) {
    const len = el.value.length;
    const sms = Math.ceil(len / 160) || 1;
    document.getElementById('charCount').textContent = `${len} / 160 (${sms} SMS)`;
}

function loadTemplate(sel) {
    if (sel.value) {
        document.getElementById('messageText').value = sel.value;
        updateCharCount(document.getElementById('messageText'));
    }
}

document.getElementById('bulkForm').addEventListener('submit', function(e) {
    const count = parseInt(document.getElementById('recipientCount').textContent.replace(/,/g,'')) || 0;
    if (count === 0) { e.preventDefault(); alert('No recipients found for the selected filter.'); return; }
    if (!confirm(`Send SMS to ${count.toLocaleString()} recipients?`)) e.preventDefault();
});

// Initial count
previewCount();
</script>
