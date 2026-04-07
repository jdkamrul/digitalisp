<?php // views/network/mikrotik-radius.php ?>
<div class="page-header fade-in" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 class="page-title">MikroTik RADIUS Config</h1>
        <div class="page-breadcrumb">
            <i class="fa-solid fa-server" style="color:var(--blue)"></i> Network 
            <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> 
            <a href="<?= base_url('network/nas') ?>" style="color:var(--text2);text-decoration:none;">Servers</a>
            <i class="fa-solid fa-angle-right" style="margin:0 8px;font-size:10px;opacity:0.5;"></i> 
            RADIUS Config
        </div>
    </div>
    <a href="<?= base_url('network/nas') ?>" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back to NAS</a>
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

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- NAS Info -->
    <div class="card fade-in">
        <h3 style="font-weight:700;margin-bottom:16px;color:var(--blue);">
            <i class="fa-solid fa-server" style="margin-right:8px;"></i>
            <?= htmlspecialchars($nas['name']) ?>
        </h3>
        <div style="display:grid;gap:8px;font-size:13px;">
            <div style="display:flex;justify-content:space-between;padding:8px;background:var(--bg2);border-radius:6px;">
                <span style="color:var(--text2);">IP Address</span>
                <code style="font-family:monospace;"><?= htmlspecialchars($nas['ip_address']) ?></code>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px;background:var(--bg2);border-radius:6px;">
                <span style="color:var(--text2);">API Port</span>
                <span><?= $nas['api_port'] ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px;background:var(--bg2);border-radius:6px;">
                <span style="color:var(--text2);">Username</span>
                <span><?= htmlspecialchars($nas['username']) ?></span>
            </div>
        </div>
    </div>

    <!-- Current RADIUS Status -->
    <div class="card fade-in">
        <h3 style="font-weight:700;margin-bottom:16px;color:var(--purple);">
            <i class="fa-solid fa-satellite-dish" style="margin-right:8px;"></i>
            RADIUS Status
        </h3>
        <?php if(empty($radiusConfig)): ?>
        <div style="text-align:center;padding:20px;color:var(--text2);">
            <i class="fa-solid fa-circle-exclamation" style="font-size:24px;margin-bottom:8px;display:block;color:var(--yellow);"></i>
            No RADIUS server configured
        </div>
        <?php else: ?>
        <div style="display:grid;gap:8px;font-size:13px;">
            <?php foreach($radiusConfig as $r): ?>
            <div style="display:flex;justify-content:space-between;padding:8px;background:var(--bg2);border-radius:6px;">
                <span style="color:var(--text2);">Address</span>
                <code><?= htmlspecialchars($r['address'] ?? '') ?></code>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px;background:var(--bg2);border-radius:6px;">
                <span style="color:var(--text2);">Port</span>
                <span><?= $r['port'] ?? '1812' ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px;background:var(--bg2);border-radius:6px;">
                <span style="color:var(--text2);">Service</span>
                <span><?= $r['service'] ?? '' ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:8px;background:var(--bg2);border-radius:6px;">
                <span style="color:var(--text2);">Status</span>
                <?php 
                $isDisabled = isset($r['disabled']) && ($r['disabled'] === true || $r['disabled'] === 'true' || $r['disabled'] === 'yes');
                ?>
                <span class="badge <?= $isDisabled ? 'badge-red' : 'badge-green' ?>">
                    <?= $isDisabled ? 'Disabled' : 'Active' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Configure RADIUS -->
    <div class="card fade-in" style="grid-column: 1 / -1;">
        <h3 style="font-weight:700;margin-bottom:16px;">
            <i class="fa-solid fa-gear" style="margin-right:8px;color:var(--blue);"></i>
            Configure RADIUS Server
        </h3>
        <form method="POST" action="<?= base_url('network/mikrotik-radius/configure/'.$nas['id']) ?>">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                <div>
                    <label class="form-label">RADIUS Server Address *</label>
                    <input type="text" name="radius_address" class="form-input" placeholder="e.g. 127.0.0.1 or your.server.com" required>
                </div>
                <div>
                    <label class="form-label">RADIUS Secret *</label>
                    <input type="password" name="radius_secret" class="form-input" placeholder="Enter secret key" required>
                </div>
                <div>
                    <label class="form-label">RADIUS Port</label>
                    <input type="number" name="radius_port" class="form-input" value="1812" placeholder="1812">
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Configure RADIUS
                </button>
            </div>
        </form>
    </div>

    <!-- PPP RADIUS Settings -->
    <div class="card fade-in" style="grid-column: 1 / -1;">
        <h3 style="font-weight:700;margin-bottom:16px;">
            <i class="fa-solid fa-plug" style="margin-right:8px;color:var(--purple);"></i>
            PPP Service RADIUS Settings
        </h3>
        
        <?php if(!empty($pppAaa)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px;">
            <div style="padding:12px;background:var(--bg2);border-radius:8px;text-align:center;">
                <div style="font-size:11px;color:var(--text2);margin-bottom:4px;">Use RADIUS</div>
                <span class="badge <?= ($pppAaa['use-radius'] ?? 'no') === 'yes' ? 'badge-green' : 'badge-gray' ?>">
                    <?= ($pppAaa['use-radius'] ?? 'no') === 'yes' ? 'Yes' : 'No' ?>
                </span>
            </div>
            <div style="padding:12px;background:var(--bg2);border-radius:8px;text-align:center;">
                <div style="font-size:11px;color:var(--text2);margin-bottom:4px;">Accounting</div>
                <span class="badge <?= ($pppAaa['accounting'] ?? 'no') === 'yes' ? 'badge-green' : 'badge-gray' ?>">
                    <?= ($pppAaa['accounting'] ?? 'no') === 'yes' ? 'Yes' : 'No' ?>
                </span>
            </div>
            <div style="padding:12px;background:var(--bg2);border-radius:8px;text-align:center;">
                <div style="font-size:11px;color:var(--text2);margin-bottom:4px;">Interim Update</div>
                <span style="font-weight:600;"><?= $pppAaa['interim-update'] ?? '300s' ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= base_url('network/mikrotik-radius/enable-ppp/'.$nas['id']) ?>" style="display:inline;">
            <button type="submit" class="btn btn-purple">
                <i class="fa-solid fa-toggle-on"></i> Enable PPP RADIUS
            </button>
        </form>
        
        <form method="POST" action="<?= base_url('network/mikrotik-radius/sync-users/'.$nas['id']) ?>" style="display:inline;margin-left:12px;">
            <button type="submit" class="btn btn-green">
                <i class="fa-solid fa-sync"></i> Sync Users from RADIUS
            </button>
        </form>
    </div>

    <!-- PPPoE Servers -->
    <?php if(!empty($pppoeServers)): ?>
    <div class="card fade-in" style="grid-column: 1 / -1;">
        <h3 style="font-weight:700;margin-bottom:16px;">
            <i class="fa-solid fa-broadcast-tower" style="margin-right:8px;color:var(--green);"></i>
            PPPoE Servers
        </h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Interface</th>
                    <th>Default Profile</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pppoeServers as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['service-name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($s['interface'] ?? '') ?></td>
                    <td><?= htmlspecialchars($s['default-profile'] ?? '') ?></td>
                    <td>
                        <span class="badge <?= ($s['disabled'] ?? 'no') === 'no' ? 'badge-green' : 'badge-red' ?>">
                            <?= ($s['disabled'] ?? 'no') === 'no' ? 'Running' : 'Disabled' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: 1fr; }
@media (min-width: 768px) { .md\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); } }
.grid-column-1-\/-1 { grid-column: 1 / -1; }
</style>