<?php

class PortalAuthController extends PortalController {
    
    public function __construct() {
        parent::__construct();
    }

    public function showLogin(): void {
        if ($this->isAuthenticated) {
            redirect(base_url('portal/dashboard'));
        }
        
        $error = $_SESSION['portal_error'] ?? null;
        unset($_SESSION['portal_error']);
        
        $settings = $this->getPortalSettings();
        require_once BASE_PATH . '/views/portal/auth/login.php';
    }

    public function login(): void {
        $identifier = sanitize($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        $loginMethod = $this->detectLoginMethod($identifier);

        if (empty($identifier) || empty($password)) {
            $_SESSION['portal_error'] = 'Please enter your login credentials.';
            $this->logLoginAttempt(null, $identifier, 'unknown', 'failed', 'empty_credentials');
            redirect(base_url('portal/login'));
        }

        $customer = $this->findCustomerByIdentifier($identifier, $loginMethod);

        if (!$customer) {
            $_SESSION['portal_error'] = 'Account not found with this ' . ($loginMethod === 'pppoe' ? 'username' : ($loginMethod === 'email' ? 'email' : 'phone number')) . '.';
            $this->logLoginAttempt(null, $identifier, $loginMethod, 'failed', 'not_found');
            redirect(base_url('portal/login'));
        }

        // Auto-activate portal for customers with PPPoE credentials
        if (!$customer['portal_active'] && !empty($customer['pppoe_username'])) {
            $this->db->update('customers', ['portal_active' => 1], 'id=?', [$customer['id']]);
            $customer['portal_active'] = 1;
        }

        if (!$customer['portal_active']) {
            // Auto-activate for RADIUS users or customers with PPPoE
            if (!empty($customer['pppoe_password']) && $loginMethod === 'pppoe') {
                // Allow login
            } else {
                $_SESSION['portal_error'] = 'Your portal access is not activated. Please contact support.';
                $this->logLoginAttempt($customer['id'], $identifier, $loginMethod, 'blocked', 'not_active');
                redirect(base_url('portal/login'));
            }
        }

        $passwordValid = false;
        if (!empty($customer['portal_password'])) {
            $passwordValid = password_verify($password, $customer['portal_password']);
        } elseif (!empty($customer['pppoe_password']) && $loginMethod === 'pppoe') {
            $passwordValid = ($password === $customer['pppoe_password']);
        }

        if (!$passwordValid) {
            $_SESSION['portal_error'] = 'Invalid password.';
            $this->logLoginAttempt($customer['id'], $identifier, $loginMethod, 'failed', 'invalid_password');
            redirect(base_url('portal/login'));
        }

        // Skip MAC check for RADIUS users without customer record
        if (($customer['from_radius'] ?? false) && (empty($customer['id']) || $customer['id'] == 0)) {
            // Create session for RADIUS-only user
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 86400);
            
            $this->db->insert('customer_portal_sessions', [
                'customer_id' => 0,
                'username' => $identifier,
                'session_token' => $token,
                'device_info' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'expires_at' => $expiresAt,
            ]);
            
            setcookie('portal_token', $token, time() + 86400, '/', '', false, true);
            $_SESSION['portal_token'] = $token;
            $_SESSION['portal_radius_user'] = $identifier;
            $_SESSION['portal_logged_in'] = true;
            $_SESSION['portal_user_name'] = $identifier;
            
            $this->logLoginAttempt(null, $identifier, $loginMethod, 'success');
            redirect(base_url('portal/dashboard'));
        }

        if (!$this->checkMacAccess($customer['id'])) {
            $_SESSION['portal_error'] = 'Access denied. Your MAC address is not registered.';
            $this->logLoginAttempt($customer['id'], $identifier, $loginMethod, 'blocked', 'mac_blocked');
            redirect(base_url('portal/login'));
        }

        $this->createSession($customer['id'], 24);
        $this->logLoginAttempt($customer['id'], $identifier, $loginMethod, 'success');

        redirect(base_url('portal/dashboard'));
    }

    public function showForgotPassword(): void {
        $settings = $this->getPortalSettings();
        require_once BASE_PATH . '/views/portal/auth/forgot-password.php';
    }

