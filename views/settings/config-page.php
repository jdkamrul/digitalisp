<?php // views/settings/config-page.php
// $type, $pageTitle, $items, $currentSubPage, $successMsg, $errorMsg are set by controller
$isReadOnly = in_array($currentSubPage, ['zone', 'package']); // managed elsewhere
?>

<div class="page-header fade-in">
    <div>
        <h1 class="page-title">
            <?= htmlspecialchars($pageTitle) ?>
        </h1>
        <div class="page-breadcrumb">
            <i class="fa-solid fa-gear" style="color:var(--blue)"></i>
            Configuration
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
            <?= htmlspecialchars($pageTitle) ?>
        </div>
    </div>
    <?php if (!$isReadOnly): ?>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">
        <i class="fa-solid fa-plus"></i> Add <?= htmlspecialchars($pageTitle) ?>
    </button>
    <?php elseif ($currentSubPage === 'zone'): ?>
    <a href="<?= base_url('settings#branches') ?>" class="btn btn-primary">
        <i class="fa-solid fa-map-location-dot"></i> Manage Zones
    </a>
    <?php elseif ($currentSubPage === 'package'): ?>
    <a href="<?= base_url('settings#packages') ?>" class="btn btn-primary">
        <i class="fa-solid fa-wifi"></i> Manage Packages
    </a>
    <?php endif; ?>
</div>

<?php if ($successMsg): ?>
<div class="card fade-in" style="padding:12px 18px;margin-bottom:14px;border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.08);">
    <span style="color:var(--green);"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMsg) ?></span>
</div>
<?php endif; ?>
<?php if ($errorMsg): ?>
<div class="card fade-in" style="padding:12px 18px;margin-bottom:14px;border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.08);">
    <span style="color:var(--red);"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?></span>
</div>
<?php endif; ?>

<div class="card fade-in" style="overflow:hidden;">
    <!-- Table controls -->
    <div style="padding:14px 18px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);">
        <div style="font-size:13px;color:var(--text2);">
            SHOW
            <select id="perPage" class="form-input" style="width:70px;padding:5px 8px;margin:0 6px;" onchange="filterTable()">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
            </select>
            ENTRIES
        </div>
        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text2);">
            SEARCH:
            <input type="text" id="searchInput" class="form-input" style="width:200px;padding:6px 10px;" placeholder="Search..." oninput="filterTable()">
        </div>
    </div>

    <table class="data-table" id="configTable">
        <thead>
            <tr style="background:linear-gradient(90deg,#1e3a8a,#1e40af);">
                <th style="color:#fff;width:70px;text-align:center;">#</th>
                <th style="color:#fff;">Name</th>
                <th style="color:#fff;">Details</th>
                <?php if (!$isReadOnly): ?>
                <th style="color:#fff;width:120px;text-align:center;">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody id="configBody">
            <?php if (empty($items)): ?>
            <tr>
                <td colspan="<?= $isReadOnly ? 3 : 4 ?>" style="text-align:center;padding:40px;color:var(--text2);">
                    <i class="fa-solid fa-inbox" style="font-size:28px;display:block;margin-bottom:10px;opacity:.4;"></i>
                    No <?= htmlspecialchars($pageTitle) ?> entries yet.
                    <?php if (!$isReadOnly): ?>
                    <br><button class="btn btn-primary btn-sm" style="margin-top:10px;" onclick="document.getElementById('addModal').classList.add('open')">
                        <i class="fa-solid fa-plus"></i> Add First Entry
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php else: $sl = 1; foreach ($items as $item): ?>
            <tr data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>">
                <td style="text-align:center;font-weight:600;color:var(--text2);"><?= $sl++ ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($item['name']) ?></td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($item['details'] ?? '') ?></td>
                <?php if (!$isReadOnly): ?>
                <td style="text-align:center;">
                    <div style="display:inline-flex;gap:6px;">
                        <button class="btn btn-ghost btn-sm"
                            onclick='openEdit(<?= $item["id"] ?>, <?= json_encode($item["name"]) ?>, <?= json_encode($item["details"] ?? "") ?>)'
                            title="Edit">
                            <i class="fa-solid fa-pen-to-square" style="color:var(--green);"></i>
                        </button>
                        <form method="POST" action="<?= base_url("settings/config/delete/{$item['id']}") ?>"
                              onsubmit="return confirm('Delete \'<?= addslashes($item['name']) ?>\'?');"
                              style="display:inline;">
                            <button type="submit" class="btn btn-ghost btn-sm" title="Delete">
                                <i class="fa-solid fa-trash" style="color:var(--red);"></i>
                            </button>
                        </form>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div style="padding:12px 18px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);font-size:13px;color:var(--text2);">
        <span id="showingInfo">Showing <?= count($items) ?> of <?= count($items) ?> entries</span>
        <div id="paginationArea" style="display:flex;gap:4px;"></div>
    </div>
