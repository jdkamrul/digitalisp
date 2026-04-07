<?php

class MikroTikService {
    private $socket = null;
    private bool $connected = false;
    private string $ip;
    private int $port;
    private string $username;
    private string $password;
    private int $timeout;

    public function __construct(array $config) {
        $this->ip       = $config['ip'] ?? '';
        $this->port     = (int)($config['port'] ?? 8728);
        $this->username = $config['username'] ?? 'admin';
        $this->password = $config['password'] ?? '';
        $this->timeout  = (int)($config['timeout'] ?? 10);
    }

    public function connect(): bool {
        try {
            $this->socket = fsockopen($this->ip, $this->port, $errno, $errstr, $this->timeout);
            if (!$this->socket) { return false; }
            stream_set_timeout($this->socket, $this->timeout);
            $this->connected = $this->login();
            return $this->connected;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function login(): bool {
        $result = $this->communicate(['/login', '=name=' . $this->username, '=password=' . $this->password]);
        return !empty($result) && isset($result[0]) && $result[0] === '!done';
    }

    public function addPPPoEUser(string $username, string $password, string $profile = 'default', string $remoteAddress = '', string $comment = ''): bool {
        if (!$this->connected) return false;
        $cmd = [
            '/ppp/secret/add',
            '=name='     . $username,
            '=password=' . $password,
            '=profile='  . $profile,
            '=service=pppoe',
        ];
        if ($remoteAddress !== '') $cmd[] = '=remote-address=' . $remoteAddress;
        if ($comment !== '')       $cmd[] = '=comment=' . $comment;
        $result = $this->communicate($cmd);
        return $this->isSuccess($result);
    }

    /** Update PPPoE user password. Alias used by bulk operations. */
    public function updatePPPoEUser(string $username, string $newPassword): bool {
        if (!$this->connected) return false;
        $id = $this->getUserId('/ppp/secret', $username);
        if (!$id) return false;
        $result = $this->communicate(['/ppp/secret/set', '=.id=' . $id, '=password=' . $newPassword]);
        return $this->isSuccess($result);
    }

    /** Delete PPPoE user. Alias for removePPPoEUser used by bulk operations. */
    public function deletePPPoEUser(string $username): bool {
        return $this->removePPPoEUser($username);
    }

    /** Set user profile. Alias for changeUserProfile used by bulk operations. */
    public function setUserProfile(string $username, string $profile): bool {
        return $this->changeUserProfile($username, $profile);
    }

    /** Enable or disable a PPPoE user. Used by bulk operations. */
    public function setUserStatus(string $username, bool $enabled): bool {
        return $enabled ? $this->enablePPPoEUser($username) : $this->disablePPPoEUser($username);
    }

    public function removePPPoEUser(string $username): bool {
        if (!$this->connected) return false;
        $id = $this->getUserId('/ppp/secret', $username);
        if (!$id) return false;
        $result = $this->communicate(['/ppp/secret/remove', '=.id=' . $id]);
        return $this->isSuccess($result);
    }

    public function disablePPPoEUser(string $username): bool {
        if (!$this->connected) return false;
        $id = $this->getUserId('/ppp/secret', $username);
        if (!$id) return false;
        $result = $this->communicate(['/ppp/secret/set', '=.id=' . $id, '=disabled=yes']);
        return $this->isSuccess($result);
    }

    public function enablePPPoEUser(string $username): bool {
        if (!$this->connected) return false;
        $id = $this->getUserId('/ppp/secret', $username);
        if (!$id) return false;
        $result = $this->communicate(['/ppp/secret/set', '=.id=' . $id, '=disabled=no']);
        return $this->isSuccess($result);
    }

    public function changeUserProfile(string $username, string $newProfile): bool {
        if (!$this->connected) return false;
        $id = $this->getUserId('/ppp/secret', $username);
        if (!$id) return false;
        $result = $this->communicate(['/ppp/secret/set', '=.id=' . $id, '=profile=' . $newProfile]);
        return $this->isSuccess($result);
    }

    public function getActiveSessions(): array {
        if (!$this->connected) return [];
        $result = $this->communicate([
            '/ppp/active/print',
            '=.proplist=.id,name,address,uptime,bytes-in,bytes-out,profile,service,caller-id',
        ]);
        return $this->parseResponse($result);
    }

    /**
     * Returns a map of username => ['bytes_in' => int, 'bytes_out' => int, 'rate_in' => int, 'rate_out' => int]
     * by reading /queue/simple where names match PPPoE usernames.
     * The 'bytes' field format: "upload_bytes/download_bytes"
     * The 'rate'  field format: "upload_bps/download_bps"
     */
    public function getQueueStats(): array {
        if (!$this->connected) return [];
        $raw = $this->communicate([
            '/queue/simple/print',
            '=.proplist=name,bytes,rate',
        ]);
        $queues = $this->parseResponse($raw);
        $stats = [];
        foreach ($queues as $q) {
            $name = $q['name'] ?? '';
            if (empty($name)) continue;
            // bytes = "tx_bytes/rx_bytes" (tx = upload from router PoV = download for user)
            $bytesParts = explode('/', $q['bytes'] ?? '0/0');
            $rateParts  = explode('/', $q['rate']  ?? '0/0');
            $stats[$name] = [
                'bytes_tx'  => (int)($bytesParts[0] ?? 0),  // router→user = download
                'bytes_rx'  => (int)($bytesParts[1] ?? 0),  // user→router = upload
                'rate_tx'   => (int)($rateParts[0]  ?? 0),
                'rate_rx'   => (int)($rateParts[1]  ?? 0),
            ];
        }
        return $stats;
    }

    /**
     * Fetches statistics for all interfaces.
     * Returns a map of name => ['rx' => int, 'tx' => int]
     */
    public function getInterfaceStats(): array {
        if (!$this->connected) return [];
        $raw = $this->communicate([
            '/interface/print',
            '=.proplist=name,rx-byte,tx-byte,type,running'
        ]);
        $ifaces = $this->parseResponse($raw);
        $stats = [];
        foreach ($ifaces as $i) {
            if (empty($i['name'])) continue;
            $stats[$i['name']] = [
                'rx' => (int)($i['rx-byte'] ?? 0),
                'tx' => (int)($i['tx-byte'] ?? 0)
            ];
        }
        return $stats;
    }

    public function kickSession(string $username): bool {
        if (!$this->connected) return false;
        foreach ($this->getActiveSessions() as $session) {
            if (($session['name'] ?? '') === $username) {
                $result = $this->communicate(['/ppp/active/remove', '=.id=' . $session['.id']]);
                return $this->isSuccess($result);
            }
        }
        return false;
    }

    public function addBandwidthProfile(string $name, string $rateLimit, string $burstLimit = ''): bool {
        if (!$this->connected) return false;
        $cmd = ['/ppp/profile/add', '=name=' . $name, '=rate-limit=' . $rateLimit];
        if ($burstLimit) $cmd[] = '=burst-limit=' . $burstLimit;
        return $this->isSuccess($this->communicate($cmd));
    }

    /**
     * Create or update a full PPPoE profile with all attributes.
     * Used by syncProfileToNas().
     */
    public function createPppoeProfile(string $name, array $attrs = []): bool {
        if (!$this->connected) return false;

        // Check if profile already exists
        $existing = $this->communicate(['/ppp/profile/print', '?name=' . $name]);
        $rows = $this->parseResponse($existing);

        $cmd = [];
        if (!empty($rows) && isset($rows[0]['.id'])) {
            // Update existing
            $cmd[] = '/ppp/profile/set';
            $cmd[] = '=.id=' . $rows[0]['.id'];
        } else {
            // Create new
            $cmd[] = '/ppp/profile/add';
            $cmd[] = '=name=' . $name;
        }

        $map = [
            'local-address'   => 'local-address',
            'remote-address'  => 'remote-address',
            'dns-server'      => 'dns-server',
            'session-timeout' => 'session-timeout',
            'idle-timeout'    => 'idle-timeout',
            'rate-limit'      => 'rate-limit',
        ];
        foreach ($map as $key => $apiKey) {
            if (!empty($attrs[$key])) {
                $cmd[] = '=' . $apiKey . '=' . $attrs[$key];
            }
        }

        return $this->isSuccess($this->communicate($cmd));
    }

    public function getSystemInfo(): array {
        if (!$this->connected) return [];
        $result = $this->communicate(['/system/resource/print']);
        return $this->parseResponse($result)[0] ?? [];
    }

    public function getProfiles(): array {
        if (!$this->connected) return [];
        $result = $this->communicate(['/ppp/profile/print']);
        return $this->parseResponse($result);
    }

    public function getIPPoolUsage(string $poolName): array {
        if (!$this->connected) return [];
        $result = $this->communicate(['/ip/pool/used/print', '?pool=' . $poolName]);
        return $this->parseResponse($result);
    }

    private function getUserId(string $path, string $name): ?string {
        $result = $this->communicate([$path . '/print', '?name=' . $name]);
        $rows   = $this->parseResponse($result);
        return $rows[0]['.id'] ?? null;
    }

    public function communicate(array $cmd): array {
        foreach ($cmd as $word) {
            $len = strlen($word);
            $lenBytes = '';
            if ($len < 0x80)       $lenBytes = chr($len);
            elseif ($len < 0x4000) $lenBytes = chr(($len >> 8) | 0x80) . chr($len & 0xFF);
            elseif ($len < 0x200000) $lenBytes = chr(($len >> 16) | 0xC0) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
            else $lenBytes = chr(0xE0) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF);
            fwrite($this->socket, $lenBytes . $word);
        }
        fwrite($this->socket, chr(0)); // end of sentence
        return $this->readResponse();
    }

    private function readResponse(): array {
        $result = [];
        $done = false;
        while (!$done) {
            while (true) {
                $len = $this->getLength();
                if ($len === 0) break;
                $word = '';
                while (strlen($word) < $len) {
                    $word .= fread($this->socket, $len - strlen($word));
                }
                $result[] = $word;
                if ($word === '!done' || $word === '!trap' || $word === '!fatal') {
                    $done = true;
                }
            }
        }
        return $result;
    }

    private function getLength(): int {
        $b = ord(fread($this->socket, 1));
        if ($b < 0x80)   return $b;
        if ($b < 0xC0)   return (($b & ~0x80) << 8) | ord(fread($this->socket, 1));
        if ($b < 0xE0) { $b2 = unpack('n', fread($this->socket, 2))[1]; return (($b & ~0xC0) << 16) | $b2; }
        $b234 = unpack('N', "\x00" . fread($this->socket, 3))[1]; return (($b & 0x0F) << 24) | $b234;
    }

    public function parseResponse(array $result): array {
        $rows = []; $current = [];
        foreach ($result as $word) {
            if ($word === '!re') { if (!empty($current)) { $rows[] = $current; $current = []; } continue; }
            if ($word === '!done') { if (!empty($current)) $rows[] = $current; break; }
            if (str_starts_with($word, '=')) {
                [,$kv] = explode('=', $word, 2);
                [$k, $v] = array_pad(explode('=', $kv, 2), 2, '');
                $current[$k] = $v;
            }
        }
        return $rows;
    }

    private function isSuccess(array $result): bool {
        return in_array('!done', $result);
    }

    public function disconnect(): void {
        if ($this->socket) { fclose($this->socket); $this->socket = null; }
        $this->connected = false;
    }

    public function __destruct() { $this->disconnect(); }

    // ── RADIUS Configuration ─────────────────────────────────────────

    /**
     * Configure RADIUS server settings on MikroTik
     */
    public function configureRadius(string $address, string $secret, int $port = 1812, string $service = 'ppp'): bool {
        if (!$this->connected) return false;

        // Remove existing RADIUS entries for this service
        $existing = $this->communicate(['/radius/print', '?service=' . $service]);
        foreach ($this->parseResponse($existing) as $row) {
            if (!empty($row['.id'])) {
                $this->communicate(['/radius/remove', '=.id=' . $row['.id']]);
            }
        }

        // Add new RADIUS server
        $result = $this->communicate([
            '/radius/add',
            '=address='  . $address,
            '=secret='   . $secret,
            '=port='     . $port,
            '=service='  . $service,
            '=timeout=3000ms',
            '=disabled=no',
        ]);
        return $this->isSuccess($result);
    }

    /**
     * Enable/disable RADIUS for specific service
     */
    public function setRadiusForService(string $service, bool $enabled): bool {
        if (!$this->connected) return false;
        
        $result = $this->communicate([
            '/ip-service/set',
            '=.id=' . $service,
            '=disabled=' . ($enabled ? 'no' : 'yes'),
        ]);
        return $this->isSuccess($result);
    }

    /**
     * Get current RADIUS configuration
     */
    public function getRadiusConfig(): array {
        if (!$this->connected) return [];
        $result = $this->communicate(['/radius/print']);
        return $this->parseResponse($result);
    }

    /**
     * Configure PPP service to use RADIUS
     */
    public function enablePppRadius(): bool {
        if (!$this->connected) return false;
        $result = $this->communicate([
            '/ppp/aaa/set',
            '=use-radius=yes',
            '=accounting=yes',
            '=interim-update=300s',
        ]);
        return $this->isSuccess($result);
    }

    /**
     * Get PPP service RADIUS status
     */
    public function getPppRadiusStatus(): array {
        if (!$this->connected) return [];
        $result = $this->communicate(['/ppp/aaa/print']);
        return $this->parseResponse($result)[0] ?? [];
    }

    // ── PPPoE Server Configuration ──────────────────────────────────

    /**
     * Create PPPoE server profile
     */
    public function createPppoeServer(string $name, string $interface, string $profile = 'default'): bool {
        if (!$this->connected) return false;
        $result = $this->communicate([
            '/interface/pppoe-server-server/add',
            '=service-name=' . $name,
            '=interface=' . $interface,
            '=default-profile=' . $profile,
            '=enabled=yes',
        ]);
        return $this->isSuccess($result);
    }

    /**
     * Get PPPoE server status
     */
    public function getPppoeServers(): array {
        if (!$this->connected) return [];
        $result = $this->communicate(['/interface/pppoe-server-server/print']);
        return $this->parseResponse($result);
    }

    // ── Queue Management ───────────────────────────────────────────

    /**
     * Create simple queue for bandwidth control
     */
    public function createQueue(string $name, string $target, string $rateLimit, int $burstTime = 0): bool {
        if (!$this->connected) return false;
        $cmd = [
            '/queue/simple/add',
            '=name=' . $name,
            '=target=' . $target,
            '=max-limit=' . $rateLimit,
        ];
        if ($burstTime > 0) {
            $cmd[] = '=burst-limit=' . (intval($rateLimit) * 2);
            $cmd[] = '=burst-threshold=' . $rateLimit;
            $cmd[] = '=burst-time=' . $burstTime . 's';
        }
        return $this->isSuccess($this->communicate($cmd));
    }

    /**
     * Update queue bandwidth
     */
    public function updateQueue(string $name, string $rateLimit): bool {
        if (!$this->connected) return false;
        $id = $this->getQueueId($name);
        if (!$id) return false;
        $result = $this->communicate([
            '/queue/simple/set',
            '=.id=' . $id,
            '=max-limit=' . $rateLimit,
        ]);
        return $this->isSuccess($result);
    }

    private function getQueueId(string $name): ?string {
        $result = $this->communicate(['/queue/simple/print', '?name=' . $name]);
        $rows = $this->parseResponse($result);
        return $rows[0]['.id'] ?? null;
    }

    // ── IP Pool Management ──────────────────────────────────────────

    /**
     * Create IP pool
     */
    public function createIpPool(string $name, string $range): bool {
        if (!$this->connected) return false;
        $result = $this->communicate([
            '/ip/pool/add',
            '=name=' . $name,
            '=ranges=' . $range,
        ]);
        return $this->isSuccess($result);
    }

    /**
     * Get IP pools
     */
    public function getIpPools(): array {
        if (!$this->connected) return [];
        $result = $this->communicate(['/ip/pool/print']);
        return $this->parseResponse($result);
    }

    /**
     * Get used addresses in pool
     */
    public function getPoolUsage(string $poolName): array {
        if (!$this->connected) return [];
        $result = $this->communicate(['/ip/pool/used/print', '?pool=' . $poolName]);
        return $this->parseResponse($result);
    }

    // ── Radius Accounting Sync ─────────────────────────────────────

    /**
     * Sync PPP users from RADIUS to MikroTik
     */
    public function syncUsersFromRadius(array $radiusUsers): array {
        if (!$this->connected) return ['error' => 'Not connected'];
        
        $synced = 0;
        $errors = [];
        
        // Get current MikroTik users
        $mtUsers = $this->communicate(['/ppp/secret/print', '=.proplist=name']);
        $mtUserList = array_column($this->parseResponse($mtUsers), 'name');
        
        foreach ($radiusUsers as $user) {
            try {
                if (!in_array($user['username'], $mtUserList)) {
                    $this->addPPPoEUser(
                        $user['username'],
                        $user['password'],
                        $user['profile'] ?? 'default'
                    );
                    $synced++;
                }
            } catch (Exception $e) {
                $errors[] = "Failed to sync {$user['username']}: " . $e->getMessage();
            }
        }
        
        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Get RADIUS accounting data
     */
    public function getAccountingData(): array {
        if (!$this->connected) return [];
        $result = $this->communicate([
            '/tool/user-manager/user/print',
        ]);
        return $this->parseResponse($result);
    }

    // ── Bulk Operations for High-Volume Management ─────────────────

    /**
     * Bulk add multiple PPPoE users (optimized for 500+ clients)
     * @param array $users Array of user data: [['username', 'password', 'profile', 'remote_address'], ...]
     * @return array ['success' => count, 'errors' => ['username' => 'error'], 'failed' => count]
     */
    public function bulkAddPPPoEUsers(array $users): array {
        if (!$this->connected) return ['success' => 0, 'errors' => [], 'failed' => count($users)];

        $success = 0;
        $errors = [];
        $batchSize = 50; // Process in batches to avoid timeout

        foreach (array_chunk($users, $batchSize) as $batch) {
            foreach ($batch as $user) {
                try {
                    $result = $this->addPPPoEUser(
                        $user['username'],
                        $user['password'],
                        $user['profile'] ?? 'default',
                        $user['remote_address'] ?? '',
                        $user['comment'] ?? ''
                    );
                    if ($result) {
                        $success++;
                    } else {
                        $errors[$user['username']] = 'Failed to add user';
                    }
                } catch (Exception $e) {
                    $errors[$user['username']] = $e->getMessage();
                }
            }
            // Small delay between batches to prevent overwhelming the device
            usleep(100000);
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Bulk update PPPoE users (password, profile, status)
     * @param array $users Array of user updates: [['username', 'updates' => ['password', 'profile', 'disabled']], ...]
     * @return array ['success' => count, 'errors' => ['username' => 'error'], 'failed' => count]
     */
    public function bulkUpdatePPPoEUsers(array $users): array {
        if (!$this->connected) return ['success' => 0, 'errors' => [], 'failed' => count($users)];

        $success = 0;
        $errors = [];
        $batchSize = 100; // Larger batches for updates

        foreach (array_chunk($users, $batchSize) as $batch) {
            foreach ($batch as $user) {
                try {
                    $username = $user['username'];
                    $updates = $user['updates'];

                    if (isset($updates['password'])) {
                        $this->updatePPPoEUser($username, $updates['password']);
                    }

                    if (isset($updates['profile'])) {
                        $this->setUserProfile($username, $updates['profile']);
                    }

                    if (isset($updates['disabled'])) {
                        $this->setUserStatus($username, !$updates['disabled']);
                    }

                    $success++;
                } catch (Exception $e) {
                    $errors[$user['username']] = $e->getMessage();
                }
            }
            usleep(50000);
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Bulk delete PPPoE users
     * @param array $usernames Array of usernames to delete
     * @return array ['success' => count, 'errors' => ['username' => 'error'], 'failed' => count]
     */
    public function bulkDeletePPPoEUsers(array $usernames): array {
        if (!$this->connected) return ['success' => 0, 'errors' => [], 'failed' => count($usernames)];

        $success = 0;
        $errors = [];
        $batchSize = 200; // Large batches for deletions

        foreach (array_chunk($usernames, $batchSize) as $batch) {
            foreach ($batch as $username) {
                try {
                    if ($this->deletePPPoEUser($username)) {
                        $success++;
                    } else {
                        $errors[$username] = 'Failed to delete user';
                    }
                } catch (Exception $e) {
                    $errors[$username] = $e->getMessage();
                }
            }
            usleep(20000);
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Bulk get PPPoE users with pagination support
     * @param int $offset Starting offset
     * @param int $limit Number of users to retrieve
     * @return array Array of user data
     */
    public function bulkGetPPPoEUsers(int $offset = 0, int $limit = 500): array {
        if (!$this->connected) return [];

        $result = $this->communicate([
            '/ppp/secret/print',
            '=from=' . $offset,
            '=count=' . $limit,
        ]);

        return $this->parseResponse($result);
    }

    /**
     * Get all PPPoE users count
     * @return int Total number of PPPoE users
     */
    public function getPPPoEUserCount(): int {
        if (!$this->connected) return 0;

        $result = $this->communicate(['/ppp/secret/print', '=.proplist=.id', '=count-only=']);
        $response = $this->parseResponse($result);
        return (int)($response[0]['count'] ?? 0);
    }

    /**
     * Bulk get active PPPoE sessions
     * @param int $limit Maximum sessions to retrieve
     * @return array Array of active sessions
     */
    public function bulkGetActiveSessions(int $limit = 1000): array {
        if (!$this->connected) return [];

        $result = $this->communicate([
            '/ppp/active/print',
            '=count=' . $limit,
        ]);

        return $this->parseResponse($result);
    }

    /**
     * Bulk create queues for multiple users
     * @param array $queues Array of queue data: [['name', 'target', 'max_limit'], ...]
     * @return array ['success' => count, 'errors' => [], 'failed' => count]
     */
    public function bulkCreateQueues(array $queues): array {
        if (!$this->connected) return ['success' => 0, 'errors' => [], 'failed' => count($queues)];

        $success = 0;
        $errors = [];
        $batchSize = 100;

        foreach (array_chunk($queues, $batchSize) as $batch) {
            foreach ($batch as $queue) {
                try {
                    if ($this->createQueue(
                        $queue['name'],
                        $queue['target'],
                        $queue['max_limit'],
                        $queue['burst_time'] ?? 0
                    )) {
                        $success++;
                    } else {
                        $errors[$queue['name']] = 'Failed to create queue';
                    }
                } catch (Exception $e) {
                    $errors[$queue['name']] = $e->getMessage();
                }
            }
            usleep(50000);
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }

    /**
     * Bulk update queue bandwidth limits
     * @param array $updates Array of updates: [['name', 'max_limit'], ...]
     * @return array ['success' => count, 'errors' => [], 'failed' => count]
     */
    public function bulkUpdateQueues(array $updates): array {
        if (!$this->connected) return ['success' => 0, 'errors' => [], 'failed' => count($updates)];

        $success = 0;
        $errors = [];
        $batchSize = 200;

        foreach (array_chunk($updates, $batchSize) as $batch) {
            foreach ($batch as $update) {
                try {
                    if ($this->updateQueue($update['name'], $update['max_limit'])) {
                        $success++;
                    } else {
                        $errors[$update['name']] = 'Failed to update queue';
                    }
                } catch (Exception $e) {
                    $errors[$update['name']] = $e->getMessage();
                }
            }
            usleep(20000);
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'failed' => count($errors)
        ];
    }
}