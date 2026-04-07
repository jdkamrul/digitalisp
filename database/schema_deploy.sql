-- ============================================================
-- Digital ISP ERP — Full MySQL Database Schema
-- Bangladesh ISP ERP System
-- Version: 1.1.0 | Timezone: Asia/Dhaka
-- 
-- DEPLOYMENT VERSION FOR SHARED HOSTING
-- Database: digital_isp
-- User: root
-- ============================================================

USE digital_isp;

SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = '';

-- ============================================================
-- BRANCHES, ZONES, AREAS
-- ============================================================
CREATE TABLE IF NOT EXISTS branches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    manager_name VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS zones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20),
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS areas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- ROLES & PERMISSIONS (RBAC)
-- ============================================================
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    module VARCHAR(50) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    zone_id INT UNSIGNED,
    role_id INT UNSIGNED NOT NULL,
    username VARCHAR(80) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    avatar VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    last_ip VARCHAR(45),
    api_token VARCHAR(255),
    api_token_expires TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_username (username),
    KEY idx_role (role_id),
    KEY idx_branch (branch_id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (zone_id) REFERENCES zones(id)
) ENGINE=InnoDB;

-- ============================================================
-- CUSTOMERS
-- ============================================================
CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    zone_id INT UNSIGNED,
    area_id INT UNSIGNED,
    customer_type ENUM('residential', 'business', 'corporate') DEFAULT 'residential',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    company_name VARCHAR(150),
    email VARCHAR(150),
    phone VARCHAR(20) NOT NULL,
    alt_phone VARCHAR(20),
    nid_number VARCHAR(50),
    birth_date DATE,
    gender ENUM('male', 'female', 'other') DEFAULT 'male',
    contact_person VARCHAR(100),
    installation_address TEXT,
    billing_address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    map_link VARCHAR(255),
    package_id INT UNSIGNED,
    bandwidth VARCHAR(50),
    ip_address VARCHAR(45),
    mac_address VARCHAR(17),
    nas_id INT UNSIGNED,
    pppoe_username VARCHAR(100),
    pppoe_password VARCHAR(100),
    static_ip ENUM('yes', 'no') DEFAULT 'no',
    connection_type ENUM('cable', 'wireless', 'gpON') DEFAULT 'cable',
    port_no VARCHAR(50),
    ont_serial VARCHAR(100),
    registration_date DATE NOT NULL,
    connection_date DATE,
    status ENUM('active', 'inactive', 'suspended', 'pending', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    referral_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_customer_name (first_name, last_name),
    KEY idx_phone (phone),
    KEY idx_email (email),
    KEY idx_status (status),
    KEY idx_package (package_id),
    KEY idx_area (area_id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    FOREIGN KEY (area_id) REFERENCES areas(id),
    FOREIGN KEY (package_id) REFERENCES packages(id),
    FOREIGN KEY (referral_by) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- PACKAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50),
    description TEXT,
    bandwidth VARCHAR(50) NOT NULL,
    bandwidth_unit ENUM('kbps', 'mbps', 'gbps') DEFAULT 'mbps',
    upload_speed VARCHAR(50),
    monthly_price DECIMAL(10, 2) NOT NULL,
    setup_fee DECIMAL(10, 2) DEFAULT 0.00,
    installation_charge DECIMAL(10, 2) DEFAULT 0.00,
    modem_price DECIMAL(10, 2) DEFAULT 0.00,
    other_charges DECIMAL(10, 2) DEFAULT 0.00,
    tax_rate DECIMAL(5, 2) DEFAULT 0.00,
    discount_percent DECIMAL(5, 2) DEFAULT 0.00,
    max_distance INT DEFAULT 0,
    distance_unit ENUM('meters', 'kilometers') DEFAULT 'meters',
    available_for ENUM('all', 'residential', 'business', 'corporate') DEFAULT 'all',
    is_active TINYINT(1) DEFAULT 1,
    priority INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_name (name),
    KEY idx_active (is_active),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

-- ============================================================
-- INVOICES
-- ============================================================
CREATE TABLE IF NOT EXISTS invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    branch_id INT UNSIGNED,
    package_id INT UNSIGNED,
    invoice_type ENUM('monthly', 'setup', 'additional', 'adjustment') DEFAULT 'monthly',
    billing_period_start DATE,
    billing_period_end DATE,
    due_date DATE,
    subtotal DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    discount_reason VARCHAR(255),
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    tax_percent DECIMAL(5, 2) DEFAULT 0.00,
    other_charges DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    paid_amount DECIMAL(10, 2) DEFAULT 0.00,
    due_amount DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('paid', 'partial', 'unpaid', 'cancelled', 'refunded') DEFAULT 'unpaid',
    payment_status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    notes TEXT,
    terms_conditions TEXT,
    footer_text TEXT,
    generated_by INT UNSIGNED,
    approved_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_invoice_number (invoice_number),
    KEY idx_customer (customer_id),
    KEY idx_status (status),
    KEY idx_due_date (due_date),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (package_id) REFERENCES packages(id),
    FOREIGN KEY (generated_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- PAYMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_number VARCHAR(50) UNIQUE NOT NULL,
    receipt_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    invoice_id INT UNSIGNED,
    branch_id INT UNSIGNED,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'card', 'mobile_banking', 'bank_transfer', 'cheque', 'online') DEFAULT 'cash',
    amount DECIMAL(10, 2) NOT NULL,
    payment_type ENUM('full', 'partial', 'advance', 'adjustment') DEFAULT 'full',
    reference_number VARCHAR(100),
    cheque_number VARCHAR(50),
    bank_name VARCHAR(100),
    transaction_id VARCHAR(100),
    mobile_banking_provider VARCHAR(50),
    card_type VARCHAR(50),
    card_number VARCHAR(20),
    notes TEXT,
    received_by INT UNSIGNED,
    approved_by INT UNSIGNED,
    is_refunded TINYINT(1) DEFAULT 0,
    refund_reason TEXT,
    refunded_at TIMESTAMP NULL,
    refunded_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_payment_number (payment_number),
    KEY idx_receipt (receipt_number),
    KEY idx_customer (customer_id),
    KEY idx_date (payment_date),
    KEY idx_method (payment_method),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (received_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (refunded_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- SUPPORT TICKETS
-- ============================================================
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    branch_id INT UNSIGNED,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('technical', 'billing', 'general', 'complaint', 'feedback') DEFAULT 'technical',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'pending_customer', 'resolved', 'closed') DEFAULT 'open',
    assigned_to INT UNSIGNED,
    resolved_by INT UNSIGNED,
    resolution_notes TEXT,
    resolved_at TIMESTAMP NULL,
    customer_rating ENUM('1', '2', '3', '4', '5'),
    customer_feedback TEXT,
    source ENUM('phone', 'email', 'portal', 'walk_in', 'chat') DEFAULT 'phone',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_ticket_number (ticket_number),
    KEY idx_customer (customer_id),
    KEY idx_status (status),
    KEY idx_category (category),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (resolved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- NAS (Network Access Server)
-- ============================================================
CREATE TABLE IF NOT EXISTS nas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    name VARCHAR(150) NOT NULL,
    short_name VARCHAR(50),
    nas_type ENUM('mikrotik', 'cisco', 'huawei', 'other') DEFAULT 'mikrotik',
    ip_address VARCHAR(45) NOT NULL,
    hostname VARCHAR(150),
    username VARCHAR(100),
    password VARCHAR(255),
    port INT DEFAULT 8728,
    location VARCHAR(255),
    description TEXT,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    last_connection TIMESTAMP NULL,
    total_users INT DEFAULT 0,
    active_users INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_name (name),
    KEY idx_ip (ip_address),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

-- ============================================================
-- GPON - OLT (Optical Line Terminal)
-- ============================================================
CREATE TABLE IF NOT EXISTS olts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    name VARCHAR(150) NOT NULL,
    model VARCHAR(100),
    manufacturer VARCHAR(100),
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100),
    password VARCHAR(255),
    serial_number VARCHAR(100),
    location VARCHAR(255),
    total_ports INT DEFAULT 0,
    used_ports INT DEFAULT 0,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    last_sync TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_name (name),
    KEY idx_ip (ip_address),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

-- ============================================================
-- GPON - ONU (Optical Network Unit)
-- ============================================================
CREATE TABLE IF NOT EXISTS onus (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    olt_id INT UNSIGNED NOT NULL,
    pon_port VARCHAR(50),
    serial_number VARCHAR(100) NOT NULL,
    model VARCHAR(100),
    manufacturer VARCHAR(100),
    status ENUM('active', 'inactive', 'offline', 'dying_gasp') DEFAULT 'inactive',
    distance INT,
    distance_unit VARCHAR(20) DEFAULT 'meters',
    rx_power DECIMAL(6, 2),
    tx_power DECIMAL(6, 2),
    voltage DECIMAL(6, 2),
    temperature DECIMAL(6, 2),
    bias_current DECIMAL(6, 2),
    customer_id INT UNSIGNED,
    installed_at TIMESTAMP NULL,
    last_seen TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_serial (serial_number),
    KEY idx_olt (olt_id),
    KEY idx_customer (customer_id),
    FOREIGN KEY (olt_id) REFERENCES olts(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- GPON - SPLITTERS
-- ============================================================
CREATE TABLE IF NOT EXISTS splitters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    name VARCHAR(150) NOT NULL,
    splitter_type ENUM('1:4', '1:8', '1:16', '1:32', '1:64', '1:128') DEFAULT '1:32',
    location VARCHAR(255),
    total_ports INT NOT NULL,
    used_ports INT DEFAULT 0,
    parent_splitter_id INT UNSIGNED,
    olt_id INT UNSIGNED,
    pon_port VARCHAR(50),
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    installed_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_name (name),
    KEY idx_parent (parent_splitter_id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (parent_splitter_id) REFERENCES splitters(id) ON DELETE SET NULL,
    FOREIGN KEY (olt_id) REFERENCES olts(id)
) ENGINE=InnoDB;

-- ============================================================
-- CASHBOOK
-- ============================================================
CREATE TABLE IF NOT EXISTS cashbook (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    date DATE NOT NULL,
    particular TEXT NOT NULL,
    debit DECIMAL(10, 2) DEFAULT 0.00,
    credit DECIMAL(10, 2) DEFAULT 0.00,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    category ENUM('income', 'expense', 'transfer') DEFAULT 'income',
    payment_method ENUM('cash', 'card', 'mobile_banking', 'bank_transfer', 'cheque') DEFAULT 'cash',
    reference_number VARCHAR(100),
    attached_with ENUM('payment', 'expense', 'manual') DEFAULT 'manual',
    attachment_id INT UNSIGNED,
    remarks TEXT,
    created_by INT UNSIGNED,
    approved_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_date (date),
    KEY idx_category (category),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- EXPENSES
-- ============================================================
CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    expense_number VARCHAR(50) UNIQUE NOT NULL,
    date DATE NOT NULL,
    category VARCHAR(100) NOT NULL,
    sub_category VARCHAR(100),
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'card', 'mobile_banking', 'bank_transfer', 'cheque') DEFAULT 'cash',
    particular TEXT NOT NULL,
    bill_number VARCHAR(100),
    vendor_name VARCHAR(150),
    vendor_contact VARCHAR(100),
    vendor_phone VARCHAR(20),
    reference_number VARCHAR(100),
    attachment VARCHAR(255),
    notes TEXT,
    approved_by INT UNSIGNED,
    approved_at TIMESTAMP NULL,
    is_recurring TINYINT(1) DEFAULT 0,
    recurring_frequency ENUM('daily', 'weekly', 'monthly', 'yearly') NULL,
    next_occurrence DATE NULL,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_expense_number (expense_number),
    KEY idx_date (date),
    KEY idx_category (category),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- INVENTORY - STOCK
-- ============================================================
CREATE TABLE IF NOT EXISTS stock_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id INT UNSIGNED,
    item_name VARCHAR(150) NOT NULL,
    item_code VARCHAR(50),
    category ENUM('ont', 'modem', 'router', 'cable', 'connector', 'splitter', 'olt_parts', 'other') DEFAULT 'other',
    unit VARCHAR(50) DEFAULT 'piece',
    quantity INT DEFAULT 0,
    min_stock_level INT DEFAULT 0,
    buy_price DECIMAL(10, 2) DEFAULT 0.00,
    sale_price DECIMAL(10, 2) DEFAULT 0.00,
    supplier_name VARCHAR(150),
    supplier_contact VARCHAR(100),
    description TEXT,
    location VARCHAR(100),
    rack_shelf VARCHAR(50),
    status ENUM('available', 'low_stock', 'out_of_stock', 'discontinued') DEFAULT 'available',
    last_stock_count DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_item_code (item_code),
    KEY idx_category (category),
    KEY idx_branch (branch_id),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

-- ============================================================
-- INVENTORY - PURCHASES
-- ============================================================
CREATE TABLE IF NOT EXISTS purchases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_number VARCHAR(50) UNIQUE NOT NULL,
    branch_id INT UNSIGNED,
    supplier_id INT UNSIGNED,
    purchase_date DATE NOT NULL,
    delivery_date DATE,
    subtotal DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    paid_amount DECIMAL(10, 2) DEFAULT 0.00,
    due_amount DECIMAL(10, 2) DEFAULT 0.00,
    payment_status ENUM('paid', 'partial', 'unpaid') DEFAULT 'unpaid',
    status ENUM('pending', 'received', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('cash', 'card', 'mobile_banking', 'bank_transfer', 'cheque', 'credit') DEFAULT 'cash',
    notes TEXT,
    ordered_by INT UNSIGNED,
    received_by INT UNSIGNED,
    approved_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_purchase_number (purchase_number),
    KEY idx_date (purchase_date),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (ordered_by) REFERENCES users(id),
    FOREIGN KEY (received_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- PORTAL USERS (Customer Portal Access)
-- ============================================================
CREATE TABLE IF NOT EXISTS portal_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    last_ip VARCHAR(45),
    password_reset_token VARCHAR(255),
    token_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_email (email),
    KEY idx_customer (customer_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SYSTEM SETTINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    module VARCHAR(50),
    description TEXT,
    is_public TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_module (module)
) ENGINE=InnoDB;

-- ============================================================
-- ACTIVITY LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    model VARCHAR(100),
    model_id INT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_action (action),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- SESSIONS (for web application)
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    ip_address VARCHAR(45),
    timestamp INT UNSIGNED DEFAULT 0,
    data MEDIUMTEXT
) ENGINE=InnoDB;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
