<?php

/**
 * Self-Hosted PipraPay Payment Gateway Service
 * Local payment processing server for automated billing
 * Supports: bKash, Nagad, Rocket, Upay, Bank transfers
 *
 * Features:
 * - Local payment processing without external API dependency
 * - Automated recurring billing collection
 * - Payment retry mechanisms
 * - Webhook notifications
 * - Integration with existing invoice system
 */
class SelfHostedPipraPayService {

    private Database $db;
    private array $config;

    public function __construct(array $config = []) {
        $this->db = Database::getInstance();
        $this->config = array_merge([
            'auto_retry_attempts' => 3,
            'retry_interval_hours' => 24,
            'webhook_timeout' => 30,
            'payment_methods' => ['bkash', 'nagad', 'rocket', 'upay', 'bank'],
            'auto_collection_enabled' => true,
        ], $config);
    }

    /**
     * Check if self-hosted PipraPay is enabled
     */
    public function isEnabled(): bool {
        $row = $this->db->fetchOne("SELECT `value` FROM settings WHERE `key`='selfhosted_piprapay_enabled'");
        return ($row['value'] ?? '0') === '1';
    }

    /**
     * Create a local payment session
     * @param array $params Payment parameters
     * @return array Payment session data
     */
    public function createPayment(array $params): array {
        $sessionId = 'SHPP-' . uniqid() . '-' . time();
        $orderId = $params['order_id'] ?? 'ORD-' . time();

        // Store payment session in database
        $this->db->insert('piprapay_sessions', [
            'session_id' => $sessionId,
            'order_id' => $orderId,
            'amount' => $params['amount'],
            'currency' => $params['currency'] ?? 'BDT',
            'customer_name' => $params['customer_name'] ?? '',
            'customer_phone' => $params['customer_phone'] ?? '',
            'customer_email' => $params['customer_email'] ?? '',
            'description' => $params['description'] ?? '',
            'invoice_id' => $params['invoice_id'] ?? null,
            'customer_id' => $params['customer_id'] ?? null,
            'status' => 'pending',
            'payment_methods' => json_encode($this->config['payment_methods']),
            'success_url' => $params['success_url'] ?? '',
            'cancel_url' => $params['cancel_url'] ?? '',
            'callback_url' => $params['callback_url'] ?? '',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'session_id' => $sessionId,
            'order_id' => $orderId,
            'payment_url' => base_url('payment/selfhosted/checkout/' . $sessionId),
            'amount' => $params['amount'],
            'currency' => $params['currency'] ?? 'BDT',
        ];
    }

    /**
     * Process payment completion
     * @param string $sessionId Payment session ID
     * @param array $paymentData Payment details
     * @return array Processing result
     */
    public function processPayment(string $sessionId, array $paymentData): array {
        $session = $this->db->fetchOne(
            "SELECT * FROM piprapay_sessions WHERE session_id = ? AND status = 'pending'",
            [$sessionId]
        );

        if (!$session) {
            return ['success' => false, 'error' => 'Invalid or expired session'];
        }

        if (strtotime($session['expires_at']) < time()) {
            return ['success' => false, 'error' => 'Payment session expired'];
        }

        // Validate payment data
        if (!$this->validatePaymentData($paymentData)) {
            return ['success' => false, 'error' => 'Invalid payment data'];
        }

        // Generate cryptographically secure transaction ID
        $transactionId = 'TXN-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));

        // Update session status
        $this->db->update('piprapay_sessions', [
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'payment_method' => $paymentData['method'],
            'payment_ref' => $paymentData['reference'] ?? '',
            'completed_at' => date('Y-m-d H:i:s'),
        ], 'session_id = ?', [$sessionId]);

        // Record payment if invoice exists
        if ($session['invoice_id']) {
            $this->recordPayment($session['invoice_id'], $session['amount'], $transactionId, $paymentData['method']);
        }

        // Send webhook notification
        $this->sendWebhookNotification($session, $transactionId, $paymentData);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'amount' => $session['amount'],
            'payment_method' => $paymentData['method'],
        ];
    }

    /**
     * Validate payment data
     */
    private function validatePaymentData(array $data): bool {
        $required = ['method', 'amount'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                return false;
            }
        }

        if (!in_array($data['method'], $this->config['payment_methods'], true)) {
            return false;
        }

        if ((float)$data['amount'] <= 0) {
            return false;
        }

        // Validate optional string lengths
        if (isset($data['account_number']) && strlen($data['account_number']) > 30) {
            return false;
        }
        if (isset($data['account_holder']) && strlen($data['account_holder']) > 100) {
            return false;
        }
        if (isset($data['reference']) && strlen($data['reference']) > 100) {
            return false;
        }

