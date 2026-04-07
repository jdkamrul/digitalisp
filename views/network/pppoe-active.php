<?php // views/network/pppoe-active.php ?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Active PPPoE Sessions</h1>
        <div class="page-breadcrumb"><i class="fa-solid fa-bolt" style="color:var(--yellow)"></i> Network</div>
    </div>
    <div style="display:flex;gap:12px;align-items:center;">
        <form method="GET" action="<?= base_url('network/pppoe-active') ?>" style="display:flex; gap:8px; align-items:center;">
            <select name="nas_id" class="input" id="nasFilter" style="padding:6px 12px; font-size:13px; min-width:180px;" onchange="this.form.submit()">
                <option value="">All NAS Devices</option>
                <?php foreach($nasDevices as $n): ?>
                    <option value="<?= $n['id'] ?>" <?= ($nasIdFilter == $n['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($n['name']) ?> (<?= htmlspecialchars($n['ip_address']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div id="liveIndicator" style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--green);">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--green);display:inline-block;animation:pulse 2s infinite;"></span>
            <span id="liveStatus">Live</span>
        </div>
        <button class="btn btn-ghost" onclick="fetchSessions(true)"><i class="fa-solid fa-sync" id="refreshIcon"></i> Refresh</button>
    </div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:14px; font-weight:600;">
            Currently Connected Users 
            <span id="sessionCount" style="color:var(--text2); font-weight:400; margin-left:8px;"><?= count($allSessions) ?> online</span>
        </div>
        <div id="lastUpdated" style="font-size:12px; color:var(--text2);">
            <i class="fa-solid fa-circle-info" style="margin-right:6px;"></i>Auto-refreshes every 30s
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table" id="sessionTable">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Customer</th>
                    <th>Profile</th>
                    <th>IP Address</th>
                    <th>Uptime</th>
                    <th>Total Data</th>
                    <th>NAS Device</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody id="sessionBody">
                <?php if(empty($allSessions)): ?>
                <tr>
                    <td colspan="8" style="padding:40px; text-align:center; color:var(--text2);">
                        <i class="fa-solid fa-user-slash" style="font-size:32px; display:block; margin-bottom:12px; opacity:.3;"></i>
                        No active PPPoE sessions found.
                    </td>
                </tr>
                <?php else: foreach($allSessions as $s): ?>
                <tr>
                    <td>
                        <div style="font-weight:600; color:var(--blue);"><?= htmlspecialchars($s['name'] ?? '—') ?></div>
                        <div style="font-size:11px; color:var(--text2);"><?= htmlspecialchars($s['service'] ?? 'pppoe') ?></div>
                    </td>
                    <td>
                        <?php if(isset($s['customer_id'])): ?>
                        <a href="<?= base_url('customers/view/' . $s['customer_id']) ?>" style="text-decoration:none; color:inherit;">
                            <div style="font-weight:600;"><?= htmlspecialchars($s['customer_name']) ?></div>
                            <div style="font-size:11px; color:var(--text2);"><?= htmlspecialchars($s['customer_code']) ?></div>
                        </a>
                        <?php else: ?>
                        <div style="display:flex; flex-direction:column; gap:4px;">
                            <span style="color:var(--text2); font-style:italic; font-size:12px;">Unmatched</span>
                            <a href="<?= base_url('customers/create?pppoe_username=' . urlencode($s['name'] ?? '') . '&static_ip=' . urlencode($s['address'] ?? '')) ?>" class="btn btn-ghost btn-sm" style="padding:2px 8px; font-size:10px; border-color:var(--blue); color:var(--blue);">
                                <i class="fa-solid fa-user-plus"></i> Add
                            </a>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-blue" style="font-size:11px;"><?= htmlspecialchars($s['profile'] ?? 'default') ?></span></td>
                    <td><code style="background:var(--bg3); padding:2px 6px; border-radius:4px; font-size:12px;"><?= htmlspecialchars($s['address'] ?? '—') ?></code></td>
                    <td><span class="badge badge-green"><i class="fa-regular fa-clock" style="margin-right:4px;"></i><?= htmlspecialchars($s['uptime'] ?? '—') ?></span></td>
                    <td>
                        <?php
                        $dl = (int)($s['bytes-out'] ?? 0);
                        $ul = (int)($s['bytes-in'] ?? 0);
                        $total = $ul + $dl;
                        ?>
                        <div style="font-size:13px; font-weight:600; font-family:monospace; color:var(--blue);">
                            <i class="fa-solid fa-database" style="width:14px; opacity:.7;"></i>
                            <?= formatBytes($total) ?>
                        </div>
                        <div style="font-size:10px; color:var(--text2); margin-top:2px;">
                            ↑ <?= formatBytes($ul) ?> &nbsp; ↓ <?= formatBytes($dl) ?>
                        </div>
                    </td>
                    <td>
                        <span style="font-size:12px; font-weight:500;">
                            <i class="fa-solid fa-router" style="margin-right:6px; opacity:.5;"></i><?= htmlspecialchars($s['nas_name']) ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <form method="POST" action="<?= base_url('network/pppoe-kick/' . $s['nas_id'] . '/' . urlencode($s['name'] ?? '')) ?>" onsubmit="return confirm('Disconnect this user?')" style="display:inline;">
                            <button type="submit" class="btn btn-ghost btn-danger btn-sm" title="Kick">
                                <i class="fa-solid fa-right-from-bracket"></i> Kick
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div id="flashMsg" style="position:fixed; top:24px; left:50%; transform:translateX(-50%); z-index:9999; background:var(--green); color:#fff; padding:12px 24px; border-radius:12px; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
        <i class="fa-solid fa-check-circle" style="margin-right:8px;"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <script>setTimeout(() => document.getElementById('flashMsg')?.remove(), 3000);</script>
<?php endif; ?>

<?php
function formatBytes($bytes, $precision = 2) {
    $bytes = (int)$bytes;
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $pow = min(floor(log($bytes, 1024)), count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}
?>

<style>
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.3;} }
@keyframes spin { to { transform: rotate(360deg); } }
.spinning { animation: spin 1s linear infinite; }
</style>

<script>
const nasIdFilter = '<?= htmlspecialchars($nasIdFilter ?? '') ?>';
let refreshTimer = null;
let isRefreshing = false;

function formatBytes(bytes) {
    bytes = parseInt(bytes) || 0;
    if (bytes <= 0) return '0 B';
    const units = ['B','KB','MB','GB','TB'];
    const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), 4);
    return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
}

