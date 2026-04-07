<?php

/**
 * Self-Hosted PipraPay Controller
 * Handles local payment processing, checkout, and automated billing
 */
class SelfHostedPipraPayController {
    private Database $db;
    private SelfHostedPipraPayService $piprapay;

    public function __construct() {
        $this->db = Database::getInstance();
        require_once BASE_PATH . '/app/Services/SelfHostedPipraPayService.php';
        $this->piprapay = new SelfHostedPipraPayService();
    }

    /**
     * Initiate a self-hosted payment session for an invoice.
     * POST /payment/selfhosted/initiate/{invoice_id}
     */
    public function initiate(string $invoiceId): void {
        if (!$this->piprapay->isEnabled()) {
            $_SESSION['error'] = 'Self-Hosted PipraPay is not enabled. Please configure it in Settings → Payment Gateways.';
            redirect(base_url("billing/invoice/{$invoiceId}"));
        }

        $invoice = $this->db->fetchOne(
            "SELECT i.*, c.full_name, c.phone, c.email FROM invoices i JOIN customers c ON c.id=i.customer_id WHERE i.id=?",
            [$invoiceId]
        );

        if (!$invoice || $invoice['due_amount'] <= 0) {
            $_SESSION['error'] = 'Invoice not found or already paid.';
            redirect(base_url('billing/invoices'));
        }

        $result = $this->piprapay->createPayment([
            'invoice_id'     => $invoice['id'],
            'amount'         => $invoice['due_amount'],
            'currency'       => 'BDT',
            'customer_name'  => $invoice['full_name'],
            'customer_phone' => $invoice['phone'] ?? '',
            'customer_email' => $invoice['email'] ?? '',
            'description'    => 'Invoice ' . $invoice['invoice_number'] . ' — ISP Bill Payment',
            'success_url'    => base_url('payment/selfhosted/success?invoice_id=' . $invoiceId),
            'cancel_url'     => base_url('payment/selfhosted/cancel?invoice_id=' . $invoiceId),
        ]);

        if ($result['success']) {
            redirect(base_url('payment/selfhosted/checkout/' . $result['session_id']));
        } else {
            $_SESSION['error'] = 'Could not create payment session: ' . ($result['error'] ?? 'Unknown error.');
            redirect(base_url("billing/invoice/{$invoiceId}"));
        }
    }

    /**
     * Payment success redirect handler.
     * GET /payment/selfhosted/success
     */
    public function success(): void {
        $invoiceId     = (int)($_GET['invoice_id'] ?? 0);
        $transactionId = sanitize($_GET['transaction_id'] ?? '');

        if (!$invoiceId) {
            redirect(base_url('billing/invoices'));
        }

        $invoice = $this->db->fetchOne("SELECT * FROM invoices WHERE id=?", [$invoiceId]);

        require_once BASE_PATH . '/views/payment/selfhosted/success.php';
    }

    /**
     * Payment cancel redirect handler.
     * GET /payment/selfhosted/cancel
     */
    public function cancel(): void {
        $invoiceId = (int)($_GET['invoice_id'] ?? 0);
        $_SESSION['error'] = 'Payment was cancelled.';
        redirect($invoiceId ? base_url("billing/invoice/{$invoiceId}") : base_url('billing/invoices'));
    }

    /**
     * Show payment checkout page
     * GET /payment/selfhosted/checkout/{session_id}
     */
    public function checkout(string $sessionId): void {
        if (!$this->piprapay->isEnabled()) {
            $_SESSION['error'] = 'Self-hosted PipraPay is not enabled.';
            redirect(base_url('billing/invoices'));
        }

        $session = $this->piprapay->getSession($sessionId);

        if (!$session || $session['status'] !== 'pending') {
            $_SESSION['error'] = 'Invalid or expired payment session.';
            redirect(base_url('billing/invoices'));
        }

        if (strtotime($session['expires_at']) < time()) {
            $_SESSION['error'] = 'Payment session has expired.';
            redirect(base_url('billing/invoices'));
        }

        $paymentMethods = json_decode($session['payment_methods'], true);

        // Load checkout view
        require_once BASE_PATH . '/views/payment/selfhosted/checkout.php';
    }

