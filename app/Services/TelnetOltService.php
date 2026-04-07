<?php
/**
 * TelnetOltService — Telnet-based OLT communication service.
 *
 * Supports AC-FD1304E-B1 EPON OLT and similar devices
 * that expose a CLI via Telnet (port 23).
 *
 * Commands discovered:
 *   show ont info all  — lists all ONTs with MAC, port, status
 *   show version       — firmware/hardware info
 *   show device        — device model and slot info
 */
class TelnetOltService {

    private string $ip;
    private string $username;
    private string $password;
    private int    $port;
    private int    $timeout;
    private $sock = null;

    public function __construct(string $ip, string $username, string $password, int $port = 23, int $timeout = 8) {
        $this->ip       = $ip;
        $this->username = $username;
        $this->password = $password;
        $this->port     = $port;
        $this->timeout  = $timeout;
    }

    /** Test Telnet connectivity and login. */
    public function testConnection(): array {
        try {
            if (!$this->connect()) {
                return ['success' => false, 'error' => 'Cannot connect to Telnet port ' . $this->port];
            }
            $info = $this->getSystemInfo();
            $this->disconnect();
            return [
                'success'     => true,
                'description' => $info['model'] ?? 'EPON OLT',
                'method'      => 'telnet',
                'error'       => '',
                'system_info' => $info,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'method' => 'telnet'];
        }
    }

    /** Get system information from OLT. */
    public function getSystemInfo(): array {
        $raw = $this->sendCommand('show version');
        $info = ['model' => '', 'firmware' => '', 'hardware' => '', 'uptime' => '', 'mac' => ''];

        if (preg_match('/Firmware version\s*:\s*(\S+)/i', $raw, $m)) $info['firmware'] = $m[1];
        if (preg_match('/Hardware version\s*:\s*(\S+)/i', $raw, $m)) $info['hardware'] = $m[1];
        if (preg_match('/uptime is (.+)/i', $raw, $m)) $info['uptime'] = trim($m[1]);

        $devRaw = $this->sendCommand('show device');
        if (preg_match('/Device type\s*:\s*(.+)/i', $devRaw, $m)) $info['model'] = trim($m[1]);
        if (preg_match('/Device MAC address\s*:\s*(\S+)/i', $devRaw, $m)) $info['mac'] = $m[1];
        if (preg_match('/Device serial-number\s*:\s*(\S+)/i', $devRaw, $m)) $info['serial'] = $m[1];

        return $info;
    }

    /**
     * Get full ONU list via "show ont info all".
     * Returns array of ONUs with mac_address, olt_port, status, deregister_reason.
     */
    public function getOnuList(): array {
        $raw = $this->sendCommand('show ont info all', 8);
        return $this->parseOnuList($raw);
    }

    /**
     * Parse the "show ont info all" output.
     * Format: F/S P  ONT_ID  MAC  Control  Run_state  Config  Match  Last_cause  Desc
     */
    public function parseOnuList(string $raw): array {
        $onus  = [];
        $lines = explode("\n", $raw);

        foreach ($lines as $line) {
            $line = trim($line);
            // Match: "  0/1 1  1      70:A5:6A:4D:46:D6    Active   Online  success   match     --"
            if (!preg_match('/^\s*(\d+\/\d+)\s+(\d+)\s+(\d+)\s+([\dA-Fa-f:]{17})\s+(\w+)\s+(\w+)\s+(\w+)\s+(\w+)\s+(.+)$/', $line, $m)) {
                continue;
            }

            $frame_slot = $m[1];
            $port       = (int)$m[2];
            $ont_id     = (int)$m[3];
            $mac        = strtoupper(trim($m[4]));
            $run_state  = strtolower(trim($m[6]));
            $last_cause = trim($m[9]);

            $onus[] = [
                'serial'           => 'EPON-' . str_replace(':', '', $mac),
                'mac_address'      => $mac,
                'olt_port'         => "{$frame_slot}/{$port}",
                'ont_id'           => $ont_id,
                'status'           => ($run_state === 'online') ? 'online' : 'offline',
                'deregister_reason'=> ($last_cause !== '--' && $last_cause !== '') ? $last_cause : null,
                'signal_dbm'       => null, // Telnet output doesn't include signal
                'description'      => '',
                'vendor'           => 'EPON',
            ];
        }

        return $onus;
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function connect(): bool {
        $this->sock = @fsockopen($this->ip, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->sock) return false;

        stream_set_timeout($this->sock, 3);

        // Read banner until login prompt
        $buf = ''; $start = time();
        while (time() - $start < 5) {
            $c = @fread($this->sock, 512);
            if (!$c) { usleep(200000); continue; }
            $buf .= $c;
            if (str_contains($buf, 'name:') || str_contains($buf, 'Username') || str_contains($buf, 'login:')) break;
        }

        // Send username
        fwrite($this->sock, $this->username . "\r\n");
        usleep(800000);
        @fread($this->sock, 512);

        // Send password
        fwrite($this->sock, $this->password . "\r\n");
        usleep(1200000);
        $resp = @fread($this->sock, 1024);
        $clean = preg_replace('/[^\x20-\x7E\n]/', '', $resp);

        // Check if logged in (prompt ends with > or #)
        if (!preg_match('/[>#]\s*$/', trim($clean))) {
            // Try enable
            fwrite($this->sock, "enable\r\n");
            usleep(800000);
            @fread($this->sock, 512);
        }

        // Enter enable mode
        fwrite($this->sock, "enable\r\n");
        usleep(800000);
        @fread($this->sock, 512);

        return true;
    }

    private function disconnect(): void {
        if ($this->sock) {
            @fwrite($this->sock, "logout\r\n");
            @fclose($this->sock);
            $this->sock = null;
        }
    }

    private function sendCommand(string $cmd, int $waitSec = 4): string {
        if (!$this->sock) {
            if (!$this->connect()) return '';
        }

        fwrite($this->sock, $cmd . "\r\n");
        usleep($waitSec * 1000000);

        $resp = '';
        $idle = 0;
        stream_set_timeout($this->sock, 3);

        while ($idle < 8) {
            $chunk = @fread($this->sock, 16384);
            if ($chunk === false || $chunk === '') {
                $idle++;
                usleep(400000);
                continue;
            }
            $resp .= $chunk;
            $idle = 0;

            // Handle pagination
            if (str_contains($resp, '--More--') || str_contains($resp, '-- More --')) {
                fwrite($this->sock, ' ');
                usleep(300000);
            }

            // Stop at prompt
            if (preg_match('/[>#]\s*$/', trim(preg_replace('/[^\x20-\x7E\n]/', '', $resp)))) break;
        }

        return preg_replace('/[^\x20-\x7E\n\r\t]/', '', $resp);
    }
}
