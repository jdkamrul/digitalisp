<?php // views/workorders/create.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Create Work Order</h1><div class="page-breadcrumb"><a href="<?= base_url('workorders') ?>" style="color:var(--blue);text-decoration:none;">Work Orders</a> › New</div></div>
    <a href="<?= base_url('workorders') ?>" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>
<form method="POST" action="<?= base_url('workorders/store') ?>">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card fade-in" style="padding:20px;">
            <div style="font-size:14px;font-weight:700;margin-bottom:16px;"><i class="fa-solid fa-file-lines" style="color:var(--blue);margin-right:8px;"></i>Work Order Info</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <label class="form-label">Type</label>
                    <select name="type" class="form-input">
                        <?php foreach(['new_connection'=>'New Connection','relocation'=>'Relocation','repair'=>'Repair','upgrade'=>'Package Upgrade','suspension'=>'Suspension','disconnection'=>'Disconnection','other'=>'Other'] as $v=>$l): ?>
                        <option value="<?=$v?>"><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-input">
                        <option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option><option value="urgent">Urgent</option>
                    </select>
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">Title <span style="color:var(--red)">*</span></label>
                    <input type="text" name="title" class="form-input" placeholder="Brief description of work" required>
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="3" placeholder="Detailed description..."></textarea>
                </div>
                <div style="grid-column:1/-1;">
                    <label class="form-label">Work Address</label>
                    <input type="text" name="address" class="form-input" placeholder="Installation/service address">
                </div>
                <div>
                    <label class="form-label">Scheduled Date</label>
                    <input type="date" name="scheduled_date" class="form-input" min="<?= date('Y-m-d') ?>">
                </div>
                <div>
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-input" required>
                        <?php foreach($branches as $b): ?><option value="<?= $b['id'] ?>" <?= $b['id']==$_SESSION['branch_id']?'selected':'' ?>><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card fade-in" style="padding:18px;position:relative;">
            <div style="font-size:13px;font-weight:700;margin-bottom:12px;"><i class="fa-solid fa-user" style="color:var(--green);margin-right:6px;"></i>Customer (Optional)</div>
            <div style="position:relative;">
                <input type="text" id="customerSearch" class="form-input" placeholder="Search customer name, ID, phone..." autocomplete="off">
                <input type="hidden" name="customer_id" id="selectedCustomerId" value="<?= $_GET['customer_id'] ?? '' ?>">
                <div id="customerResults" class="search-results-panel" style="width:100%;"></div>
            </div>
            <div id="selectedCustomerDisplay" style="margin-top:10px; display:none;">
                <div style="padding:10px; border-radius:10px; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <div style="font-size:12px; font-weight:700;" id="dispName"></div>
                        <div style="font-size:11px; color:var(--text2);" id="dispMeta"></div>
                    </div>
                    <button type="button" class="icon-btn btn-sm" onclick="clearCustomer()"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
        </div>

        <script>
        let custTimer;
        const custInput = document.getElementById('customerSearch');
        const custPanel = document.getElementById('customerResults');
        const custId    = document.getElementById('selectedCustomerId');
        const custDisp  = document.getElementById('selectedCustomerDisplay');

        custInput.addEventListener('input', (e) => {
            clearTimeout(custTimer);
            const q = e.target.value.trim();
            if (q.length < 2) { custPanel.classList.remove('open'); return; }
            custTimer = setTimeout(() => searchCustomers(q), 300);
        });

        async function searchCustomers(q) {
            try {
                const r = await fetch('<?= base_url('customers/search') ?>?q=' + encodeURIComponent(q));
                const data = await r.json();
                if (data.length === 0) {
                    custPanel.innerHTML = '<div style="padding:12px;text-align:center;font-size:12px;color:var(--text2);">No matches</div>';
                } else {
                    custPanel.innerHTML = data.map(c => `
                        <div class="search-result-item" onclick="selectCustomer(${c.id}, '${esc(c.full_name)}', '${esc(c.customer_code)}')">
                            <div class="sr-icon"><i class="fa-solid fa-user"></i></div>
                            <div class="sr-info">
                                <div class="sr-title">${esc(c.full_name)}</div>
                                <div class="sr-meta">${esc(c.customer_code)} • ${esc(c.phone)}</div>
                            </div>
                        </div>
                    `).join('');
                }
                custPanel.classList.add('open');
            } catch(e) { console.error(e); }
        }

        function selectCustomer(id, name, code) {
            custId.value = id;
            document.getElementById('dispName').textContent = name;
            document.getElementById('dispMeta').textContent = code;
            custDisp.style.display = 'block';
            custInput.value = '';
            custPanel.classList.remove('open');
            custInput.placeholder = 'Change customer...';
        }

        function clearCustomer() {
            custId.value = '';
            custDisp.style.display = 'none';
            custInput.placeholder = 'Search customer name, ID, phone...';
        }

        function esc(str) { return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

        // Click outside
        document.addEventListener('click', (e) => { if (!e.target.closest('#customerSearch')) custPanel.classList.remove('open'); });
        
        // Initial load if coming from ?customer_id=
        <?php if (!empty($_GET['customer_id'])): 
            $c = $this->db->fetchOne("SELECT full_name, customer_code FROM customers WHERE id=?", [$_GET['customer_id']]);
            if ($c): ?>
            selectCustomer(<?= $_GET['customer_id'] ?>, '<?= addslashes($c['full_name']) ?>', '<?= addslashes($c['customer_code']) ?>');
            <?php endif; ?>
        <?php endif; ?>
        </script>
        <div class="card fade-in" style="padding:18px;">
            <div style="font-size:13px;font-weight:700;margin-bottom:12px;"><i class="fa-solid fa-helmet-safety" style="color:var(--yellow);margin-right:6px;"></i>Technician (Optional)</div>
            <select name="technician_id" class="form-input">
                <option value="">Unassigned</option>
                <?php foreach($technicians as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="card fade-in" style="padding:18px;">
            <label class="form-label">Zone</label>
            <select name="zone_id" class="form-input">
                <option value="">Select Zone</option>
                <?php foreach($zones as $z): ?><option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;"><i class="fa-solid fa-plus"></i> Create Work Order</button>
    </div>
</div>
</form>
