<?php

class PortalUsageController extends PortalController {

    public function __construct() {
        parent::__construct();
    }

    public function index(): void {
        $this->requireAuth();
        $customer = $this->getCustomer();
        $usageData = $this->getUsageData($customer);
        $pageTitle = 'Usage Statistics';
        $currentPage = 'usage';
        $portalCustomer = $this->customer;
        $content = BASE_PATH . '/views/portal/usage/index_content.php';

        require_once BASE_PATH . '/views/portal/layouts/main.php';
    }

    public function getLiveData(): void {
        $this->requireAuth();
        
        // Debug: log the customer data
        $customer = $this->getCustomer();
        
        $nasDevices = $this->db->fetchAll("SELECT n.* FROM nas_devices n WHERE n.is_active = 1");
        
        $data = [
            'online' => false,
            'speed_download' => 0,
            'speed_upload' => 0,
            'data_used_today' => 0,
            'session_time' => 'N/A',
            'ip_address' => 'N/A',
            'pppoe_username' => $customer['pppoe_username'] ?? ($customer['username'] ?? 'NOT SET'),
            'nas_count' => count($nasDevices),
            'customer_id' => $customer['id'] ?? 0,
            'from_radius' => $customer['from_radius'] ?? false,
        ];

        if (empty($nasDevices) || empty($customer['pppoe_username'])) {
            $this->jsonResponse($data);
            return;
        }

        foreach ($nasDevices as $nas) {
            try {
                require_once BASE_PATH . '/app/Services/MikroTikService.php';
                $mtConfig = [
                    'ip' => $nas['ip_address'],
                    'port' => $nas['api_port'],
                    'username' => $nas['username'],
                    'password' => $nas['password'],
                ];
                $mt = new MikroTikService($mtConfig);
                
                if ($mt->connect()) {
                    $activeSessions = $mt->getActiveSessions();
                    $queues = $mt->getQueueStats();
                    
                    $activeSession = null;
                    foreach ($activeSessions as $session) {
                        if (($session['name'] ?? '') === $customer['pppoe_username']) {
                            $activeSession = $session;
                            break;
                        }
                    }
                    
                    if ($activeSession) {
                        $userQueue = $queues[$customer['pppoe_username']] ?? [];
                        
                        $interfaces = $mt->getInterfaceStats();
                        $ifaceName = '<pppoe-' . $customer['pppoe_username'] . '>';
                        $ifaceStats = $interfaces[$ifaceName] ?? [];
                        
                        $rxBytes = $ifaceStats['rx'] ?? 0;
                        $txBytes = $ifaceStats['tx'] ?? 0;
                        
                        $downloadGB = round($txBytes / 1073741824, 2);
                        $uploadGB = round($rxBytes / 1073741824, 2);
                        
                        $data = [
                            'online' => true,
                            'speed_download' => round((($userQueue['rate_tx'] ?? 0) * 8) / 1000000, 2),
                            'speed_upload' => round((($userQueue['rate_rx'] ?? 0) * 8) / 1000000, 2),
                            'data_used_today' => round($downloadGB + $uploadGB, 2),
                            'data_rx_gb' => $uploadGB,
                            'data_tx_gb' => $downloadGB,
                            'session_time' => $activeSession['uptime'] ?? 'N/A',
                            'ip_address' => $activeSession['address'] ?? ($customer['static_ip'] ?? 'N/A'),
                            'pppoe_username' => $customer['pppoe_username'],
                            'nas_name' => $nas['name'],
                            'nas_ip' => $nas['ip_address'],
                        ];
                        
                        $mt->disconnect();
                        $this->jsonResponse($data);
                        return;
                    }
                    
                    $mt->disconnect();
                }
            } catch (Exception $e) {
                // Try next NAS
            }
        }

        $this->jsonResponse($data);
    }

    public function getDailyUsage(): void {
        $this->requireAuth();
        $customer = $this->getCustomer();
        
        $days = 7;
        $dailyData = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dailyData[] = [
                'date' => date('d M', strtotime($date)),
                'download' => 0,
                'upload' => 0,
            ];
        }

