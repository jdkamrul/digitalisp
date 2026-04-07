<?php // views/reseller/create.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Add Reseller</h1><div class="page-breadcrumb"><a href="<?= base_url('resellers') ?>" style="color:var(--blue);text-decoration:none;">Resellers</a> › New</div></div>
    <a href="<?= base_url('resellers') ?>" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
<form method="POST" action="<?= base_url('resellers/store') ?>">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
    <div class="card fade-in" style="padding:20px;">
        <div style="font-size:14px;font-weight:700;margin-bottom:16px;"><i class="fa-solid fa-building" style="color:var(--blue);margin-right:8px;"></i>Reseller Information</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div><label class="form-label">Business Name <span style="color:var(--red)">*</span></label><input type="text" name="business_name" class="form-input" required></div>
            <div><label class="form-label">Contact Person <span style="color:var(--red)">*</span></label><input type="text" name="contact_person" class="form-input" required></div>
            <div><label class="form-label">Phone <span style="color:var(--red)">*</span></label><input type="tel" name="phone" class="form-input" placeholder="01XXXXXXXXX" required></div>
            <div><label class="form-label">Email</label><input type="email" name="email" class="form-input"></div>
            <div style="grid-column:1/-1;"><label class="form-label">Address</label><textarea name="address" class="form-input" rows="2"></textarea></div>
            <div><label class="form-label">Branch</label>
                <select name="branch_id" class="form-input" required><?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?></select>
            </div>
            <div><label class="form-label">Zone</label>
                <select name="zone_id" class="form-input"><option value="">Any</option><?php foreach($zones as $z): ?><option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['name']) ?></option><?php endforeach; ?></select>
            </div>
            <div><label class="form-label">Commission Rate (%)</label><input type="number" name="commission_rate" class="form-input" value="10" step="0.5" min="0" max="100"></div>
            <div><label class="form-label">Credit Limit (৳)</label><input type="number" name="credit_limit" class="form-input" value="0" step="0.01"></div>
            <div><label class="form-label">Parent Reseller</label>
                <select name="parent_reseller_id" class="form-input"><option value="">None (Top Level)</option><?php foreach($parents as $p): ?><option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['business_name']) ?></option><?php endforeach; ?></select>
            </div>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card fade-in" style="padding:18px;">
            <div style="font-size:13px;color:var(--text2);line-height:1.6;font-size:12px;">
                <i class="fa-solid fa-info-circle" style="color:var(--blue);margin-right:6px;"></i>
                Resellers can manage their own customers and collect payments. Commission is calculated automatically based on their collections.
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;"><i class="fa-solid fa-plus"></i> Add Reseller</button>
    </div>
</div>
</form>