    /**
     * Process payment submission
     * POST /payment/selfhosted/process/{session_id}
     */
    public function process(string $sessionId): void {
        if (!$this->piprapay->isEnabled()) {
            json_response(['success' => false, 'error' => 'Payment gateway not enabled'], 400);
        }

        // Validate session_id format to prevent injection
        if (!preg_match('/^SHPP-[a-f0-9]+\-\d+$/', $sessionId)) {
            json_response(['success' => false, 'error' => 'Invalid session'], 400);
        }

        $paymentData = [
            'method'         => sanitize($_POST['payment_method'] ?? ''),
            'amount'         => (float)($_POST['amount'] ?? 0),
            'reference'      => substr(sanitize($_POST['reference'] ?? ''), 0, 100),
            'account_number' => substr(sanitize($_POST['account_number'] ?? ''), 0, 30),
            'account_holder' => substr(sanitize($_POST['account_holder'] ?? ''), 0, 100),
        ];

        // Verify submitted amount matches session to prevent tampering
        $session = $this->piprapay->getSession($sessionId);
        if (!$session || abs($paymentData['amount'] - (float)$session['amount']) > 0.01) {
            json_response(['success' => false, 'error' => 'Amount mismatch. Please reload and try again.'], 400);
        }

        $result = $this->piprapay->processPayment($sessionId, $paymentData);

        if ($result['success']) {
            if (!empty($session['success_url'])) {
                redirect($session['success_url'] . '?transaction_id=' . urlencode($result['transaction_id']));
            } else {
                json_response([
                    'success'        => true,
                    'transaction_id' => $result['transaction_id'],
                    'message'        => 'Payment processed successfully',
                ]);
            }
        } else {
            json_response(['success' => false, 'error' => $result['error']], 400);
        }
    }

