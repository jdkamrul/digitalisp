<?php

/**
 * PipraPay Payment Gateway Service
 * Bangladeshi All-in-One Payment Gateway
 * Supports: bKash, Nagad, Rocket, Upay, 15+ Banks
 *
 * Docs: https://piprapay.com/developer
 * Support: support@piprapay.com | +880 1806-579249
 */
class PipraPayService {

    private string $merchantId;
    private string $apiKey;
    private string $apiSecret;
    private string $mode;
    private string $baseUrl;

    public function __construct(array $config = []) {
        $db = Database::getInstance();
        $settings = [];
        foreach ($db->fetchAll("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'piprapay_%'") as $row) {
            $settings[$row['key']] = $row['value'];
        }

        $this->merchantId = $config['merchant_id'] ?? $settings['piprapay_merchant_id'] ?? '';
        $this->apiKey     = $config['api_key']     ?? $settings['piprapay_api_key']     ?? '';
        $this->apiSecret  = $config['api_secret']  ?? $settings['piprapay_api_secret']  ?? '';
        $this->mode       = strtolower((string)($config['mode'] ?? $settings['piprapay_mode'] ?? 'sandbox'));
        if (!in_array($this->mode, ['live', 'sandbox'], true)) {
            $this->mode = 'sandbox';
        }

        $this->baseUrl = $this->mode === 'live'
            ? 'https://api.piprapay.com/v3'
            : 'https://sandbox.piprapay.com/v3';
    }

    public function isEnabled(): bool {
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT `value` FROM settings WHERE `key`='piprapay_enabled'");
        return ($row['value'] ?? '') === '1'
            && !empty($this->merchantId)
            && !empty($this->apiKey)
            && !empty($this->apiSecret);
    }

    /**
     * Create a payment session and return the redirect URL.
     *
     * @param array $params [
     *   'amount'       => float,
     *   'currency'     => 'BDT',
     *   'order_id'     => string,
     *   'customer_name'=> string,
     *   'customer_phone'=> string,
     *   'customer_email'=> string,
     *   'description'  => string,
     *   'success_url'  => string,
     *   'cancel_url'   => string,
     *   'callback_url' => string,
     * ]
     * @return array ['success'=>bool, 'payment_url'=>string, 'session_id'=>string, 'error'=>string]
     */
    public function createPayment(array $params): array {
        $payload = [
            'merchant_id'    => $this->merchantId,
            'amount'         => number_format((float)$params['amount'], 2, '.', ''),
            'currency'       => $params['currency'] ?? 'BDT',
            'order_id'       => $params['order_id'],
            'customer_name'  => $params['customer_name'] ?? '',
            'customer_phone' => $params['customer_phone'] ?? '',
            'customer_email' => $params['customer_email'] ?? '',
            'description'    => $params['description'] ?? 'ISP Bill Payment',
            'success_url'    => $params['success_url'] ?? base_url('payment/piprapay/success'),
            'cancel_url'     => $params['cancel_url']  ?? base_url('payment/piprapay/cancel'),
            'callback_url'   => $params['callback_url'] ?? base_url('payment/piprapay/callback'),
        ];

        $response = $this->request('POST', '/payment/create', $payload);

        if ($response && isset($response['payment_url'])) {
            return [
                'success'     => true,
                'payment_url' => $response['payment_url'],
                'session_id'  => $response['session_id'] ?? '',
                'transaction_id' => $response['transaction_id'] ?? '',
            ];
        }

        return [
            'success' => false,
            'error'   => $response['message'] ?? 'Failed to create PipraPay payment session.',
        ];
    }

    /**
     * Verify a payment by transaction ID.
     */
    public function verifyPayment(string $transactionId): array {
        $response = $this->request('GET', '/payment/verify/' . $transactionId);

        if ($response && isset($response['status'])) {
            return [
                'success'        => $response['status'] === 'COMPLETED',
                'status'         => $response['status'],
                'amount'         => $response['amount'] ?? 0,
                'currency'       => $response['currency'] ?? 'BDT',
                'transaction_id' => $response['transaction_id'] ?? $transactionId,
                'payment_method' => $response['payment_method'] ?? '',
                'paid_at'        => $response['paid_at'] ?? null,
            ];
        }

        return ['success' => false, 'error' => 'Verification failed.'];
    }

    /**
     * Validate IPN/callback signature.
     */
    public function validateCallback(array $data): bool {
        if (empty($this->apiSecret)) return false;
        if (empty($data['signature']) || empty($data['transaction_id']) || !isset($data['amount']) || empty($data['order_id'])) return false;
        $expected = hash_hmac('sha256', $data['transaction_id'] . $data['amount'] . $data['order_id'], $this->apiSecret);
        return hash_equals($expected, $data['signature']);
    }

    /**
     * Make an authenticated HTTP request to PipraPay API.
     */
    private function request(string $method, string $endpoint, array $data = []): ?array {
        $url  = $this->baseUrl . $endpoint;
        $body = json_encode($data);
        $ts   = time();
        $sig  = hash_hmac('sha256', $ts . $body, $this->apiSecret);

        $headers = [
            'Content-Type: application/json',
            'X-Merchant-ID: ' . $this->merchantId,
            'X-API-Key: ' . $this->apiKey,
            'X-Timestamp: ' . $ts,
            'X-Signature: ' . $sig,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => $this->mode === 'live',
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false) return null;

        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : null;
    }
}
