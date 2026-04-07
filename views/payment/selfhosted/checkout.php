<?php
/**
 * Self-Hosted PipraPay Checkout View
 * Rendered by SelfHostedPipraPayController::checkout() — $session and $paymentMethods are already set.
 */

$methodLabels = [
    'bkash'  => ['label' => 'bKash',  'color' => '#e2136e', 'icon' => 'fa-mobile-screen-button'],
    'nagad'  => ['label' => 'Nagad',  'color' => '#f7941d', 'icon' => 'fa-mobile-screen-button'],
    'rocket' => ['label' => 'Rocket', 'color' => '#8b5cf6', 'icon' => 'fa-mobile-screen-button'],
    'upay'   => ['label' => 'Upay',   'color' => '#059669', 'icon' => 'fa-mobile-screen-button'],
    'bank'   => ['label' => 'Bank',   'color' => '#374151', 'icon' => 'fa-building-columns'],
];
?>
<style>
.checkout-wrap { max-width:680px; margin:40px auto; }
.method-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(110px,1fr)); gap:10px; margin-bottom:20px; }
.method-card {
    border:2px solid var(--border);
    border-radius:12px;
    padding:14px 10px;
    text-align:center;
    cursor:pointer;
    transition:all .2s;
    background:var(--bg2);
}
.method-card:hover { border-color:var(--blue); background:var(--bg3); }
.method-card.selected { border-color:var(--blue); background:rgba(37,99,235,0.06); box-shadow:0 0 0 3px rgba(37,99,235,0.15); }
.method-card i { font-size:22px; margin-bottom:6px; display:block; }
.method-card span { font-size:12px; font-weight:700; }
.checkout-timer { font-size:12px; color:var(--text2); display:flex; align-items:center; gap:6px; }
</style>

<div class="page-header fade-in">
    <div>
        <h1 class="page-title">Complete Payment</h1>
        <div class="page-breadcrumb">
            <span style="color:var(--text2);">Self-Hosted PipraPay Checkout</span>
        </div>
    </div>
    <div class="checkout-timer">
        <i class="fa-solid fa-clock"></i>
        Session expires in <strong id="countdown">--:--</strong>
    </div>
</div>

