# Self-Hosted PipraPay Payment Gateway

A local payment processing system for Digital ISP ERP that eliminates dependency on external payment APIs while maintaining compatibility with Bangladeshi payment methods.

## Features

- **Local Payment Processing**: No external API dependency
- **Automated Billing**: Scheduled payment collection for recurring invoices
- **Payment Retry Logic**: Configurable retry attempts with backoff
- **Webhook Support**: Real-time payment notifications
- **Multi-Method Support**: bKash, Nagad, Rocket, Upay, Bank transfers
- **Security**: HMAC signature validation for webhooks
- **Integration**: Full compatibility with existing invoice and cashbook systems

## Installation

1. **Run Database Migration**:
   ```bash
   php migrate_selfhosted_piprapay.php
   ```

2. **Enable in Settings**:
   - Go to Settings → Payment Gateways
   - Enable "Self-Hosted PipraPay"
   - Configure webhook secret and other settings

3. **Set Up Cron Job** (for automated billing):
   ```bash
   # Run every 15 minutes
   */15 * * * * php /path/to/ispd/cron_selfhosted_piprapay.php
   ```

## Configuration

### Settings Page Configuration

- **Webhook Secret**: Secure secret for webhook signature validation
- **Auto Billing Enabled**: Enable/disable automated payment collection
- **Retry Attempts**: Maximum retry attempts for failed payments (default: 3)
- **Retry Interval**: Hours to wait between retry attempts (default: 24)

### Customer Setup for Auto-Payment

1. Enable auto-payment for customers:
   ```sql
   UPDATE customers SET auto_payment_enabled = 1 WHERE id = {customer_id};
   ```

2. Set up payment subscriptions:
   ```sql
   INSERT INTO piprapay_subscriptions (
       customer_id, payment_method, account_number, account_holder
   ) VALUES (
       {customer_id}, 'bkash', '01XXXXXXXXX', 'Customer Name'
   );
   ```

## API Endpoints

### Payment Processing

- **Checkout Page**: `GET /payment/selfhosted/checkout/{session_id}`
- **Process Payment**: `POST /payment/selfhosted/process/{session_id}`
- **Webhook Handler**: `POST /payment/selfhosted/webhook`

### Automated Billing

- **Queue Payment**: `POST /payment/selfhosted/queue/{invoice_id}`
- **Billing Status**: `GET /payment/selfhosted/status/{customer_id}`
- **Process Queue**: `POST /payment/selfhosted/process-automated`

## Usage Examples

### Manual Payment Processing

```php
// Create payment session
$piprapay = new SelfHostedPipraPayService();
$result = $piprapay->createPayment([
    'amount' => 1000.00,
    'order_id' => 'INV-001-123456',
    'customer_name' => 'John Doe',
    'customer_phone' => '01XXXXXXXXX',
    'description' => 'Internet Bill Payment',
    'invoice_id' => 123,
    'success_url' => base_url('billing/invoice/123'),
    'callback_url' => base_url('payment/selfhosted/webhook'),
]);

if ($result['success']) {
    // Redirect to checkout
    redirect($result['payment_url']);
}
```

### Automated Billing

```php
// Process automated billing for a customer
$result = $piprapay->processAutomatedBilling($customerId, $invoiceId);

if ($result['success']) {
    echo "Payment processed: " . $result['transaction_id'];
} else {
    echo "Payment failed: " . $result['error'];
}
```

### Webhook Integration

```php
// Validate webhook signature
if ($piprapay->validateWebhookSignature($_POST)) {
    // Process payment notification
    $piprapay->processWebhook($_POST);
}
```

## Database Schema

### Payment Sessions (`piprapay_sessions`)
Tracks payment requests and their status.

### Customer Subscriptions (`piprapay_subscriptions`)
Stores customer payment method preferences for auto-billing.

### Auto Billing Queue (`piprapay_auto_billing_queue`)
Manages scheduled automated payment attempts.

## Security

- **Webhook Signatures**: All webhooks are validated using HMAC-SHA256
- **Session Expiry**: Payment sessions expire after 1 hour
- **Input Validation**: All inputs are sanitized and validated
- **Duplicate Prevention**: Payment records prevent duplicate processing

## Troubleshooting

### Common Issues

1. **Migration Fails**: Ensure database connection and permissions
2. **Webhook Validation Fails**: Check webhook secret configuration
3. **Auto-Billing Not Working**: Verify cron job is running and customer subscriptions are set up
4. **Payment Session Expired**: Sessions expire after 1 hour, create new session

### Logs

Check application logs for detailed error information:
- Payment processing errors
- Webhook validation failures
- Database connection issues

## Development

### Adding New Payment Methods

1. Update `SelfHostedPipraPayService::config['payment_methods']`
2. Add validation logic in `validatePaymentData()`
3. Update checkout UI in `checkout.php`

### Customizing Retry Logic

Modify retry parameters in service configuration:
```php
$config = [
    'auto_retry_attempts' => 5,
    'retry_interval_hours' => 12,
];
```

## Support

For technical support or feature requests:
- Check existing PipraPay documentation
- Review system logs for errors
- Test with sandbox mode first

## License

This implementation is part of the Digital ISP ERP system.