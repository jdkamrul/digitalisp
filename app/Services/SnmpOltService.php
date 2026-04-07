<?php
/**
 * SnmpOltService — SNMP-based OLT communication service.
 *
 * Supports:
 *  - Huawei GPON (MA5600/MA5800) via SNMP v2c
 *  - ZTE EPON/GPON (C300/C320) via SNMP v2c
 *  - Generic SNMP fallback
 *  - TCP socket ping fallback when SNMP is blocked
 */
class SnmpOltService
{
    private string $ip;
    private string $community;
    private string $version;
    private int    $timeout;   // microseconds
    private int    $retries;

    // ── Standard MIB-II OIDs ─────────────────────────────────────────────────
    const OID_SYS_DESCR  = '.1.3.6.1.2.1.1.1.0';
    const OID_SYS_NAME   = '.1.3.6.1.2.1.1.5.0';
    const OID_SYS_UPTIME = '.1.3.6.1.2.1.1.3.0';
    const OID_IF_DESCR   = '.1.3.6.1.2.1.2.2.1.2';

    // ── Huawei GPON OIDs (MA5600/MA5800) ─────────────────────────────────────
    const OID_HW_ONU_SERIAL = '.1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3';
    const OID_HW_ONU_STATUS = '.1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15';
    const OID_HW_ONU_SIGNAL = '.1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4';
    const OID_HW_ONU_DESC   = '.1.3.6.1.4.1.2011.6.128.1.1.2.43.1.9';

    // ── ZTE EPON OIDs (C300/C320) ─────────────────────────────────────────────
    const OID_ZTE_ONU_MAC    = '.1.3.6.1.4.1.3902.1012.3.28.1.1.3';
    const OID_ZTE_ONU_STATUS = '.1.3.6.1.4.1.3902.1012.3.28.1.1.10';
    const OID_ZTE_ONU_DESC   = '.1.3.6.1.4.1.3902.1012.3.28.1.1.2';
    const OID_ZTE_ONU_SIGNAL = '.1.3.6.1.4.1.3902.1012.3.50.1.1.1.1.4';

    // ── FiberHome GPON OIDs ───────────────────────────────────────────────────
    const OID_FH_ONU_SERIAL = '.1.3.6.1.4.1.5875.91.1.8.1.1.1.3';
    const OID_FH_ONU_STATUS = '.1.3.6.1.4.1.5875.91.1.8.1.1.1.10';

    public function __construct(
        string $ip,
        string $community = 'public',
        string $version   = '2c',
        int    $timeout   = 5000000,  // 5 seconds
        int    $retries   = 2
    ) {
        $this->ip        = $ip;
        $this->community = $community ?: 'public';
        $this->version   = $version ?: '2c';
        $this->timeout   = $timeout;
        $this->retries   = $retries;
    }

    /** Check whether the PHP SNMP extension is loaded. */
    public function isAvailable(): bool
    {
        return extension_loaded('snmp') && function_exists('snmp2_get');
    }

    /**
     * Test SNMP connectivity.
     * Returns: ['success'=>bool, 'description'=>string, 'method'=>string, 'error'=>string]
     */
    public function testConnection(): array
    {
        // First check basic TCP reachability
        $tcpReachable = $this->tcpPing();

        if (!$this->isAvailable()) {
            return [
                'success'     => $tcpReachable,
                'description' => $tcpReachable ? "Host reachable via TCP (SNMP extension not loaded)" : "Host unreachable",
                'method'      => 'tcp_ping',
                'error'       => 'PHP SNMP extension not loaded',
            ];
        }

        // Configure SNMP
        snmp_set_quick_print(true);
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);

        // Try SNMP GET sysDescr
        $result = $this->snmpGet(self::OID_SYS_DESCR);

        if ($result !== false && $result !== null && $result !== '') {
            return [
                'success'     => true,
                'description' => $this->cleanValue($result),
                'method'      => 'snmp',
                'error'       => '',
            ];
        }

        // SNMP failed — try TCP ping as fallback
        if ($tcpReachable) {
            return [
                'success'     => true,
                'description' => "Host reachable via TCP (SNMP community/version may be wrong)",
                'method'      => 'tcp_ping',
                'error'       => 'SNMP GET failed — check community string and SNMP version',
            ];
        }

