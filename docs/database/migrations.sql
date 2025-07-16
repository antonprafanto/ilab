-- ILab UNMUL Database Migrations
-- Version control for database schema changes

-- ==============================================================================
-- MIGRATION TRACKING TABLE
-- ==============================================================================

CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT TRUE
);

-- ==============================================================================
-- MIGRATION 001: Initial Schema Creation
-- ==============================================================================

-- Check if migration already applied
SET @migration_exists = (SELECT COUNT(*) FROM information_schema.tables 
                        WHERE table_schema = 'ilab_unmul' AND table_name = 'users');

-- Apply migration if not exists
-- This would typically be handled by a migration script runner
-- For now, we'll document the migration structure

INSERT INTO migrations (version, description) VALUES 
('001_initial_schema', 'Initial database schema creation with all core tables')
ON DUPLICATE KEY UPDATE version = version;

-- ==============================================================================
-- MIGRATION 002: Add Indexes for Performance
-- ==============================================================================

-- Additional indexes for better query performance
ALTER TABLE bookings 
ADD INDEX idx_user_status (user_id, status),
ADD INDEX idx_equipment_time (equipment_id, start_time, end_time),
ADD INDEX idx_date_range (start_time, end_time);

ALTER TABLE samples 
ADD INDEX idx_booking_status (booking_id, status),
ADD INDEX idx_submitted_status (submitted_by, status);

ALTER TABLE payments 
ADD INDEX idx_user_status (user_id, status),
ADD INDEX idx_booking_status (booking_id, status);

ALTER TABLE notifications 
ADD INDEX idx_user_type_read (user_id, type, is_read);

INSERT INTO migrations (version, description) VALUES 
('002_performance_indexes', 'Added indexes for better query performance')
ON DUPLICATE KEY UPDATE version = version;

-- ==============================================================================
-- MIGRATION 003: Add Full-Text Search Indexes
-- ==============================================================================

-- Full-text search for equipment
ALTER TABLE equipment 
ADD FULLTEXT(name, description);

-- Full-text search for samples
ALTER TABLE samples 
ADD FULLTEXT(sample_name, description);

-- Full-text search for users (for admin search)
ALTER TABLE users 
ADD FULLTEXT(first_name, last_name, email);

INSERT INTO migrations (version, description) VALUES 
('003_fulltext_search', 'Added full-text search indexes')
ON DUPLICATE KEY UPDATE version = version;

-- ==============================================================================
-- MIGRATION 004: Add Triggers for Audit Logging
-- ==============================================================================

DELIMITER $$

-- Trigger for users table audit
CREATE TRIGGER users_audit_insert 
AFTER INSERT ON users 
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, new_values, user_id, created_at)
    VALUES ('users', NEW.id, 'INSERT', JSON_OBJECT(
        'email', NEW.email,
        'first_name', NEW.first_name,
        'last_name', NEW.last_name,
        'role_id', NEW.role_id,
        'status', NEW.status
    ), NEW.id, NOW());
END$$

CREATE TRIGGER users_audit_update 
AFTER UPDATE ON users 
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, old_values, new_values, user_id, created_at)
    VALUES ('users', NEW.id, 'UPDATE', 
        JSON_OBJECT(
            'email', OLD.email,
            'first_name', OLD.first_name,
            'last_name', OLD.last_name,
            'role_id', OLD.role_id,
            'status', OLD.status
        ),
        JSON_OBJECT(
            'email', NEW.email,
            'first_name', NEW.first_name,
            'last_name', NEW.last_name,
            'role_id', NEW.role_id,
            'status', NEW.status
        ), 
        NEW.id, NOW());
END$$

-- Trigger for bookings audit
CREATE TRIGGER bookings_audit_insert 
AFTER INSERT ON bookings 
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, new_values, user_id, created_at)
    VALUES ('bookings', NEW.id, 'INSERT', JSON_OBJECT(
        'user_id', NEW.user_id,
        'equipment_id', NEW.equipment_id,
        'start_time', NEW.start_time,
        'end_time', NEW.end_time,
        'status', NEW.status
    ), NEW.user_id, NOW());
END$$

CREATE TRIGGER bookings_audit_update 
AFTER UPDATE ON bookings 
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (table_name, record_id, action, old_values, new_values, user_id, created_at)
    VALUES ('bookings', NEW.id, 'UPDATE', 
        JSON_OBJECT(
            'status', OLD.status,
            'start_time', OLD.start_time,
            'end_time', OLD.end_time
        ),
        JSON_OBJECT(
            'status', NEW.status,
            'start_time', NEW.start_time,
            'end_time', NEW.end_time
        ), 
        NEW.user_id, NOW());
END$$

DELIMITER ;

INSERT INTO migrations (version, description) VALUES 
('004_audit_triggers', 'Added audit logging triggers')
ON DUPLICATE KEY UPDATE version = version;

-- ==============================================================================
-- MIGRATION 005: Add Stored Procedures
-- ==============================================================================

DELIMITER $$

-- Procedure to check equipment availability
CREATE PROCEDURE CheckEquipmentAvailability(
    IN p_equipment_id CHAR(36),
    IN p_start_time TIMESTAMP,
    IN p_end_time TIMESTAMP,
    OUT p_is_available BOOLEAN
)
BEGIN
    DECLARE conflict_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO conflict_count
    FROM bookings 
    WHERE equipment_id = p_equipment_id
    AND status IN ('confirmed', 'in_progress')
    AND (
        (p_start_time >= start_time AND p_start_time < end_time) OR
        (p_end_time > start_time AND p_end_time <= end_time) OR
        (p_start_time <= start_time AND p_end_time >= end_time)
    );
    
    SET p_is_available = (conflict_count = 0);