    /**
     * Handle payment webhooks
     * POST /payment/selfhosted/webhook
     */
    public function webhook(): void {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        // Validate webhook signature
        if (!$this->validateWebhookSignature($data)) {
            http_response_code(400);
            echo json_encode(['status' => 'invalid_signature']);
            exit;
        }

        $transactionId = $data['transaction_id'] ?? '';
        $orderId = $data['order_id'] ?? '';
        $status = $data['status'] ?? '';
        $amount = (float)($data['amount'] ?? 0);

        if ($status !== 'COMPLETED' || !$transactionId) {
            http_response_code(200);
            echo json_encode(['status' => 'ignored']);
            exit;
        }

        // Find session by order_id
        $session = $this->db->fetchOne("SELECT * FROM piprapay_sessions WHERE order_id = ?", [$orderId]);

        if ($session && $session['status'] === 'pending') {
            // Update session status
            $this->db->update('piprapay_sessions', [
                'status' => 'completed',
                'transaction_id' => $transactionId,
                'payment_method' => $data['payment_method'] ?? 'unknown',
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'order_id = ?', [$orderId]);

            // Record payment if invoice exists
            if ($session['invoice_id']) {
                $this->recordPayment($session['invoice_id'], $amount, $transactionId, $data['payment_method'] ?? 'webhook');
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    /**
     * Validate webhook signature
     */
    private function validateWebhookSignature(array $data): bool {
        if (empty($data['signature'])) return false;

        $secret = $this->getWebhookSecret();
        // Reject if no real secret is configured
        if (empty($secret)) return false;

        $expected = hash_hmac('sha256',
            ($data['transaction_id'] ?? '') .
            ($data['amount'] ?? '') .
            ($data['order_id'] ?? ''),
            $secret
        );

        return hash_equals($expected, $data['signature']);
    }

    /**
     * Get webhook secret
     */
    private function getWebhookSecret(): string {
        $row = $this->db->fetchOne("SELECT `value` FROM settings WHERE `key`='selfhosted_piprapay_webhook_secret'");
        $secret = $row['value'] ?? '';
        // Refuse to operate with the default placeholder — forces admin to set a real secret
        if (empty($secret) || $secret === 'change_this_webhook_secret_in_production') {
            return '';
        }
        return $secret;
    }

    /**
     * Record payment (similar to PipraPayController)
     */
    private function recordPayment(int $invoiceId, float $amount, string $transactionId, string $method): void {
        $invoice = $this->db->fetchOne("SELECT * FROM invoices WHERE id=?", [$invoiceId]);
        if (!$invoice) return;

        // Prevent duplicate recording
        $exists = $this->db->fetchOne("SELECT id FROM payments WHERE mobile_banking_ref=?", [$transactionId]);
        if ($exists) return;

        $receiptNo = 'RCP-SHPP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $this->db->insert('payments', [
            'receipt_number' => $receiptNo,
            'customer_id' => $invoice['customer_id'],
            'invoice_id' => $invoice['id'],
            'branch_id' => $invoice['branch_id'],
            'collector_id' => null, // Automated or webhook
            'amount' => $amount,
            'payment_method' => 'online',
            'mobile_banking_ref' => $transactionId,
            'notes' => 'Self-Hosted PipraPay · ' . strtoupper($method),
            'payment_date' => date('Y-m-d H:i:s'),
        ]);

        // Update invoice
        $newPaid = $invoice['paid_amount'] + $amount;
        $newDue = max(0, $invoice['total'] - $newPaid);
        $this->db->update('invoices', [
            'paid_amount' => $newPaid,
            'due_amount' => $newDue,
            'status' => $newDue <= 0 ? 'paid' : 'partial',
        ], 'id=?', [$invoice['id']]);

        // Update customer due
        $cust = $this->db->fetchOne("SELECT due_amount FROM customers WHERE id=?", [$invoice['customer_id']]);
        if ($cust) {
            $this->db->update('customers', ['due_amount' => max(0, $cust['due_amount'] - $amount)], 'id=?', [$invoice['customer_id']]);
        }

        // Cashbook entry
        $this->db->insert('cashbook_entries', [
            'branch_id' => $invoice['branch_id'],
            'entry_type' => 'credit',
            'entry_category' => 'payment_received',
            'amount' => $amount,
            'reference_id' => $invoice['id'],
            'reference_type' => 'invoice',
            'description' => "Self-Hosted PipraPay · {$transactionId} · {$receiptNo}",
            'entry_date' => date('Y-m-d'),
            'created_by' => null, // System
        ]);
    }

    /**
     * Process automated billing queue
     * This can be called by a cron job
     */
    public function processAutomatedBilling(): void {
        if (!$this->piprapay->isEnabled()) {
            return;
        }

        $queue = $this->db->fetchAll("
            SELECT * FROM piprapay_auto_billing_queue
            WHERE status = 'pending'
            AND next_attempt <= ?
            ORDER BY priority DESC, scheduled_date ASC
            LIMIT 10
        ", [date('Y-m-d H:i:s')]);

        foreach ($queue as $item) {
            // Mark as processing
            $this->db->update('piprapay_auto_billing_queue', [
                'status' => 'processing',
                'last_attempt' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$item['id']]);

            // Process payment
            $result = $this->piprapay->processAutomatedBilling($item['customer_id'], $item['invoice_id']);

            if ($result['success']) {
                // Mark as completed
                $this->db->update('piprapay_auto_billing_queue', [
                    'status' => 'completed',
                    'transaction_id' => $result['transaction_id'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$item['id']]);
            } else {
                // Handle failure
                $retryCount = $item['retry_count'] + 1;

                if ($retryCount >= $item['max_retries']) {
                    // Mark as failed
                    $this->db->update('piprapay_auto_billing_queue', [
                        'status' => 'failed',
                        'error_message' => $result['error'] ?? 'Unknown error',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$item['id']]);
                } else {
                    // Schedule next retry
                    $nextAttempt = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    $this->db->update('piprapay_auto_billing_queue', [
                        'status' => 'pending',
                        'retry_count' => $retryCount,
                        'next_attempt' => $nextAttempt,
                        'error_message' => $result['error'] ?? 'Unknown error',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$item['id']]);
                }
            }
        }

        // Clean up expired sessions
        $this->piprapay->cleanupExpiredSessions();
    }

    /**
     * Add invoice to automated billing queue
     * POST /payment/selfhosted/queue/{invoice_id}
     */
    public function queueAutomatedPayment(string $invoiceId): void {
        $invoiceId = (int)$invoiceId;
        if ($invoiceId <= 0) {
            json_response(['success' => false, 'error' => 'Invalid invoice ID'], 400);
        }

        $invoice = $this->db->fetchOne("SELECT * FROM invoices WHERE id=?", [$invoiceId]);

        if (!$invoice || $invoice['due_amount'] <= 0) {
            json_response(['success' => false, 'error' => 'Invalid invoice'], 400);
        }

        $customer = $this->db->fetchOne("SELECT * FROM customers WHERE id=?", [$invoice['customer_id']]);

        if (!$customer || !$customer['auto_payment_enabled']) {
            json_response(['success' => false, 'error' => 'Auto payment not enabled for this customer'], 400);
        }

        // Check if already in queue
        $exists = $this->db->fetchOne("
            SELECT id FROM piprapay_auto_billing_queue
            WHERE invoice_id = ? AND status IN ('pending', 'processing')
        ", [$invoiceId]);

        if ($exists) {
            json_response(['success' => false, 'error' => 'Invoice already in automated billing queue'], 400);
        }

        // Add to queue
        $this->db->insert('piprapay_auto_billing_queue', [
            'customer_id' => $invoice['customer_id'],
            'invoice_id' => $invoice['id'],
            'amount' => $invoice['due_amount'],
            'priority' => 'medium',
            'scheduled_date' => date('Y-m-d H:i:s'),
            'next_attempt' => date('Y-m-d H:i:s'),
            'status' => 'pending',
        ]);

        json_response(['success' => true, 'message' => 'Invoice added to automated billing queue']);
    }

    /**
     * Get automated billing status for customer
     * GET /payment/selfhosted/status/{customer_id}
     */
    public function getBillingStatus(string $customerId): void {
        $subscription = $this->db->fetchOne("SELECT * FROM piprapay_subscriptions WHERE customer_id = ?", [$customerId]);

        $queue = $this->db->fetchAll("
            SELECT * FROM piprapay_auto_billing_queue
            WHERE customer_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ", [$customerId]);

        json_response([
            'subscription' => $subscription,
            'queue' => $queue,
        ]);
    }
}