function buildRow(s) {
    const ul = parseInt(s['bytes-in'] || s.ul_bytes || 0);
    const dl = parseInt(s['bytes-out'] || s.dl_bytes || 0);
    const profile = s.profile || 'default';
    const nasName = s.nas_name || '—';
    const nasId   = s.nas_id || '';
    const name    = s.name || '—';
    const ip      = s.address || '—';
    const uptime  = s.uptime || '—';
    const service = s.service || 'pppoe';

    const customerCell = s.customer_id
        ? `<a href="/customers/view/${s.customer_id}" style="text-decoration:none;color:inherit;">
               <div style="font-weight:600;">${s.customer_name||''}</div>
               <div style="font-size:11px;color:var(--text2);">${s.customer_code||''}</div>
           </a>`
        : `<div style="display:flex;flex-direction:column;gap:4px;">
               <span style="color:var(--text2);font-style:italic;font-size:12px;">Unmatched</span>
               <a href="/customers/create?pppoe_username=${encodeURIComponent(name)}&static_ip=${encodeURIComponent(ip)}" class="btn btn-ghost btn-sm" style="padding:2px 8px;font-size:10px;border-color:var(--blue);color:var(--blue);">
                   <i class="fa-solid fa-user-plus"></i> Add
               </a>
           </div>`;

    return `<tr>
        <td>
            <div style="font-weight:600;color:var(--blue);">${name}</div>
            <div style="font-size:11px;color:var(--text2);">${service}</div>
        </td>
        <td>${customerCell}</td>
        <td><span class="badge badge-blue" style="font-size:11px;">${profile}</span></td>
        <td><code style="background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:12px;">${ip}</code></td>
        <td><span class="badge badge-green"><i class="fa-regular fa-clock" style="margin-right:4px;"></i>${uptime}</span></td>
        <td>
            <div style="font-size:13px;font-weight:600;font-family:monospace;color:var(--blue);">
                <i class="fa-solid fa-database" style="width:14px;opacity:.7;"></i>
                ${formatBytes(ul + dl)}
            </div>
            <div style="font-size:10px;color:var(--text2);margin-top:2px;">
                ↑ ${formatBytes(ul)} &nbsp; ↓ ${formatBytes(dl)}
            </div>
        </td>
        <td><span style="font-size:12px;font-weight:500;"><i class="fa-solid fa-router" style="margin-right:6px;opacity:.5;"></i>${nasName}</span></td>
        <td style="text-align:right;">
            <form method="POST" action="<?= base_url('network/pppoe-kick/') ?>${nasId}/${encodeURIComponent(name)}" onsubmit="return confirm('Disconnect this user?')" style="display:inline;">
                <button type="submit" class="btn btn-ghost btn-danger btn-sm" title="Kick">
                    <i class="fa-solid fa-right-from-bracket"></i> Kick
                </button>
            </form>
        </td>
    </tr>`;
}

async function fetchSessions(manual = false) {
    if (isRefreshing) return;
    isRefreshing = true;
    const icon = document.getElementById('refreshIcon');
    const status = document.getElementById('liveStatus');
    icon.classList.add('spinning');
    status.textContent = 'Updating...';

    try {
        const url = '<?= base_url('network/live-sessions') ?>' + (nasIdFilter ? '?nas_id=' + nasIdFilter : '');
        const resp = await fetch(url);
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const sessions = await resp.json();

        const tbody = document.getElementById('sessionBody');
        if (sessions.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="padding:40px;text-align:center;color:var(--text2);">
                <i class="fa-solid fa-user-slash" style="font-size:32px;display:block;margin-bottom:12px;opacity:.3;"></i>
                No active PPPoE sessions found.
            </td></tr>`;
        } else {
            tbody.innerHTML = sessions.map(buildRow).join('');
        }

        document.getElementById('sessionCount').textContent = sessions.length + ' online';
        const now = new Date().toLocaleTimeString();
        document.getElementById('lastUpdated').innerHTML = `<i class="fa-solid fa-circle-check" style="color:var(--green);margin-right:6px;"></i>Updated at ${now}`;
        status.textContent = 'Live';
    } catch(e) {
        status.textContent = 'Error - retrying';
        document.getElementById('liveIndicator').style.color = 'var(--red)';
        console.error('Session fetch failed:', e);
    } finally {
        icon.classList.remove('spinning');
        isRefreshing = false;
    }
}

// Auto-refresh every 30 seconds
function startAutoRefresh() {
    if (refreshTimer) clearInterval(refreshTimer);
    refreshTimer = setInterval(() => fetchSessions(), 30000);
}

startAutoRefresh();
</script>
