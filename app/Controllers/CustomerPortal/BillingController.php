<?php

class PortalBillingController extends PortalController {

    public function __construct() {
        parent::__construct();
    }

    public function invoices(): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $invoices = $this->db->fetchAll(
            "SELECT * FROM invoices WHERE customer_id = ? ORDER BY billing_month DESC LIMIT ? OFFSET ?",
            [$customerId, $perPage, $offset]
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM invoices WHERE customer_id = ?",
            [$customerId]
        )['count'] ?? 0;

        $totalPages = ceil($total / $perPage);
        $pageTitle = 'My Invoices';
        $currentPage = 'billing-invoices';
        $portalCustomer = $this->customer;
        $content = BASE_PATH . '/views/portal/billing/invoices_content.php';

        require_once BASE_PATH . '/views/portal/layouts/main.php';
    }

    public function viewInvoice(string $id): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();

        $invoice = $this->db->fetchOne(
            "SELECT i.*, p.name as package_name, p.speed_download, p.speed_upload 
             FROM invoices i 
             LEFT JOIN packages p ON p.id = i.package_id 
             WHERE i.id = ? AND i.customer_id = ?",
            [$id, $customerId]
        );

        if (!$invoice) {
            $_SESSION['portal_error'] = 'Invoice not found.';
            redirect(base_url('portal/billing/invoices'));
        }

        $payments = $this->db->fetchAll(
            "SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC",
            [$id]
        );

        $customer = $this->getCustomer();
        $pageTitle = 'Invoice ' . $invoice['invoice_number'];
        $currentPage = 'billing-invoice';
        $portalCustomer = $this->customer;
        $content = BASE_PATH . '/views/portal/billing/view_invoice_content.php';

        require_once BASE_PATH . '/views/portal/layouts/main.php';
    }

    public function payments(): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $payments = $this->db->fetchAll(
            "SELECT p.*, i.invoice_number 
             FROM payments p 
             LEFT JOIN invoices i ON i.id = p.invoice_id 
             WHERE p.customer_id = ? 
             ORDER BY p.payment_date DESC 
             LIMIT ? OFFSET ?",
            [$customerId, $perPage, $offset]
        );

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM payments WHERE customer_id = ?",
            [$customerId]
        )['count'] ?? 0;

        $totalPages = ceil($total / $perPage);
        $pageTitle = 'Payment History';
        $currentPage = 'billing-payments';
        $portalCustomer = $this->customer;
        $content = BASE_PATH . '/views/portal/billing/payments_content.php';

        require_once BASE_PATH . '/views/portal/layouts/main.php';
    }

    public function payForm(string $invoiceId): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();

        $invoice = $this->db->fetchOne(
            "SELECT * FROM invoices WHERE id = ? AND customer_id = ? AND status != 'paid'",
            [$invoiceId, $customerId]
        );

        if (!$invoice) {
            $_SESSION['portal_error'] = 'Invoice not found or already paid.';
            redirect(base_url('portal/billing/invoices'));
        }

        $settings = $this->getPortalSettings();
        $customer = $this->getCustomer();
        $pageTitle = 'Pay Invoice ' . $invoice['invoice_number'];
        $currentPage = 'billing-pay';
        $portalCustomer = $this->customer;
        $content = BASE_PATH . '/views/portal/billing/pay_content.php';

        require_once BASE_PATH . '/views/portal/layouts/main.php';
    }

    public function initiatePayment(): void {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('portal/billing/invoices'));
        }

        $customerId = $this->getCustomerId();
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = sanitize($_POST['payment_method'] ?? '');

        $invoice = $this->db->fetchOne(
            "SELECT * FROM invoices WHERE id = ? AND customer_id = ?",
            [$invoiceId, $customerId]
        );

        if (!$invoice || $amount <= 0) {
            $_SESSION['portal_error'] = 'Invalid payment request.';
            redirect(base_url('portal/billing/invoices'));
        }

        if ($amount > $invoice['due_amount']) {
            $amount = $invoice['due_amount'];
        }

        $txId = 'TXN-' . date('YmdHis') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->db->insert('payment_transactions', [
            'transaction_id' => $txId,
            'customer_id' => $customerId,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'payment_method' => $method,
            'status' => 'pending',
            'customer_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $_SESSION['pending_payment'] = [
            'tx_id' => $txId,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'method' => $method,
        ];

        $pageTitle = 'Confirm Payment';
        require_once BASE_PATH . '/views/portal/billing/confirm-payment.php';
    }

    public function confirmPayment(): void {
        $this->requireAuth();
        
        $pending = $_SESSION['pending_payment'] ?? null;
        if (!$pending) {
            redirect(base_url('portal/billing/invoices'));
        }

        $txId = $pending['tx_id'];
        $refNumber = sanitize($_POST['ref_number'] ?? '');

        $tx = $this->db->fetchOne("SELECT * FROM payment_transactions WHERE transaction_id = ?", [$txId]);
        if (!$tx) {
            redirect(base_url('portal/billing/invoices'));
        }

        $this->db->update('payment_transactions', [
            'status' => 'completed',
            'payment_ref' => $refNumber,
            'completed_at' => date('Y-m-d H:i:s'),
            'gateway_response' => json_encode(['manual_confirm' => true, 'ref' => $refNumber]),
        ], 'transaction_id = ?', [$txId]);

        $invoice = $this->db->fetchOne("SELECT * FROM invoices WHERE id = ?", [$tx['invoice_id']]);
        
        $receiptNo = generateReceiptNumber();
        $this->db->insert('payments', [
            'receipt_number' => $receiptNo,
            'customer_id' => $tx['customer_id'],
            'invoice_id' => $tx['invoice_id'],
            'branch_id' => $invoice['branch_id'] ?? 1,
            'amount' => $tx['amount'],
            'payment_method' => $tx['payment_method'],
            'mobile_banking_ref' => $refNumber,
            'notes' => 'Online payment via portal. TXN: ' . $txId,
        ]);

        $newPaid = $invoice['paid_amount'] + $tx['amount'];
        $newDue = max(0, $invoice['total'] - $newPaid);
        $this->db->update('invoices', [
            'paid_amount' => $newPaid,
            'due_amount' => $newDue,
            'status' => $newDue <= 0 ? 'paid' : 'partial',
        ], 'id = ?', [$tx['invoice_id']]);

        $customer = $this->getCustomer();
        $this->db->update('customers', 
            ['due_amount' => max(0, $customer['due_amount'] - $tx['amount'])],
            'id = ?', [$tx['customer_id']]
        );

        $this->db->insert('customer_notifications', [
            'customer_id' => $tx['customer_id'],
            'type' => 'payment',
            'title' => 'Payment Successful',
            'message' => 'Payment of ' . formatMoney($tx['amount']) . ' received. Receipt: ' . $receiptNo,
            'link' => base_url('portal/billing/receipt/' . $receiptNo),
        ]);

        unset($_SESSION['pending_payment']);
        $_SESSION['payment_success'] = 'Payment of ' . formatMoney($tx['amount']) . ' successful. Receipt: ' . $receiptNo;
        
        redirect(base_url('portal/billing/payment-success'));
    }

    public function paymentSuccess(): void {
        $this->requireAuth();
        $message = $_SESSION['payment_success'] ?? 'Payment completed successfully.';
        unset($_SESSION['payment_success']);
        $pageTitle = 'Payment Successful';
        require_once BASE_PATH . '/views/portal/billing/payment-success.php';
    }

    public function printReceipt(string $receiptNumber): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();

        $payment = $this->db->fetchOne(
            "SELECT p.*, i.invoice_number, i.billing_month, c.full_name, c.phone, c.address
             FROM payments p 
             LEFT JOIN invoices i ON i.id = p.invoice_id 
             LEFT JOIN customers c ON c.id = p.customer_id
             WHERE p.receipt_number = ? AND p.customer_id = ?",
            [$receiptNumber, $customerId]
        );

        if (!$payment) {
            $_SESSION['portal_error'] = 'Receipt not found.';
            redirect(base_url('portal/billing/payments'));
        }

        $customer = $this->getCustomer();
        $pageTitle = 'Receipt ' . $receiptNumber;
        require_once BASE_PATH . '/views/portal/billing/receipt.php';
    }

    public function getUnpaidInvoices(): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();

        $invoices = $this->db->fetchAll(
            "SELECT id, invoice_number, billing_month, total, due_amount, due_date 
             FROM invoices 
             WHERE customer_id = ? AND status IN ('unpaid', 'partial') 
             ORDER BY billing_month ASC",
            [$customerId]
        );

        $this->jsonResponse(['invoices' => $invoices]);
    }

    private function getPortalSettings(): array {
        $rows = $this->db->fetchAll("SELECT setting_key, setting_value FROM portal_settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge([
            'enable_bkash_payment' => '1',
            'enable_nagad_payment' => '1',
            'support_phone' => '01700000000',
        ], $settings);
    }
}