        $this->jsonResponse([
            'success' => true,
            'data' => $dailyData,
            'customer' => [
                'package' => $customer['package_name'] ?? 'N/A',
                'speed' => $customer['speed_download'] ?? 'N/A',
            ]
        ]);
    }

    public function connectionStatus(): void {
        $this->requireAuth();
        $customer = $this->getCustomer();
        
        $nas = $this->db->fetchOne(
            "SELECT n.* FROM nas_devices n WHERE n.is_active = 1 LIMIT 1"
        );

        $status = [
            'connection_status' => $customer['status'] ?? 'unknown',
            'pppoe_status' => 'disconnected',
            'ip_address' => $customer['static_ip'] ?? 'N/A',
            'mac_address' => 'N/A',
            'nas_name' => $nas['name'] ?? 'N/A',
            'last_connected' => null,
            'package' => $customer['package_name'] ?? 'N/A',
            'profile' => $customer['profile'] ?? ($customer['package_name'] ?? 'N/A'),
        ];

        if ($nas && !empty($customer['pppoe_username'])) {
            try {
                require_once BASE_PATH . '/app/Services/MikroTikService.php';
                $mtConfig = [
                    'ip' => $nas['ip_address'],
                    'port' => $nas['api_port'],
                    'username' => $nas['username'],
                    'password' => $nas['password'],
                ];
                $mt = new MikroTikService($mtConfig);
                
                if ($mt->connect()) {
                    $activeSessions = $mt->getActiveSessions();
                    
                    foreach ($activeSessions as $session) {
                        if (($session['name'] ?? '') === $customer['pppoe_username']) {
                            $status['pppoe_status'] = 'connected';
                            $status['ip_address'] = $session['address'] ?? $status['ip_address'];
                            $status['mac_address'] = $session['caller-id'] ?? 'N/A';
                            $status['session_uptime'] = $session['uptime'] ?? 'N/A';
                            $status['session_profile'] = $session['profile'] ?? 'N/A';
                            $status['connection_status'] = 'online';
                            break;
                        }
                    }
                    
                    $mt->disconnect();
                }
            } catch (Exception $e) {
            }
        }

        $pageTitle = 'Connection Status';
        require_once BASE_PATH . '/views/portal/usage/connection.php';
    }

    private function getUsageData(array $customer): array {
        $nas = $this->db->fetchOne(
            "SELECT n.* FROM nas_devices n WHERE n.is_active = 1 LIMIT 1"
        );

        if (!$nas || empty($customer['pppoe_username'])) {
            return [
                'online' => false,
                'speed_download' => 0,
                'speed_upload' => 0,
                'today_download' => 0,
                'today_upload' => 0,
                'today_total' => 0,
                'month_download' => 0,
                'month_upload' => 0,
                'month_total' => 0,
                'session_time' => 'N/A',
                'ip_address' => $customer['static_ip'] ?? 'N/A',
                'nas_status' => 'unavailable',
            ];
        }

        try {
            require_once BASE_PATH . '/app/Services/MikroTikService.php';
            $mtConfig = [
                'ip' => $nas['ip_address'],
                'port' => $nas['api_port'],
                'username' => $nas['username'],
                'password' => $nas['password'],
            ];
            $mt = new MikroTikService($mtConfig);
            
            if (!$mt->connect()) {
                return $this->getEmptyUsageData($customer, 'offline');
            }

            $activeSessions = $mt->getActiveSessions();
            $queues = $mt->getQueueStats();
            
            $activeSession = null;
            foreach ($activeSessions as $session) {
                if (($session['name'] ?? '') === $customer['pppoe_username']) {
                    $activeSession = $session;
                    break;
                }
            }
            
            $userQueue = $queues[$customer['pppoe_username']] ?? [];
            
            $mt->disconnect();

            return [
                'online' => !empty($activeSession),
                'speed_download' => round((($userQueue['rate_tx'] ?? 0) * 8) / 1000000, 2),
                'speed_upload' => round((($userQueue['rate_rx'] ?? 0) * 8) / 1000000, 2),
                'today_download' => round(($userQueue['bytes_tx'] ?? 0) / 1073741824, 3),
                'today_upload' => round(($userQueue['bytes_rx'] ?? 0) / 1073741824, 3),
                'today_total' => round((($userQueue['bytes_tx'] ?? 0) + ($userQueue['bytes_rx'] ?? 0)) / 1073741824, 3),
                'month_download' => 0,
                'month_upload' => 0,
                'month_total' => 0,
                'session_time' => $activeSession['uptime'] ?? 'N/A',
                'ip_address' => $activeSession['address'] ?? ($customer['static_ip'] ?? 'N/A'),
                'nas_name' => $nas['name'] ?? 'N/A',
                'nas_status' => 'online',
                'package' => $customer['package_name'] ?? ($customer['profile'] ?? 'N/A'),
                'profile' => $customer['profile'] ?? ($customer['package_name'] ?? 'N/A'),
            ];
        } catch (Exception $e) {
            return $this->getEmptyUsageData($customer, 'error');
        }
    }

    private function getEmptyUsageData(array $customer, string $nasStatus = 'offline'): array {
        return [
            'online' => false,
            'speed_download' => 0,
            'speed_upload' => 0,
            'today_download' => 0,
            'today_upload' => 0,
            'today_total' => 0,
            'month_download' => 0,
            'month_upload' => 0,
            'month_total' => 0,
            'session_time' => 'N/A',
            'ip_address' => $customer['static_ip'] ?? 'N/A',
            'nas_name' => 'N/A',
            'nas_status' => $nasStatus,
        ];
    }
}
