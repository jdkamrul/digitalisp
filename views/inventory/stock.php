<?php // views/inventory/stock.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Inventory Stock</h1><div class="page-breadcrumb"><i class="fa-solid fa-boxes-stacked" style="color:var(--blue)"></i> Inventory</div></div>
    <div style="display:flex;gap:8px;">
        <button class="btn btn-primary" onclick="document.getElementById('addItemModal').classList.add('open')"><i class="fa-solid fa-plus"></i> Add Item</button>
        <button class="btn btn-ghost" onclick="document.getElementById('stockInModal').classList.add('open')"><i class="fa-solid fa-arrow-down"></i> Stock In</button>
        <button class="btn btn-ghost" onclick="document.getElementById('stockOutModal').classList.add('open')"><i class="fa-solid fa-arrow-up"></i> Stock Out</button>
    </div>
</div>

<?php if(!empty($_SESSION['success'])): ?>
<div class="card fade-in" style="padding:12px 18px;margin-bottom:14px;border-color:rgba(34,197,94,.4);background:rgba(34,197,94,.08);">
    <span style="color:var(--green);"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></span>
    <?php unset($_SESSION['success']); ?>
</div>
<?php endif; ?>
<?php if(!empty($_SESSION['error'])): ?>
<div class="card fade-in" style="padding:12px 18px;margin-bottom:14px;border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.08);">
    <span style="color:var(--red);"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_SESSION['error']) ?></span>
    <?php unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<!-- Summary Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px;" class="fade-in">
    <?php
    $total    = count($items);
    $lowStock = count(array_filter($items, fn($i) => $i['quantity'] > 0 && $i['quantity'] <= ($i['minimum_stock']??5)));
    $outOfSt  = count(array_filter($items, fn($i) => $i['quantity'] <= 0));
    $totalVal = array_sum(array_map(fn($i) => $i['quantity']*($i['purchase_price']??0), $items));
    ?>
    <div class="card stat-card" style="padding:14px;"><div class="stat-label">Total Items</div><div class="stat-value" style="font-size:22px;"><?= $total ?></div></div>
    <div class="card stat-card" style="padding:14px;"><div class="stat-label">Low Stock</div><div class="stat-value" style="font-size:22px;color:var(--yellow);"><?= $lowStock ?></div></div>
    <div class="card stat-card" style="padding:14px;"><div class="stat-label">Out of Stock</div><div class="stat-value" style="font-size:22px;color:var(--red);"><?= $outOfSt ?></div></div>
    <div class="card stat-card" style="padding:14px;"><div class="stat-label">Total Value</div><div class="stat-value" style="font-size:22px;">৳<?= number_format($totalVal,0) ?></div></div>
</div>

<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>Item</th><th>Category</th><th>Warehouse</th><th>Qty</th><th>Unit Price</th><th>Total Value</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if(empty($items)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text2);">No items in inventory. Add your first item.</td></tr>
            <?php else: foreach($items as $item):
                $isLow = $item['quantity'] > 0 && $item['quantity'] <= ($item['minimum_stock']??5);
                $isOut = $item['quantity'] <= 0;
            ?>
            <tr>
                <td>
                    <div style="font-weight:600;"><?= htmlspecialchars($item['name']) ?></div>
                    <div style="font-size:11px;color:var(--text2);font-family:monospace;"><?= htmlspecialchars($item['code']??'') ?></div>
                </td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($item['category_name']??'—') ?></td>
                <td style="font-size:12px;color:var(--text2);"><?= htmlspecialchars($item['warehouse_name']??'—') ?></td>
                <td>
                    <span style="font-size:16px;font-weight:800;<?= $isOut?'color:var(--red)':($isLow?'color:var(--yellow)':'color:var(--green)') ?>"><?= number_format($item['quantity']) ?></span>
                    <div style="font-size:10px;color:var(--text2);"><?= htmlspecialchars($item['unit']??'pcs') ?></div>
                </td>
                <td>৳<?= number_format($item['purchase_price']??0,2) ?></td>
                <td style="font-weight:600;">৳<?= number_format($item['quantity']*($item['purchase_price']??0),0) ?></td>
                <td><?php
                    if($isOut) echo '<span class="badge badge-red">Out of Stock</span>';
                    elseif($isLow) echo '<span class="badge badge-yellow">Low Stock</span>';
                    else echo '<span class="badge badge-green">In Stock</span>';
                ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button class="btn btn-ghost btn-sm" onclick='editItem(<?= json_encode($item) ?>)' title="Edit"><i class="fa-solid fa-pen"></i></button>
                        <form method="POST" action="<?= base_url("inventory/stock/delete/{$item['id']}") ?>" onsubmit="return confirm('Deactivate this item?');" style="display:inline;">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Item Modal -->
