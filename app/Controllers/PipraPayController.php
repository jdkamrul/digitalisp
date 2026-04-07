<?php

/**
 * PipraPay Payment Controller
 * Handles payment initiation, callbacks, success and cancel flows.
 */
class PipraPayController {
    private Database $db;
    private PipraPayService $piprapay;

    public function __construct() {
        $this->db       = Database::getInstance();
        require_once BASE_PATH . '/app/Services/PipraPayService.php';
        $this->piprapay = new PipraPayService();
    }

    /**
     * Initiate a PipraPay payment for an invoice.
     * POST /payment/piprapay/initiate/{invoice_id}
     */
    public function initiate(string $invoiceId): void {
        if (!$this->piprapay->isEnabled()) {
            $_SESSION['error'] = 'PipraPay is not configured. Please enable it in Settings → Payment Gateways.';
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

        $orderId = 'INV-' . $invoice['invoice_number'] . '-' . time();

        $result = $this->piprapay->createPayment([
            'amount'         => $invoice['due_amount'],
            'currency'       => 'BDT',
            'order_id'       => $orderId,
            'customer_name'  => $invoice['full_name'],
            'customer_phone' => $invoice['phone'] ?? '',
            'customer_email' => $invoice['email'] ?? '',
            'description'    => 'Invoice ' . $invoice['invoice_number'] . ' — ISP Bill Payment',
            'success_url'    => base_url('payment/piprapay/success?invoice_id=' . $invoiceId),
            'cancel_url'     => base_url('payment/piprapay/cancel?invoice_id=' . $invoiceId),
            'callback_url'   => base_url('payment/piprapay/callback'),
        ]);

        if ($result['success']) {
            // Store session_id for verification
            $_SESSION['piprapay_session']  = $result['session_id'];
            $_SESSION['piprapay_invoice']  = $invoiceId;
            $_SESSION['piprapay_order_id'] = $orderId;
            redirect($result['payment_url']);
        } else {
            $_SESSION['error'] = 'PipraPay: ' . ($result['error'] ?? 'Could not initiate payment.');
            redirect(base_url("billing/invoice/{$invoiceId}"));
        }
    }

    /**
     * Payment success redirect handler.
     * GET /payment/piprapay/success
     */
    public function success(): void {
        $invoiceId    = (int)($_GET['invoice_id'] ?? $_SESSION['piprapay_invoice'] ?? 0);
        $transactionId = sanitize($_GET['transaction_id'] ?? '');

        if (!$invoiceId) {
            redirect(base_url('billing/invoices'));
        }

        // Verify with PipraPay API
        if ($transactionId) {
            $verify = $this->piprapay->verifyPayment($transactionId);

            if ($verify['success']) {
                $this->recordPayment($invoiceId, $verify['amount'], $transactionId, $verify['payment_method'] ?? 'piprapay');
                unset($_SESSION['piprapay_session'], $_SESSION['piprapay_invoice'], $_SESSION['piprapay_order_id']);
                $_SESSION['success'] = 'Payment of ৳' . number_format($verify['amount'], 2) . ' received via PipraPay. Transaction: ' . $transactionId;
                redirect(base_url("billing/invoice/{$invoiceId}"));
                return;
            }
        }

        $_SESSION['error'] = 'Payment could not be verified. Please contact support with your transaction ID.';
        redirect(base_url("billing/invoice/{$invoiceId}"));
    }

    /**
     * Payment cancel redirect handler.
     * GET /payment/piprapay/cancel
     */
    public function cancel(): void {
        $invoiceId = (int)($_GET['invoice_id'] ?? $_SESSION['piprapay_invoice'] ?? 0);
        unset($_SESSION['piprapay_session'], $_SESSION['piprapay_invoice'], $_SESSION['piprapay_order_id']);
        $_SESSION['error'] = 'Payment was cancelled.';
        redirect($invoiceId ? base_url("billing/invoice/{$invoiceId}") : base_url('billing/invoices'));
    }

    /**
     * IPN / Webhook callback from PipraPay.
     * POST /payment/piprapay/callback
     */
    public function callback(): void {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        // Validate signature
        if (!$this->piprapay->validateCallback($data)) {
            http_response_code(400);
            echo json_encode(['status' => 'invalid_signature']);
            exit;
        }

        $transactionId = $data['transaction_id'] ?? '';
        $orderId       = $data['order_id'] ?? '';
        $status        = $data['status'] ?? '';
        $amount        = (float)($data['amount'] ?? 0);

        if ($status !== 'COMPLETED' || !$transactionId) {
            http_response_code(200);
            echo json_encode(['status' => 'ignored']);
            exit;
        }

        // Extract invoice ID from order_id (format: INV-{invoice_number}-{timestamp})
        // Find invoice by invoice_number
        preg_match('/INV-([A-Z0-9\-]+)-\d+$/', $orderId, $m);
        $invoiceNumber = $m[1] ?? '';

        if ($invoiceNumber) {
            $invoice = $this->db->fetchOne("SELECT * FROM invoices WHERE invoice_number=?", [$invoiceNumber]);
            if ($invoice && $invoice['status'] !== 'paid') {
                $this->recordPayment($invoice['id'], $amount, $transactionId, $data['payment_method'] ?? 'piprapay');
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    /**
     * Record a verified payment into the database.
     */
    private function recordPayment(int $invoiceId, float $amount, string $transactionId, string $method): void {
        $invoice = $this->db->fetchOne("SELECT * FROM invoices WHERE id=?", [$invoiceId]);
        if (!$invoice) return;

        // Prevent duplicate recording
        $exists = $this->db->fetchOne("SELECT id FROM payments WHERE mobile_banking_ref=?", [$transactionId]);
        if ($exists) return;

        $receiptNo = 'RCP-PP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->db->insert('payments', [
            'receipt_number'     => $receiptNo,
            'customer_id'        => $invoice['customer_id'],
            'invoice_id'         => $invoice['id'],
            'branch_id'          => $invoice['branch_id'],
            'collector_id'       => $_SESSION['user_id'] ?? null,
            'amount'             => $amount,
            'payment_method'     => 'online',
            'mobile_banking_ref' => $transactionId,
            'notes'              => 'PipraPay · ' . strtoupper($method),
            'payment_date'       => date('Y-m-d H:i:s'),
        ]);

        // Update invoice
        $newPaid = $invoice['paid_amount'] + $amount;
        $newDue  = max(0, $invoice['total'] - $newPaid);
        $this->db->update('invoices', [
            'paid_amount' => $newPaid,
            'due_amount'  => $newDue,
            'status'      => $newDue <= 0 ? 'paid' : 'partial',
        ], 'id=?', [$invoice['id']]);

        // Update customer due
        $cust = $this->db->fetchOne("SELECT due_amount FROM customers WHERE id=?", [$invoice['customer_id']]);
        if ($cust) {
            $this->db->update('customers', ['due_amount' => max(0, $cust['due_amount'] - $amount)], 'id=?', [$invoice['customer_id']]);
        }

        // Cashbook entry
        $this->db->insert('cashbook_entries', [
            'branch_id'      => $invoice['branch_id'],
            'entry_type'     => 'credit',
            'entry_category' => 'payment_received',
            'amount'         => $amount,
            'reference_id'   => $invoice['id'],
            'reference_type' => 'invoice',
            'description'    => "PipraPay · {$transactionId} · {$receiptNo}",
            'entry_date'     => date('Y-m-d'),
            'created_by'     => $_SESSION['user_id'] ?? null,
        ]);
    }
}
