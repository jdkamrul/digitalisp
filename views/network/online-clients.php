<?php // views/network/online-clients.php ?>

<style>
/* ── TABS ── */
.monitor-tabs { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:20px; }
.monitor-tab {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 16px; border-radius:999px; font-size:13px; font-weight:500;
    border:1px solid var(--border); background:var(--bg3); color:var(--text2);
    cursor:pointer; transition:all 0.2s; text-decoration:none; white-space:nowrap;
}
.monitor-tab:hover { color:var(--text); border-color:var(--blue); }
.monitor-tab.active { background:var(--bg2); color:var(--text); border-color:var(--border); }
.monitor-tab i { font-size:11px; }
.tab-spacer { flex:1; }

/* ── STAT CARDS ── */
.ocm-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px; }
.ocm-stat {
    border-radius:14px; padding:24px 28px;
    display:flex; align-items:center; gap:20px;
    color:#fff; position:relative; overflow:hidden; min-height:100px;
}
.ocm-stat::after {
    content:''; position:absolute; right:-20px; top:50%; transform:translateY(-50%);
    width:100px; height:100px; border-radius:50%;
    background:rgba(255,255,255,0.08); pointer-events:none;
}
.ocm-stat.total  { background:linear-gradient(135deg,#1e40af,#3b82f6); }
.ocm-stat.online { background:linear-gradient(135deg,#0d9488,#14b8a6); }
.ocm-stat.offline{ background:linear-gradient(135deg,#374151,#4b5563); }
.ocm-stat-icon { font-size:36px; opacity:0.9; }
.ocm-stat-label { font-size:14px; font-weight:500; opacity:0.85; }
.ocm-stat-value { font-size:48px; font-weight:800; line-height:1; }

/* ── FILTERS ── */
.filter-grid-1 { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:12px; }
.filter-grid-2 { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
.filter-label { font-size:11px; font-weight:600; color:var(--text2); text-transform:uppercase; letter-spacing:.5px; margin-bottom:5px; }
.filter-select {
    width:100%; background:var(--bg3); border:1px solid var(--border);
    color:var(--text); border-radius:8px; padding:8px 12px; font-size:13px;
    transition:border-color 0.2s; cursor:pointer; outline:none;
}
.filter-select:focus { border-color:var(--blue); }

/* ── TABLE CONTROLS ── */
.tbl-controls {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:14px; flex-wrap:wrap; gap:10px;
}
.tbl-show-wrap { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text2); }
.tbl-show-wrap select {
    background:var(--bg3); border:1px solid var(--border); color:var(--text);
    border-radius:6px; padding:4px 8px; font-size:13px; cursor:pointer;
}
.tbl-search-wrap { display:flex; align-items:center; gap:8px; }
.tbl-search-wrap label { font-size:13px; color:var(--text2); }
.tbl-search-input {
    background:var(--bg3); border:1px solid var(--border); color:var(--text);
    border-radius:6px; padding:6px 12px; font-size:13px; outline:none; transition:border-color 0.2s;
}
.tbl-search-input:focus { border-color:var(--blue); }

/* ── SORTABLE HEADERS ── */
.data-table th.sortable { cursor:pointer; user-select:none; }
.data-table th.sortable:hover { color:var(--text); }
.sort-icon { margin-left:4px; font-size:9px; opacity:.5; }

/* ── ACTION BTNS ── */
.action-set { display:flex; align-items:center; gap:4px; }
.act-btn {
    width:28px; height:28px; border-radius:6px; border:1px solid var(--border);
    background:var(--bg3); color:var(--text2); display:inline-flex; align-items:center;
    justify-content:center; font-size:12px; cursor:pointer; transition:all 0.18s;
    text-decoration:none;
}
.act-btn:hover { color:var(--blue); border-color:var(--blue); background:rgba(59,130,246,.08); }
.act-btn.danger:hover { color:var(--red); border-color:var(--red); background:rgba(239,68,68,.08); }

/* ── LOADING SKELETON ── */
@keyframes shimmer { 0%{opacity:.4;} 50%{opacity:1;} 100%{opacity:.4;} }
.skeleton-row td { padding:14px 14px; }
.skeleton-cell { height:14px; border-radius:4px; background:var(--bg3); animation:shimmer 1.4s ease infinite; }

/* ── STATUS ── */
.badge-connected { background:rgba(16,185,129,.15); color:#10b981; }
.badge-offline-tag { background:rgba(239,68,68,.15); color:#ef4444; }
@keyframes pulse-live { 0%,100%{opacity:1;} 50%{opacity:.3;} }
.live-dot { width:7px;height:7px;border-radius:50%;background:var(--green);display:inline-block;animation:pulse-live 2s infinite; }
</style>

<!-- ─── PAGE HEADER ─── -->
<div class="page-header fade-in">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-desktop" style="color:var(--blue);margin-right:10px;"></i>Online Clients Monitoring</h1>
        <div class="page-breadcrumb">
            <i class="fa-solid fa-display" style="color:var(--text2);"></i>
            <span>Client Monitoring</span>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
            <span style="color:var(--text);">Online Clients Monitoring</span>
        </div>
    </div>
    <button class="btn btn-ghost" style="border-color:rgba(59,130,246,.3);color:var(--blue);" id="settingsBtn">
        <i class="fa-solid fa-circle-xmark"></i>
    </button>
</div>

<!-- ─── TABS ─── -->
<div class="monitor-tabs fade-in">
    <a href="<?= base_url('network/online-clients') ?>" class="monitor-tab active" id="tab-online">
        <i class="fa-solid fa-list"></i> Online Client Monitoring
    </a>
    <a href="#" class="monitor-tab" onclick="return showTab('disabled-system')">
        <i class="fa-solid fa-list"></i> Disabled in system enabled in Mikrotik
    </a>
    <a href="#" class="monitor-tab" onclick="return showTab('enabled-system')">
        <i class="fa-solid fa-list"></i> Enabled in system disabled in Mikrotik
    </a>
    <a href="#" class="monitor-tab" onclick="return showTab('profile-mismatch')">
        <i class="fa-solid fa-list"></i> Profile Mismatch
    </a>
    <div class="tab-spacer"></div>
    <button class="btn btn-primary" id="syncBtn" onclick="syncNow()">
        <i class="fa-solid fa-rotate" id="syncIcon"></i> Sync Clients &amp; Servers
    </button>
</div>

<!-- ─── STAT CARDS ─── -->
<div class="ocm-stats fade-in">
    <div class="ocm-stat total">
        <div class="ocm-stat-icon"><i class="fa-solid fa-user-group"></i></div>
        <div>
            <div class="ocm-stat-label">Total Users</div>
            <div class="ocm-stat-value" id="statTotal"><?= number_format($totalUsers) ?></div>
        </div>
    </div>
    <div class="ocm-stat online">
        <div class="ocm-stat-icon"><i class="fa-solid fa-user-check"></i></div>
        <div>
            <div class="ocm-stat-label">Online Users</div>
            <div class="ocm-stat-value" id="statOnline">—</div>
        </div>
    </div>
    <div class="ocm-stat offline">
        <div class="ocm-stat-icon"><i class="fa-solid fa-user-xmark"></i></div>
        <div>
            <div class="ocm-stat-label">Offline Users</div>
            <div class="ocm-stat-value" id="statOffline">—</div>
        </div>
    </div>
</div>

<!-- ─── FILTERS ─── -->
<div class="card fade-in" style="padding:18px 20px; margin-bottom:20px;">
    <!-- Row 1: Server / Protocol / PPPoE Profile / Status -->
    <div class="filter-grid-2">
        <div>
            <div class="filter-label">SERVER</div>
            <select class="filter-select" id="f_server">
                <option value="">All Servers</option>
                <?php foreach($nasDevices as $n): ?>
                <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <div class="filter-label">PROTOCOL</div>
            <select class="filter-select" id="f_protocol">
                <option value="">PPPOE</option>
                <option value="pppoe">PPPOE</option>
                <option value="hotspot">Hotspot</option>
                <option value="static">Static</option>
                <option value="cgnat">CGNAT</option>
            </select>
        </div>
        <div>
            <div class="filter-label">PPPOE PROFILE</div>
            <select class="filter-select" id="f_profile">
                <option value="">Select Profile</option>
                <?php foreach($packages as $p): ?>
                <option value="<?= htmlspecialchars($p['mikrotik_profile']) ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['mikrotik_profile']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <div class="filter-label">STATUS</div>
            <select class="filter-select" id="f_status">
                <option value="">Select</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>
        </div>
    </div>

    <!-- Row 2: Zone / Sub Zone / Box / Connection Type -->
    <div class="filter-grid-2">
        <div>
            <div class="filter-label">ZONE</div>
            <select class="filter-select" id="f_zone">
                <option value="">Select</option>
                <?php foreach($zones as $z): ?>
                <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <div class="filter-label">SUB ZONE</div>
            <select class="filter-select" id="f_area">
                <option value="">Select</option>
                <?php foreach($areas as $a): ?>
                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <div class="filter-label">BOX</div>
            <select class="filter-select" id="f_box">
                <option value="">Select</option>
            </select>
        </div>
        <div>
            <div class="filter-label">CONNECTION TYPE</div>
            <select class="filter-select" id="f_conntype">
                <option value="">Select</option>
                <option value="pppoe">PPPoE</option>
                <option value="hotspot">Hotspot</option>
                <option value="static">Static</option>
                <option value="cgnat">CGNAT</option>
            </select>
        </div>
    </div>
</div>

<!-- ─── TABLE ─── -->
<div class="card fade-in" style="overflow:hidden;">
    <!-- Table Controls -->
    <div style="padding:14px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <div class="tbl-show-wrap">
            SHOW
            <select id="perPage" onchange="applyFilters()">
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100" selected>100</option>
                <option value="200">200</option>
            </select>
            ENTRIES
        </div>
        <div style="display:flex; align-items:center; gap:16px;">
            <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:var(--green);">
                <span class="live-dot"></span>
                <span id="liveLabel">Live</span>
            </div>
            <div class="tbl-search-wrap">
                <label>SEARCH:</label>
                <input type="text" class="tbl-search-input" id="searchInput" placeholder="" oninput="filterTable(this.value)">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div style="overflow-x:auto;">
        <table class="data-table" id="ocmTable">
            <thead>
                <tr>
                    <th class="sortable" onclick="sortTable(0)">C.Code <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(1)">ID/IP <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(2)">Name <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(3)">Mobile <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(4)">Zone <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(5)">Subzone <span class="sort-icon">⇅</span></th>
                    <th>Box</th>
                    <th class="sortable" onclick="sortTable(7)">Connection Type <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(8)">Server <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(9)">Profile <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(10)">Package <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(11)">IP Address <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(12)">Status <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(13)">Duration <span class="sort-icon">⇅</span></th>
                    <th class="sortable" onclick="sortTable(14)">Logout Time <span class="sort-icon">⇅</span></th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody id="ocmBody">
                <!-- skeleton while loading -->
                <?php for($i=0;$i<6;$i++): ?>
                <tr class="skeleton-row">
                    <?php for($j=0;$j<16;$j++): ?>
                    <td><div class="skeleton-cell" style="width:<?= [55,65,90,80,70,70,50,80,70,70,55,80,70,80,95,60][$j] ?>px;"></div></td>
                    <?php endfor; ?>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination + count -->
    <div style="padding:12px 18px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; font-size:13px; color:var(--text2);">
        <div id="tableInfo">Loading…</div>
        <div id="paginationArea" style="display:flex;gap:6px;"></div>
    </div>
</div>

<script>
// ── DATA & STATE ──────────────────────────────────────────────────────────────
let allRows     = [];
let filteredRows= [];
let currentPage = 1;
let sortCol     = -1;
let sortDir     = 1; // 1 = asc, -1 = desc

const dataUrl = '<?= base_url('network/online-clients/data') ?>';

// ── FETCH DATA ────────────────────────────────────────────────────────────────
async function fetchData() {
    document.getElementById('liveLabel').textContent = 'Updating…';

    const params = new URLSearchParams();
    const sv = document.getElementById('f_server').value;
    const pv = document.getElementById('f_protocol').value;
    const pfv= document.getElementById('f_profile').value;
    const stv= document.getElementById('f_status').value;
    const zv = document.getElementById('f_zone').value;
    const av = document.getElementById('f_area').value;
    const cv = document.getElementById('f_conntype').value;
    if(sv)  params.set('server', sv);
    if(pv)  params.set('protocol', pv);
    if(pfv) params.set('profile', pfv);
    if(stv) params.set('status', stv);
    if(zv)  params.set('zone_id', zv);
    if(av)  params.set('area_id', av);
    if(cv)  params.set('connection_type', cv);

    try {
        const r = await fetch(dataUrl + '?' + params.toString());
        if(!r.ok) throw new Error('HTTP ' + r.status);
        allRows = await r.json();

        // Update stat cards
        document.getElementById('statOnline').textContent  = allRows.length;
        const total = parseInt(document.getElementById('statTotal').textContent.replace(/,/g,'')) || 0;
        document.getElementById('statOffline').textContent = Math.max(0, total - allRows.length);

        document.getElementById('liveLabel').textContent = 'Live';
        applyFilters();
    } catch(e) {
        document.getElementById('liveLabel').textContent = 'Error';
        document.getElementById('ocmBody').innerHTML = emptyRow('Failed to load data. Check server connectivity.');
        console.error(e);
    }
}

// ── LOCAL FILTER (search box) ─────────────────────────────────────────────────
function filterTable(q) {
    q = q.toLowerCase();
    filteredRows = allRows.filter(r =>
        Object.values(r).some(v => String(v).toLowerCase().includes(q))
    );
    currentPage = 1;
    renderTable();
}

function applyFilters() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    filteredRows = q
        ? allRows.filter(r => Object.values(r).some(v => String(v).toLowerCase().includes(q)))
        : [...allRows];
    currentPage = 1;
    renderTable();
}

// ── SORT ──────────────────────────────────────────────────────────────────────
const colKeys = ['customer_code','pppoe_username','full_name','phone','zone','area','','connection_type','server','profile','service','ip_address','online_status','uptime',''];
function sortTable(idx) {
    if(!colKeys[idx]) return;
    if(sortCol === idx) sortDir *= -1;
    else { sortCol = idx; sortDir = 1; }
    filteredRows.sort((a,b) => {
        const av = String(a[colKeys[idx]] ?? '').toLowerCase();
        const bv = String(b[colKeys[idx]] ?? '').toLowerCase();
        return av < bv ? -sortDir : av > bv ? sortDir : 0;
    });
    renderTable();
}

// ── RENDER ────────────────────────────────────────────────────────────────────
function renderTable() {
    const perPage = parseInt(document.getElementById('perPage').value) || 100;
    const start   = (currentPage - 1) * perPage;
    const page    = filteredRows.slice(start, start + perPage);
    const total   = filteredRows.length;

    if(total === 0) {
        document.getElementById('ocmBody').innerHTML = emptyRow('No online clients found matching the current filters.');
        document.getElementById('tableInfo').textContent = 'Showing 0 of 0 entries';
        document.getElementById('paginationArea').innerHTML = '';
        return;
    }

    let counter = start + 1;
    document.getElementById('ocmBody').innerHTML = page.map(r => buildRow(r, counter++)).join('');
    document.getElementById('tableInfo').textContent = `Showing ${start+1}–${Math.min(start+perPage,total)} of ${total} entries`;
    renderPagination(total, perPage);
}

function buildRow(r, n) {
    const cidLink = r.customer_id
        ? `<a href="<?= base_url('customers/view/') ?>${r.customer_id}" style="text-decoration:none;color:var(--blue);font-weight:600;">${r.customer_code}</a>`
        : `<span style="color:var(--text2);font-style:italic;font-size:12px;">—</span>`;

    const nameCell = r.customer_id
        ? `<a href="<?= base_url('customers/view/') ?>${r.customer_id}" style="text-decoration:none;color:inherit;font-weight:500;">${esc(r.full_name)}</a>`
        : `<span style="color:var(--text2);">${esc(r.full_name)}</span>`;

    const connBadge = `<span style="font-size:11px;font-weight:600;color:var(--text2);">${esc(r.connection_type?.toUpperCase())}</span>`;

    const connStatus = `<span class="badge badge-connected" style="background:rgba(16,185,129,.15);color:#10b981;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;">Connected</span>`;

    const actions = `<div class="action-set">
        <a href="<?= base_url('customers/view/') ?>${r.customer_id||''}" class="act-btn" title="View Customer"><i class="fa-solid fa-sitemap"></i></a>
        <a href="#" class="act-btn" title="Bandwidth Graph" onclick="showGraph('${esc(r.pppoe_username)}');return false;"><i class="fa-solid fa-chart-line"></i></a>
        <a href="#" class="act-btn danger" title="Disconnect" onclick="return confirmKick('${esc(r.pppoe_username)}','${r.nas_id}')"><i class="fa-solid fa-rotate-right"></i></a>
    </div>`;

    return `<tr>
        <td>${cidLink}</td>
        <td>
            <div style="font-size:12px;font-weight:600;color:var(--blue);">${esc(r.pppoe_username)}</div>
            <div style="font-size:10px;color:var(--text2);margin-top:2px;">${esc(r.ip_address)}</div>
        </td>
        <td>${nameCell}</td>
        <td style="font-size:12px;">${esc(r.phone)}</td>
        <td style="font-size:12px;">${esc(r.zone)}</td>
        <td style="font-size:12px;">${esc(r.area)}</td>
        <td style="font-size:12px;color:var(--text2);">—</td>
        <td>${connBadge}</td>
        <td style="font-size:12px;font-weight:500;">${esc(r.server)}</td>
        <td><span class="badge badge-blue" style="font-size:11px;">${esc(r.profile)}</span></td>
        <td style="font-size:12px;">${esc(r.service)}</td>
        <td><code style="background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:11px;">${esc(r.ip_address)}</code></td>
        <td>${connStatus}</td>
        <td style="font-size:12px;color:var(--green);font-weight:600;">${esc(r.uptime)}</td>
        <td style="font-size:11px;color:var(--text2);">—</td>
        <td style="text-align:center;">${actions}</td>
    </tr>`;
}

function emptyRow(msg) {
    return `<tr><td colspan="16" style="padding:48px;text-align:center;color:var(--text2);">
        <i class="fa-solid fa-circle-exclamation" style="font-size:28px;display:block;margin-bottom:10px;opacity:.35;"></i>
        ${msg}
    </td></tr>`;
}

function esc(v) {
    if(v == null || v === 'undefined' || v === 'null') return '—';
    return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── PAGINATION ────────────────────────────────────────────────────────────────
function renderPagination(total, perPage) {
    const pages = Math.ceil(total / perPage);
    if(pages <= 1) { document.getElementById('paginationArea').innerHTML = ''; return; }
    let html = '';
    const btnStyle = 'padding:4px 10px;border-radius:6px;border:1px solid var(--border);background:var(--bg3);color:var(--text2);cursor:pointer;font-size:12px;transition:all 0.15s;';
    const activeStyle = 'padding:4px 10px;border-radius:6px;border:1px solid var(--blue);background:rgba(59,130,246,.15);color:var(--blue);cursor:pointer;font-size:12px;font-weight:700;';
    for(let i=1;i<=Math.min(pages,10);i++) {
        html += `<button style="${i===currentPage?activeStyle:btnStyle}" onclick="goPage(${i})">${i}</button>`;
    }
    document.getElementById('paginationArea').innerHTML = html;
}
function goPage(p) { currentPage = p; renderTable(); }

// ── SYNC & KICK ───────────────────────────────────────────────────────────────
async function syncNow() {
    const icon = document.getElementById('syncIcon');
    icon.style.animation = 'spin 1s linear infinite';
    icon.style.display = 'inline-block';
    await fetchData();
    icon.style.animation = '';
}

function confirmKick(username, nasId) {
    if(!confirm(`Disconnect "${username}"?`)) return false;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url('network/pppoe-kick/') ?>' + nasId + '/' + encodeURIComponent(username);
    document.body.appendChild(form);
    form.submit();
    return false;
}

function showGraph(username) {
    alert('Bandwidth graph for: ' + username + '\n(Feature coming soon)');
}

// ── TABS ──────────────────────────────────────────────────────────────────────
function showTab(name) {
    document.querySelectorAll('.monitor-tab').forEach(t => t.classList.remove('active'));
    event.target.closest('.monitor-tab').classList.add('active');
    if(name !== 'online') {
        document.getElementById('ocmBody').innerHTML = emptyRow('This tab is under development. Only "Online Client Monitoring" is currently live.');
        document.getElementById('tableInfo').textContent = '';
        document.getElementById('paginationArea').innerHTML = '';
    } else fetchData();
    return false;
}

// ── FILTER CHANGE ─────────────────────────────────────────────────────────────
['f_server','f_protocol','f_profile','f_status','f_zone','f_area','f_conntype'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
        allRows = []; // force re-fetch with new server-side filters
        fetchData();
    });
});

// ── SPIN KEYFRAME ─────────────────────────────────────────────────────────────
const styleEl = document.createElement('style');
styleEl.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(styleEl);

// ── BOOT ───────────────────────────────────────────────────────────────────────
fetchData();
// Auto-refresh every 60 seconds
setInterval(fetchData, 60000);
</script>
