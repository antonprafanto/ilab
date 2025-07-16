-- ILab UNMUL Database Schema (MySQL Compatible)
-- Fixed version for XAMPP/MySQL compatibility

USE ilab_unmul;

-- ==============================================================================
-- CORE TABLES
-- ==============================================================================

-- Roles table
CREATE TABLE roles (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions TEXT,
    level INT NOT NULL DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    role_id VARCHAR(36) NOT NULL,
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
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Equipment table
CREATE TABLE equipment (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type ENUM(
        'gc_ms', 'lc_ms', 'aas', 'ftir', 'pcr', 'freeze_dryer', 
        'hplc', 'spectrophotometer', 'microscope', 'centrifuge', 
        'incubator', 'other'
    ) NOT NULL,
    category_id VARCHAR(36),
    description TEXT,
    specifications TEXT,
    status ENUM('available', 'in_use', 'maintenance', 'out_of_order', 'reserved') DEFAULT 'available',
    location VARCHAR(200) NOT NULL,
    responsible_person VARCHAR(200) NOT NULL,
    contact_info VARCHAR(200),
    booking_rules TEXT NOT NULL,
    pricing TEXT NOT NULL,
    images TEXT,
    documents TEXT,
    maintenance_schedule TEXT,
    calibration_schedule TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_category (category_id)
);

-- Sessions for JWT management
CREATE TABLE user_sessions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    refresh_token VARCHAR(500) NOT NULL,
    device_info TEXT,
    ip_address VARCHAR(45),
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_refresh_token (refresh_token(255)),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_active (is_active)
);