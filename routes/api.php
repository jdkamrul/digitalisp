<?php

// REST API Routes — loaded after web.php
// All API routes use /api/v1/ prefix

class ApiMiddleware {
    public function handle(): void {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $token);
        if (empty($token)) { jsonResponse(['error' => 'Unauthorized'], 401); }

        $db   = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON r.id=u.role_id
             WHERE u.api_token=? AND u.api_token_expires > DATETIME('now') AND u.is_active=1",
            [$token]
        );
        if (!$user) { jsonResponse(['error' => 'Invalid or expired token'], 401); }

        // Set user context
        $_SESSION['api_user_id']   = $user['id'];
        $_SESSION['api_user_role'] = $user['role_name'];
        $_SESSION['api_branch_id'] = $user['branch_id'];
    }
}

Router::prefix('/api/v1', function() {

    // ── Auth ──────────────────────────────────────────
    Router::post('/auth/login', function() {
        $body     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($username) || empty($password)) {
            jsonResponse(['error' => 'Username and password required'], 400);
        }

        $db   = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON r.id=u.role_id
             WHERE u.username=? AND u.is_active=1",
            [$username]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }

        // Generate API token
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 86400); // 24h
        $db->update('users', ['api_token' => $token, 'api_token_expires' => $expires, 'last_login' => date('Y-m-d H:i:s')], 'id=?', [$user['id']]);

        jsonResponse([
            'success' => true,
            'token'   => $token,
            'expires' => $expires,
            'user' => [
                'id'         => $user['id'],
                'name'       => $user['full_name'],
                'username'   => $user['username'],
                'role'       => $user['role_name'],
                'branch_id'  => $user['branch_id'],
            ]
        ]);
    });

    Router::post('/auth/logout', function() {
        $db    = Database::getInstance();
        $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if ($token) {
            $db->update('users', [
                'api_token'         => null,
                'api_token_expires' => null,
            ], 'api_token = ?', [$token]);
        }
        jsonResponse(['success' => true, 'message' => 'Logged out']);
    }, ['ApiMiddleware']);

    // ── Dashboard Stats ────────────────────────────────
    Router::get('/dashboard/stats', function() {
        $db = Database::getInstance();
        $branchId = $_SESSION['api_branch_id'] ?? null;
        $bf = $branchId ? 'AND branch_id=?' : '';
        $bp = $branchId ? [$branchId] : [];

        jsonResponse([
            'total_customers' => (int)$db->fetchOne("SELECT COUNT(*) as c FROM customers WHERE 1=1 $bf", $bp)['c'],
            'active'          => (int)$db->fetchOne("SELECT COUNT(*) as c FROM customers WHERE status='active' $bf", $bp)['c'],
            'suspended'       => (int)$db->fetchOne("SELECT COUNT(*) as c FROM customers WHERE status='suspended' $bf", $bp)['c'],
            'today_collection' => (float)$db->fetchOne("SELECT COALESCE(SUM(amount),0) as s FROM payments WHERE DATE(payment_date)=DATE('now') $bf", $bp)['s'],
            'month_collection' => (float)$db->fetchOne("SELECT COALESCE(SUM(amount),0) as s FROM payments WHERE strftime('%m', payment_date)=strftime('%m', 'now') AND strftime('%Y', payment_date)=strftime('%Y', 'now') $bf", $bp)['s'],
            'total_due'        => (float)$db->fetchOne("SELECT COALESCE(SUM(due_amount),0) as s FROM invoices WHERE status IN ('unpaid','partial') $bf", $bp)['s'],
        ]);
    }, ['ApiMiddleware']);

    // ── Customers ──────────────────────────────────────
    Router::get('/customers/search', function() {
        $db = Database::getInstance();
        $q  = sanitize($_GET['q'] ?? '');
        if (strlen($q) < 2) jsonResponse(['customers' => []]);

        $customers = $db->fetchAll(
            "SELECT c.id, c.customer_code, c.full_name, c.phone, c.status, c.due_amount,
                    p.name as package_name, p.speed_download
             FROM customers c LEFT JOIN packages p ON p.id=c.package_id
             WHERE c.full_name LIKE ? OR c.phone LIKE ? OR c.customer_code LIKE ?
             LIMIT 15",
            ["%$q%", "%$q%", "%$q%"]
        );
        jsonResponse(['customers' => $customers]);
    }, ['ApiMiddleware']);

    Router::get('/customers/{id}', function($id) {
        $db = Database::getInstance();
        $c  = $db->fetchOne(
            "SELECT c.*, p.name as package_name, p.speed_download, p.speed_upload, z.name as zone_name
             FROM customers c LEFT JOIN packages p ON p.id=c.package_id LEFT JOIN zones z ON z.id=c.zone_id
             WHERE c.id=?",
            [$id]
        );
        if (!$c) jsonResponse(['error' => 'Customer not found'], 404);
        jsonResponse(['customer' => $c]);
    }, ['ApiMiddleware']);

    Router::get('/customers/{id}/invoices', function($id) {
        $db = Database::getInstance();
        $invoices = $db->fetchAll(
            "SELECT id, invoice_number, billing_month, total, paid_amount, due_amount, status, due_date
             FROM invoices WHERE customer_id=? ORDER BY billing_month DESC LIMIT 12",
            [$id]
        );
        jsonResponse(['invoices' => $invoices]);
    }, ['ApiMiddleware']);

    // ── Payments ───────────────────────────────────────
    Router::post('/payments', function() {
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $db   = Database::getInstance();

        $invoiceId = (int)($body['invoice_id'] ?? 0);
        $amount    = (float)($body['amount'] ?? 0);
        $method    = sanitize($body['payment_method'] ?? 'cash');

        if (!$invoiceId || $amount <= 0) jsonResponse(['error' => 'Invalid parameters'], 400);

        $invoice = $db->fetchOne("SELECT * FROM invoices WHERE id=?", [$invoiceId]);
        if (!$invoice) jsonResponse(['error' => 'Invoice not found'], 404);

        $receiptNo = 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $db->insert('payments', [
            'receipt_number' => $receiptNo,
            'customer_id'    => $invoice['customer_id'],
            'invoice_id'     => $invoice['id'],
            'branch_id'      => $invoice['branch_id'],
            'collector_id'   => $_SESSION['api_user_id'],
            'amount'         => $amount,
            'payment_method' => $method,
            'payment_date'   => date('Y-m-d H:i:s'),
        ]);

        // Update invoice
        $newPaid = $invoice['paid_amount'] + $amount;
        $newDue  = max(0, $invoice['total'] - $newPaid);
        $db->update('invoices', [
            'paid_amount' => $newPaid,
            'due_amount'  => $newDue,
            'status'      => $newDue <= 0 ? 'paid' : 'partial',
        ], 'id=?', [$invoice['id']]);

        // Update customer due
        $cust = $db->fetchOne("SELECT due_amount FROM customers WHERE id=?", [$invoice['customer_id']]);
        if ($cust) {
            $db->update('customers', ['due_amount' => max(0, $cust['due_amount'] - $amount)], 'id=?', [$invoice['customer_id']]);
        }

        // Cashbook entry
        $db->insert('cashbook_entries', [
            'branch_id'      => $invoice['branch_id'],
            'entry_type'     => 'credit',
            'entry_category' => 'payment_received',
            'amount'         => $amount,
            'reference_id'   => $invoice['id'],
            'reference_type' => 'invoice',
            'description'    => "API Payment · {$receiptNo}",
            'entry_date'     => date('Y-m-d'),
            'created_by'     => $_SESSION['api_user_id'],
        ]);

        jsonResponse(['success' => true, 'receipt_number' => $receiptNo, 'amount' => $amount]);
    }, ['ApiMiddleware']);

    // ── Collections ────────────────────────────────────
    Router::get('/collections/today', function() {
        $db = Database::getInstance();
        $uid = $_SESSION['api_user_id'];
        $payments = $db->fetchAll(
            "SELECT p.receipt_number, p.amount, p.payment_method, p.payment_date,
                    c.full_name, c.customer_code
             FROM payments p JOIN customers c ON c.id=p.customer_id
             WHERE p.collector_id=? AND DATE(p.payment_date)=DATE('now')
             ORDER BY p.payment_date DESC",
            [$uid]
        );
        $total = array_sum(array_column($payments, 'amount'));
        jsonResponse(['payments' => $payments, 'total' => $total, 'count' => count($payments)]);
    }, ['ApiMiddleware']);

    // ── Work Orders ────────────────────────────────────
    Router::get('/workorders', function() {
        $db = Database::getInstance();
        $status = sanitize($_GET['status'] ?? 'pending');
        $wos = $db->fetchAll(
            "SELECT wo.*, c.full_name as customer_name, c.phone as customer_phone
             FROM work_orders wo LEFT JOIN customers c ON c.id=wo.customer_id
             WHERE wo.status=? ORDER BY wo.created_at DESC LIMIT 50",
            [$status]
        );
        jsonResponse(['work_orders' => $wos]);
    }, ['ApiMiddleware']);

    Router::post('/workorders', function() {
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $db   = Database::getInstance();

        $num = 'WO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $id  = $db->insert('work_orders', [
            'wo_number'   => $num,
            'customer_id' => (int)($body['customer_id'] ?? 0) ?: null,
            'branch_id'   => (int)($body['branch_id'] ?? $_SESSION['api_branch_id'] ?? 1),
            'type'        => sanitize($body['type'] ?? 'other'),
            'priority'    => sanitize($body['priority'] ?? 'normal'),
            'title'       => sanitize($body['title'] ?? ''),
            'description' => sanitize($body['description'] ?? ''),
            'address'     => sanitize($body['address'] ?? ''),
            'status'      => 'pending',
            'created_by'  => $_SESSION['api_user_id'],
        ]);
        jsonResponse(['success' => true, 'wo_number' => $num, 'id' => $id]);
    }, ['ApiMiddleware']);

    Router::post('/workorders/{id}/status', function($id) {
        $body   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $db     = Database::getInstance();
        $status = sanitize($body['status'] ?? '');
        if (!in_array($status, ['assigned','in_progress','completed','cancelled'])) {
            jsonResponse(['error' => 'Invalid status'], 400);
        }
        $update = ['status' => $status];
        if ($status === 'completed') $update['completed_at'] = date('Y-m-d H:i:s');
        $db->update('work_orders', $update, 'id=?', [$id]);
        jsonResponse(['success' => true]);
    }, ['ApiMiddleware']);

    // ── Notifications ──────────────────────────────────
    Router::get('/notifications', function() {
        $db = Database::getInstance();
        $uid = $_SESSION['api_user_id'] ?? null;
        if (!$uid) jsonResponse(['count' => 0, 'notifications' => []]);
        $notifs = $db->fetchAll(
            "SELECT * FROM notifications WHERE user_id=? AND is_read=0 ORDER BY created_at DESC LIMIT 20",
            [$uid]
        );
        jsonResponse(['count' => count($notifs), 'notifications' => $notifs]);
    }, ['ApiMiddleware']);

});
