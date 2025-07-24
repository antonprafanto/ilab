<?php
/**
 * Database Configuration untuk ILab UNMUL
 * Auto-switch between local and production
 */

// Detect environment
$is_local = (
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
    strpos($_SERVER['HTTP_HOST'], '.local') !== false
);

if ($is_local && file_exists(__DIR__ . '/database_local.php')) {
    // Use local configuration
    require_once __DIR__ . '/database_local.php';
    return;
}

// Production Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ilab');
define('DB_USER', 'ilab');
define('DB_PASS', 'yG2cSqEwGWIKumX');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// PDO connection
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
            error_log("Database connection failed: " . $e->getMessage());
            die("Connection failed. Please check database configuration.");
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
    
    // Prevent cloning and serialization
    private function __clone() {}
    public function __wakeup() {}
}

// Site configuration
define('SITE_NAME', 'Integrated Laboratory UNMUL');
define('SITE_URL', 'https://ilab.unmul.ac.id');
define('SITE_EMAIL', 'ict@unmul.ac.id');
define('ADMIN_EMAIL', 'admin@ilab.unmul.ac.id');

// Institution details
define('INSTITUTION_NAME', 'UNIVERSITAS MULAWARMAN');
define('INSTITUTION_UNIT', 'UPT. TEKNOLOGI INFORMASI DAN KOMUNIKASI');
define('INSTITUTION_ADDRESS', 'Jl. Sambaliung Gedung Unmul HUB Lantai 2, Kampus Gunung Kelua Universitas Mulawarman, Samarinda, Kalimantan Timur');
define('INSTITUTION_PHONE', '+62 541 735055 - 738327');
define('INSTITUTION_EMAIL', 'ict@unmul.ac.id');
define('INSTITUTION_WEBSITE', 'https://ict.unmul.ac.id');

// File upload settings
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('UPLOAD_PATH', __DIR__ . '/../../public/uploads/');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// SFTP Configuration (dari dokumen)
define('SFTP_HOST', '192.168.33.240');
define('SFTP_PORT', 22);
define('SFTP_USERNAME', 'ilab');
define('SFTP_PASSWORD', 'yG2cSqEwGWIKumX');

// Email configuration
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@ilab.unmul.ac.id');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// Timezone
date_default_timezone_set('Asia/Makassar');

// Error reporting
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>