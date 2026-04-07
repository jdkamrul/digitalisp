<?php

class PortalDashboardController extends PortalController {

    public function __construct() {
        parent::__construct();
    }

    public function index(): void {
        $this->requireAuth();
        
        $customer = $this->getCustomer();
        $customerId = $this->getCustomerId();

        $currentBill = $this->db->fetchOne(
            "SELECT i.*, (julianday(i.due_date) - julianday('now')) as days_until_due 
             FROM invoices i 
             WHERE i.customer_id = ? AND i.status != 'paid' 
             ORDER BY i.billing_month DESC 
             LIMIT 1",
            [$customerId]
        );

        $unpaidInvoices = $this->db->fetchAll(
            "SELECT SUM(due_amount) as total_due, COUNT(*) as count 
             FROM invoices 
             WHERE customer_id = ? AND status IN ('unpaid', 'partial')",
            [$customerId]
        );

        $recentPayments = $this->db->fetchAll(
            "SELECT * FROM payments WHERE customer_id = ? ORDER BY payment_date DESC LIMIT 5",
            [$customerId]
        );

        $openTickets = $this->db->fetchAll(
            "SELECT COUNT(*) as count FROM support_tickets 
             WHERE customer_id = ? AND status IN ('open', 'in_progress', 'pending_customer')",
            [$customerId]
        );

        $usageData = $this->getUsageData($customer);

        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';
        $portalCustomer = $this->customer;
        $content = BASE_PATH . '/views/portal/dashboard/content.php';
        
        require_once BASE_PATH . '/views/portal/layouts/main.php';
    }

    public function getLiveStats(): void {
        $this->requireAuth();
        $customer = $this->getCustomer();
        
        $stats = [
            'connection_status' => $customer['status'] ?? 'unknown',
            'package' => $customer['package_name'] ?? 'N/A',
            'speed' => ($customer['speed_download'] ?? 'N/A') . ' Down / ' . ($customer['speed_upload'] ?? 'N/A') . ' Up',
            'monthly_charge' => formatMoney($customer['monthly_charge'] ?? 0),
            'due_amount' => formatMoney($customer['due_amount'] ?? 0),
            'advance_balance' => formatMoney($customer['advance_balance'] ?? 0),
        ];

        $this->jsonResponse($stats);
    }

    private function getUsageData(array $customer): array {
        $nas = $this->db->fetchOne(
            "SELECT n.* FROM nas_devices n WHERE n.is_active = 1 LIMIT 1"
        );

        if (!$nas || empty($customer['pppoe_username'])) {
            return [
                'today_download' => 0,
                'today_upload' => 0,
                'today_total' => 0,
                'month_download' => 0,
                'month_upload' => 0,
                'month_total' => 0,
                'session_time' => 'N/A',
                'session_ip' => $customer['static_ip'] ?? 'N/A',
                'online' => false,
            ];
        }

        try {
            $sock = @fsockopen($nas['ip_address'], $nas['api_port'], $errno, $errstr, 2);
            if (!$sock) {
                return $this->getEmptyUsageData($customer);
            }

            fclose($sock);

            require_once BASE_PATH . '/app/Services/MikroTikService.php';
            $mtConfig = [
                'ip' => $nas['ip_address'],
                'port' => $nas['api_port'],
                'username' => $nas['username'],
                'password' => $nas['password'],
            ];
            $mt = new MikroTikService($mtConfig);
            
            if (!$mt->connect()) {
                return $this->getEmptyUsageData($customer);
            }

            $activeSessions = $mt->getActiveSessions();
            $activeSession = null;
            foreach ($activeSessions as $session) {
                if (($session['name'] ?? '') === $customer['pppoe_username']) {
                    $activeSession = $session;
                    break;
                }
            }

            $queues = $mt->getQueueStats();
            $userQueue = $queues[$customer['pppoe_username']] ?? [];
            
            $mt->disconnect();

            return [
                'today_download' => round(($userQueue['bytes_tx'] ?? 0) / 1073741824, 2),
                'today_upload' => round(($userQueue['bytes_rx'] ?? 0) / 1073741824, 2),
                'today_total' => round((($userQueue['bytes_tx'] ?? 0) + ($userQueue['bytes_rx'] ?? 0)) / 1073741824, 2),
                'month_download' => 0,
                'month_upload' => 0,
                'month_total' => 0,
                'session_time' => $activeSession['uptime'] ?? 'N/A',
                'session_ip' => $activeSession['address'] ?? ($customer['static_ip'] ?? 'N/A'),
                'online' => !empty($activeSession),
            ];
        } catch (Exception $e) {
            return $this->getEmptyUsageData($customer);
        }
    }

    private function getEmptyUsageData(array $customer): array {
        return [
            'today_download' => 0,
            'today_upload' => 0,
            'today_total' => 0,
            'month_download' => 0,
            'month_upload' => 0,
            'month_total' => 0,
            'session_time' => 'N/A',
            'session_ip' => $customer['static_ip'] ?? 'N/A',
            'online' => false,
        ];
    }
}