<div class="checkout-wrap fade-in">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <!-- Order Summary -->
        <div class="card" style="padding:20px;">
            <div style="font-size:13px;font-weight:700;color:var(--text2);margin-bottom:14px;text-transform:uppercase;letter-spacing:.5px;">Order Summary</div>
            <div style="display:grid;gap:10px;font-size:13px;">
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--text2);">Description</span>
                    <span style="font-weight:600;text-align:right;max-width:60%;"><?= htmlspecialchars($session['description']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--text2);">Order ID</span>
                    <span style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($session['order_id']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--text2);">Customer</span>
                    <span style="font-weight:600;"><?= htmlspecialchars($session['customer_name']) ?></span>
                </div>
                <?php if ($session['customer_phone']): ?>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:var(--text2);">Phone</span>
                    <span><?= htmlspecialchars($session['customer_phone']) ?></span>
                </div>
                <?php endif; ?>
                <div style="border-top:1px solid var(--border);padding-top:10px;display:flex;justify-content:space-between;font-size:18px;font-weight:900;color:var(--blue);">
                    <span>Total</span>
                    <span>৳<?= number_format($session['amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Security Info -->
        <div class="card" style="padding:20px;display:flex;flex-direction:column;justify-content:center;gap:12px;">
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
                <i class="fa-solid fa-shield-halved" style="color:var(--green);font-size:20px;"></i>
                <div>
                    <div style="font-weight:700;">Secure Payment</div>
                    <div style="font-size:11px;color:var(--text2);">Your transaction is protected</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
                <i class="fa-solid fa-lock" style="color:var(--blue);font-size:20px;"></i>
                <div>
                    <div style="font-weight:700;">Encrypted Session</div>
                    <div style="font-size:11px;color:var(--text2);">Session ID: <?= substr(htmlspecialchars($session['session_id']), 0, 16) ?>…</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
                <i class="fa-solid fa-clock-rotate-left" style="color:var(--yellow);font-size:20px;"></i>
                <div>
                    <div style="font-weight:700;">Session Valid Until</div>
                    <div style="font-size:11px;color:var(--text2);"><?= date('d M Y H:i', strtotime($session['expires_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="card" style="padding:24px;">
        <div style="font-size:15px;font-weight:700;margin-bottom:18px;">Select Payment Method</div>

        <form id="paymentForm" method="POST" action="<?= base_url('payment/selfhosted/process/' . htmlspecialchars($session['session_id'])) ?>">
            <input type="hidden" name="amount" value="<?= htmlspecialchars($session['amount']) ?>">

            <!-- Method Cards -->
            <div class="method-grid">
                <?php foreach ($paymentMethods as $method):
                    $info = $methodLabels[$method] ?? ['label' => ucfirst($method), 'color' => '#64748b', 'icon' => 'fa-credit-card'];
                ?>
                <label class="method-card" for="method_<?= $method ?>">
                    <input type="radio" name="payment_method" id="method_<?= $method ?>" value="<?= htmlspecialchars($method) ?>"
                           style="display:none;" onchange="onMethodChange('<?= $method ?>')">
                    <i class="fa-solid <?= $info['icon'] ?>" style="color:<?= $info['color'] ?>;"></i>
                    <span><?= $info['label'] ?></span>
                </label>
                <?php endforeach; ?>
            </div>

            <!-- Payment Details (shown after method selection) -->
            <div id="paymentDetails" style="display:none;border-top:1px solid var(--border);padding-top:20px;">
                <div style="font-size:14px;font-weight:700;margin-bottom:14px;" id="methodTitle">Payment Details</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label class="form-label">Account Number <span style="color:var(--red)">*</span></label>
                        <input type="text" name="account_number" id="account_number" class="form-input"
                               placeholder="01XXXXXXXXX" maxlength="30" required>
                    </div>
                    <div>
                        <label class="form-label">Account Holder Name <span style="color:var(--red)">*</span></label>
                        <input type="text" name="account_holder" id="account_holder" class="form-input"
                               placeholder="Name on account" maxlength="100" required>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label class="form-label">Transaction Reference / TrxID</label>
                        <input type="text" name="reference" id="reference" class="form-input"
                               placeholder="e.g. 8N5K2A1B3C (from your payment app)" maxlength="100">
                        <div style="font-size:11px;color:var(--text2);margin-top:4px;">
                            Complete the payment in your mobile banking app first, then enter the transaction ID here.
                        </div>
                    </div>
                </div>

                <div style="margin-top:16px;background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);border-radius:8px;padding:12px;font-size:12px;color:var(--text2);">
                    <i class="fa-solid fa-circle-info" style="color:var(--green);margin-right:6px;"></i>
                    Send <strong>৳<?= number_format($session['amount'], 2) ?></strong> to the merchant account, then fill in the details above and click Complete Payment.
                </div>

                <div style="display:flex;gap:10px;margin-top:18px;">
                    <button type="submit" class="btn btn-success" id="submitBtn" style="flex:1;">
                        <i class="fa-solid fa-check"></i> Complete Payment
                    </button>
                    <a href="<?= htmlspecialchars($session['cancel_url'] ?: base_url('billing/invoices')) ?>" class="btn btn-ghost">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                </div>
            </div>

            <?php if (empty($paymentMethods)): ?>
            <div style="text-align:center;padding:24px;color:var(--text2);">No payment methods configured.</div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
const methodLabels = <?= json_encode(array_map(fn($m) => $m['label'], $methodLabels)) ?>;

function onMethodChange(method) {
    // Update card selection
    document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`label[for="method_${method}"]`).classList.add('selected');

    // Show details panel
    document.getElementById('paymentDetails').style.display = 'block';
    document.getElementById('methodTitle').textContent = (methodLabels[method] || method) + ' Payment Details';

    // Adjust placeholder
    const acct = document.getElementById('account_number');
    if (['bkash','nagad','rocket','upay'].includes(method)) {
        acct.placeholder = '01XXXXXXXXX';
        acct.pattern = '01[0-9]{9}';
    } else {
        acct.placeholder = 'Account number';
        acct.removeAttribute('pattern');
    }
}

// Countdown timer
(function() {
    const expires = new Date('<?= $session['expires_at'] ?>').getTime();
    const el = document.getElementById('countdown');
    const tick = () => {
        const diff = Math.max(0, Math.floor((expires - Date.now()) / 1000));
        const m = String(Math.floor(diff / 60)).padStart(2, '0');
        const s = String(diff % 60).padStart(2, '0');
        el.textContent = m + ':' + s;
        if (diff === 0) {
            el.style.color = 'var(--red)';
            el.textContent = 'Expired';
            document.getElementById('submitBtn') && (document.getElementById('submitBtn').disabled = true);
        }
    };
    tick();
    setInterval(tick, 1000);
})();

// Form validation
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const method = document.querySelector('input[name="payment_method"]:checked');
    if (!method) { e.preventDefault(); alert('Please select a payment method.'); return; }
    const acct = document.getElementById('account_number').value.trim();
    const holder = document.getElementById('account_holder').value.trim();
    if (!acct || !holder) { e.preventDefault(); alert('Please fill in all required fields.'); return; }
});
</script>