<div class="modal-overlay" id="addItemModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-box" style="color:var(--blue);margin-right:8px;"></i>Add New Item</div>
            <button class="icon-btn" onclick="document.getElementById('addItemModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('inventory/stock/store') ?>">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="grid-column:1/-1;"><label class="form-label">Item Name <span style="color:var(--red)">*</span></label><input type="text" name="name" class="form-input" placeholder="e.g. ONU Indoor Unit" required></div>
                <div><label class="form-label">Item Code</label><input type="text" name="code" class="form-input" placeholder="SKU-001"></div>
                <div><label class="form-label">Unit</label><input type="text" name="unit" class="form-input" placeholder="pcs / meter / kg" value="pcs"></div>
                <div><label class="form-label">Category</label>
                    <select name="category_id" class="form-input">
                        <option value="">None</option>
                        <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Warehouse</label>
                    <select name="warehouse_id" class="form-input">
                        <option value="">None</option>
                        <?php foreach($warehouses as $w): ?><option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Initial Qty</label><input type="number" name="quantity" class="form-input" value="0" min="0"></div>
                <div><label class="form-label">Min Stock Alert</label><input type="number" name="minimum_stock" class="form-input" value="5" min="0"></div>
                <div><label class="form-label">Purchase Price (৳)</label><input type="number" name="purchase_price" class="form-input" step="0.01" value="0"></div>
                <div><label class="form-label">Sale Price (৳)</label><input type="number" name="sale_price" class="form-input" step="0.01" value="0"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addItemModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal-overlay" id="editItemModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--blue);margin-right:8px;"></i>Edit Item</div>
            <button class="icon-btn" onclick="document.getElementById('editItemModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('inventory/stock/update') ?>">
            <input type="hidden" name="id" id="edit_item_id">
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="grid-column:1/-1;"><label class="form-label">Item Name</label><input type="text" name="name" id="edit_item_name" class="form-input" required></div>
                <div><label class="form-label">Item Code</label><input type="text" name="code" id="edit_item_code" class="form-input"></div>
                <div><label class="form-label">Unit</label><input type="text" name="unit" id="edit_item_unit" class="form-input"></div>
                <div><label class="form-label">Category</label>
                    <select name="category_id" id="edit_item_cat" class="form-input">
                        <option value="">None</option>
                        <?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Warehouse</label>
                    <select name="warehouse_id" id="edit_item_wh" class="form-input">
                        <option value="">None</option>
                        <?php foreach($warehouses as $w): ?><option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Min Stock Alert</label><input type="number" name="minimum_stock" id="edit_item_min" class="form-input"></div>
                <div><label class="form-label">Purchase Price (৳)</label><input type="number" name="purchase_price" id="edit_item_pp" class="form-input" step="0.01"></div>
                <div><label class="form-label">Sale Price (৳)</label><input type="number" name="sale_price" id="edit_item_sp" class="form-input" step="0.01"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('editItemModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Stock In Modal -->
<div class="modal-overlay" id="stockInModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-arrow-down" style="color:var(--green);margin-right:8px;"></i>Stock In</div>
            <button class="icon-btn" onclick="document.getElementById('stockInModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('inventory/stock/in') ?>">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div><label class="form-label">Item <span style="color:var(--red)">*</span></label>
                    <select name="item_id" class="form-input" required>
                        <option value="">Select item</option>
                        <?php foreach($items as $i): ?><option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?> (<?= $i['quantity'] ?> in stock)</option><?php endforeach; ?>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div><label class="form-label">Quantity <span style="color:var(--red)">*</span></label><input type="number" name="quantity" class="form-input" min="1" required></div>
                    <div><label class="form-label">Unit Price (৳)</label><input type="number" name="unit_price" class="form-input" step="0.01"></div>
                </div>
                <div><label class="form-label">Notes</label><input type="text" name="notes" class="form-input" placeholder="Purchase ref / supplier"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('stockInModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-plus"></i> Add Stock</button>
            </div>
        </form>
    </div>
</div>

<!-- Stock Out Modal -->
<div class="modal-overlay" id="stockOutModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-arrow-up" style="color:var(--red);margin-right:8px;"></i>Stock Out</div>
            <button class="icon-btn" onclick="document.getElementById('stockOutModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('inventory/stock/out') ?>">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div><label class="form-label">Item <span style="color:var(--red)">*</span></label>
                    <select name="item_id" class="form-input" required>
                        <option value="">Select item</option>
                        <?php foreach($items as $i): if($i['quantity']<=0) continue; ?><option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?> (<?= $i['quantity'] ?> available)</option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Quantity <span style="color:var(--red)">*</span></label><input type="number" name="quantity" class="form-input" min="1" required></div>
                <div><label class="form-label">Notes / Reason</label><input type="text" name="notes" class="form-input" placeholder="Installation / transfer / damaged"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('stockOutModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-minus"></i> Remove Stock</button>
            </div>
        </form>
    </div>
</div>

<script>
function editItem(item) {
    document.getElementById('edit_item_id').value   = item.id;
    document.getElementById('edit_item_name').value = item.name;
    document.getElementById('edit_item_code').value = item.code || '';
    document.getElementById('edit_item_unit').value = item.unit || 'pcs';
    document.getElementById('edit_item_cat').value  = item.category_id || '';
    document.getElementById('edit_item_wh').value   = item.warehouse_id || '';
    document.getElementById('edit_item_min').value  = item.minimum_stock || 5;
    document.getElementById('edit_item_pp').value   = item.purchase_price || 0;
    document.getElementById('edit_item_sp').value   = item.sale_price || 0;
    document.getElementById('editItemModal').classList.add('open');
}
</script>
