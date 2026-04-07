<?php // views/inventory/purchases.php ?>
<div class="page-header fade-in">
    <div><h1 class="page-title">Purchase Orders</h1><div class="page-breadcrumb"><i class="fa-solid fa-truck" style="color:var(--purple)"></i> Inventory</div></div>
    <button class="btn btn-primary" onclick="document.getElementById('addPoModal').classList.add('open')"><i class="fa-solid fa-plus"></i> New Purchase Order</button>
</div>
<div class="card fade-in" style="overflow:hidden;">
    <table class="data-table">
        <thead><tr><th>PO Number</th><th>Supplier</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if(empty($pos)): ?>
            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text2);">No purchase orders yet.</td></tr>
            <?php else: foreach($pos as $po): ?>
            <tr>
                <td style="font-family:monospace;font-weight:600;"><?= htmlspecialchars($po['po_number']) ?></td>
                <td><?= htmlspecialchars($po['supplier_name']??'—') ?></td>
                <td style="font-size:12px;"><?= date('d M Y',strtotime($po['order_date'])) ?></td>
                <td style="font-weight:700;">৳<?= number_format($po['total_amount'],2) ?></td>
                <td><?php $psc=['ordered'=>'badge-yellow','received'=>'badge-green','partial'=>'badge-blue','cancelled'=>'badge-gray'];
                    echo '<span class="badge '.($psc[$po['status']]??'badge-gray').'">'.ucfirst($po['status']).'</span>'; ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <?php if($po['status']==='ordered'): ?>
                        <form method="POST" action="<?= base_url("inventory/purchases/receive/{$po['id']}") ?>" onsubmit="return confirm('Mark as received?');" style="display:inline;">
                            <button type="submit" class="btn btn-success btn-sm" title="Mark Received"><i class="fa-solid fa-check"></i> Receive</button>
                        </form>
                        <?php endif; ?>
                        <?php if(!in_array($po['status'],['received','cancelled'])): ?>
                        <form method="POST" action="<?= base_url("inventory/purchases/delete/{$po['id']}") ?>" onsubmit="return confirm('Cancel this PO?');" style="display:inline;">
                            <button type="submit" class="btn btn-danger btn-sm" title="Cancel"><i class="fa-solid fa-ban"></i></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="addPoModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fa-solid fa-truck" style="color:var(--purple);margin-right:8px;"></i>New Purchase Order</div>
            <button class="icon-btn" onclick="document.getElementById('addPoModal').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="<?= base_url('inventory/purchases/store') ?>">
            <div class="modal-body" style="display:grid;gap:12px;">
                <div><label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-input" required>
                        <option value="">Select supplier</option>
                        <?php foreach($suppliers as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="form-label">Total Amount (৳)</label><input type="number" name="total_amount" class="form-input" step="0.01" required></div>
                <div><label class="form-label">Notes</label><textarea name="notes" class="form-input" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('addPoModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create PO</button>
            </div>
        </form>
    </div>
</div>