</div>

<?php if (!$isReadOnly): ?>
<!-- ── ADD MODAL ── -->
<div class="modal-overlay" id="addModal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-plus" style="color:var(--blue);margin-right:8px;"></i>
                Add <?= htmlspecialchars($pageTitle) ?>
            </div>
            <button class="icon-btn" onclick="document.getElementById('addModal').classList.remove('open')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="<?= base_url('settings/config/store') ?>">
            <input type="hidden" name="type" value="<?= htmlspecialchars($currentSubPage) ?>">
            <div class="modal-body" style="display:grid;gap:14px;">
                <div>
                    <label class="form-label">Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="Enter name..." required autofocus>
                </div>
                <div>
                    <label class="form-label">Details <span style="font-size:11px;color:var(--text2);">(optional)</span></label>
                    <input type="text" name="details" class="form-input" placeholder="Additional info...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── EDIT MODAL ── -->
<div class="modal-overlay" id="editModal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-pen" style="color:var(--blue);margin-right:8px;"></i>
                Edit <?= htmlspecialchars($pageTitle) ?>
            </div>
            <button class="icon-btn" onclick="document.getElementById('editModal').classList.remove('open')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="<?= base_url('settings/config/update') ?>">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="type" value="<?= htmlspecialchars($currentSubPage) ?>">
            <div class="modal-body" style="display:grid;gap:14px;">
                <div>
                    <label class="form-label">Name <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Details <span style="font-size:11px;color:var(--text2);">(optional)</span></label>
                    <input type="text" name="details" id="edit_details" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
// ── Open edit modal ──
function openEdit(id, name, details) {
    document.getElementById('edit_id').value      = id;
    document.getElementById('edit_name').value    = name;
    document.getElementById('edit_details').value = details;
    document.getElementById('editModal').classList.add('open');
}

// ── Client-side search + pagination ──
let allRows = Array.from(document.querySelectorAll('#configBody tr[data-name]'));
let currentPage = 1;

function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allRows.filter(r => r.getAttribute('data-name').includes(q));
    currentPage = 1;
    renderPage(filtered);
}

function renderPage(rows) {
    const perPage = parseInt(document.getElementById('perPage').value) || 10;
    const total   = rows.length;
    const start   = (currentPage - 1) * perPage;
    const end     = start + perPage;

    allRows.forEach(r => r.style.display = 'none');
    rows.slice(start, end).forEach(r => r.style.display = '');

    document.getElementById('showingInfo').textContent =
        total === 0 ? 'No entries found' :
        `Showing ${start + 1}–${Math.min(end, total)} of ${total} entries`;

    // Pagination buttons
    const pages = Math.ceil(total / perPage);
    const area  = document.getElementById('paginationArea');
    area.innerHTML = '';
    if (pages <= 1) return;

    const btn = (label, page, active) => {
        const b = document.createElement('button');
        b.textContent = label;
        b.className = 'btn btn-sm ' + (active ? 'btn-primary' : 'btn-ghost');
        b.style.padding = '4px 10px';
        if (active) b.disabled = true;
        b.onclick = () => { currentPage = page; renderPage(rows); };
        area.appendChild(b);
    };

    btn('‹', Math.max(1, currentPage - 1), false);
    for (let i = 1; i <= pages; i++) btn(i, i, i === currentPage);
    btn('›', Math.min(pages, currentPage + 1), false);
}

// Init
filterTable();
</script>
