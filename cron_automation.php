<?php
/**
 * Billing Automation Cron Script
 *
 * Usage:
 *   php cron_automation.php              — runs all jobs
 *   php cron_automation.php invoices     — invoice generation only
 *   php cron_automation.php suspend      — auto suspension only
 *   php cron_automation.php reconnect    — auto reconnection only
 *   php cron_automation.php due-reminders
 *   php cron_automation.php expiry-reminders
 *
 * Suggested crontab entries:
 *   0 0 * * *   php /path/to/ispd/cron_automation.php
 *   0 8 * * *   php /path/to/ispd/cron_automation.php due-reminders
 */
// Every 6h suspend: 0 */6 * * * php /path/to/ispd/cron_automation.php suspend

define('BASE_PATH', __DIR__);
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Services/AutomationService.php';

$job = $argv[1] ?? 'all';
$automation = new AutomationService();

// Log to file in production, stdout in dev
$logDir  = BASE_PATH . '/storage/logs';
$logFile = $logDir . '/automation_cron.log';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

function cronLog(string $message, string $logFile): void {
    $line = $message . PHP_EOL;
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

$ts = '[' . date('Y-m-d H:i:s') . ']';

try {
    $jobs = $job === 'all'
        ? ['invoices', 'reconnect', 'suspend', 'due-reminders', 'expiry-reminders']
        : [$job];

    foreach ($jobs as $j) {
        $result = match($j) {
            'invoices'         => $automation->generateMonthlyInvoices(),
            'suspend'          => $automation->suspendOverdue(0),
            'reconnect'        => $automation->reconnectPaidCustomers(),
            'due-reminders'    => $automation->sendDueReminders(3),
            'expiry-reminders' => $automation->sendExpiryReminders(5),
            default            => ['success'=>false,'message'=>"Unknown job: {$j}",'affected'=>0],
        };
        cronLog("{$ts} [{$j}] {$result['message']}", $logFile);
        if (!empty($result['errors'])) {
            foreach (array_slice($result['errors'], 0, 5) as $err) {
                cronLog("{$ts} [{$j}] ERROR: {$err}", $logFile);
            }
        }
    }
} catch (Exception $e) {
    cronLog("{$ts} FATAL: " . $e->getMessage(), $logFile);
    exit(1);
}
