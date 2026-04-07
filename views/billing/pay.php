<?php
// views/billing/pay.php
?>
<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Record Payment</h1>
        <div class="page-breadcrumb">
            <a href="<?= base_url('billing/invoices') ?>" style="color:var(--blue);text-decoration:none;">Invoices</a>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i> Pay Invoice <?= htmlspecialchars($invoice['invoice_number']) ?>
        </div>
    </div>
    <a href="<?= base_url('billing/invoices') ?>" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div style="max-width:640px;margin:0 auto;display:flex;flex-direction:column;gap:16px;">

    <!-- Invoice Summary -->
    <div class="card fade-in" style="padding:20px;">
        <div style="font-size:13px;font-weight:700;color:var(--text2);margin-bottom:14px;"><i class="fa-solid fa-file-invoice" style="color:var(--blue);margin-right:8px;"></i>Invoice Details</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div><div style="font-size:11px;color:var(--text2);">Customer</div><div style="font-weight:700;"><?= htmlspecialchars($invoice['full_name']) ?></div><div style="font-size:11px;font-family:monospace;color:var(--text2);"><?= $invoice['customer_code'] ?></div></div>
            <div><div style="font-size:11px;color:var(--text2);">Invoice Number</div><div style="font-weight:600;font-family:monospace;"><?= $invoice['invoice_number'] ?></div></div>
            <div><div style="font-size:11px;color:var(--text2);">Billing Month</div><div style="font-weight:600;"><?= date('F Y',strtotime($invoice['billing_month'])) ?></div></div>
            <div><div style="font-size:11px;color:var(--text2);">Due Date</div><div style="font-weight:600;"><?= $invoice['due_date'] ? date('d M Y',strtotime($invoice['due_date'])) : '—' ?></div></div>
        </div>
        <div style="border-top:1px solid var(--border);margin-top:14px;padding-top:14px;display:grid;grid-template-columns:repeat(3,1fr);gap:12px;text-align:center;">
            <div><div style="font-size:11px;color:var(--text2);">Invoice Total</div><div style="font-size:18px;font-weight:800;">৳<?= number_format($invoice['total'],2) ?></div></div>
            <div><div style="font-size:11px;color:var(--text2);">Already Paid</div><div style="font-size:18px;font-weight:800;color:var(--green);">৳<?= number_format($invoice['paid_amount'],2) ?></div></div>
            <div><div style="font-size:11px;color:var(--text2);">Remaining Due</div><div style="font-size:18px;font-weight:800;color:var(--red);">৳<?= number_format($invoice['due_amount'],2) ?></div></div>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="card fade-in" style="padding:20px;">
        <div style="font-size:13px;font-weight:700;color:var(--text2);margin-bottom:16px;"><i class="fa-solid fa-money-bill-wave" style="color:var(--green);margin-right:8px;"></i>Payment Details</div>
        <form method="POST" action="<?= base_url("billing/pay/{$invoice['id']}") ?>">
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <label class="form-label">Payment Amount (৳) <span style="color:var(--red)">*</span></label>
                    <input type="number" name="amount" class="form-input" value="<?= $invoice['due_amount'] ?>" step="0.01" min="1" max="<?= $invoice['due_amount'] ?>" id="payAmount" required oninput="updateSummary()">
                    <div style="display:flex;gap:8px;margin-top:8px;">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="setAmount(<?= $invoice['due_amount'] ?>)">Full Amount</button>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="setAmount(<?= round($invoice['due_amount']/2,2) ?>)">Half</button>
                    </div>
                </div>
                <div>
                    <label class="form-label">Payment Method</label>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;" id="methodSelect">
                        <?php foreach(['cash'=>'💵 Cash','mobile_banking'=>'📱 Mobile Banking','bank_transfer'=>'🏦 Bank Transfer','online'=>'🌐 Online','other'=>'💳 Other'] as $val=>$label): ?>
                        <label style="cursor:pointer;">
                            <input type="radio" name="payment_method" value="<?= $val ?>" class="hidden" <?= $val==='cash'?'checked':'' ?> onchange="toggleMobileBanking('<?= $val ?>')">
                            <div class="method-btn" style="padding:10px;border-radius:10px;border:2px solid var(--border);text-align:center;font-size:12px;font-weight:600;transition:all .2s;" data-value="<?= $val ?>">
                                <?= $label ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="mobileRef" style="display:none;">
                    <label class="form-label">Transaction Reference (bKash/Nagad ID)</label>
                    <input type="text" name="mobile_banking_ref" class="form-input" placeholder="Transaction ID">
                </div>
                <div>
                    <label class="form-label">Payment Date</label>
                    <input type="datetime-local" name="payment_date" class="form-input" value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                <div>
                    <label class="form-label">Notes (optional)</label>
                    <input type="text" name="notes" class="form-input" placeholder="Any additional notes">
                </div>
                <!-- Summary -->
                <div style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);border-radius:12px;padding:16px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px;">
                        <span style="color:var(--text2);">Amount to pay</span>
                        <strong id="sumAmount">৳<?= number_format($invoice['due_amount'],2) ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                        <span style="color:var(--text2);">Remaining after payment</span>
                        <strong id="sumRemaining">৳0.00</strong>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="justify-content:center;padding:13px;font-size:15px;">
                    <i class="fa-solid fa-check-circle"></i> Confirm Payment
                </button>
            </div>
        </form>
    </div>
</div>

<style>
input[type="radio"]:checked + .method-btn { border-color:var(--blue)!important; background:rgba(59,130,246,0.1); color:var(--blue); }
</style>
<script>
function setAmount(v) { document.getElementById('payAmount').value=v; updateSummary(); }
function updateSummary() {
    const a = parseFloat(document.getElementById('payAmount').value)||0;
    const due = <?= $invoice['due_amount'] ?>;
    document.getElementById('sumAmount').textContent = '৳' + a.toFixed(2);
    document.getElementById('sumRemaining').textContent = '৳' + Math.max(0,due-a).toFixed(2);
}
function toggleMobileBanking(val) {
    document.getElementById('mobileRef').style.display = val==='mobile_banking'?'block':'none';
}
updateSummary();
</script>
