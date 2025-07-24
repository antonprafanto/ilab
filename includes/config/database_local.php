<?php
/**
 * LOCAL Database Configuration untuk ILab UNMUL Testing
 * Configuration khusus untuk local development
 */

// Local Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ilab_local');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty for default XAMPP
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// PDO connection for local
if (!class_exists('Database')) {
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // For local development, show detailed error
            die("LOCAL DATABASE CONNECTION FAILED: " . $e->getMessage() . 
                "<br><br>Troubleshooting:<br>" .
                "1. Make sure XAMPP MySQL is running<br>" .
                "2. Create database 'ilab_local' in phpMyAdmin<br>" .
                "3. Check MySQL port is 3306<br>" .
                "4. Verify username 'root' with empty password");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function __clone() {}
    public function __wakeup() {}
}
}

// Local Site configuration
define('SITE_NAME', 'ILab UNMUL - Local Testing');
define('SITE_URL', 'http://localhost/ilab');
define('SITE_EMAIL', 'admin@localhost');
define('ADMIN_EMAIL', 'admin@localhost');

// Institution details (for local)
define('INSTITUTION_NAME', 'UNIVERSITAS MULAWARMAN');
define('INSTITUTION_UNIT', 'UPT. TEKNOLOGI INFORMASI DAN KOMUNIKASI');
define('INSTITUTION_ADDRESS', 'Jl. Sambaliung Gedung Unmul HUB Lantai 2, Kampus Gunung Kelua Universitas Mulawarman, Samarinda, Kalimantan Timur');
define('INSTITUTION_PHONE', '+62 541 735055 - 738327');
define('INSTITUTION_EMAIL', 'ict@unmul.ac.id');
define('INSTITUTION_WEBSITE', 'https://ict.unmul.ac.id');

// Local development settings
define('ENVIRONMENT', 'development');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// File upload settings (local)
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/../../public/uploads/');

// Security settings (relaxed for local)
define('SESSION_TIMEOUT', 7200); // 2 hours
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// SFTP Configuration (dummy for local)
define('SFTP_HOST', 'localhost');
define('SFTP_PORT', 22);
define('SFTP_USERNAME', 'dummy');
define('SFTP_PASSWORD', 'dummy');

// Email configuration (disabled for local testing)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@localhost');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// Timezone
date_default_timezone_set('Asia/Makassar');

echo "<!-- ILab UNMUL Local Configuration Loaded -->";
?>