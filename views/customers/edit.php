<?php
// views/customers/edit.php
?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Edit Customer</h1>
        <div class="page-breadcrumb">
            <a href="<?= base_url('customers') ?>" style="color:var(--blue);text-decoration:none;">Customers</a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
            <a href="<?= base_url("customers/view/{$customer['id']}") ?>" style="color:var(--blue);text-decoration:none;"><?= htmlspecialchars($customer['full_name']) ?></a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i> Edit
        </div>
    </div>
    <a href="<?= base_url("customers/view/{$customer['id']}") ?>" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<form method="POST" action="<?= base_url("customers/update/{$customer['id']}") ?>">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Personal Info -->
        <div class="card fade-in" style="padding:20px;">
            <div style="font-size:14px;font-weight:700;margin-bottom:16px;"><i class="fa-solid fa-user" style="color:var(--blue);margin-right:8px;"></i>Personal Information</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:1/-1;">
                    <label class="form-label">Full Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="full_name" class="form-input" value="<?= htmlspecialchars($customer['full_name']) ?>" required>
                </div>
                <div>
                    <label class="form-label">Father's Name</label>
                    <input type="text" name="father_name" class="form-input" value="<?= htmlspecialchars($customer['father_name']??'') ?>">
                </div>
                <div>
                    <label class="form-label">NID Number</label>
                    <input type="text" name="nid_number" class="form-input" value="<?= htmlspecialchars($customer['nid_number']??'') ?>">
                </div>
                <div>
                    <label class="form-label">Primary Phone <span style="color:var(--red)">*</span></label>
                    <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
                <div>
                    <label class="form-label">Alternate Phone</label>
                    <input type="tel" name="phone_alt" class="form-input" value="<?= htmlspecialchars($customer['phone_alt']??'') ?>">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($customer['email']??'') ?>">
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-input" rows="2"><?= htmlspecialchars($customer['address']) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Package & Billing -->
        <div class="card fade-in" style="padding:20px;">
            <div style="font-size:14px;font-weight:700;margin-bottom:16px;"><i class="fa-solid fa-money-bill" style="color:var(--green);margin-right:8px;"></i>Package & Billing</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label class="form-label">Package</label>
                    <select name="package_id" class="form-input" onchange="updateCharge()">
                        <option value="">No package</option>
                        <?php foreach($packages as $p): ?>
                        <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-profile="<?= $p['mikrotik_profile'] ?? '' ?>" <?= ($p['id']==$customer['package_id'])?'selected':'' ?>><?= htmlspecialchars($p['name']) ?> — ৳<?= number_format($p['price'],0) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:14px;background:var(--bg2);padding:14px;border-radius:12px;border:1px solid var(--border);">
                    <div>
                        <label class="form-label">MikroTik Server (NAS)</label>
                        <select name="nas_id" id="nasId" class="form-input" onchange="loadProfiles()">
                            <option value="">Select MikroTik</option>
                            <?php foreach($nasDevices as $nas): ?>
                            <option value="<?= $nas['id'] ?>" <?= ($nas['id']==($customer['nas_id']??''))?'selected':'' ?>><?= htmlspecialchars($nas['name']) ?> (<?= $nas['ip_address'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">PPPoE Profile</label>
                        <select name="mikrotik_profile" id="profileSel" class="form-input">
                            <option value="<?= htmlspecialchars($customer['mikrotik_profile']??'') ?>"><?= htmlspecialchars($customer['mikrotik_profile']?:'Select Profile') ?></option>
                        </select>
                        <div id="profileLoading" style="display:none;font-size:11px;color:var(--blue);margin-top:4px;"><i class="fa-solid fa-sync fa-spin"></i> Fetching profiles...</div>
                    </div>
                </div>
                <div>
                    <label class="form-label">Monthly Charge (৳)</label>
                    <input type="number" name="monthly_charge" class="form-input" value="<?= $customer['monthly_charge'] ?>" step="0.01" id="monthlyCharge">
                </div>
                <div>
                    <label class="form-label">Zone</label>
                    <select name="zone_id" class="form-input">
                        <option value="">Select Zone</option>
                        <?php foreach($zones as $z): ?>
                        <option value="<?= $z['id'] ?>" <?= $customer['zone_id']==$z['id']?'selected':'' ?>><?= htmlspecialchars($z['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Billing Day</label>
                    <select name="billing_day" class="form-input">
                        <?php for($d=1;$d<=28;$d++): ?>
                        <option value="<?=$d?>" <?=$customer['billing_day']==$d?'selected':''?>><?=$d?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card fade-in" style="padding:20px;">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-input" rows="3"><?= htmlspecialchars($customer['notes']??'') ?></textarea>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <!-- Customer ID -->
        <div class="card fade-in" style="padding:20px;">
            <div style="font-size:11px;color:var(--text2);margin-bottom:4px;">CUSTOMER ID</div>
            <div style="font-size:20px;font-weight:800;font-family:monospace;color:var(--blue);"><?= htmlspecialchars($customer['customer_code']) ?></div>
            <div style="font-size:12px;color:var(--text2);margin-top:8px;">Connection: <?= $customer['connection_date'] ? date('d M Y',strtotime($customer['connection_date'])) : '—' ?></div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;">
            <i class="fa-solid fa-floppy-disk"></i> Save Changes
        </button>
        <a href="<?= base_url("customers/view/{$customer['id']}") ?>" class="btn btn-ghost" style="width:100%;justify-content:center;">Cancel</a>
    </div>
</div>
</form>

<script>
function updateCharge() {
    const sel = document.querySelector('[name="package_id"]');
    const price = sel.options[sel.selectedIndex]?.dataset?.price;
    if (price) document.getElementById('monthlyCharge').value = price;
}

async function loadProfiles() {
    const nasId = document.getElementById('nasId').value;
    const profileSel = document.getElementById('profileSel');
    const loading = document.getElementById('profileLoading');
    const currentProfile = "<?= htmlspecialchars($customer['mikrotik_profile']??'') ?>";
    
    if (!nasId) {
        profileSel.innerHTML = '<option value="">Select Profile</option>';
        return;
    }

    loading.style.display = 'block';
    profileSel.disabled = true;

    try {
        const response = await fetch('<?= base_url('network/nas-profiles') ?>?nas_id=' + nasId);
        const profiles = await response.json();
        
        let html = '<option value="">Select Profile</option>';
        profiles.sort((a,b) => a.name.localeCompare(b.name)).forEach(p => {
            const selected = p.name === currentProfile ? 'selected' : '';
            html += `<option value="${p.name}" ${selected}>${p.name}</option>`;
        });
        profileSel.innerHTML = html;
        
        // Also check if package has a suggested profile if not already set
        if (!profileSel.value || profileSel.value === '') {
            const pkgProfile = document.querySelector('[name="package_id"]').selectedOptions[0]?.dataset.profile;
            if (pkgProfile) {
                Array.from(profileSel.options).forEach(opt => {
                    if (opt.value === pkgProfile) opt.selected = true;
                });
            }
        }
    } catch (e) {
        console.error('Failed to load profiles:', e);
    } finally {
        loading.style.display = 'none';
        profileSel.disabled = false;
    }
}

// Load profiles on startup if NAS is already selected
window.addEventListener('load', () => {
    if (document.getElementById('nasId').value) loadProfiles();
});
</script>
