<?php

/**
 * Self-Hosted PipraPay Automated Billing Cron Job
 * Processes queued automated payments
 *
 * Run this script periodically (e.g., every 15 minutes):
 *   php cron_selfhosted_piprapay.php
 *
 * Cron entry (every 15 minutes):
 */
// * /15 * * * * php /path/to/ispd/cron_selfhosted_piprapay.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Controllers/SelfHostedPipraPayController.php';

try {
    echo "[" . date('Y-m-d H:i:s') . "] Starting automated billing processing...\n";

    $controller = new SelfHostedPipraPayController();
    $controller->processAutomatedBilling();

    echo "[" . date('Y-m-d H:i:s') . "] Automated billing processing completed.\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
    exit(1);
}
