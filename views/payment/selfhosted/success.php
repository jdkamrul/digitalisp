<?php // views/payment/selfhosted/success.php ?>
<div class="page-header fade-in">
    <h1 class="page-title">Payment Successful</h1>
</div>

<div style="max-width:520px;margin:40px auto;" class="fade-in">
    <div class="card" style="padding:40px;text-align:center;">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(34,197,94,0.12);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <i class="fa-solid fa-circle-check" style="font-size:36px;color:var(--green);"></i>
        </div>
        <div style="font-size:22px;font-weight:800;margin-bottom:8px;">Payment Received</div>
        <div style="color:var(--text2);font-size:14px;margin-bottom:24px;">
            Your payment has been processed successfully via Self-Hosted PipraPay.
        </div>

        <?php if ($transactionId): ?>
        <div style="background:var(--bg2);border-radius:10px;padding:16px;margin-bottom:24px;text-align:left;">
            <div style="font-size:11px;color:var(--text2);font-weight:700;text-transform:uppercase;margin-bottom:6px;">Transaction ID</div>
            <div style="font-family:monospace;font-size:14px;font-weight:600;"><?= htmlspecialchars($transactionId) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($invoice): ?>
        <div style="background:var(--bg2);border-radius:10px;padding:16px;margin-bottom:24px;text-align:left;display:grid;gap:8px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;">
                <span style="color:var(--text2);">Invoice</span>
                <span style="font-weight:600;"><?= htmlspecialchars($invoice['invoice_number']) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;">
                <span style="color:var(--text2);">Amount Paid</span>
                <span style="font-weight:700;color:var(--green);">৳<?= number_format($invoice['paid_amount'], 2) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;">
                <span style="color:var(--text2);">Status</span>
                <span class="badge <?= $invoice['status'] === 'paid' ? 'badge-green' : 'badge-blue' ?>"><?= ucfirst($invoice['status']) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:10px;justify-content:center;">
            <?php if ($invoiceId): ?>
            <a href="<?= base_url("billing/invoice/{$invoiceId}") ?>" class="btn btn-primary">
                <i class="fa-solid fa-file-invoice"></i> View Invoice
            </a>
            <?php endif; ?>
            <a href="<?= base_url('billing/invoices') ?>" class="btn btn-ghost">
                <i class="fa-solid fa-list"></i> All Invoices
            </a>
        </div>
    </div>
</div>