END$$

-- Procedure to generate sample code
CREATE PROCEDURE GenerateSampleCode(
    IN p_prefix VARCHAR(10),
    OUT p_sample_code VARCHAR(50)
)
BEGIN
    DECLARE v_date_part VARCHAR(10);
    DECLARE v_sequence INT;
    
    SET v_date_part = DATE_FORMAT(NOW(), '%y%m%d');
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(sample_code, -3) AS UNSIGNED)), 0) + 1 
    INTO v_sequence
    FROM samples 
    WHERE sample_code LIKE CONCAT(p_prefix, v_date_part, '%');
    
    SET p_sample_code = CONCAT(p_prefix, v_date_part, LPAD(v_sequence, 3, '0'));
END$$

-- Procedure to calculate payment amount
CREATE PROCEDURE CalculateBookingCost(
    IN p_booking_id CHAR(36),
    OUT p_total_cost DECIMAL(12,2)
)
BEGIN
    DECLARE v_duration_hours DECIMAL(6,2);
    DECLARE v_price_per_hour DECIMAL(12,2);
    DECLARE v_setup_fee DECIMAL(12,2);
    DECLARE v_user_role VARCHAR(50);
    DECLARE v_discount_percentage INT DEFAULT 0;
    
    -- Get booking duration and user role
    SELECT 
        TIMESTAMPDIFF(MINUTE, b.start_time, b.end_time) / 60.0,
        r.name
    INTO v_duration_hours, v_user_role
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN roles r ON u.role_id = r.id
    WHERE b.id = p_booking_id;
    
    -- Get equipment pricing
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(pricing, '$.pricePerHour')),
        JSON_UNQUOTE(JSON_EXTRACT(pricing, '$.setupFee'))
    INTO v_price_per_hour, v_setup_fee
    FROM equipment e
    JOIN bookings b ON e.id = b.equipment_id
    WHERE b.id = p_booking_id;
    
    -- Calculate discount based on user role
    IF v_user_role = 'student' THEN
        SET v_discount_percentage = 30;
    ELSEIF v_user_role = 'lecturer' THEN
        SET v_discount_percentage = 20;
    END IF;
    
    -- Calculate total cost
    SET p_total_cost = (v_duration_hours * v_price_per_hour + v_setup_fee) * 
                       (100 - v_discount_percentage) / 100;
END$$

DELIMITER ;

INSERT INTO migrations (version, description) VALUES 
('005_stored_procedures', 'Added stored procedures for common operations')
ON DUPLICATE KEY UPDATE version = version;

-- ==============================================================================
-- MIGRATION 006: Add Views for Common Queries
-- ==============================================================================

-- View for equipment availability
CREATE VIEW equipment_availability AS
SELECT 
    e.id,
    e.name,
    e.type,
    e.status,
    e.location,
    COUNT(CASE WHEN b.status IN ('confirmed', 'in_progress') 
               AND b.start_time <= NOW() 
               AND b.end_time > NOW() 
          THEN 1 END) as current_bookings,
    MIN(CASE WHEN b.status = 'confirmed' 
             AND b.start_time > NOW() 
        THEN b.start_time END) as next_booking
FROM equipment e
LEFT JOIN bookings b ON e.id = b.equipment_id 
    AND b.status IN ('confirmed', 'in_progress')
GROUP BY e.id, e.name, e.type, e.status, e.location;

-- View for user booking summary
CREATE VIEW user_booking_summary AS
SELECT 
    u.id as user_id,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as full_name,
    r.display_name as role,
    COUNT(b.id) as total_bookings,
    COUNT(CASE WHEN b.status = 'completed' THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN b.status = 'cancelled' THEN 1 END) as cancelled_bookings,
    COUNT(CASE WHEN b.status = 'no_show' THEN 1 END) as no_show_bookings,
    SUM(CASE WHEN b.actual_cost IS NOT NULL THEN b.actual_cost ELSE 0 END) as total_spent
FROM users u
JOIN roles r ON u.role_id = r.id
LEFT JOIN bookings b ON u.id = b.user_id
GROUP BY u.id, u.email, u.first_name, u.last_name, r.display_name;

-- View for equipment utilization
CREATE VIEW equipment_utilization AS
SELECT 
    e.id,
    e.name,
    e.type,
    COUNT(b.id) as total_bookings,
    SUM(TIMESTAMPDIFF(HOUR, b.start_time, b.end_time)) as total_hours_booked,
    AVG(TIMESTAMPDIFF(HOUR, b.start_time, b.end_time)) as avg_session_duration,
    COUNT(CASE WHEN b.status = 'completed' THEN 1 END) as successful_sessions,
    COUNT(CASE WHEN b.status = 'no_show' THEN 1 END) as no_show_sessions
FROM equipment e
LEFT JOIN bookings b ON e.id = b.equipment_id 
    AND b.start_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY e.id, e.name, e.type;

INSERT INTO migrations (version, description) VALUES 
('006_reporting_views', 'Added views for reporting and analytics')
ON DUPLICATE KEY UPDATE version = version;

-- ==============================================================================
-- MIGRATION STATUS
-- ==============================================================================

-- Show applied migrations
SELECT * FROM migrations ORDER BY executed_at;