        return [
            'success'     => false,
            'description' => '',
            'method'      => 'none',
            'error'       => 'Host unreachable via SNMP and TCP',
        ];
    }

    /**
     * Get system information from OLT.
     */
    public function getSystemInfo(): array
    {
        $info = [
            'sysDescr'  => '',
            'sysName'   => '',
            'sysUpTime' => '',
            'vendor'    => 'Unknown',
            'error'     => '',
        ];

        if (!$this->isAvailable()) {
            $info['error'] = 'SNMP extension not loaded';
            return $info;
        }

        snmp_set_quick_print(true);
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);

        $descr  = $this->snmpGet(self::OID_SYS_DESCR);
        $name   = $this->snmpGet(self::OID_SYS_NAME);
        $uptime = $this->snmpGet(self::OID_SYS_UPTIME);

        $info['sysDescr']  = $this->cleanValue((string)($descr  ?? ''));
        $info['sysName']   = $this->cleanValue((string)($name   ?? ''));
        $info['sysUpTime'] = $this->cleanValue((string)($uptime ?? ''));
        $info['vendor']    = $this->detectVendor($info['sysDescr']);

        return $info;
    }

    /**
     * Get ONU list from OLT.
     * Auto-detects vendor (Huawei GPON / ZTE EPON / FiberHome) and uses appropriate OIDs.
     */
    public function getOnuList(): array
    {
        if (!$this->isAvailable()) return [];

        snmp_set_quick_print(true);
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);

        // Detect vendor from sysDescr
        $descr  = $this->cleanValue((string)($this->snmpGet(self::OID_SYS_DESCR) ?? ''));
        $vendor = $this->detectVendor($descr);

        switch ($vendor) {
            case 'Huawei':
                return $this->getHuaweiOnuList();
            case 'ZTE':
                return $this->getZteOnuList();
            case 'FiberHome':
                return $this->getFiberHomeOnuList();
            default:
                // Try Huawei first, then ZTE
                $list = $this->getHuaweiOnuList();
                if (!empty($list)) return $list;
                return $this->getZteOnuList();
        }
    }

    /**
     * Detect OLT vendor from sysDescr string.
     */
    public function detectVendor(string $descr): string
    {
        $d = strtolower($descr);
        if (str_contains($d, 'huawei') || str_contains($d, 'ma5') || str_contains($d, 'vrp')) return 'Huawei';
        if (str_contains($d, 'zte') || str_contains($d, 'c300') || str_contains($d, 'c320') || str_contains($d, 'zxan')) return 'ZTE';
        if (str_contains($d, 'fiberhome') || str_contains($d, 'fiber home') || str_contains($d, 'an5516')) return 'FiberHome';
        if (str_contains($d, 'bdcom') || str_contains($d, 'p3310')) return 'BDCOM';
        if (str_contains($d, 'vsol') || str_contains($d, 'v1600')) return 'VSOL';
        return 'Unknown';
    }

    // ── Huawei GPON ONU list ──────────────────────────────────────────────────

    private function getHuaweiOnuList(): array
    {
        $serials = $this->snmpRealWalk(self::OID_HW_ONU_SERIAL);
        if (empty($serials)) return [];

        $statuses = $this->snmpRealWalk(self::OID_HW_ONU_STATUS);
        $signals  = $this->snmpRealWalk(self::OID_HW_ONU_SIGNAL);
        $descs    = $this->snmpRealWalk(self::OID_HW_ONU_DESC);

        $onus = [];
        foreach ($serials as $oid => $rawSerial) {
            $index  = $this->extractIndex($oid, self::OID_HW_ONU_SERIAL);
            $serial = $this->cleanValue($rawSerial);
            if (empty($serial)) continue;

            $signalDbm = null;
            $rawSig = $signals[$this->buildOid(self::OID_HW_ONU_SIGNAL, $index)] ?? null;
            if ($rawSig !== null) {
                $v = (int)$this->cleanValue($rawSig);
                if ($v !== 0) {
                    if ($v > 2147483647) $v -= 4294967296;
                    $signalDbm = round($v / 100, 2);
                }
            }

            $rawStatus = $statuses[$this->buildOid(self::OID_HW_ONU_STATUS, $index)] ?? null;
            $rawDesc   = $descs[$this->buildOid(self::OID_HW_ONU_DESC, $index)] ?? null;

            $onus[] = [
                'serial'      => $serial,
                'mac_address' => '',
                'status'      => $this->mapHuaweiStatus($rawStatus),
                'signal_dbm'  => $signalDbm,
                'olt_port'    => $this->extractOltPort($index),
                'description' => $rawDesc ? $this->cleanValue($rawDesc) : '',
                'onu_index'   => $index,
                'vendor'      => 'Huawei',
            ];
        }
        return $onus;
    }

    // ── ZTE EPON ONU list ─────────────────────────────────────────────────────

    private function getZteOnuList(): array
    {
        $macs = $this->snmpRealWalk(self::OID_ZTE_ONU_MAC);
        if (empty($macs)) return [];

        $statuses = $this->snmpRealWalk(self::OID_ZTE_ONU_STATUS);
        $descs    = $this->snmpRealWalk(self::OID_ZTE_ONU_DESC);
        $signals  = $this->snmpRealWalk(self::OID_ZTE_ONU_SIGNAL);

        $onus = [];
        foreach ($macs as $oid => $rawMac) {
            $index = $this->extractIndex($oid, self::OID_ZTE_ONU_MAC);
            $mac   = $this->formatMac($this->cleanValue($rawMac));
            if (empty($mac)) continue;

            $rawStatus = $statuses[$this->buildOid(self::OID_ZTE_ONU_STATUS, $index)] ?? null;
            $rawDesc   = $descs[$this->buildOid(self::OID_ZTE_ONU_DESC, $index)] ?? null;
            $rawSig    = $signals[$this->buildOid(self::OID_ZTE_ONU_SIGNAL, $index)] ?? null;

            $signalDbm = null;
            if ($rawSig !== null) {
                $v = (int)$this->cleanValue($rawSig);
                if ($v !== 0) {
                    if ($v > 2147483647) $v -= 4294967296;
                    $signalDbm = round($v / 10, 2); // ZTE uses 0.1 dBm units
                }
            }

            $onus[] = [
                'serial'      => 'ZTE-' . strtoupper(str_replace(':', '', $mac)),
                'mac_address' => $mac,
                'status'      => $this->mapZteStatus($rawStatus),
                'signal_dbm'  => $signalDbm,
                'olt_port'    => $this->extractOltPort($index),
                'description' => $rawDesc ? $this->cleanValue($rawDesc) : '',
                'onu_index'   => $index,
                'vendor'      => 'ZTE',
            ];
        }
        return $onus;
    }

    // ── FiberHome ONU list ────────────────────────────────────────────────────

    private function getFiberHomeOnuList(): array
    {
        $serials = $this->snmpRealWalk(self::OID_FH_ONU_SERIAL);
        if (empty($serials)) return [];

        $statuses = $this->snmpRealWalk(self::OID_FH_ONU_STATUS);

        $onus = [];
        foreach ($serials as $oid => $rawSerial) {
            $index  = $this->extractIndex($oid, self::OID_FH_ONU_SERIAL);
            $serial = $this->cleanValue($rawSerial);
            if (empty($serial)) continue;

            $rawStatus = $statuses[$this->buildOid(self::OID_FH_ONU_STATUS, $index)] ?? null;

            $onus[] = [
                'serial'      => $serial,
                'mac_address' => '',
                'status'      => $rawStatus ? 'online' : 'offline',
                'signal_dbm'  => null,
                'olt_port'    => $this->extractOltPort($index),
                'description' => '',
                'onu_index'   => $index,
                'vendor'      => 'FiberHome',
            ];
        }
        return $onus;
    }

    // ── SNMP helpers ──────────────────────────────────────────────────────────

    private function snmpGet(string $oid): mixed
    {
        if ($this->version === '2c' || $this->version === '2') {
            return @snmp2_get($this->ip, $this->community, $oid, $this->timeout, $this->retries);
        }
        return @snmpget($this->ip, $this->community, $oid, $this->timeout, $this->retries);
    }

    private function snmpRealWalk(string $baseOid): array
    {
        $result = [];
        if ($this->version === '2c' || $this->version === '2') {
            $result = @snmp2_real_walk($this->ip, $this->community, $baseOid, $this->timeout, $this->retries);
        } else {
            $result = @snmprealwalk($this->ip, $this->community, $baseOid, $this->timeout, $this->retries);
        }
        return is_array($result) ? $result : [];
    }

    /** Strip SNMP type prefix and quotes from a value. */
    private function cleanValue(string $value): string
    {
        // Remove type prefix: "STRING: ", "INTEGER: ", "Hex-STRING: ", "Timeticks: (xxx) "
        $value = preg_replace('/^[A-Za-z0-9\-]+:\s*/', '', $value);
        $value = trim($value, '"');
        return trim($value);
    }

    private function extractIndex(string $fullOid, string $baseOid): string
    {
        foreach ([$baseOid, ltrim($baseOid, '.')] as $base) {
            $base = rtrim($base, '.');
            $full = ltrim($fullOid, '.');
            $b    = ltrim($base, '.');
            if (str_starts_with($full, $b)) {
                return ltrim(substr($full, strlen($b)), '.');
            }
        }
        return $fullOid;
    }

    private function buildOid(string $base, string $index): string
    {
        return rtrim($base, '.') . '.' . ltrim($index, '.');
    }

    private function extractOltPort(string $index): ?string
    {
        $parts = explode('.', $index);
        if (count($parts) >= 3) {
            return $parts[0] . '/' . $parts[1] . '/' . $parts[2];
        }
        return $index ?: null;
    }

    private function formatMac(string $raw): string
    {
        // Convert hex string like "00 1a 2b 3c 4d 5e" or "001a2b3c4d5e" to "00:1A:2B:3C:4D:5E"
        $hex = preg_replace('/[^0-9a-fA-F]/', '', $raw);
        if (strlen($hex) === 12) {
            return implode(':', str_split(strtoupper($hex), 2));
        }
        return $raw;
    }

    private function mapHuaweiStatus(mixed $raw): string
    {
        if ($raw === null) return 'unknown';
        return match ((int)$this->cleanValue((string)$raw)) {
            1 => 'online', 2, 3, 4, 5, 6 => 'offline', default => 'unknown'
        };
    }

    private function mapZteStatus(mixed $raw): string
    {
        if ($raw === null) return 'unknown';
        // ZTE: 1=online, 2=offline, 3=power-off
        return match ((int)$this->cleanValue((string)$raw)) {
            1 => 'online', default => 'offline'
        };
    }

    // ── TCP ping fallback ─────────────────────────────────────────────────────

    public function tcpPing(int $timeoutSec = 3): bool
    {
        if (empty($this->ip)) return false;
        foreach ([80, 443, 22, 23, 8080] as $port) {
            $sock = @fsockopen($this->ip, $port, $errno, $errstr, $timeoutSec);
            if ($sock) { fclose($sock); return true; }
        }
        return false;
    }

    // ── Bulk Operations for High-Volume ONU Management ──────────────────────

    /**
     * Get ONU count by status (optimized for large networks)
     * @return array ['total' => int, 'online' => int, 'offline' => int, 'unknown' => int]
     */
    public function getOnuCountByStatus(): array
    {
        $onus = $this->getOnuList();
        $counts = ['total' => count($onus), 'online' => 0, 'offline' => 0, 'unknown' => 0];

        foreach ($onus as $onu) {
            $status = strtolower($onu['status'] ?? 'unknown');
            if (isset($counts[$status])) {
                $counts[$status]++;
            } else {
                $counts['unknown']++;
            }
        }

        return $counts;
    }

    /**
     * Bulk get ONU details with filtering and pagination
     * @param array $filters ['status' => 'online', 'olt_port' => '1/1/1', 'vendor' => 'Huawei']
     * @param int $offset Starting offset
     * @param int $limit Maximum ONUs to return
     * @return array Filtered and paginated ONU list
     */
    public function bulkGetOnuDetails(array $filters = [], int $offset = 0, int $limit = 1000): array
    {
        $allOnus = $this->getOnuList();
        $filtered = [];

        foreach ($allOnus as $onu) {
            $match = true;

            foreach ($filters as $key => $value) {
                if (!isset($onu[$key]) || strtolower($onu[$key]) !== strtolower($value)) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                $filtered[] = $onu;
            }
        }

        return array_slice($filtered, $offset, $limit);
    }

    /**
     * Bulk get ONU signal levels for monitoring
     * @param array $serialNumbers Array of ONU serial numbers to check
     * @return array ['serial' => signal_dbm, ...]
     */
    public function bulkGetOnuSignals(array $serialNumbers = []): array
    {
        $allOnus = $this->getOnuList();
        $signals = [];

        foreach ($allOnus as $onu) {
            $serial = $onu['serial'];
            if (empty($serialNumbers) || in_array($serial, $serialNumbers)) {
                $signals[$serial] = $onu['signal_dbm'];
            }
        }

        return $signals;
    }

    /**
     * Get ONU statistics for dashboard
     * @return array ['total_onus' => int, 'online_percentage' => float, 'avg_signal' => float, 'by_port' => [...]]
     */
    public function getOnuStatistics(): array
    {
        $onus = $this->getOnuList();
        if (empty($onus)) {
            return [
                'total_onus' => 0,
                'online_percentage' => 0.0,
                'avg_signal' => 0.0,
                'by_port' => []
            ];
        }

        $total = count($onus);
        $online = 0;
        $signalSum = 0;
        $signalCount = 0;
        $byPort = [];

        foreach ($onus as $onu) {
            // Count online status
            if (strtolower($onu['status'] ?? '') === 'online') {
                $online++;
            }

            // Calculate average signal
            if (isset($onu['signal_dbm']) && $onu['signal_dbm'] !== null) {
                $signalSum += $onu['signal_dbm'];
                $signalCount++;
            }

            // Group by port
            $port = $onu['olt_port'] ?? 'unknown';
            if (!isset($byPort[$port])) {
                $byPort[$port] = ['total' => 0, 'online' => 0];
            }
            $byPort[$port]['total']++;
            if (strtolower($onu['status'] ?? '') === 'online') {
                $byPort[$port]['online']++;
            }
        }

        return [
            'total_onus' => $total,
            'online_percentage' => $total > 0 ? round(($online / $total) * 100, 2) : 0.0,
            'avg_signal' => $signalCount > 0 ? round($signalSum / $signalCount, 2) : 0.0,
            'by_port' => $byPort
        ];
    }

    /**
     * Bulk check ONU connectivity status
     * @param array $serialNumbers Array of ONU serials to check
     * @return array ['serial' => ['status' => 'online|offline', 'last_seen' => timestamp], ...]
     */
    public function bulkCheckOnuConnectivity(array $serialNumbers = []): array
    {
        $allOnus = $this->getOnuList();
        $connectivity = [];

        foreach ($allOnus as $onu) {
            $serial = $onu['serial'];
            if (empty($serialNumbers) || in_array($serial, $serialNumbers)) {
                $connectivity[$serial] = [
                    'status' => $onu['status'] ?? 'unknown',
                    'last_seen' => time(), // Current time as last check
                    'signal_dbm' => $onu['signal_dbm'],
                    'olt_port' => $onu['olt_port']
                ];
            }
        }

        return $connectivity;
    }

    /**
     * Get OLT port utilization statistics
     * @return array ['port' => ['total_onus' => int, 'online_onus' => int, 'utilization' => float], ...]
     */
    public function getPortUtilization(): array
    {
        $stats = $this->getOnuStatistics();
        $utilization = [];

        foreach ($stats['by_port'] as $port => $data) {
            $total = $data['total'];
            $online = $data['online'];
            $utilization[$port] = [
                'total_onus' => $total,
                'online_onus' => $online,
                'utilization' => $total > 0 ? round(($online / $total) * 100, 2) : 0.0
            ];
        }

        return $utilization;
    }

    /**
     * Bulk get ONU information by port
     * @param string $port OLT port (e.g., "1/1/1")
     * @return array Array of ONUs on the specified port
     */
    public function getOnusByPort(string $port): array
    {
        return $this->bulkGetOnuDetails(['olt_port' => $port]);
    }

    /**
     * Get offline ONUs for troubleshooting
     * @param int $limit Maximum number to return
     * @return array Array of offline ONUs
     */
    public function getOfflineOnus(int $limit = 100): array
    {
        return $this->bulkGetOnuDetails(['status' => 'offline'], 0, $limit);
    }

    /**
     * Get ONUs with low signal strength
     * @param float $threshold Signal threshold in dBm (e.g., -25.0)
     * @param int $limit Maximum number to return
     * @return array Array of ONUs with low signal
     */
    public function getLowSignalOnus(float $threshold = -25.0, int $limit = 50): array
    {
        $allOnus = $this->getOnuList();
        $lowSignal = [];

        foreach ($allOnus as $onu) {
            $signal = $onu['signal_dbm'];
            if ($signal !== null && $signal < $threshold) {
                $lowSignal[] = $onu;
                if (count($lowSignal) >= $limit) break;
            }
        }

        return $lowSignal;
    }

    /** Ping via exec (Windows/Linux compatible). */
    public function execPing(): bool
    {
        if (empty($this->ip)) return false;
        $cmd = PHP_OS_FAMILY === 'Windows'
            ? "ping -n 1 -w 1000 " . escapeshellarg($this->ip)
            : "ping -c 1 -W 2 " . escapeshellarg($this->ip);
        exec($cmd . " 2>&1", $out, $ret);
        return $ret === 0;
    }
}
