<?php

class AuthController {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function showLogin(): void {
        if (isset($_SESSION['user_id'])) {
            redirect(base_url('dashboard'));
        }
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require_once BASE_PATH . '/views/auth/login.php';
    }

    public function login(): void {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter username and password.';
            redirect(base_url('login'));
        }

        $user = $this->db->fetchOne(
            "SELECT u.*, r.name as role_name, r.display_name as role_display, b.name as branch_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN branches b ON b.id = u.branch_id
             WHERE u.username = ? AND u.is_active = 1",
            [$username]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['login_error'] = 'Invalid username or password.';
            $this->logActivity(null, 'login_failed', 'auth', null, ['username' => $username]);
            redirect(base_url('login'));
        }

        // Set session
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['user_name']     = $user['full_name'];
        $_SESSION['username']      = $user['username'];
        $_SESSION['user_role']     = $user['role_name'];
        $_SESSION['role_display']  = $user['role_display'];
        $_SESSION['branch_id']     = $user['branch_id'];
        $_SESSION['branch_name']   = $user['branch_name'] ?? 'Head Office';
        $_SESSION['user_avatar']   = $user['avatar'];

        // Update last login
        $this->db->update('users',
            ['last_login' => date('Y-m-d H:i:s'), 'last_ip' => $_SERVER['REMOTE_ADDR'] ?? ''],
            'id = ?', [$user['id']]
        );

        $this->logActivity($user['id'], 'login', 'auth', $user['id']);
        redirect(base_url('dashboard'));
    }

    public function logout(): void {
        $userId = $_SESSION['user_id'] ?? null;
        $this->logActivity($userId, 'logout', 'auth', $userId);
        session_destroy();
        redirect(base_url('login'));
    }

    private function logActivity(?int $userId, string $action, string $module, ?int $recordId, array $extra = []): void {
        try {
            $this->db->insert('activity_logs', [
                'user_id'    => $userId,
                'action'     => $action,
                'module'     => $module,
                'record_id'  => $recordId,
                'new_values' => !empty($extra) ? json_encode($extra) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);
        } catch (Exception $e) {
            // Silently fail on log errors
        }
    }
}
