<?php

class SmsService {
    private Database $db;
    private array $gateway;

    public function __construct() {
        $this->db      = Database::getInstance();
        $gw = $this->db->fetchOne("SELECT * FROM sms_gateways WHERE is_default=1 AND is_active=1");
        $this->gateway = $gw ?? [];
    }

    /**
     * Send SMS to a phone number
     */
    public function send(string $phone, string $message, ?int $customerId = null, ?int $templateId = null): bool {
        // Normalize BD phone number
        $phone = $this->normalizePhone($phone);
        if (!$phone) return false;

        // Try to send via gateway
        $status = 'pending';
        $response = '';

        if (!empty($this->gateway)) {
            [$status, $response] = $this->sendViaGateway($phone, $message);
        }

        // Log the SMS
        $this->db->insert('sms_logs', [
            'gateway_id'       => $this->gateway['id'] ?? null,
            'customer_id'      => $customerId,
            'phone'            => $phone,
            'message'          => $message,
            'template_id'      => $templateId,
            'status'           => $status,
            'gateway_response' => $response,
            'sent_at'          => date('Y-m-d H:i:s'),
        ]);

        return $status === 'sent';
    }

    /**
     * Send from template with variable substitution
     */
    public function sendTemplate(string $eventType, string $phone, array $vars = [], ?int $customerId = null): bool {
        $template = $this->db->fetchOne(
            "SELECT * FROM sms_templates WHERE event_type=? AND is_active=1 LIMIT 1",
            [$eventType]
        );
        if (!$template) return false;

        $message = $template['message_bn']; // Bangla by default
        foreach ($vars as $key => $val) {
            $message = str_replace('{' . $key . '}', $val, $message);
        }

        return $this->send($phone, $message, $customerId, $template['id']);
    }

    /**
     * Send bill generated SMS
     */
    public function sendBillGenerated(array $customer, array $invoice): bool {
        return $this->sendTemplate('bill_generated', $customer['phone'], [
            'name'     => $customer['full_name'],
            'month'    => date('F Y', strtotime($invoice['billing_month'])),
            'amount'   => number_format($invoice['total'], 0),
            'due_date' => date('d M Y', strtotime($invoice['due_date'])),
        ], $customer['id']);
    }

    /**
     * Send payment received SMS
     */
    public function sendPaymentReceived(array $customer, array $payment): bool {
        return $this->sendTemplate('payment_received', $customer['phone'], [
            'name'    => $customer['full_name'],
            'amount'  => number_format($payment['amount'], 0),
            'receipt' => $payment['receipt_number'],
        ], $customer['id']);
    }

    /**
     * Send due reminder SMS to all due customers
     */
    public function sendDueReminders(?int $branchId = null): array {
        $where = "c.status='active' AND i.status IN ('unpaid','partial') AND i.due_date < DATETIME('now')";
        $params = [];
        if ($branchId) { $where .= ' AND c.branch_id=?'; $params[] = $branchId; }

        $duers = $this->db->fetchAll(
            "SELECT c.id, c.full_name, c.phone, i.due_amount
             FROM customers c JOIN invoices i ON i.customer_id=c.id
             WHERE $where GROUP BY c.id LIMIT 100",
            $params
        );

        $sent = 0;
        foreach ($duers as $d) {
            $ok = $this->sendTemplate('due_reminder', $d['phone'], [
                'name'   => $d['full_name'],
                'amount' => number_format($d['due_amount'], 0),
            ], $d['id']);
            if ($ok) $sent++;
        }
        return ['attempted' => count($duers), 'sent' => $sent];
    }

    private function sendViaGateway(string $phone, string $message): array {
        $gateway = $this->gateway;
        $url     = $gateway['api_url'];

        // SSL Wireless / Bulk SMS API style
        $params = [
            'api_key'   => $gateway['api_key'],
            'senderid'  => $gateway['sender_id'],
            'number'    => $phone,
            'message'   => $message,
            'type'      => 'unicode', // For Bangla
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $status = ($httpCode === 200 && !str_contains(strtolower($response ?? ''), 'error')) ? 'sent' : 'failed';
        return [$status, $response ?? ''];
    }

    private function normalizePhone(string $phone): ?string {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            return '88' . $phone;
        }
        if (strlen($phone) === 13 && str_starts_with($phone, '88')) {
            return $phone;
        }
        if (strlen($phone) === 10) {
            return '880' . $phone;
        }
        return null;
    }
}
