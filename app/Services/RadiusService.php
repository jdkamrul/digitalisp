<?php

class RadiusService {
    private ?Database $db;
    private bool $enabled = false;

    public function __construct() {
        try {
            $this->db = Database::getInstance('radius');
            $this->enabled = true;
        } catch (Exception $e) {
            try {
                $this->db = Database::getInstance('default');
                $this->enabled = true;
            } catch (Exception $ex) {
                $this->db = null;
                $this->enabled = false;
            }
        }
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    /**
     * Add a user to radcheck for authentication.
     */
    public function addUser(string $username, string $password, string $attribute = 'Cleartext-Password', string $op = ':='): bool {
        if (!$this->enabled) return false;
        
        // Remove existing if any
        $this->db->delete('radcheck', 'username = ? AND attribute = ?', [$username, $attribute]);
        
        $this->db->insert('radcheck', [
            'username'  => $username,
            'attribute' => $attribute,
            'op'        => $op,
            'value'     => $password
        ]);
        return true;
    }

    /**
     * Update user password in radcheck.
     */
    public function updatePassword(string $username, string $newPassword): bool {
        if (!$this->enabled) return false;
        return (bool)$this->db->update('radcheck', 
            ['value' => $newPassword], 
            'username = ? AND attribute = ?', 
            [$username, 'Cleartext-Password']
        );
    }

    /**
     * Delete user from all radius tables.
     */
    public function deleteUser(string $username): bool {
        if (!$this->enabled) return false;
        $this->db->delete('radcheck', 'username = ?', [$username]);
        $this->db->delete('radreply', 'username = ?', [$username]);
        $this->db->delete('radusergroup', 'username = ?', [$username]);
        return true;
    }

    /**
     * Assign user to a group (Profile).
     */
    public function assignGroup(string $username, string $groupName): bool {
        if (!$this->enabled) return false;
        
        // Remove existing groups
        $this->db->delete('radusergroup', 'username = ?', [$username]);
        
        $this->db->insert('radusergroup', [
            'username'  => $username,
            'groupname' => $groupName,
            'priority'  => 1
        ]);
        return true;
    }

    /**
     * Get active sessions from radacct.
     */
    public function getActiveSessions(int $limit = 100): array {
        if (!$this->enabled) return [];
        return $this->db->fetchAll(
            "SELECT * FROM radacct WHERE acctstoptime IS NULL ORDER BY acctstarttime DESC LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get usage history for a user.
     */
    public function getUserUsage(string $username, int $limit = 50): array {
        if (!$this->enabled) return [];
        return $this->db->fetchAll(
            "SELECT * FROM radacct WHERE username = ? ORDER BY acctstarttime DESC LIMIT ?",
            [$username, $limit]
        );
    }

    /**
     * Get total data usage (Upload/Download) for a user in current month.
     */
    public function getMonthlyUsage(string $username): array {
        if (!$this->enabled) return ['download' => 0, 'upload' => 0];
        
        $sql = "SELECT SUM(acctinputoctets) as upload, SUM(acctoutputoctets) as download 
                FROM radacct 
                WHERE username = ? 
                AND acctstarttime >= DATE_FORMAT(NOW() ,'%Y-%m-01')";
        
        // Handle SQLite if needed (though RADIUS is usually MySQL)
        if ($this->isSQLite()) {
            $sql = "SELECT SUM(acctinputoctets) as upload, SUM(acctoutputoctets) as download 
                    FROM radacct 
                    WHERE username = ? 
                    AND acctstarttime >= date('now', 'start of month')";
        }

        $row = $this->db->fetchOne($sql, [$username]);
        return [
            'upload'   => (int)($row['upload'] ?? 0),
            'download' => (int)($row['download'] ?? 0)
        ];
    }

    private function isSQLite(): bool {
        return $this->db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
    }

    /**
     * Add/update reply attribute (e.g., static IP).
     */
    public function addReply(string $username, string $attribute, string $value, string $op = '='): bool {
        if (!$this->enabled) return false;
        
        $this->db->delete('radreply', 'username = ? AND attribute = ?', [$username, $attribute]);
        
        $this->db->insert('radreply', [
            'username'  => $username,
            'attribute' => $attribute,
            'op'        => $op,
            'value'     => $value
        ]);
        return true;
    }

    /**
     * Get all RADIUS users with their profiles.
     */
    public function getAllUsers(): array {
        if (!$this->enabled) return [];
        
        return $this->db->fetchAll(
            "SELECT c.username, c.attribute, c.value as password, 
                    c.op, g.groupname as profile, r.value as ip_address
             FROM radcheck c
             LEFT JOIN radusergroup g ON g.username = c.username
             LEFT JOIN radreply r ON r.username = c.username AND r.attribute = 'Framed-IP-Address'
             WHERE c.attribute = 'Cleartext-Password'
             ORDER BY c.username"
        );
    }

    /**
     * Get count of online sessions.
     */
    public function getOnlineCount(): int {
        if (!$this->enabled) return 0;
        
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM radacct WHERE acctstoptime IS NULL"
        );
        return (int)($result['cnt'] ?? 0);
    }

    /**
     * Get NAS devices for accounting.
     */
    public function getNasDevices(): array {
        if (!$this->enabled) return [];
        
        return $this->db->fetchAll("SELECT nasipaddress, COUNT(*) as cnt 
            FROM radacct WHERE acctstoptime IS NULL GROUP BY nasipaddress");
    }

    // ── Bulk Operations for High-Volume RADIUS Management ───────────────────

    /**
     * Bulk add RADIUS users (optimized for 500+ users)
     * @param array $users Array of user data: [['username', 'password', 'profile', 'ip_address'], ...]
     * @return array ['success' => count, 'errors' => ['username' => 'error'], 'failed' => count]
     */
    public function bulkAddUsers(array $users): array {
        if (!$this->enabled) return ['success' => 0, 'errors' => [], 'failed' => count($users)];

        $success = 0;
        $errors = [];
        $batchSize = 100; // Process in batches

        foreach (array_chunk($users, $batchSize) as $batch) {
            foreach ($batch as $user) {
                try {
                    // Add to radcheck
                    $this->addUser(
                        $user['username'],
                        $user['password'],
                        $user['attribute'] ?? 'Cleartext-Password'
                    );

                    // Assign profile if provided
                    if (isset($user['profile'])) {
                        $this->assignGroup($user['username'], $user['profile']);
                    }

                    // Add static IP if provided
                    if (isset($user['ip_address'])) {
                        $this->addReply($user['username'], 'Framed-IP-Address', $user['ip_address']);
                    }

                    $success++;
                } catch (Exception $e) {
                    $errors[$user['username']] = $e->getMessage();
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Bulk update RADIUS users
     * @param array $users Array of updates: [['username', 'updates' => ['password', 'profile', 'ip_address']], ...]
     * @return array ['success' => count, 'errors' => ['username' => 'error'], 'failed' => count]
     */
    public function bulkUpdateUsers(array $users): array {
        if (!$this->enabled) return ['success' => 0, 'errors' => [], 'failed' => count($users)];

        $success = 0;
        $errors = [];
        $batchSize = 200;

        foreach (array_chunk($users, $batchSize) as $batch) {
            foreach ($batch as $user) {
                try {
                    $username = $user['username'];
                    $updates = $user['updates'];

                    if (isset($updates['password'])) {
                        $this->updatePassword($username, $updates['password']);
                    }

                    if (isset($updates['profile'])) {
                        $this->assignGroup($username, $updates['profile']);
                    }

                    if (isset($updates['ip_address'])) {
                        $this->addReply($username, 'Framed-IP-Address', $updates['ip_address']);
                    }

                    $success++;
                } catch (Exception $e) {
                    $errors[$user['username']] = $e->getMessage();
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Bulk delete RADIUS users
     * @param array $usernames Array of usernames to delete
     * @return array ['success' => count, 'errors' => ['username' => 'error'], 'failed' => count]
     */
    public function bulkDeleteUsers(array $usernames): array {
        if (!$this->enabled) return ['success' => 0, 'errors' => [], 'failed' => count($usernames)];

        $success = 0;
        $errors = [];
        $batchSize = 500; // Large batches for deletions

        foreach (array_chunk($usernames, $batchSize) as $batch) {
            foreach ($batch as $username) {
                try {
                    if ($this->deleteUser($username)) {
                        $success++;
                    } else {
                        $errors[$username] = 'Failed to delete user';
                    }
                } catch (Exception $e) {
                    $errors[$username] = $e->getMessage();
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Bulk assign users to a profile/group
     * @param array $assignments Array of ['username' => 'profile', ...]
     * @return array ['success' => count, 'errors' => ['username' => 'error'], 'failed' => count]
     */
    public function bulkAssignGroups(array $assignments): array {
        if (!$this->enabled) return ['success' => 0, 'errors' => [], 'failed' => count($assignments)];

        $success = 0;
        $errors = [];
        $batchSize = 200;

        foreach (array_chunk($assignments, $batchSize, true) as $batch) {
            foreach ($batch as $username => $profile) {
                try {
                    if ($this->assignGroup($username, $profile)) {
                        $success++;
                    } else {
                        $errors[$username] = 'Failed to assign group';
                    }
                } catch (Exception $e) {
                    $errors[$username] = $e->getMessage();
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Bulk get user usage statistics
     * @param array $usernames Array of usernames to get usage for
     * @param string $period 'current_month', 'last_month', 'current_year'
     * @return array ['username' => ['upload' => bytes, 'download' => bytes, 'sessions' => count], ...]
     */
    public function bulkGetUserUsage(array $usernames, string $period = 'current_month'): array {
        if (!$this->enabled) return [];

        $usage = [];
        $batchSize = 50;

        foreach (array_chunk($usernames, $batchSize) as $batch) {
            foreach ($batch as $username) {
                try {
                    $monthly = $this->getMonthlyUsage($username);
                    $sessions = count($this->getUserUsage($username, 1000)); // Get all sessions

                    $usage[$username] = [
                        'upload' => $monthly['upload'],
                        'download' => $monthly['download'],
                        'sessions' => $sessions,
                        'total_data' => $monthly['upload'] + $monthly['download']
                    ];
                } catch (Exception $e) {
                    $usage[$username] = [
                        'upload' => 0,
                        'download' => 0,
                        'sessions' => 0,
                        'total_data' => 0,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return $usage;
    }

    /**
     * Get RADIUS user count statistics
     * @return array ['total_users' => int, 'active_sessions' => int, 'online_users' => int]
     */
    public function getUserStatistics(): array {
        if (!$this->enabled) return ['total_users' => 0, 'active_sessions' => 0, 'online_users' => 0];

        try {
            $totalUsers = count($this->getAllUsers());
            $activeSessions = $this->getOnlineCount();
            $onlineUsers = count(array_unique(array_column($this->getActiveSessions(1000), 'username')));

            return [
                'total_users' => $totalUsers,
                'active_sessions' => $activeSessions,
                'online_users' => $onlineUsers
            ];
        } catch (Exception $e) {
            return [
                'total_users' => 0,
                'active_sessions' => 0,
                'online_users' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Bulk get active sessions with pagination
     * @param int $offset Starting offset
     * @param int $limit Maximum sessions to return
     * @return array Array of active sessions
     */
    public function bulkGetActiveSessions(int $offset = 0, int $limit = 1000): array {
        if (!$this->enabled) return [];

        return $this->db->fetchAll(
            "SELECT * FROM radacct WHERE acctstoptime IS NULL ORDER BY acctstarttime DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Get top data users for the current month
     * @param int $limit Number of top users to return
     * @return array Array of users with their data usage
     */
    public function getTopDataUsers(int $limit = 20): array {
        if (!$this->enabled) return [];

        $sql = "SELECT username, 
                       SUM(acctinputoctets) as upload, 
                       SUM(acctoutputoctets) as download,
                       (SUM(acctinputoctets) + SUM(acctoutputoctets)) as total
                FROM radacct 
                WHERE acctstarttime >= DATE_FORMAT(NOW() ,'%Y-%m-01')
                GROUP BY username 
                ORDER BY total DESC 
                LIMIT ?";

        if ($this->isSQLite()) {
            $sql = "SELECT username, 
                           SUM(acctinputoctets) as upload, 
                           SUM(acctoutputoctets) as download,
                           (SUM(acctinputoctets) + SUM(acctoutputoctets)) as total
                    FROM radacct 
                    WHERE acctstarttime >= date('now', 'start of month')
                    GROUP BY username 
                    ORDER BY total DESC 
                    LIMIT ?";
        }

        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Bulk add reply attributes for multiple users
     * @param array $attributes Array of ['username' => [['attribute', 'value', 'op'], ...], ...]
     * @return array ['success' => count, 'errors' => [], 'failed' => count]
     */
    public function bulkAddReplyAttributes(array $attributes): array {
        if (!$this->enabled) return ['success' => 0, 'errors' => [], 'failed' => count($attributes)];

        $success = 0;
        $errors = [];
        $batchSize = 100;

        foreach (array_chunk($attributes, $batchSize, true) as $batch) {
            foreach ($batch as $username => $userAttributes) {
                foreach ($userAttributes as $attr) {
                    try {
                        $this->addReply($username, $attr[0], $attr[1], $attr[2] ?? '=');
                        $success++;
                    } catch (Exception $e) {
                        $errors[$username . ':' . $attr[0]] = $e->getMessage();
                    }
                }
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Record accounting start.
     */
    public function startAccounting(string $sessionId, string $username, string $nasIp, 
        string $framedIp = '', string $callingStationId = ''): bool {
        if (!$this->enabled) return false;
        
        $uniqueId = bin2hex(random_bytes(16));
        
        $this->db->insert('radacct', [
            'acctsessionid'    => $sessionId,
            'acctuniqueid'     => $uniqueId,
            'username'        => $username,
            'nasipaddress'    => $nasIp,
            'acctstarttime'   => date('Y-m-d H:i:s'),
            'acctsessiontime' => 0,
            'acctinputoctets' => 0,
            'acctoutputoctets'=> 0,
            'framedipaddress' => $framedIp,
            'callingstationid'=> $callingStationId
        ]);
        return true;
    }

    /**
     * Stop accounting (session end).
     */
    public function stopAccounting(string $sessionId, string $terminateCause = 'User-Request'): bool {
        if (!$this->enabled) return false;
        
        $this->db->update('radacct', [
            'acctstoptime'       => date('Y-m-d H:i:s'),
            'acctterminatecause'=> $terminateCause
        ], 'acctsessionid = ? AND acctstoptime IS NULL', [$sessionId]);
        return true;
    }

    /**
     * Update accounting interim.
     */
    public function interimUpdate(string $sessionId, int $input, int $output, int $sessionTime): bool {
        if (!$this->enabled) return false;
        
        $this->db->update('radacct', [
            'acctupdatetime'  => date('Y-m-d H:i:s'),
            'acctinputoctets'  => $input,
            'acctoutputoctets' => $output,
            'acctsessiontime'  => $sessionTime
        ], 'acctsessionid = ? AND acctstoptime IS NULL', [$sessionId]);
        return true;
    }

    /**
     * Kick/disconnect active session.
     */
    public function kickUser(string $username): bool {
        if (!$this->enabled) return false;
        
        $this->db->update('radacct', [
            'acctstoptime'       => date('Y-m-d H:i:s'),
            'acctterminatecause'=> 'Admin-Reset'
        ], 'username = ? AND acctstoptime IS NULL', [$username]);
        return true;
    }

    /**
     * Get group attributes (profile settings).
     */
    public function getGroupAttributes(string $groupName): array {
        if (!$this->enabled) return [];
        
        return $this->db->fetchAll(
            "SELECT attribute, op, value FROM radgroupreply WHERE groupname = ?",
            [$groupName]
        );
    }

    /**
     * Add group attribute (e.g., speed limit).
     */
    public function addGroupAttribute(string $groupName, string $attribute, string $value, string $op = '='): bool {
        if (!$this->enabled) return false;
        
        $this->db->insert('radgroupreply', [
            'groupname' => $groupName,
            'attribute' => $attribute,
            'op'        => $op,
            'value'     => $value
        ]);
        return true;
    }
}
