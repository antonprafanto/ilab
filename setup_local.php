<?php
/**
 * Local Setup Script untuk ILab UNMUL
 * Script untuk setup database dan sample data di local environment
 */

// Local database configuration
$local_config = [
    'host' => 'localhost',
    'dbname' => 'ilab_local',
    'username' => 'root',
    'password' => ''  // Default XAMPP password
];

echo "<h1>🚀 ILab UNMUL - Local Setup</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .step { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
</style>";

try {
    // Step 1: Test MySQL connection
    echo "<div class='step'>";
    echo "<h3>Step 1: Testing MySQL Connection</h3>";
    
    $pdo = new PDO(
        "mysql:host={$local_config['host']}", 
        $local_config['username'], 
        $local_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✓ MySQL connection successful</div>";
    
    // Step 2: Create database
    echo "<h3>Step 2: Creating Database</h3>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$local_config['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<div class='success'>✓ Database 'ilab_local' created</div>";
    
    // Step 3: Use database
    $pdo->exec("USE {$local_config['dbname']}");
    echo "<div class='success'>✓ Using database 'ilab_local'</div>";
    
    // Step 4: Create basic tables for testing
    echo "<h3>Step 3: Creating Basic Tables</h3>";
    
    // Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role_name ENUM('fakultas', 'mahasiswa', 'peneliti_internal', 'staf_ilab', 'industri', 'pemerintah', 'masyarakat', 'umkm') NOT NULL DEFAULT 'mahasiswa',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Users table created</div>";
    
    // Service categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS service_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL,
            description TEXT,
            fields JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Service categories table created</div>";
    
    // Equipment table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS equipment (
            id INT AUTO_INCREMENT PRIMARY KEY,
            equipment_name VARCHAR(100) NOT NULL,
            equipment_code VARCHAR(50) UNIQUE NOT NULL,
            category_id INT,
            brand VARCHAR(50),
            model VARCHAR(50),
            status ENUM('available', 'in_use', 'maintenance', 'out_of_order') DEFAULT 'available',
            location VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Equipment table created</div>";
    
    // Activities table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            start_date DATE NOT NULL,
            end_date DATE,
            start_time TIME,
            location VARCHAR(100),
            is_featured BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✓ Activities table created</div>";
    
    // Step 5: Insert sample data
    echo "<h3>Step 4: Inserting Sample Data</h3>";
    
    // Sample admin user
    $password_hash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (username, email, password, full_name, role_name) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute(['admin', 'admin@ilab.local', $password_hash, 'Administrator', 'staf_ilab']);
    echo "<div class='success'>✓ Admin user created (username: admin, password: password)</div>";
    
    // Sample service categories
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO service_categories (id, category_name, description, fields) 
        VALUES (?, ?, ?, ?)
    ");
    $categories = [
        [1, 'Laboratorium Saintek', 'Layanan analisis kimia dan material', '["Analisis Kimia", "Material Testing", "Environmental Analysis"]'],
        [2, 'Laboratorium Kedokteran', 'Layanan diagnostik medis', '["Clinical Chemistry", "Hematology", "Microbiology"]'],
        [3, 'Laboratorium Sosial Humaniora', 'Penelitian sosial dan budaya', '["Survey Research", "Data Analysis", "Community Studies"]'],
        [4, 'Kalibrasi Peralatan', 'Layanan kalibrasi terakreditasi KAN', '["Instrument Calibration", "Measurement Standards", "Quality Assurance"]']
    ];
    
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "<div class='success'>✓ Service categories inserted (4 categories)</div>";
    
    // Sample equipment
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO equipment (equipment_name, equipment_code, category_id, brand, model, status, location) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $equipment = [
        ['GC-MS System', 'GC-MS-001', 1, 'Agilent', '7890B/5977B', 'available', 'Lab Saintek Room A101'],
        ['HPLC System', 'HPLC-001', 1, 'Shimadzu', 'Nexera X2', 'available', 'Lab Saintek Room A102'],
        ['FTIR Spectrometer', 'FTIR-001', 1, 'PerkinElmer', 'Spectrum Two', 'available', 'Lab Saintek Room A103'],
        ['Chemistry Analyzer', 'CA-001', 2, 'Beckman Coulter', 'AU5800', 'available', 'Lab Kedokteran Room C301'],
        ['Universal Testing Machine', 'UTM-001', 1, 'Instron', '5984', 'available', 'Material Testing Lab'],
    ];
    
    foreach ($equipment as $eq) {
        $stmt->execute($eq);
    }
    echo "<div class='success'>✓ Sample equipment inserted (5 items)</div>";
    
    // Sample activities
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO activities (title, description, start_date, start_time, location, is_featured) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $activities = [
        ['Workshop GC-MS Advanced Techniques', 'Pelatihan teknik analisis menggunakan GC-MS', '2024-08-15', '08:00:00', 'Lab Saintek ILab UNMUL', 1],
        ['Seminar Laboratorium Digital', 'Seminar implementasi teknologi digital di laboratorium', '2024-09-10', '08:30:00', 'Auditorium Unmul', 1],
        ['Training ISO 17025', 'Pelatihan sistem manajemen mutu laboratorium', '2024-10-05', '08:00:00', 'Meeting Room ILab', 1]
    ];
    
    foreach ($activities as $activity) {
        $stmt->execute($activity);
    }
    echo "<div class='success'>✓ Sample activities inserted (3 activities)</div>";
    
    echo "</div>";
    
    // Step 6: Success message
    echo "<div class='step'>";
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<div class='success'>Local database setup successful!</div>";
    echo "<div class='info'>";
    echo "<h4>Access Your Local Website:</h4>";
    echo "<ul>";
    echo "<li><strong>Homepage:</strong> <a href='public/index.php'>http://localhost/ilab/public/</a></li>";
    echo "<li><strong>About:</strong> <a href='public/about.php'>http://localhost/ilab/public/about.php</a></li>";
    echo "<li><strong>Services:</strong> <a href='public/services.php'>http://localhost/ilab/public/services.php</a></li>";
    echo "<li><strong>Organization:</strong> <a href='public/organization.php'>http://localhost/ilab/public/organization.php</a></li>";
    echo "<li><strong>Admin Panel:</strong> <a href='admin/dashboard/'>http://localhost/ilab/admin/dashboard/</a></li>";
    echo "</ul>";
    echo "<h4>Admin Login:</h4>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> admin</li>";
    echo "<li><strong>Password:</strong> password</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
    // Update local config file
    echo "<div class='step'>";
    echo "<h3>Step 5: Updating Configuration</h3>";
    
    $config_content = "<?php
// LOCAL DATABASE CONFIGURATION (Auto-generated)
define('DB_HOST', 'localhost');
define('DB_NAME', 'ilab_local');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Development environment settings
define('ENVIRONMENT', 'development');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Other configurations...
define('SITE_NAME', 'Integrated Laboratory UNMUL - Local');
define('SITE_URL', 'http://localhost/ilab');
?>";
    
    if (file_put_contents('includes/config/database_local.php', $config_content)) {
        echo "<div class='success'>✓ Local configuration file created</div>";
        echo "<div class='info'>Note: Update includes/config/database.php to use local settings</div>";
    }
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>";
    echo "<h4>Troubleshooting:</h4>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Check if port 3306 is available</li>";
    echo "<li>Verify MySQL username/password (default: root with empty password)</li>";
    echo "</ul>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}
?>