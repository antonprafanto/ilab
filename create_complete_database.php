<?php
/**
 * Create Complete Database Schema
 * Script untuk membuat database lengkap dengan semua tabel dan relasi
 */

require_once 'includes/config/database.php';

echo "<h1>🗄️ Create Complete Database Schema</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .step { background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 8px; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
</style>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Disable foreign key checks temporarily
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    echo "<div class='step'>";
    echo "<h3>Step 1: Creating Core System Tables</h3>";
    
    // 1. Roles table
    $db->exec("DROP TABLE IF EXISTS roles");
    $db->exec("
        CREATE TABLE roles (
            id INT PRIMARY KEY AUTO_INCREMENT,
            role_name VARCHAR(50) NOT NULL UNIQUE,
            role_display_name VARCHAR(100) NOT NULL,
            description TEXT,
            permissions JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Roles table created</div>";
    
    // 2. Users table (recreate with proper structure)
    $db->exec("DROP TABLE IF EXISTS users");
    $db->exec("
        CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role_id INT NOT NULL,
            institution VARCHAR(200),
            phone VARCHAR(20),
            address TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            email_verified BOOLEAN DEFAULT FALSE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_users_role (role_id),
            INDEX idx_users_active (is_active),
            FOREIGN KEY (role_id) REFERENCES roles(id)
        )
    ");
    echo "<div class='success'>✓ Users table created</div>";
    
    // 3. Service Categories
    $db->exec("DROP TABLE IF EXISTS service_categories");
    $db->exec("
        CREATE TABLE service_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            fields JSON,
            icon VARCHAR(50),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Service categories table created</div>";
    
    // 4. Service Types
    $db->exec("DROP TABLE IF EXISTS service_types");
    $db->exec("
        CREATE TABLE service_types (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_id INT NOT NULL,
            service_name VARCHAR(100) NOT NULL,
            description TEXT,
            price_range VARCHAR(50),
            duration_estimate VARCHAR(50),
            requirements TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES service_categories(id)
        )
    ");
    echo "<div class='success'>✓ Service types table created</div>";
    
    // 5. Equipment Categories
    $db->exec("DROP TABLE IF EXISTS equipment_categories");
    $db->exec("
        CREATE TABLE equipment_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Equipment categories table created</div>";
    
    // 6. Equipment
    $db->exec("DROP TABLE IF EXISTS equipment");
    $db->exec("
        CREATE TABLE equipment (
            id INT PRIMARY KEY AUTO_INCREMENT,
            equipment_name VARCHAR(100) NOT NULL,
            equipment_code VARCHAR(50) UNIQUE NOT NULL,
            category_id INT,
            brand VARCHAR(50),
            model VARCHAR(50),
            serial_number VARCHAR(100),
            specifications TEXT,
            status ENUM('available', 'in_use', 'maintenance', 'out_of_order') DEFAULT 'available',
            location VARCHAR(100),
            purchase_date DATE,
            warranty_until DATE,
            maintenance_schedule JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES equipment_categories(id)
        )
    ");
    echo "<div class='success'>✓ Equipment table created</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 2: Creating Booking & Process Tables</h3>";
    
    // 7. Bookings
    $db->exec("DROP TABLE IF EXISTS bookings");
    $db->exec("
        CREATE TABLE bookings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            booking_code VARCHAR(50) UNIQUE NOT NULL,
            user_id INT NOT NULL,
            service_id INT,
            equipment_id INT,
            booking_date DATE NOT NULL,
            time_slot TIME,
            duration_hours INT DEFAULT 1,
            estimated_cost DECIMAL(10,2),
            actual_cost DECIMAL(10,2),
            priority ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
            status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            sample_description TEXT,
            special_requirements TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (service_id) REFERENCES service_types(id),
            FOREIGN KEY (equipment_id) REFERENCES equipment(id)
        )
    ");
    echo "<div class='success'>✓ Bookings table created</div>";
    
    // 8. Process Types
    $db->exec("DROP TABLE IF EXISTS process_types");
    $db->exec("
        CREATE TABLE process_types (
            id INT PRIMARY KEY AUTO_INCREMENT,
            process_name VARCHAR(100) NOT NULL,
            description TEXT,
            step_count INT DEFAULT 8,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Process types table created</div>";
    
    // 9. Processes
    $db->exec("DROP TABLE IF EXISTS processes");
    $db->exec("
        CREATE TABLE processes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            process_code VARCHAR(50) UNIQUE NOT NULL,
            booking_id INT,
            process_type_id INT NOT NULL,
            current_step INT DEFAULT 1,
            status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            started_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(id),
            FOREIGN KEY (process_type_id) REFERENCES process_types(id)
        )
    ");
    echo "<div class='success'>✓ Processes table created</div>";
    
    // 10. Process Steps
    $db->exec("DROP TABLE IF EXISTS process_steps");
    $db->exec("
        CREATE TABLE process_steps (
            id INT PRIMARY KEY AUTO_INCREMENT,
            process_id INT NOT NULL,
            step_number INT NOT NULL,
            step_name VARCHAR(100) NOT NULL,
            description TEXT,
            status ENUM('pending', 'in_progress', 'completed', 'skipped') DEFAULT 'pending',
            assigned_to INT,
            started_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (process_id) REFERENCES processes(id),
            FOREIGN KEY (assigned_to) REFERENCES users(id)
        )
    ");
    echo "<div class='success'>✓ Process steps table created</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 3: Creating Content Management Tables</h3>";
    
    // 11. Activity Types
    $db->exec("DROP TABLE IF EXISTS activity_types");
    $db->exec("
        CREATE TABLE activity_types (
            id INT PRIMARY KEY AUTO_INCREMENT,
            type_name VARCHAR(100) NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#007bff',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Activity types table created</div>";
    
    // 12. Activities
    $db->exec("DROP TABLE IF EXISTS activities");
    $db->exec("
        CREATE TABLE activities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            activity_code VARCHAR(50) NOT NULL UNIQUE,
            title VARCHAR(500) NOT NULL,
            type_id INT NOT NULL,
            description TEXT,
            start_date DATE NOT NULL,
            end_date DATE,
            start_time TIME,
            end_time TIME,
            location VARCHAR(255),
            facilitator VARCHAR(255),
            max_participants INT,
            registration_required BOOLEAN DEFAULT FALSE,
            registration_deadline DATE,
            cost DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('planned', 'open_registration', 'full', 'ongoing', 'completed', 'cancelled') DEFAULT 'planned',
            participants JSON,
            institutions JSON,
            equipment_used JSON,
            outcomes TEXT,
            attachments JSON,
            is_featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (type_id) REFERENCES activity_types(id)
        )
    ");
    echo "<div class='success'>✓ Activities table created</div>";
    
    // 13. SOP Categories
    $db->exec("DROP TABLE IF EXISTS sop_categories");
    $db->exec("
        CREATE TABLE sop_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ SOP categories table created</div>";
    
    // 14. SOP Documents
    $db->exec("DROP TABLE IF EXISTS sop_documents");
    $db->exec("
        CREATE TABLE sop_documents (
            id INT PRIMARY KEY AUTO_INCREMENT,
            sop_code VARCHAR(50) NOT NULL UNIQUE,
            title VARCHAR(500) NOT NULL,
            category_id INT NOT NULL,
            description TEXT,
            version VARCHAR(20) DEFAULT '1.0',
            file_path VARCHAR(255),
            file_size INT,
            effective_date DATE NOT NULL,
            review_date DATE,
            approval_authority VARCHAR(255),
            tags JSON,
            download_count INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES sop_categories(id)
        )
    ");
    echo "<div class='success'>✓ SOP documents table created</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 4: Creating Organization & Quality Tables</h3>";
    
    // 15. Organizational Levels
    $db->exec("DROP TABLE IF EXISTS organizational_levels");
    $db->exec("
        CREATE TABLE organizational_levels (
            id INT PRIMARY KEY AUTO_INCREMENT,
            level_number INT NOT NULL UNIQUE,
            level_name VARCHAR(100) NOT NULL,
            description TEXT,
            responsibilities JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Organizational levels table created</div>";
    
    // 16. Organizational Structure
    $db->exec("DROP TABLE IF EXISTS organizational_structure");
    $db->exec("
        CREATE TABLE organizational_structure (
            id INT PRIMARY KEY AUTO_INCREMENT,
            position_name VARCHAR(100) NOT NULL,
            level_id INT NOT NULL,
            parent_id INT,
            person_name VARCHAR(100),
            responsibilities JSON,
            contact_info JSON,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (level_id) REFERENCES organizational_levels(id),
            FOREIGN KEY (parent_id) REFERENCES organizational_structure(id)
        )
    ");
    echo "<div class='success'>✓ Organizational structure table created</div>";
    
    // 17. Quality Metrics
    $db->exec("DROP TABLE IF EXISTS quality_metrics");
    $db->exec("
        CREATE TABLE quality_metrics (
            id INT PRIMARY KEY AUTO_INCREMENT,
            metric_name VARCHAR(100) NOT NULL,
            category ENUM('implementation', 'evaluation', 'improvement', 'consistency') NOT NULL,
            description TEXT,
            measurement_unit VARCHAR(50),
            target_value DECIMAL(10,2),
            current_value DECIMAL(10,2),
            measurement_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Quality metrics table created</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 5: Creating Supporting Tables</h3>";
    
    // 18. Payments
    $db->exec("DROP TABLE IF EXISTS payments");
    $db->exec("
        CREATE TABLE payments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            booking_id INT NOT NULL,
            payment_code VARCHAR(50) UNIQUE NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('transfer', 'cash', 'check', 'digital') NOT NULL,
            status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            payment_date TIMESTAMP NULL,
            notes TEXT,
            receipt_path VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(id)
        )
    ");
    echo "<div class='success'>✓ Payments table created</div>";
    
    // 19. Equipment Usage Log
    $db->exec("DROP TABLE IF EXISTS equipment_usage_log");
    $db->exec("
        CREATE TABLE equipment_usage_log (
            id INT PRIMARY KEY AUTO_INCREMENT,
            equipment_id INT NOT NULL,
            user_id INT NOT NULL,
            booking_id INT,
            start_time TIMESTAMP NOT NULL,
            end_time TIMESTAMP,
            duration_hours DECIMAL(4,2),
            purpose TEXT,
            condition_before TEXT,
            condition_after TEXT,
            issues_reported TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (equipment_id) REFERENCES equipment(id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (booking_id) REFERENCES bookings(id)
        )
    ");
    echo "<div class='success'>✓ Equipment usage log table created</div>";
    
    // 20. News & Announcements
    $db->exec("DROP TABLE IF EXISTS news_announcements");
    $db->exec("
        CREATE TABLE news_announcements (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            category ENUM('news', 'announcement', 'maintenance', 'event') DEFAULT 'news',
            author_id INT,
            featured_image VARCHAR(255),
            is_published BOOLEAN DEFAULT FALSE,
            is_featured BOOLEAN DEFAULT FALSE,
            publish_date TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id)
        )
    ");
    echo "<div class='success'>✓ News announcements table created</div>";
    
    echo "</div>";
    
    // Enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<div class='step'>";
    echo "<h3>🎉 Complete Database Schema Created!</h3>";
    echo "<div class='success'>All 20 tables created successfully with proper relationships!</div>";
    echo "<div class='info'>";
    echo "<h4>Database Structure:</h4>";
    echo "<ul>";
    echo "<li><strong>Core System:</strong> roles, users (2 tables)</li>";
    echo "<li><strong>Services:</strong> service_categories, service_types (2 tables)</li>";
    echo "<li><strong>Equipment:</strong> equipment_categories, equipment, equipment_usage_log (3 tables)</li>";
    echo "<li><strong>Booking & Process:</strong> bookings, process_types, processes, process_steps (4 tables)</li>";
    echo "<li><strong>Activities:</strong> activity_types, activities (2 tables)</li>";
    echo "<li><strong>SOP:</strong> sop_categories, sop_documents (2 tables)</li>";
    echo "<li><strong>Organization:</strong> organizational_levels, organizational_structure (2 tables)</li>";
    echo "<li><strong>Quality:</strong> quality_metrics (1 table)</li>";
    echo "<li><strong>Support:</strong> payments, news_announcements (2 tables)</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
?>

<div style="margin-top: 30px; padding: 20px; background: #d1ecf1; border-radius: 8px; border: 1px solid #bee5eb;">
    <h4>✅ Database Schema Complete!</h4>
    <p>Complete database with all relationships has been created.</p>
    <p><strong>Next:</strong> <a href="populate_complete_data.php">Populate with sample data</a></p>
</div>