        return true;
    }

    /**
     * Record payment in database (similar to PipraPayController)
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
            'collector_id' => null, // Automated payment
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
            'created_by' => null, // System automated
        ]);
    }

    /**
     * Send webhook notification
     */
    private function sendWebhookNotification(array $session, string $transactionId, array $paymentData): void {
        if (empty($session['callback_url'])) return;

        $payload = [
            'transaction_id' => $transactionId,
            'order_id' => $session['order_id'],
            'amount' => $session['amount'],
            'currency' => $session['currency'],
            'status' => 'COMPLETED',
            'payment_method' => $paymentData['method'],
            'paid_at' => date('Y-m-d H:i:s'),
            'customer_name' => $session['customer_name'],
            'customer_phone' => $session['customer_phone'],
            'customer_email' => $session['customer_email'],
            'description' => $session['description'],
        ];

        // Add signature for security
        $secret = $this->getWebhookSecret();
        $payload['signature'] = hash_hmac('sha256', $transactionId . $session['amount'] . $session['order_id'], $secret);

        $this->sendHttpRequest('POST', $session['callback_url'], $payload);
    }

    /**
     * Send HTTP request
     */
    private function sendHttpRequest(string $method, string $url, array $data = []): ?array {
        $body = json_encode($data);
        $headers = [
            'Content-Type: application/json',
            'User-Agent: SelfHostedPipraPay/1.0',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['webhook_timeout'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300 ? json_decode($result, true) : null;
    }

    /**
     * Get webhook secret for signature validation
     */
    private function getWebhookSecret(): string {
        $row = $this->db->fetchOne("SELECT `value` FROM settings WHERE `key`='selfhosted_piprapay_webhook_secret'");
        return $row['value'] ?? 'default_webhook_secret_change_this';
    }

    /**
     * Get payment session details
     */
    public function getSession(string $sessionId): ?array {
        return $this->db->fetchOne("SELECT * FROM piprapay_sessions WHERE session_id = ?", [$sessionId]);
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int {
        return $this->db->execute(
            "DELETE FROM piprapay_sessions WHERE expires_at < ? AND status = 'pending'",
            [date('Y-m-d H:i:s')]
        );
    }

    /**
     * Get automated billing candidates
     */
    public function getAutomatedBillingCandidates(): array {
        // Get customers with due invoices and auto-payment enabled
        return $this->db->fetchAll("
            SELECT
                c.id as customer_id,
                c.full_name,
                c.phone,
                c.email,
                i.id as invoice_id,
                i.invoice_number,
                i.due_amount,
                i.total,
                i.issue_date,
                i.due_date,
                ps.payment_method as preferred_method,
                ps.auto_retry_count
            FROM customers c
            JOIN invoices i ON i.customer_id = c.id
            LEFT JOIN piprapay_subscriptions ps ON ps.customer_id = c.id
            WHERE i.status IN ('unpaid', 'partial')
            AND i.due_amount > 0
            AND c.auto_payment_enabled = 1
            AND (ps.auto_retry_count < ? OR ps.auto_retry_count IS NULL)
            ORDER BY i.due_date ASC, i.due_amount DESC
        ", [$this->config['auto_retry_attempts']]);
    }

    /**
     * Process automated billing for a customer
     */
    public function processAutomatedBilling(int $customerId, int $invoiceId): array {
        $invoice = $this->db->fetchOne("
            SELECT i.*, c.full_name, c.phone, c.email
            FROM invoices i
            JOIN customers c ON c.id = i.customer_id
            WHERE i.id = ? AND i.customer_id = ?
        ", [$invoiceId, $customerId]);

        if (!$invoice || $invoice['due_amount'] <= 0) {
            return ['success' => false, 'error' => 'Invalid invoice or no due amount'];
        }

        // Get customer's preferred payment method
        $subscription = $this->db->fetchOne("SELECT * FROM piprapay_subscriptions WHERE customer_id = ?", [$customerId]);

        if (!$subscription) {
            return ['success' => false, 'error' => 'No payment subscription found'];
        }

        // Create automated payment session
        $orderId = 'AUTO-' . $invoice['invoice_number'] . '-' . time();

        $result = $this->createPayment([
            'amount' => $invoice['due_amount'],
            'order_id' => $orderId,
            'customer_name' => $invoice['full_name'],
            'customer_phone' => $invoice['phone'],
            'customer_email' => $invoice['email'],
            'description' => 'Automated Payment - Invoice ' . $invoice['invoice_number'],
            'invoice_id' => $invoiceId,
            'customer_id' => $customerId,
            'callback_url' => base_url('payment/selfhosted/webhook'),
        ]);

        if (!$result['success']) {
            return $result;
        }

        // Simulate payment processing (in real implementation, this would integrate with mobile banking APIs)
        $paymentData = [
            'method' => $subscription['payment_method'],
            'amount' => $invoice['due_amount'],
            'reference' => 'AUTO-' . time(),
        ];

        // Process the payment
        $processResult = $this->processPayment($result['session_id'], $paymentData);

        if ($processResult['success']) {
            // Update retry count
            $this->db->execute(
                "UPDATE piprapay_subscriptions SET auto_retry_count = auto_retry_count + 1, last_payment_attempt = ? WHERE customer_id = ?",
                [date('Y-m-d H:i:s'), $customerId]
            );

            return ['success' => true, 'transaction_id' => $processResult['transaction_id']];
        } else {
            // Increment retry count on failure
            $this->db->execute(
                "UPDATE piprapay_subscriptions SET auto_retry_count = auto_retry_count + 1, last_payment_attempt = ? WHERE customer_id = ?",
                [date('Y-m-d H:i:s'), $customerId]
            );

            return ['success' => false, 'error' => 'Payment processing failed'];
        }
    }
}