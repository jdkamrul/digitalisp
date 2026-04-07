<?php

class BillingController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        redirect(base_url('billing/invoices'));
    }

    public function invoices(): void {
        $pageTitle   = 'Invoices';
        $currentPage = 'billing';
        $currentSubPage = 'invoices';

        $status  = sanitize($_GET['status'] ?? '');
        $month   = sanitize($_GET['month'] ?? date('Y-m'));
        $search  = sanitize($_GET['search'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = 25;
        $offset  = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if (!empty($status))      { $where[] = 'i.status=?'; $params[] = $status; }
        if (!empty($month))       { $where[] = "strftime('%Y-%m', i.billing_month)=?"; $params[] = $month; }
        if (!empty($search)) {
            $where[] = '(c.full_name LIKE ? OR c.customer_code LIKE ? OR i.invoice_number LIKE ?)';
            $s = "%$search%"; $params = array_merge($params, [$s,$s,$s]);
        }

        $whereStr = implode(' AND ', $where);

        $summary = $this->db->fetchOne(
            "SELECT COUNT(*) as total, 
             SUM(CASE WHEN i.status='paid' THEN i.total ELSE 0 END) as paid_total,
             SUM(CASE WHEN i.status IN ('unpaid','partial') THEN i.due_amount ELSE 0 END) as due_total,
             SUM(i.total) as gross
             FROM invoices i JOIN customers c ON c.id=i.customer_id WHERE $whereStr",
            $params
        );

        $invoices = $this->db->fetchAll(
            "SELECT i.*, c.full_name, c.customer_code, c.phone, p.name as package_name
             FROM invoices i
             JOIN customers c ON c.id=i.customer_id
             LEFT JOIN packages p ON p.id=i.package_id
             WHERE $whereStr ORDER BY i.generated_at DESC LIMIT $limit OFFSET $offset",
            $params
        );

        $totalPages = ceil(($summary['total']??0) / $limit);
        $viewFile   = BASE_PATH . '/views/billing/invoices.php';
        require_once BASE_PATH . '/views/layouts/main.php';
    }

    public function generateInvoices(): void {
        $month      = sanitize($_POST['billing_month'] ?? date('Y-m-01'));
        $branchId   = (int)($_POST['branch_id'] ?? 0);
        $generated  = 0;
        $skipped    = 0;
        $errors     = [];

        $query = "SELECT c.*, p.price FROM customers c LEFT JOIN packages p ON p.id=c.package_id WHERE c.status='active'";
        $params = [];
        if ($branchId) { $query .= ' AND c.branch_id=?'; $params[] = $branchId; }

        $customers = $this->db->fetchAll($query, $params);
        $billingMonth = date('Y-m-01', strtotime($month));

        foreach ($customers as $c) {
            // Check if invoice already exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM invoices WHERE customer_id=? AND billing_month=?",
                [$c['id'], $billingMonth]
            );
            if ($existing) { $skipped++; continue; }

            $amount = $c['monthly_charge'] ?: ($c['price'] ?? 0);
            if ($amount <= 0) { $skipped++; continue; }

            // Pro-rata calculation for new connections
            $isProrata = false;
            $proratadays = 0;
            $connectionDate = $c['connection_date'];
            if ($connectionDate && date('Y-m', strtotime($connectionDate)) === date('Y-m', strtotime($billingMonth))) {
                $daysInMonth  = (int)date('t', strtotime($billingMonth));
                $connDay      = (int)date('j', strtotime($connectionDate));
                $proratadays  = $daysInMonth - $connDay + 1;
                $amount       = round(($amount / $daysInMonth) * $proratadays, 2);
                $isProrata    = true;
            }

            $invoiceNo = $this->generateInvoiceNumber();
            $dueDate   = date('Y-m-' . str_pad($c['billing_day'] ?? 10, 2, '0', STR_PAD_LEFT), strtotime("+1 month", strtotime($billingMonth)));

            $this->db->insert('invoices', [
                'invoice_number'  => $invoiceNo,
                'customer_id'     => $c['id'],
                'branch_id'       => $c['branch_id'],
                'package_id'      => $c['package_id'],
                'billing_month'   => $billingMonth,
                'amount'          => $amount,
                'discount'        => 0,
                'vat'             => 0,
                'total'           => $amount,
                'paid_amount'     => 0,
                'due_amount'      => $amount,
                'status'          => 'unpaid',
                'due_date'        => $dueDate,
                'is_prorata'      => $isProrata ? 1 : 0,
                'prorata_days'    => $proratadays,
                'generated_by'    => $_SESSION['user_id'],
            ]);

            // Update customer due balance
            $this->db->update('customers',
                ['due_amount' => $this->db->fetchOne("SELECT due_amount FROM customers WHERE id=?",[$c['id']])['due_amount'] + $amount],
                'id=?', [$c['id']]
            );

            $generated++;
        }

        $_SESSION['success'] = "Generated $generated invoices. Skipped $skipped (already existed).";
        redirect(base_url('billing/invoices'));
    }

    public function viewInvoice(string $id): void {
        $pageTitle   = 'Invoice Detail';
        $currentPage = 'billing';
        $invoice = $this->db->fetchOne(
            "SELECT i.*, c.full_name, c.customer_code, c.phone, c.address, c.pppoe_username,
                    p.name as package_name, p.speed_download, b.name as branch_name
             FROM invoices i
             JOIN customers c ON c.id=i.customer_id
             LEFT JOIN packages p ON p.id=i.package_id
             LEFT JOIN branches b ON b.id=i.branch_id
             WHERE i.id=?",
            [$id]
        );
        if (!$invoice) { http_response_code(404); die('Invoice not found'); }
        $payments = $this->db->fetchAll("SELECT * FROM payments WHERE invoice_id=? ORDER BY payment_date DESC", [$id]);
        $viewFile = BASE_PATH . '/views/billing/invoice-detail.php';
        require_once BASE_PATH . '/views/layouts/main.php';
    }

    public function payForm(string $id): void {
        $pageTitle   = 'Record Payment';
        $currentPage = 'billing';
        $invoice = $this->db->fetchOne(
            "SELECT i.*, c.full_name, c.customer_code, c.phone FROM invoices i JOIN customers c ON c.id=i.customer_id WHERE i.id=?",
            [$id]
        );
        if (!$invoice) { http_response_code(404); die('Invoice not found'); }
        $viewFile = BASE_PATH . '/views/billing/pay.php';
        require_once BASE_PATH . '/views/layouts/main.php';
    }

    public function recordPayment(string $invoiceId): void {
        $invoice = $this->db->fetchOne("SELECT * FROM invoices WHERE id=?", [$invoiceId]);
        if (!$invoice) { die('Invoice not found'); }

        $amount = (float)($_POST['amount'] ?? 0);
        $method = sanitize($_POST['payment_method'] ?? 'cash');
        $ref    = sanitize($_POST['mobile_banking_ref'] ?? '');
        $notes  = sanitize($_POST['notes'] ?? '');
        $date   = sanitize($_POST['payment_date'] ?? date('Y-m-d H:i:s'));

        if ($amount <= 0) {
            $_SESSION['error'] = 'Invalid payment amount.';
            redirect(base_url("billing/pay/{$invoiceId}"));
        }

        $receiptNo = $this->generateReceiptNumber();

        // Record payment
        $this->db->insert('payments', [
            'receipt_number'      => $receiptNo,
            'customer_id'         => $invoice['customer_id'],
            'invoice_id'          => $invoice['id'],
            'branch_id'           => $invoice['branch_id'],
            'collector_id'        => $_SESSION['user_id'],
            'amount'              => $amount,
            'payment_method'      => $method,
            'mobile_banking_ref'  => $ref,
            'notes'               => $notes,
            'payment_date'        => $date,
        ]);

        // Update invoice
        $newPaid = $invoice['paid_amount'] + $amount;
        $newDue  = max(0, $invoice['total'] - $newPaid);
        $status  = $newDue <= 0 ? 'paid' : 'partial';
        $this->db->update('invoices', [
            'paid_amount' => $newPaid,
            'due_amount'  => $newDue,
            'status'      => $status,
        ], 'id=?', [$invoice['id']]);

        // Update customer due
        $custDue = $this->db->fetchOne("SELECT due_amount FROM customers WHERE id=?", [$invoice['customer_id']]);
        $newCustDue = max(0, $custDue['due_amount'] - $amount);
        $this->db->update('customers', ['due_amount' => $newCustDue], 'id=?', [$invoice['customer_id']]);

        // Cashbook entry
        $this->db->insert('cashbook_entries', [
            'branch_id'       => $invoice['branch_id'],
            'entry_type'      => 'credit',
            'entry_category'  => 'payment_received',
            'amount'          => $amount,
            'reference_id'    => $invoice['id'],
            'reference_type'  => 'invoice',
            'description'     => "Payment for invoice {$invoice['invoice_number']} — {$receiptNo}",
            'entry_date'      => date('Y-m-d', strtotime($date)),
            'created_by'      => $_SESSION['user_id'],
        ]);

        redirect(base_url("billing/receipt/{$this->getPaymentId($receiptNo)}"));
    }

    public function printReceipt(string $paymentId): void {
        $pageTitle   = 'Money Receipt';
        $currentPage = 'billing';
        $payment = $this->db->fetchOne(
            "SELECT p.*, c.full_name, c.customer_code, c.phone, c.address, c.pppoe_username,
                    i.invoice_number, i.billing_month, pkg.name as package_name,
                    b.name as branch_name, b.address as branch_address, b.phone as branch_phone
             FROM payments p
             JOIN customers c ON c.id=p.customer_id
             LEFT JOIN invoices i ON i.id=p.invoice_id
             LEFT JOIN packages pkg ON pkg.id=i.package_id
             LEFT JOIN branches b ON b.id=p.branch_id
             WHERE p.id=?",
            [$paymentId]
        );
        if (!$payment) { http_response_code(404); die('Receipt not found'); }
        $viewFile = BASE_PATH . '/views/billing/receipt.php';
        require_once BASE_PATH . '/views/layouts/main.php';
    }

    public function cashbook(): void {
        $pageTitle   = 'Billing Cashbook';
        $currentPage = 'billing';
        $currentSubPage = 'billing-cashbook';
        $month       = sanitize($_GET['month'] ?? date('Y-m'));
        $branchId    = $_SESSION['branch_id'] ?? 1;

        $entries = $this->db->fetchAll(
            "SELECT p.*, c.full_name, c.customer_code, i.billing_month, i.invoice_number
             FROM payments p
             JOIN customers c ON c.id=p.customer_id
             LEFT JOIN invoices i ON i.id=p.invoice_id
             WHERE p.branch_id=? AND strftime('%Y-%m', p.payment_date)=?
             ORDER BY p.payment_date DESC",
            [$branchId, $month]
        );

        $viewFile = BASE_PATH . '/views/billing/cashbook.php';
        require_once BASE_PATH . '/views/layouts/main.php';
    }

    private function generateInvoiceNumber(): string {
        $last = $this->db->fetchOne("SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1");
        $num  = $last ? ((int)substr($last['invoice_number'], -6) + 1) : 1;
        return 'INV-' . date('Y') . '-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }

    private function generateReceiptNumber(): string {
        $last = $this->db->fetchOne("SELECT receipt_number FROM payments ORDER BY id DESC LIMIT 1");
        $num  = $last ? ((int)substr($last['receipt_number'], -6) + 1) : 1;
        return 'RCP-' . date('Ymd') . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    private function getPaymentId(string $receiptNo): int {
        return (int)$this->db->fetchOne("SELECT id FROM payments WHERE receipt_number=?", [$receiptNo])['id'];
    }
}
