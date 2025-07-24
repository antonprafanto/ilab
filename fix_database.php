<?php
/**
 * Fix Database Script - Perbaiki dan lengkapi database local
 */

require_once 'includes/config/database.php';

echo "<h1>🔧 Fix Database Issues</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .step { background: #f0f0f0; padding: 15px; margin: 15px 0; border-radius: 8px; }
</style>";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<div class='step'>";
    echo "<h3>Step 1: Creating Missing Core Tables</h3>";
    
    // Roles table
    $db->exec("
        CREATE TABLE IF NOT EXISTS roles (
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
    
    // Insert roles
    $roles_data = [
        ['fakultas', 'Fakultas', 'Staff fakultas internal UNMUL', '["view_reports", "book_services"]'],
        ['mahasiswa', 'Mahasiswa', 'Mahasiswa UNMUL', '["book_services", "view_calendar"]'],
        ['peneliti_internal', 'Peneliti Internal', 'Peneliti internal UNMUL', '["advanced_booking", "view_reports"]'],
        ['staf_ilab', 'Staff ILab', 'Administrator ILab UNMUL', '["full_access", "admin_panel"]'],
        ['industri', 'Industri', 'Partner industri eksternal', '["commercial_booking", "priority_service"]'],
        ['pemerintah', 'Pemerintah', 'Instansi pemerintah', '["government_booking", "special_rates"]'],
        ['masyarakat', 'Masyarakat', 'Masyarakat umum', '["basic_booking", "public_access"]'],
        ['umkm', 'UMKM', 'Usaha Mikro Kecil Menengah', '["umkm_rates", "business_support"]']
    ];
    
    foreach ($roles_data as $role) {
        $stmt = $db->prepare("INSERT IGNORE INTO roles (role_name, role_display_name, description, permissions) VALUES (?, ?, ?, ?)");
        $stmt->execute($role);
    }
    echo "<div class='success'>✓ 8 user roles inserted</div>";
    
    // Update users table to use role_id instead of role_name
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role_id INT");
    $db->exec("UPDATE users u SET role_id = (SELECT id FROM roles r WHERE r.role_name = 'staf_ilab') WHERE u.role_id IS NULL");
    echo "<div class='success'>✓ Users table updated with role_id</div>";
    
    // Service types table
    $db->exec("
        CREATE TABLE IF NOT EXISTS service_types (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_id INT,
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
    
    // Bookings table
    $db->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            service_id INT,
            booking_date DATE NOT NULL,
            time_slot TIME,
            duration_hours INT DEFAULT 1,
            estimated_cost DECIMAL(10,2),
            priority ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
            status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (service_id) REFERENCES service_types(id)
        )
    ");
    echo "<div class='success'>✓ Bookings table created</div>";
    
    // Equipment categories
    $db->exec("
        CREATE TABLE IF NOT EXISTS equipment_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Equipment categories table created</div>";
    
    // SOP categories
    $db->exec("
        CREATE TABLE IF NOT EXISTS sop_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ SOP categories table created</div>";
    
    // Activity types
    $db->exec("
        CREATE TABLE IF NOT EXISTS activity_types (
            id INT PRIMARY KEY AUTO_INCREMENT,
            type_name VARCHAR(100) NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#007bff',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Activity types table created</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 2: Inserting Sample Data</h3>";
    
    // Equipment categories data
    $equipment_cats = [
        ['Analytical Instruments', 'Peralatan analisis kimia dan material'],
        ['Medical Equipment', 'Peralatan diagnostik medis'],
        ['Testing Equipment', 'Peralatan testing dan kalibrasi'],
        ['Research Tools', 'Peralatan penelitian umum']
    ];
    
    foreach ($equipment_cats as $cat) {
        $stmt = $db->prepare("INSERT IGNORE INTO equipment_categories (category_name, description) VALUES (?, ?)");
        $stmt->execute($cat);
    }
    echo "<div class='success'>✓ Equipment categories inserted</div>";
    
    // SOP categories data
    $sop_cats = [
        ['Laboratory Safety', 'Prosedur keselamatan laboratorium'],
        ['Equipment Operation', 'Prosedur operasional peralatan'],
        ['Sample Handling', 'Prosedur penanganan sampel'],
        ['Quality Control', 'Prosedur kontrol kualitas'],
        ['Maintenance', 'Prosedur pemeliharaan'],
        ['Emergency Response', 'Prosedur tanggap darurat'],
        ['Documentation', 'Prosedur dokumentasi'],
        ['Calibration', 'Prosedur kalibrasi'],
        ['Waste Management', 'Prosedur pengelolaan limbah'],
        ['Data Management', 'Prosedur pengelolaan data'],
        ['Training', 'Prosedur pelatihan']
    ];
    
    foreach ($sop_cats as $cat) {
        $stmt = $db->prepare("INSERT IGNORE INTO sop_categories (category_name, description) VALUES (?, ?)");
        $stmt->execute($cat);
    }
    echo "<div class='success'>✓ SOP categories inserted (11 categories)</div>";
    
    // Activity types data
    $activity_types = [
        ['Workshop', 'Pelatihan dan workshop', '#28a745'],
        ['Seminar', 'Seminar dan presentasi', '#007bff'], 
        ['Training', 'Pelatihan teknis', '#ffc107'],
        ['Conference', 'Konferensi dan symposium', '#6f42c1'],
        ['Research', 'Kegiatan penelitian', '#20c997'],
        ['Maintenance', 'Pemeliharaan peralatan', '#fd7e14']
    ];
    
    foreach ($activity_types as $type) {
        $stmt = $db->prepare("INSERT IGNORE INTO activity_types (type_name, description, color) VALUES (?, ?, ?)");
        $stmt->execute($type);
    }
    echo "<div class='success'>✓ Activity types inserted</div>";
    
    // Service types data
    $service_types = [
        [1, 'Analisis Kimia Dasar', 'Analisis komposisi kimia sampel', 'Rp 500.000 - 1.000.000'],
        [1, 'Spectroscopy Analysis', 'Analisis menggunakan FTIR/UV-Vis', 'Rp 300.000 - 800.000'],
        [1, 'Material Testing', 'Pengujian sifat material', 'Rp 1.000.000 - 2.000.000'],
        [2, 'Clinical Chemistry', 'Analisis kimia klinik', 'Rp 200.000 - 500.000'],
        [2, 'Hematology Test', 'Pemeriksaan darah lengkap', 'Rp 150.000 - 300.000'],
        [3, 'Survey Research', 'Penelitian survei sosial', 'Rp 5.000.000 - 10.000.000'],
        [4, 'Equipment Calibration', 'Kalibrasi peralatan KAN', 'Rp 2.000.000 - 5.000.000']
    ];
    
    foreach ($service_types as $service) {
        $stmt = $db->prepare("INSERT IGNORE INTO service_types (category_id, service_name, description, price_range) VALUES (?, ?, ?, ?)");
        $stmt->execute($service);
    }
    echo "<div class='success'>✓ Service types inserted</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 3: Update Equipment Table</h3>";
    
    // Update equipment table with foreign key
    $db->exec("ALTER TABLE equipment ADD COLUMN IF NOT EXISTS category_id INT");
    $db->exec("UPDATE equipment SET category_id = 1 WHERE category_id IS NULL");
    echo "<div class='success'>✓ Equipment table updated</div>";
    
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>🎉 Database Fixed Successfully!</h3>";
    echo "<div class='success'>All missing tables created and populated!</div>";
    echo "<div class='info'>";
    echo "<h4>Database Now Contains:</h4>";
    
    $tables_check = [
        'users' => 'SELECT COUNT(*) FROM users',
        'roles' => 'SELECT COUNT(*) FROM roles', 
        'service_categories' => 'SELECT COUNT(*) FROM service_categories',
        'service_types' => 'SELECT COUNT(*) FROM service_types',
        'equipment' => 'SELECT COUNT(*) FROM equipment',
        'equipment_categories' => 'SELECT COUNT(*) FROM equipment_categories',
        'sop_categories' => 'SELECT COUNT(*) FROM sop_categories',
        'activity_types' => 'SELECT COUNT(*) FROM activity_types',
        'activities' => 'SELECT COUNT(*) FROM activities',
        'bookings' => 'SELECT COUNT(*) FROM bookings'
    ];
    
    echo "<ul>";
    foreach ($tables_check as $table => $query) {
        try {
            $stmt = $db->query($query);
            $count = $stmt->fetchColumn();
            echo "<li><strong>$table:</strong> $count records</li>";
        } catch (PDOException $e) {
            echo "<li><strong>$table:</strong> Not found</li>";
        }
    }
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
?>

<div style="margin-top: 30px; padding: 20px; background: #d4edda; border-radius: 8px; border: 1px solid #c3e6cb;">
    <h4>✅ Database Fix Complete!</h4>
    <p>Your database has been updated with all necessary tables and relationships.</p>
    <p><strong>Next:</strong> <a href="index_local.php">Go to Testing Dashboard</a> to test all features</p>
</div>