    public function sendOtp(): void {
        $identifier = sanitize($_POST['identifier'] ?? '');
        $method = $this->detectLoginMethod($identifier);

        if (empty($identifier)) {
            $_SESSION['portal_error'] = 'Please enter your phone number or email.';
            redirect(base_url('portal/forgot-password'));
        }

        $customer = $this->findCustomerByIdentifier($identifier, $method);

        if (!$customer) {
            $_SESSION['portal_error'] = 'Account not found.';
            redirect(base_url('portal/forgot-password'));
        }

        if ($method === 'phone' && !empty($customer['phone'])) {
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', time() + 600);

            $this->db->update('customers', [
                'portal_otp' => password_hash($otp, PASSWORD_DEFAULT),
                'portal_otp_expires' => $expiresAt
            ], 'id=?', [$customer['id']]);

            $this->sendSms($customer['phone'], "Your ISP Portal OTP is: {$otp}. Valid for 10 minutes.");
            $_SESSION['reset_customer_id'] = $customer['id'];
            $_SESSION['reset_method'] = 'otp';
            $_SESSION['portal_success'] = 'OTP sent to ' . substr($customer['phone'], 0, 3) . '****' . substr($customer['phone'], -3);
            redirect(base_url('portal/reset-password'));
        }

        $_SESSION['portal_error'] = 'Phone number not found. Try secret question.';
        redirect(base_url('portal/forgot-password'));
    }

    public function showSecretQuestion(): void {
        $customerId = $_SESSION['reset_customer_id'] ?? 0;
        $customer = $this->db->fetchOne("SELECT id, full_name, secret_question FROM customers WHERE id=?", [$customerId]);

        if (!$customer || empty($customer['secret_question'])) {
            redirect(base_url('portal/forgot-password'));
        }

        require_once BASE_PATH . '/views/portal/auth/secret-question.php';
    }

