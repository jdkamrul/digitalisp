<?php // views/network/radius-profiles.php ?>
<style>
.profile-card { background:var(--bg2); border-radius:8px; padding:16px; border:1px solid var(--border); }
.profile-card h3 { font-weight:700; font-size:16px; color:var(--blue); margin-bottom:4px; }
.profile-card .meta { font-size:12px; color:var(--text2); }
.profile-attrs { background:var(--bg); border-radius:6px; padding:12px; font-size:12px; margin-top:12px; }
.profile-attrs div { display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid var(--border); }
.profile-attrs div:last-child { border-bottom:none; }
.profile-attrs .label { color:var(--text2); }
.profile-attrs .value { font-weight:600; font-family:monospace; }
.mt-badge { background:linear-gradient(135deg,#9333ea,#ec4899); color:#fff; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:600; }
.db-badge { background:linear-gradient(135deg,#2563eb,#06b6d4); color:#fff; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:600; }
</style>

<div class="page-header fade-in" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 class="page-title">RADIUS Profiles</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-satellite-dish" style="color:var(--blue)"></i> Network <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> RADIUS <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> Profiles</div>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addProfileModal').classList.add('open')"><i class="fa-solid fa-plus"></i> Add Profile</button>
    <?php if(!empty($mikrotikProfiles)): ?>
    <form method="POST" action="<?= base_url('network/radius/profiles/sync-from-mikrotik') ?>">
        <button type="submit" class="btn btn-ghost" style="background:linear-gradient(135deg,#9333ea,#ec4899);color:#fff;"><i class="fa-solid fa-sync"></i> Sync from MikroTik</button>
    </form>
    <?php endif; ?>
</div>

<?php if(isset($_SESSION['success'])): ?>
<div style="background:rgba(16,185,129,0.1); border:1px solid var(--green); color:var(--green); padding:12px; border-radius:8px; margin-bottom:16px; font-weight:600;">
    <i class="fa-solid fa-check-circle" style="margin-right:8px;"></i> <?= $_SESSION['success'] ?>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div style="background:rgba(239,68,68,0.1); border:1px solid var(--red); color:var(--red); padding:12px; border-radius:8px; margin-bottom:16px; font-weight:600;">
    <i class="fa-solid fa-triangle-exclamation" style="margin-right:8px;"></i> <?= $_SESSION['error'] ?>
</div>
<?php unset($_SESSION['error']); endif; ?>

<?php if(!empty($mikrotikProfiles)): ?>
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
        <i class="fa-solid fa-router" style="color:var(--purple);"></i>
        <h2 style="font-size:16px;font-weight:700;">MikroTik PPPoE Profiles</h2>
        <span class="mt-badge">LIVE</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach($mikrotikProfiles as $mp): ?>
        <div class="profile-card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <h3><?= htmlspecialchars($mp['name'] ?? 'Unnamed') ?></h3>
                <span class="mt-badge">MikroTik</span>
            </div>
            <div class="meta">
                <?php if(!empty($mp['rate-limit'])): ?>
                <div><i class="fa-solid fa-gauge-high" style="margin-right:4px;"></i> <?= htmlspecialchars($mp['rate-limit']) ?></div>
                <?php endif; ?>
                <?php if(!empty($mp['local-address'])): ?>
                <div><i class="fa-solid fa-network-wired" style="margin-right:4px;"></i> <?= htmlspecialchars($mp['local-address']) ?></div>
                <?php endif; ?>
                <?php if(!empty($mp['remote-address'])): ?>
                <div><i class="fa-solid fa-globe" style="margin-right:4px;"></i> <?= htmlspecialchars($mp['remote-address']) ?></div>
                <?php endif; ?>
            </div>
            <?php if(!empty($mp['rate-limit'])): ?>
            <div class="profile-attrs">
                <div><span class="label">Rate Limit</span><span class="value"><?= htmlspecialchars($mp['rate-limit']) ?></span></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
        <i class="fa-solid fa-database" style="color:var(--blue);"></i>
        <h2 style="font-size:16px;font-weight:700;">Local RADIUS Profiles</h2>
        <span class="db-badge">DATABASE</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if(empty($profiles)): ?>
        <div class="col-span-full card" style="text-align:center;padding:40px;color:var(--text2);">
            <i class="fa-solid fa-layer-group" style="font-size:32px;display:block;margin-bottom:12px;opacity:.4;"></i>
            No local RADIUS profiles configured.
        </div>
        <?php else: foreach($profiles as $p): ?>
        <div class="profile-card fade-in">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                <div>
                    <h3><?= htmlspecialchars($p['groupname']) ?></h3>
                    <span class="meta"><?= $p['attr_count'] ?> attributes</span>
                </div>
                <span class="db-badge">Local</span>
            </div>
            
            <?php if(!empty($profileAttrs[$p['groupname']])): ?>
            <div class="profile-attrs">
                <?php foreach($profileAttrs[$p['groupname']] as $a): ?>
                <?php 
                $label = match($a['attribute']) {
                    'Mikrotik-Rate-Limit' => 'Speed Limit',
                    'Session-Timeout' => 'Session Timeout',
                    'Idle-Timeout' => 'Idle Timeout',
                    default => $a['attribute']
                };
                $val = $a['value'];
                if (in_array($a['attribute'], ['Session-Timeout', 'Idle-Timeout'])) {
                    $val = match((int)$val) {
                        3600 => '1 hour',
                        7200 => '2 hours',
                        14400 => '4 hours',
                        86400 => '24 hours',
                        default => $val . ' sec'
                    };
                }
                ?>
                <div><span class="label"><?= $label ?></span><span class="value"><?= htmlspecialchars($val) ?></span></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div style="margin-top:12px;font-size:11px;color:var(--text2);display:flex;justify-content:space-between;align-items:center;">
                <span><i class="fa-solid fa-users" style="margin-right:4px;"></i><?= $profileUserCount[$p['groupname']] ?? 0 ?> users</span>
                <form action="<?= base_url('network/radius/profiles/delete/' . urlencode($p['groupname'])) ?>" method="POST" onsubmit="return confirm('Delete this profile?');">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);padding:4px 8px;"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Add Profile Modal -->
<div id="addProfileModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-layer-group" style="color:var(--blue);margin-right:8px;"></i>Add RADIUS Profile</div>
            <button class="icon-btn" onclick="document.getElementById('addProfileModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('network/radius/profiles/store') ?>">
            <div class="modal-body" style="display:grid;gap:16px;">
                <div>
                    <label class="form-label">Profile Name *</label>
                    <input type="text" name="name" class="form-input" required placeholder="e.g. 20Mbps_Unlimited">
                </div>
                <div>
                    <label class="form-label">Speed Limit (Mikrotik-Rate-Limit)</label>
                    <input type="text" name="rate_limit" class="form-input" placeholder="10M/10M/20M/0/0">
                    <div style="font-size:11px;color:var(--text2);margin-top:4px;">Format: upload/avg/burst (e.g. 10M/10M/20M)</div>
                </div>
                <div>
                    <label class="form-label">Session Timeout (seconds)</label>
                    <input type="number" name="session_timeout" class="form-input" value="86400" placeholder="86400">
                    <div style="font-size:11px;color:var(--text2);margin-top:4px;">Default: 86400 (24 hours)</div>
                </div>
                <div>
                    <label class="form-label">Idle Timeout (seconds)</label>
                    <input type="number" name="idle_timeout" class="form-input" value="1800" placeholder="1800">
                    <div style="font-size:11px;color:var(--text2);margin-top:4px;">Default: 1800 (30 min)</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addProfileModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Profile</button>
            </div>
        </form>
    </div>
</div>

<style>
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
@media (min-width: 768px) { .md\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .lg\:grid-cols-3 { grid-template-columns: repeat(3, 1fr); } }
.col-span-full { grid-column: 1 / -1; }
</style>