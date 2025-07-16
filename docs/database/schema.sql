-- ILab UNMUL Database Schema
-- Created for Integrated Laboratory Management System
-- Universitas Mulawarman

-- Create database
CREATE DATABASE IF NOT EXISTS ilab_unmul 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE ilab_unmul;

-- ==============================================================================
-- CORE TABLES
-- ==============================================================================

-- Roles table
CREATE TABLE roles (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    level INT NOT NULL DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    role_id CHAR(36) NOT NULL,
    status ENUM('pending', 'active', 'inactive', 'suspended') DEFAULT 'pending',
    faculty VARCHAR(100),
    department VARCHAR(100),
    student_id VARCHAR(50),
    nim VARCHAR(50),
    institution VARCHAR(200),
    profile_picture VARCHAR(500),
    identity_document VARCHAR(500),
    is_email_verified BOOLEAN DEFAULT FALSE,
    is_document_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_email (email),
    INDEX idx_role (role_id),
    INDEX idx_status (status),
    INDEX idx_nim (nim),
    INDEX idx_student_id (student_id)
);

-- Equipment categories
CREATE TABLE equipment_categories (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Equipment table
CREATE TABLE equipment (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(200) NOT NULL,
    type ENUM(
        'gc_ms', 'lc_ms', 'aas', 'ftir', 'pcr', 'freeze_dryer', 
        'hplc', 'spectrophotometer', 'microscope', 'centrifuge', 
        'incubator', 'other'
    ) NOT NULL,
    category_id CHAR(36),
    description TEXT,
    specifications JSON,
    status ENUM('available', 'in_use', 'maintenance', 'out_of_order', 'reserved') DEFAULT 'available',
    location VARCHAR(200) NOT NULL,
    responsible_person VARCHAR(200) NOT NULL,
    contact_info VARCHAR(200),
    booking_rules JSON NOT NULL,
    pricing JSON NOT NULL,
    images JSON,
    documents JSON,
    maintenance_schedule JSON,
    calibration_schedule JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    FULLTEXT(name, description)
);

-- ==============================================================================
-- BOOKING & RESERVATION TABLES
-- ==============================================================================

-- Bookings table
CREATE TABLE bookings (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    equipment_id CHAR(36) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    purpose TEXT NOT NULL,
    sample_type VARCHAR(100),
    number_of_samples INT,
    special_requirements TEXT,
    estimated_cost DECIMAL(12,2),
    actual_cost DECIMAL(12,2),
    notes TEXT,
    approved_by CHAR(36),
    approved_at TIMESTAMP NULL,
    cancelled_by CHAR(36),
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT,
    attachments JSON,
    reminder_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user (user_id),
    INDEX idx_equipment (equipment_id),
    INDEX idx_status (status),
    INDEX idx_start_time (start_time),
    INDEX idx_end_time (end_time),
    INDEX idx_priority (priority),
    
    CONSTRAINT chk_booking_time CHECK (end_time > start_time)
);

-- Booking history for tracking changes
CREATE TABLE booking_history (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    booking_id CHAR(36) NOT NULL,
    action ENUM('created', 'updated', 'approved', 'cancelled', 'completed') NOT NULL,
    previous_data JSON,
    new_data JSON,
    changed_by CHAR(36) NOT NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- ==============================================================================
-- SAMPLE MANAGEMENT TABLES
-- ==============================================================================

-- Samples table
CREATE TABLE samples (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    booking_id CHAR(36) NOT NULL,
    sample_code VARCHAR(50) NOT NULL UNIQUE,
    sample_name VARCHAR(200) NOT NULL,
    sample_type ENUM(
        'water', 'soil', 'food', 'pharmaceutical', 'chemical', 
        'biological', 'environmental', 'clinical', 'industrial', 'other'
    ) NOT NULL,
    description TEXT,
    quantity VARCHAR(100) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    storage_conditions TEXT,
    preparation_notes TEXT,
    analysis_requested JSON NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM(
        'submitted', 'received', 'in_analysis', 'analysis_complete', 
        'results_ready', 'delivered', 'rejected'
    ) DEFAULT 'submitted',
    submitted_by CHAR(36) NOT NULL,
    received_by CHAR(36),
    analyzed_by CHAR(36),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_at TIMESTAMP NULL,
    analysis_started_at TIMESTAMP NULL,
    analysis_completed_at TIMESTAMP NULL,
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    sample_condition JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (analyzed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_booking (booking_id),
    INDEX idx_sample_code (sample_code),
    INDEX idx_status (status),
    INDEX idx_sample_type (sample_type),
    INDEX idx_submitted_by (submitted_by),
    FULLTEXT(sample_name, description)
);

-- Sample chain of custody
CREATE TABLE sample_custody (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    sample_id CHAR(36) NOT NULL,
    action VARCHAR(100) NOT NULL,
    performed_by CHAR(36) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    location VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sample_id) REFERENCES samples(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sample (sample_id),
    INDEX idx_timestamp (timestamp)
);

-- Test results
CREATE TABLE test_results (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    sample_id CHAR(36) NOT NULL,
    test_name VARCHAR(200) NOT NULL,
    test_method VARCHAR(200) NOT NULL,
    result TEXT NOT NULL,
    unit VARCHAR(50),
    uncertainty VARCHAR(100),
    limit_of_detection VARCHAR(100),
    limit_of_quantification VARCHAR(100),
    notes TEXT,
    performed_by CHAR(36) NOT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    validated BOOLEAN DEFAULT FALSE,
    validated_by CHAR(36),
    validated_at TIMESTAMP NULL,
    equipment_used CHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sample_id) REFERENCES samples(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (equipment_used) REFERENCES equipment(id) ON DELETE SET NULL,
    
    INDEX idx_sample (sample_id),
    INDEX idx_test_name (test_name),
    INDEX idx_performed_by (performed_by),
    INDEX idx_performed_at (performed_at)
);

-- Result files
CREATE TABLE result_files (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    sample_id CHAR(36) NOT NULL,
    test_result_id CHAR(36),
    file_name VARCHAR(500) NOT NULL,
    file_path VARCHAR(1000) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    uploaded_by CHAR(36) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sample_id) REFERENCES samples(id) ON DELETE CASCADE,
    FOREIGN KEY (test_result_id) REFERENCES test_results(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sample (sample_id),
    INDEX idx_test_result (test_result_id)
);

-- ==============================================================================
-- PAYMENT & FINANCIAL TABLES
-- ==============================================================================

-- Payments table
CREATE TABLE payments (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    booking_id CHAR(36) NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    user_id CHAR(36) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    tax DECIMAL(12,2) DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    remaining_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'paid', 'partial', 'overdue', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'digital_wallet', 'check'),
    payment_reference VARCHAR(200),
    due_date DATE NOT NULL,
    paid_at TIMESTAMP NULL,
    notes TEXT,
    bill_to JSON NOT NULL,
    created_by CHAR(36) NOT NULL,
    approved_by CHAR(36),
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_booking (booking_id),
    INDEX idx_user (user_id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
);

-- Payment items
CREATE TABLE payment_items (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    payment_id CHAR(36) NOT NULL,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    category ENUM('equipment_usage', 'sample_analysis', 'consultation', 'training', 'other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    INDEX idx_payment (payment_id),
    INDEX idx_category (category)
);

-- Payment records (for tracking multiple payments)
CREATE TABLE payment_records (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    payment_id CHAR(36) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    method ENUM('cash', 'bank_transfer', 'credit_card', 'digital_wallet', 'check') NOT NULL,
    reference VARCHAR(200),
    notes TEXT,
    processed_by CHAR(36) NOT NULL,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_payment (payment_id),
    INDEX idx_processed_at (processed_at)
);

-- ==============================================================================
-- SYSTEM TABLES
-- ==============================================================================

-- Notifications
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    type ENUM('booking', 'payment', 'sample', 'system', 'reminder') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    is_sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- System settings
CREATE TABLE system_settings (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    category VARCHAR(100) NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    value TEXT NOT NULL,
    data_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_setting (category, key_name),
    INDEX idx_category (category),
    INDEX idx_is_public (is_public)
);

-- Audit logs
CREATE TABLE audit_logs (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    table_name VARCHAR(100) NOT NULL,
    record_id CHAR(36) NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    user_id CHAR(36),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Sessions for JWT management
CREATE TABLE user_sessions (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    refresh_token VARCHAR(500) NOT NULL,
    device_info JSON,
    ip_address VARCHAR(45),
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_refresh_token (refresh_token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_active (is_active)
);