    public function verifySecretQuestion(): void {
        $customerId = $_SESSION['reset_customer_id'] ?? 0;
        $answer = sanitize($_POST['answer'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';

        $customer = $this->db->fetchOne("SELECT * FROM customers WHERE id=?", [$customerId]);

        if (!$customer) {
            redirect(base_url('portal/forgot-password'));
        }

        if (strtolower(trim($answer)) !== strtolower(trim($customer['secret_answer']))) {
            $_SESSION['portal_error'] = 'Incorrect answer. Please try again.';
            redirect(base_url('portal/secret-question'));
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['portal_error'] = 'Password must be at least 6 characters.';
            redirect(base_url('portal/secret-question'));
        }

        $this->db->update('customers', [
            'portal_password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'portal_otp' => null,
            'portal_otp_expires' => null,
            'portal_active' => 1
        ], 'id=?', [$customerId]);

        unset($_SESSION['reset_customer_id'], $_SESSION['reset_method']);
        $_SESSION['portal_success'] = 'Password reset successfully. Please login.';
        redirect(base_url('portal/login'));
    }

    public function showResetPassword(): void {
        if (empty($_SESSION['reset_customer_id'])) {
            redirect(base_url('portal/forgot-password'));
        }
        require_once BASE_PATH . '/views/portal/auth/reset-password.php';
    }

    public function resetPassword(): void {
        $customerId = $_SESSION['reset_customer_id'] ?? 0;
        $otp = sanitize($_POST['otp'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $customer = $this->db->fetchOne("SELECT * FROM customers WHERE id=?", [$customerId]);

        if (!$customer || !$customer['portal_otp'] || !$customer['portal_otp_expires']) {
            $_SESSION['portal_error'] = 'Session expired. Please try again.';
            redirect(base_url('portal/forgot-password'));
        }

        if (strtotime($customer['portal_otp_expires']) < time()) {
            $_SESSION['portal_error'] = 'OTP expired. Please request a new one.';
            redirect(base_url('portal/forgot-password'));
        }

        if (!password_verify($otp, $customer['portal_otp'])) {
            $_SESSION['portal_error'] = 'Invalid OTP.';
            redirect(base_url('portal/reset-password'));
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['portal_error'] = 'Passwords do not match.';
            redirect(base_url('portal/reset-password'));
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['portal_error'] = 'Password must be at least 6 characters.';
            redirect(base_url('portal/reset-password'));
        }

        $this->db->update('customers', [
            'portal_password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'portal_otp' => null,
            'portal_otp_expires' => null,
            'portal_active' => 1
        ], 'id=?', [$customerId]);

        unset($_SESSION['reset_customer_id'], $_SESSION['reset_method']);
        $_SESSION['portal_success'] = 'Password reset successfully. Please login.';
        redirect(base_url('portal/login'));
    }

    public function logout(): void {
        // Clear RADIUS-only session if exists
        if (!empty($_SESSION['portal_radius_user'])) {
            unset($_SESSION['portal_radius_user']);
            unset($_SESSION['portal_logged_in']);
            unset($_SESSION['portal_user_name']);
        }
        
        $this->destroySession();
        redirect(base_url('portal/login'));
    }

    private function detectLoginMethod(string $identifier): string {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        if (preg_match('/^[0-9]{10,15}$/', $identifier)) {
            return 'phone';
        }
        return 'pppoe';
    }

    private function findCustomerByIdentifier(string $identifier, string $method): ?array {
        $settings = $this->getPortalSettings();
        
        switch ($method) {
            case 'phone':
                if (!$settings['allow_phone_login']) return null;
                $sql = "SELECT * FROM customers WHERE phone = ? AND status = 'active' LIMIT 1";
                break;
            case 'email':
                if (!$settings['allow_email_login']) return null;
                $sql = "SELECT * FROM customers WHERE email = ? AND status = 'active' LIMIT 1";
                break;
            case 'pppoe':
            default:
                if (!$settings['allow_pppoe_login']) return null;
                // Try customer table first
                $customer = $this->db->fetchOne(
                    "SELECT * FROM customers WHERE pppoe_username = ? AND status = 'active' LIMIT 1",
                    [$identifier]
                );
                if ($customer) return $customer;
                
                // Try RADIUS users from radcheck table
                $radiusUser = $this->db->fetchOne(
                    "SELECT value as password FROM radcheck WHERE username = ? AND attribute = 'Cleartext-Password' LIMIT 1",
                    [$identifier]
                );
                if ($radiusUser) {
                    // Find or create customer from RADIUS user
                    $customer = $this->db->fetchOne(
                        "SELECT * FROM customers WHERE pppoe_username = ? AND status = 'active' LIMIT 1",
                        [$identifier]
                    );
                    if ($customer) return $customer;
                    
                    // Return temporary customer with RADIUS password
                    return [
                        'id' => 0,
                        'pppoe_username' => $identifier,
                        'pppoe_password' => $radiusUser['password'],
                        'portal_active' => 0,
                        'full_name' => $identifier,
                        'phone' => '',
                        'email' => '',
                        'from_radius' => true
                    ];
                }
                return null;
        }

        return $this->db->fetchOne($sql, [$identifier]);
    }

    private function checkMacAccess(int $customerId): bool {
        $settings = $this->getPortalSettings();
        
        if (!$settings['require_mac_verification']) {
            return true;
        }

        $mac = $this->getClientMac();
        if (!$mac) return true;

        $allowedMac = $this->db->fetchOne(
            "SELECT * FROM customer_mac_access WHERE customer_id=? AND mac_address=? AND is_allowed=1 AND is_active=1",
            [$customerId, $mac]
        );

        if ($allowedMac) {
            $this->db->update('customer_mac_access', ['last_seen' => date('Y-m-d H:i:s')], 'id=?', [$allowedMac['id']]);
            return true;
        }

        return false;
    }

    private function getClientMac(): ?string {
        $mac = $_SERVER['HTTP_X_MAC_ADDRESS'] ?? null;
        if (!$mac && !empty($_SERVER['REMOTE_ADDR'])) {
            $mac = exec("arp -n " . $_SERVER['REMOTE_ADDR'] . " | grep -i 'hwaddr' | awk '{print $4}'") ?: null;
        }
        return $mac && strlen($mac) === 17 ? strtoupper($mac) : null;
    }

    private function sendSms(string $phone, string $message): bool {
        try {
            $gateway = $this->db->fetchOne("SELECT * FROM sms_gateways WHERE is_default=1 AND is_active=1");
            if (!$gateway) return false;

            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($phone) === 11) $phone = '88' . $phone;

            $url = str_replace(['{apikey}', '{senderid}', '{mobile}', '{message}'], 
                [$gateway['api_key'], $gateway['sender_id'], $phone, urlencode($message)],
                $gateway['api_url']);

            $ch = curl_init();
            curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
            $response = curl_exec($ch);
            curl_close($ch);

            $this->db->insert('sms_logs', [
                'gateway_id' => $gateway['id'],
                'phone' => $phone,
                'message' => $message,
                'status' => $response ? 'sent' : 'failed',
                'gateway_response' => $response,
            ]);

            return (bool)$response;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getPortalSettings(): array {
        $rows = $this->db->fetchAll("SELECT setting_key, setting_value FROM portal_settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return array_merge([
            'portal_name' => 'Customer Portal',
            'allow_phone_login' => '1',
            'allow_email_login' => '1',
            'allow_pppoe_login' => '1',
            'allow_secret_question' => '1',
            'require_mac_verification' => '0',
            'max_mac_devices' => '5',
            'session_timeout_hours' => '24',
            'enable_bkash_payment' => '1',
            'enable_nagad_payment' => '1',
        ], $settings);
    }
}
