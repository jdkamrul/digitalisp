-- ============================================================
-- Self-Hosted PipraPay Database Schema
-- Local payment processing and automation tables
-- ============================================================

-- Payment sessions table for tracking payment requests
CREATE TABLE IF NOT EXISTS piprapay_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    order_id VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'BDT',
    customer_name VARCHAR(150),
    customer_phone VARCHAR(20),
    customer_email VARCHAR(150),
    description TEXT,
    invoice_id INT UNSIGNED,
    customer_id INT UNSIGNED,
    status ENUM('pending', 'completed', 'cancelled', 'expired') DEFAULT 'pending',
    payment_methods JSON,
    transaction_id VARCHAR(100),
    payment_method VARCHAR(50),
    payment_ref VARCHAR(100),
    success_url TEXT,
    cancel_url TEXT,
    callback_url TEXT,
    expires_at DATETIME NOT NULL,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_session (session_id),
    KEY idx_order (order_id),
    KEY idx_status (status),
    KEY idx_invoice (invoice_id),
    KEY idx_customer (customer_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Customer payment subscriptions for automated billing
CREATE TABLE IF NOT EXISTS piprapay_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    payment_method VARCHAR(50) NOT NULL COMMENT 'bkash, nagad, rocket, upay, bank',
    account_number VARCHAR(100) NOT NULL COMMENT 'Mobile number or account number',
    account_holder VARCHAR(150) NOT NULL COMMENT 'Account holder name',
    auto_payment_enabled TINYINT(1) DEFAULT 1,
    auto_retry_count INT DEFAULT 0,
    max_retry_attempts INT DEFAULT 3,
    last_payment_attempt DATETIME,
    next_retry_date DATETIME,
    subscription_status ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_customer (customer_id),
    KEY idx_customer (customer_id),
    KEY idx_status (subscription_status),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Automated billing queue for scheduled payments
CREATE TABLE IF NOT EXISTS piprapay_auto_billing_queue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    priority ENUM('high', 'medium', 'low') DEFAULT 'medium',
    scheduled_date DATETIME NOT NULL,
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    last_attempt DATETIME,
    next_attempt DATETIME,
    error_message TEXT,
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_customer (customer_id),
    KEY idx_invoice (invoice_id),
    KEY idx_status (status),
    KEY idx_scheduled (scheduled_date),
    KEY idx_next_attempt (next_attempt),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Payment gateway settings
INSERT IGNORE INTO settings (`key`, `value`, `description`) VALUES
('selfhosted_piprapay_enabled', '0', 'Enable self-hosted PipraPay payment gateway'),
('selfhosted_piprapay_webhook_secret', 'change_this_webhook_secret_in_production', 'Webhook signature secret for self-hosted PipraPay'),
('selfhosted_piprapay_auto_billing_enabled', '1', 'Enable automated billing collection'),
('selfhosted_piprapay_retry_attempts', '3', 'Maximum retry attempts for failed payments'),
('selfhosted_piprapay_retry_interval_hours', '24', 'Hours to wait between retry attempts'),
('selfhosted_piprapay_webhook_timeout', '30', 'Webhook timeout in seconds');

-- Add auto_payment_enabled column to customers table
ALTER TABLE customers ADD COLUMN IF NOT EXISTS auto_payment_enabled TINYINT(1) DEFAULT 0 AFTER due_amount;