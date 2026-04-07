-- Self-Hosted PipraPay Schema - SQLite compatible

CREATE TABLE IF NOT EXISTS piprapay_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    order_id VARCHAR(100) NOT NULL,
    amount REAL NOT NULL,
    currency VARCHAR(3) DEFAULT 'BDT',
    customer_name VARCHAR(150),
    customer_phone VARCHAR(20),
    customer_email VARCHAR(150),
    description TEXT,
    invoice_id INTEGER,
    customer_id INTEGER,
    status VARCHAR(20) DEFAULT 'pending',
    payment_methods TEXT,
    transaction_id VARCHAR(100),
    payment_method VARCHAR(50),
    payment_ref VARCHAR(100),
    success_url TEXT,
    cancel_url TEXT,
    callback_url TEXT,
    expires_at DATETIME NOT NULL,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_pp_session ON piprapay_sessions(session_id);

CREATE INDEX IF NOT EXISTS idx_pp_order ON piprapay_sessions(order_id);

CREATE INDEX IF NOT EXISTS idx_pp_status ON piprapay_sessions(status);

CREATE TABLE IF NOT EXISTS piprapay_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL UNIQUE,
    payment_method VARCHAR(50) NOT NULL,
    account_number VARCHAR(100) NOT NULL,
    account_holder VARCHAR(150) NOT NULL,
    auto_payment_enabled INTEGER DEFAULT 1,
    auto_retry_count INTEGER DEFAULT 0,
    max_retry_attempts INTEGER DEFAULT 3,
    last_payment_attempt DATETIME,
    next_retry_date DATETIME,
    subscription_status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS piprapay_auto_billing_queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    invoice_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    priority VARCHAR(10) DEFAULT 'medium',
    scheduled_date DATETIME NOT NULL,
    retry_count INTEGER DEFAULT 0,
    max_retries INTEGER DEFAULT 3,
    status VARCHAR(20) DEFAULT 'pending',
    last_attempt DATETIME,
    next_attempt DATETIME,
    error_message TEXT,
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);
