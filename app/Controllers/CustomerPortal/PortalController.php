<?php

class PortalController {
    protected $db;
    protected array $customer = [];
    protected bool $isAuthenticated = false;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->checkAuth();
    }

    protected function checkAuth(): void {
        $token = $_COOKIE['portal_token'] ?? $_SESSION['portal_token'] ?? null;
        if (!$token) {
            // Check for RADIUS-only session in session
            if (!empty($_SESSION['portal_radius_user'])) {
                $this->isAuthenticated = true;
                $this->customer = [
                    'id' => 0,
                    'customer_id' => 0,
                    'full_name' => $_SESSION['portal_radius_user'],
                    'pppoe_username' => $_SESSION['portal_radius_user'],
                    'from_radius' => true
                ];
                $_SESSION['portal_logged_in'] = true;
            }
            return;
        }

        $session = $this->db->fetchOne(
            "SELECT cps.*, c.* 
             FROM customer_portal_sessions cps 
             LEFT JOIN customers c ON c.id = cps.customer_id 
             WHERE cps.session_token = ? 
               AND cps.is_active = 1 
               AND cps.expires_at > DATETIME('now')",
            [$token]
        );

        if ($session) {
            $this->isAuthenticated = true;
            if ($session['customer_id'] > 0) {
                $this->customer = $session;
                $_SESSION['portal_customer_id'] = $session['customer_id'];
                return;
            }
            
            // RADIUS-only user - get username from session record
            $radiusUser = $session['username'] ?? null;
            
            if (empty($radiusUser)) {
                // Fallback to session
                $radiusUser = $_SESSION['portal_radius_user'] ?? null;
            }
            
            if (empty($radiusUser)) {
                // Cannot identify RADIUS user - try to find from recent login logs
                $recentLog = $this->db->fetchOne(
                    "SELECT login_identifier FROM customer_portal_login_logs 
                     WHERE status = 'success' ORDER BY created_at DESC LIMIT 1"
                );
                $radiusUser = $recentLog['login_identifier'] ?? 'RADIUS User';
            }
            
            $_SESSION['portal_radius_user'] = $radiusUser;
            
            // Get RADIUS profile
            $radProfile = $this->db->fetchOne(
                "SELECT rg.groupname as profile FROM radusergroup rg WHERE rg.username = ?",
                [$radiusUser]
            );
            
            $this->customer = [
                'id' => 0,
                'customer_id' => 0,
                'full_name' => $radiusUser,
                'pppoe_username' => $radiusUser,
                'profile' => $radProfile['profile'] ?? 'N/A',
                'package_name' => $radProfile['profile'] ?? 'N/A',
                'from_radius' => true
            ];
        }
    }

    protected function requireAuth(): void {
        if (!$this->isAuthenticated) {
            redirect(base_url('portal/login'));
        }
    }

    protected function getCustomerId(): int {
        return (int)($this->customer['customer_id'] ?? $this->customer['id'] ?? 0);
    }

    protected function getCustomer(): array {
        // Check for RADIUS-only user (from customer table or radcheck)
        $radiusUser = $_SESSION['portal_radius_user'] ?? null;
        if (!empty($radiusUser)) {
            // Try to get customer from customers table by pppoe_username
            $customer = $this->db->fetchOne(
                "SELECT c.*, p.name as package_name, p.speed_download, p.speed_upload, p.price as package_price,
                        b.name as branch_name, z.name as zone_name
                 FROM customers c
                 LEFT JOIN packages p ON p.id = c.package_id
                 LEFT JOIN branches b ON b.id = c.branch_id
                 LEFT JOIN zones z ON z.id = c.zone_id
                 WHERE c.pppoe_username = ?",
                [$radiusUser]
            );
            
            if ($customer) {
                return $customer;
            }
            
            // Return RADIUS-only user data with available info
            $radUser = $this->db->fetchOne(
                "SELECT rg.groupname as profile
                 FROM radusergroup rg
                 WHERE rg.username = ?",
                [$radiusUser]
            );
            
            // Get RADIUS password for login verification
            $radPass = $this->db->fetchOne(
                "SELECT value as pppoe_password
                 FROM radcheck 
                 WHERE username = ? AND attribute = 'Cleartext-Password'",
                [$radiusUser]
            );
            
            return [
                'id' => 0,
                'customer_id' => 0,
                'full_name' => $radiusUser,
                'pppoe_username' => $radiusUser,
                'pppoe_password' => $radPass['pppoe_password'] ?? '',
                'phone' => '',
                'email' => '',
                'status' => 'active',
                'package_name' => $radUser['profile'] ?? 'N/A',
                'profile' => $radUser['profile'] ?? 'N/A',
                'speed_download' => 'N/A',
                'speed_upload' => 'N/A',
                'monthly_charge' => 0,
                'due_amount' => 0,
                'advance_balance' => 0,
                'from_radius' => true,
            ];
        }
        
        $customerId = $this->getCustomerId();
        if ($customerId <= 0) {
            return [];
        }
        
        return $this->db->fetchOne(
            "SELECT c.*, p.name as package_name, p.speed_download, p.speed_upload, p.price as package_price,
                    b.name as branch_name, z.name as zone_name
             FROM customers c
             LEFT JOIN packages p ON p.id = c.package_id
             LEFT JOIN branches b ON b.id = c.branch_id
             LEFT JOIN zones z ON z.id = c.zone_id
             WHERE c.id = ?",
            [$customerId]
        ) ?? [];
    }

    protected function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }

    protected function createSession(int $customerId, int $expiresHours = 24): string {
        $token = $this->generateToken();
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiresHours * 3600));

        $this->db->insert('customer_portal_sessions', [
            'customer_id' => $customerId,
            'session_token' => $token,
            'device_info' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'expires_at' => $expiresAt,
        ]);

        setcookie('portal_token', $token, time() + ($expiresHours * 3600), '/', '', false, true);
        $_SESSION['portal_token'] = $token;

        $this->db->update('customers', ['portal_last_login' => date('Y-m-d H:i:s')], 'id=?', [$customerId]);

        return $token;
    }

    protected function destroySession(): void {
        $token = $_COOKIE['portal_token'] ?? $_SESSION['portal_token'] ?? null;
        if ($token) {
            $this->db->update('customer_portal_sessions', 
                ['is_active' => 0, 'logout_at' => date('Y-m-d H:i:s')],
                'session_token = ?', [$token]
            );
        }
        setcookie('portal_token', '', time() - 3600, '/');
        unset($_SESSION['portal_token']);
        unset($_SESSION['portal_customer_id']);
    }

    protected function logLoginAttempt(?int $customerId, string $identifier, string $method, string $status, ?string $reason = null): void {
        $this->db->insert('customer_portal_login_logs', [
            'customer_id' => $customerId,
            'login_identifier' => $identifier,
            'login_method' => $method,
            'status' => $status,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'failure_reason' => $reason,
        ]);
    }

    protected function render(string $view, array $data = []): void {
        extract($data);
        $portalCustomer = $this->customer;
        $pageTitle = $pageTitle ?? 'Customer Portal';
        require_once BASE_PATH . "/views/portal/layouts/main.php";
    }

    protected function jsonResponse(array $data, int $status = 200): void {
        jsonResponse($data, $status);
    }

    public static function getCustomerIdFromToken(): ?int {
        $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if (empty($token)) return null;
        
        $db = Database::getInstance();
        $session = $db->fetchOne(
            "SELECT customer_id FROM customer_portal_sessions 
             WHERE session_token = ? AND is_active = 1 AND expires_at > DATETIME('now')",
            [$token]
        );
        
        return $session ? (int)$session['customer_id'] : null;
    }
}
