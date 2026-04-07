<?php

class PortalProfileController extends PortalController {

    public function __construct() {
        parent::__construct();
    }

    public function index(): void {
        $this->requireAuth();
        $customer = $this->getCustomer();
        $pageTitle = 'My Profile';
        $currentPage = 'profile';
        $portalCustomer = $this->customer;
        $content = BASE_PATH . '/views/portal/profile/index_content.php';

        require_once BASE_PATH . '/views/portal/layouts/main.php';
    }

    public function updateProfile(): void {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('portal/profile'));
        }

        $customerId = $this->getCustomerId();
        $phone = sanitize($_POST['phone'] ?? '');
        $phoneAlt = sanitize($_POST['phone_alt'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $billingAddress = sanitize($_POST['billing_address'] ?? '');
        $billingCompany = sanitize($_POST['billing_company_name'] ?? '');
        $billingVat = sanitize($_POST['billing_vat_reg'] ?? '');

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['portal_error'] = 'Invalid email address.';
            redirect(base_url('portal/profile'));
        }

        $this->db->update('customers', [
            'phone' => $phone,
            'phone_alt' => $phoneAlt,
            'email' => $email,
            'address' => $address,
            'billing_address' => $billingAddress,
            'billing_company_name' => $billingCompany,
            'billing_vat_reg' => $billingVat,
        ], 'id=?', [$customerId]);

        $_SESSION['portal_success'] = 'Profile updated successfully.';
        redirect(base_url('portal/profile'));
    }

    public function showChangePassword(): void {
        $this->requireAuth();
        $pageTitle = 'Change Password';
        require_once BASE_PATH . '/views/portal/profile/change-password.php';
    }

    public function changePassword(): void {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('portal/profile'));
        }

        $customerId = $this->getCustomerId();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $_SESSION['portal_error'] = 'All password fields are required.';
            redirect(base_url('portal/profile/change-password'));
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['portal_error'] = 'New passwords do not match.';
            redirect(base_url('portal/profile/change-password'));
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['portal_error'] = 'Password must be at least 6 characters.';
            redirect(base_url('portal/profile/change-password'));
        }

        $customer = $this->db->fetchOne("SELECT * FROM customers WHERE id = ?", [$customerId]);

        $passwordValid = false;
        if (!empty($customer['portal_password'])) {
            $passwordValid = password_verify($currentPassword, $customer['portal_password']);
        } elseif (!empty($customer['pppoe_password'])) {
            $passwordValid = ($currentPassword === $customer['pppoe_password']);
        }

        if (!$passwordValid) {
            $_SESSION['portal_error'] = 'Current password is incorrect.';
            redirect(base_url('portal/profile/change-password'));
        }

        $this->db->update('customers', [
            'portal_password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ], 'id=?', [$customerId]);

        $_SESSION['portal_success'] = 'Password changed successfully.';
        redirect(base_url('portal/profile'));
    }

    public function showSecretQuestion(): void {
        $this->requireAuth();
        $customer = $this->getCustomer();
        $pageTitle = 'Security Question';
        require_once BASE_PATH . '/views/portal/profile/secret-question.php';
    }

    public function setSecretQuestion(): void {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('portal/profile'));
        }

        $customerId = $this->getCustomerId();
        $question = sanitize($_POST['secret_question'] ?? '');
        $answer = sanitize($_POST['secret_answer'] ?? '');

        if (empty($question) || empty($answer)) {
            $_SESSION['portal_error'] = 'Question and answer are required.';
            redirect(base_url('portal/profile/secret-question'));
        }

        $this->db->update('customers', [
            'secret_question' => $question,
            'secret_answer' => $answer,
        ], 'id=?', [$customerId]);

        $_SESSION['portal_success'] = 'Security question set successfully.';
        redirect(base_url('portal/profile'));
    }

    public function macDevices(): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();
        $settings = $this->getPortalSettings();

        $devices = $this->db->fetchAll(
            "SELECT * FROM customer_mac_access WHERE customer_id = ? ORDER BY last_seen DESC",
            [$customerId]
        );

        $maxDevices = (int)($settings['max_mac_devices'] ?? 5);
        $canAdd = count($devices) < $maxDevices;
        $pageTitle = 'Registered Devices';
        require_once BASE_PATH . '/views/portal/profile/mac-devices.php';
    }

    public function addMacDevice(): void {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(base_url('portal/profile/mac-devices'));
        }

        $customerId = $this->getCustomerId();
        $mac = strtoupper(sanitize($_POST['mac_address'] ?? ''));
        $deviceName = sanitize($_POST['device_name'] ?? '');

        if (!preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/', $mac)) {
            $_SESSION['portal_error'] = 'Invalid MAC address format.';
            redirect(base_url('portal/profile/mac-devices'));
        }

        $existing = $this->db->fetchOne(
            "SELECT id FROM customer_mac_access WHERE customer_id = ? AND mac_address = ?",
            [$customerId, $mac]
        );

        if ($existing) {
            $_SESSION['portal_error'] = 'This device is already registered.';
            redirect(base_url('portal/profile/mac-devices'));
        }

        $this->db->insert('customer_mac_access', [
            'customer_id' => $customerId,
            'mac_address' => $mac,
            'device_name' => $deviceName ?: 'Unknown Device',
            'is_allowed' => 1,
            'is_active' => 1,
        ]);

        $_SESSION['portal_success'] = 'Device registered successfully.';
        redirect(base_url('portal/profile/mac-devices'));
    }

    public function removeMacDevice(): void {
        $this->requireAuth();
        
        $customerId = $this->getCustomerId();
        $deviceId = (int)($_POST['device_id'] ?? 0);

        $device = $this->db->fetchOne(
            "SELECT id FROM customer_mac_access WHERE id = ? AND customer_id = ?",
            [$deviceId, $customerId]
        );

        if (!$device) {
            $_SESSION['portal_error'] = 'Device not found.';
            redirect(base_url('portal/profile/mac-devices'));
        }

        $this->db->delete('customer_mac_access', 'id = ?', [$deviceId]);

        $_SESSION['portal_success'] = 'Device removed.';
        redirect(base_url('portal/profile/mac-devices'));
    }

    public function notifications(): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();

        $notifications = $this->db->fetchAll(
            "SELECT * FROM customer_notifications WHERE customer_id = ? ORDER BY created_at DESC LIMIT 50",
            [$customerId]
        );

        $unreadCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM customer_notifications WHERE customer_id = ? AND is_read = 0",
            [$customerId]
        )['count'] ?? 0;

        $pageTitle = 'Notifications';
        require_once BASE_PATH . '/views/portal/profile/notifications.php';
    }

    public function markNotificationRead(): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();
        $notifId = (int)($_POST['notification_id'] ?? 0);

        $this->db->update('customer_notifications', ['is_read' => 1], 'id = ? AND customer_id = ?', [$notifId, $customerId]);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->jsonResponse(['success' => true]);
        } else {
            redirect(base_url('portal/profile/notifications'));
        }
    }

    public function getLoginHistory(): void {
        $this->requireAuth();
        $customerId = $this->getCustomerId();

        $history = $this->db->fetchAll(
            "SELECT * FROM customer_portal_login_logs WHERE customer_id = ? ORDER BY created_at DESC LIMIT 20",
            [$customerId]
        );

        $pageTitle = 'Login History';
        require_once BASE_PATH . '/views/portal/profile/login-history.php';
    }

    private function getPortalSettings(): array {
        $rows = $this->db->fetchAll("SELECT setting_key, setting_value FROM portal_settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
}
