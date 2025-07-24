<?php
/**
 * Integration Test Script - iLab UNMUL
 * Script untuk testing komponen sistem secara terpadu
 */

session_start();
require_once 'config/database.php';
require_once 'functions/common.php';
require_once 'classes/NotificationSystem.php';
require_once 'classes/FileUploadSecurity.php';
require_once 'classes/BookingSystem.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ILab UNMUL - System Integration Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .test-card { margin-bottom: 20px; }
        .test-result { padding: 10px; border-radius: 5px; margin-top: 10px; }
        .test-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .test-failure { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .test-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4"><i class="fas fa-vial text-primary"></i> ILab UNMUL - System Integration Test</h1>
                <p class="lead">Testing semua komponen sistem untuk memastikan integrasi yang sempurna.</p>
            </div>
        </div>

        <div class="row">
            <!-- Database Connection Test -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header">
                        <h5><i class="fas fa-database"></i> Database Connection</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $db = Database::getInstance()->getConnection();
                            echo '<div class="test-result test-success">';
                            echo '<i class="fas fa-check-circle"></i> Database connection successful';
                            echo '<br><small>Host: ' . $db->getAttribute(PDO::ATTR_CONNECTION_STATUS) . '</small>';
                            echo '</div>';
                        } catch (Exception $e) {
                            echo '<div class="test-result test-failure">';
                            echo '<i class="fas fa-times-circle"></i> Database connection failed';
                            echo '<br><small>Error: ' . $e->getMessage() . '</small>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Tables Verification -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header">
                        <h5><i class="fas fa-table"></i> Database Tables</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $required_tables = [
                                'users', 'user_roles', 'facility_bookings', 'service_categories',
                                'service_types', 'equipment', 'equipment_bookings', 'contact_messages',
                                'activities', 'file_uploads', 'download_logs', 'email_templates',
                                'email_logs', 'system_settings'
                            ];
                            
                            $stmt = $db->prepare("SHOW TABLES");
                            $stmt->execute();
                            $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            $missing_tables = array_diff($required_tables, $existing_tables);
                            
                            if (empty($missing_tables)) {
                                echo '<div class="test-result test-success">';
                                echo '<i class="fas fa-check-circle"></i> All required tables exist';
                                echo '<br><small>' . count($existing_tables) . ' tables found</small>';
                                echo '</div>';
                            } else {
                                echo '<div class="test-result test-warning">';
                                echo '<i class="fas fa-exclamation-triangle"></i> Missing tables: ';
                                echo implode(', ', $missing_tables);
                                echo '</div>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="test-result test-failure">';
                            echo '<i class="fas fa-times-circle"></i> Table verification failed';
                            echo '<br><small>Error: ' . $e->getMessage() . '</small>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- File Upload Security Test -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header">
                        <h5><i class="fas fa-shield-alt"></i> File Upload Security</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $fileSecurity = new FileUploadSecurity();
                            
                            // Test upload directory
                            $upload_dir = realpath(__DIR__ . '/../public/uploads/');
                            
                            if ($upload_dir && is_dir($upload_dir)) {
                                echo '<div class="test-result test-success">';
                                echo '<i class="fas fa-check-circle"></i> Upload directory exists and accessible';
                                echo '<br><small>Path: ' . $upload_dir . '</small>';
                                echo '</div>';
                            } else {
                                echo '<div class="test-result test-warning">';
                                echo '<i class="fas fa-exclamation-triangle"></i> Upload directory needs to be created';
                                echo '<br><small>Will be created automatically on first upload</small>';
                                echo '</div>';
                            }
                            
                            // Test .htaccess security
                            $htaccess_path = $upload_dir . '/.htaccess';
                            if (file_exists($htaccess_path)) {
                                echo '<div class="test-result test-success">';
                                echo '<i class="fas fa-check-circle"></i> Security .htaccess file exists';
                                echo '</div>';
                            }
                            
                        } catch (Exception $e) {
                            echo '<div class="test-result test-failure">';
                            echo '<i class="fas fa-times-circle"></i> File upload security test failed';
                            echo '<br><small>Error: ' . $e->getMessage() . '</small>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Email System Test -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header">
                        <h5><i class="fas fa-envelope"></i> Email System</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $notification = new NotificationSystem();
                            
                            // Test email templates
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM email_templates WHERE is_active = TRUE");
                            $stmt->execute();
                            $template_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            
                            if ($template_count > 0) {
                                echo '<div class="test-result test-success">';
                                echo '<i class="fas fa-check-circle"></i> Email templates loaded';
                                echo '<br><small>' . $template_count . ' active templates found</small>';
                                echo '</div>';
                            } else {
                                echo '<div class="test-result test-warning">';
                                echo '<i class="fas fa-exclamation-triangle"></i> No email templates found';
                                echo '<br><small>Run email_templates.sql to install templates</small>';
                                echo '</div>';
                            }
                            
                            // Test SMTP configuration
                            $smtp_enabled = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'smtp_enabled'");
                            $smtp_enabled->execute();
                            $smtp_result = $smtp_enabled->fetch(PDO::FETCH_ASSOC);
                            
                            if ($smtp_result && $smtp_result['setting_value'] === 'true') {
                                echo '<div class="test-result test-success">';
                                echo '<i class="fas fa-check-circle"></i> SMTP notifications enabled';
                                echo '</div>';
                            } else {
                                echo '<div class="test-result test-warning">';
                                echo '<i class="fas fa-exclamation-triangle"></i> SMTP notifications disabled';
                                echo '</div>';
                            }
                            
                        } catch (Exception $e) {
                            echo '<div class="test-result test-failure">';
                            echo '<i class="fas fa-times-circle"></i> Email system test failed';
                            echo '<br><small>Error: ' . $e->getMessage() . '</small>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Booking System Test -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar-check"></i> Booking System</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $booking = new BookingSystem();
                            
                            // Test service categories
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM service_categories WHERE is_active = TRUE");
                            $stmt->execute();
                            $category_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            
                            if ($category_count > 0) {
                                echo '<div class="test-result test-success">';
                                echo '<i class="fas fa-check-circle"></i> Service categories available';
                                echo '<br><small>' . $category_count . ' active categories</small>';
                                echo '</div>';
                            } else {
                                echo '<div class="test-result test-warning">';
                                echo '<i class="fas fa-exclamation-triangle"></i> No service categories found';
                                echo '</div>';
                            }
                            
                            // Test equipment availability
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM equipment WHERE status = 'available'");
                            $stmt->execute();
                            $equipment_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            
                            if ($equipment_count > 0) {
                                echo '<div class="test-result test-success">';
                                echo '<i class="fas fa-check-circle"></i> Equipment available for booking';
                                echo '<br><small>' . $equipment_count . ' available equipment</small>';
                                echo '</div>';
                            } else {
                                echo '<div class="test-result test-warning">';
                                echo '<i class="fas fa-exclamation-triangle"></i> No available equipment found';
                                echo '</div>';
                            }
                            
                        } catch (Exception $e) {
                            echo '<div class="test-result test-failure">';
                            echo '<i class="fas fa-times-circle"></i> Booking system test failed';
                            echo '<br><small>Error: ' . $e->getMessage() . '</small>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- File System Permissions -->
            <div class="col-md-6">
                <div class="card test-card">
                    <div class="card-header">
                        <h5><i class="fas fa-folder-open"></i> File System</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $directories_to_check = [
                            '../public/uploads/' => 'Upload directory'
                        ];
                        
                        foreach ($directories_to_check as $dir => $description) {
                            $full_path = realpath(__DIR__ . '/' . $dir);
                            
                            if ($full_path && is_dir($full_path)) {
                                if (is_writable($full_path)) {
                                    echo '<div class="test-result test-success">';
                                    echo '<i class="fas fa-check-circle"></i> ' . $description . ' writable';
                                    echo '</div>';
                                } else {
                                    echo '<div class="test-result test-warning">';
                                    echo '<i class="fas fa-exclamation-triangle"></i> ' . $description . ' not writable';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="test-result test-warning">';
                                echo '<i class="fas fa-exclamation-triangle"></i> ' . $description . ' does not exist';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Environment Configuration -->
            <div class="col-md-12">
                <div class="card test-card">
                    <div class="card-header">
                        <h5><i class="fas fa-cogs"></i> Environment Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Database Settings</h6>
                                <?php
                                $env_checks = [
                                    'DB_HOST' => $_ENV['DB_HOST'] ?? 'Not set',
                                    'DB_NAME' => $_ENV['DB_NAME'] ?? 'Not set',
                                    'DB_USER' => $_ENV['DB_USER'] ?? 'Not set'
                                ];
                                
                                foreach ($env_checks as $key => $value) {
                                    echo '<small>' . $key . ': <strong>' . $value . '</strong></small><br>';
                                }
                                ?>
                            </div>
                            <div class="col-md-4">
                                <h6>SMTP Settings</h6>
                                <?php
                                $smtp_checks = [
                                    'SMTP_HOST' => $_ENV['SMTP_HOST'] ?? 'Not set',
                                    'SMTP_PORT' => $_ENV['SMTP_PORT'] ?? 'Not set',
                                    'FROM_EMAIL' => $_ENV['FROM_EMAIL'] ?? 'Not set'
                                ];
                                
                                foreach ($smtp_checks as $key => $value) {
                                    echo '<small>' . $key . ': <strong>' . $value . '</strong></small><br>';
                                }
                                ?>
                            </div>
                            <div class="col-md-4">
                                <h6>Security Settings</h6>
                                <?php
                                $security_checks = [
                                    'MAX_UPLOAD_SIZE' => format_file_size($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760),
                                    'SESSION_TIMEOUT' => ($_ENV['SESSION_TIMEOUT'] ?? 3600) . ' seconds',
                                    'APP_ENV' => $_ENV['APP_ENV'] ?? 'Not set'
                                ];
                                
                                foreach ($security_checks as $key => $value) {
                                    echo '<small>' . $key . ': <strong>' . $value . '</strong></small><br>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Summary -->
            <div class="col-md-12">
                <div class="card test-card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-chart-pie"></i> System Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            // Get system statistics
                            $stats = [];
                            
                            // User count
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
                            $stmt->execute();
                            $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            
                            // Booking count
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM facility_bookings");
                            $stmt->execute();
                            $stats['bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            
                            // Equipment count
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM equipment");
                            $stmt->execute();
                            $stats['equipment'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            
                            // Activity count
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM activities");
                            $stmt->execute();
                            $stats['activities'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                            
                            echo '<div class="row text-center">';
                            echo '<div class="col-md-3">';
                            echo '<h3 class="text-primary">' . $stats['users'] . '</h3>';
                            echo '<p class="mb-0">Total Users</p>';
                            echo '</div>';
                            echo '<div class="col-md-3">';
                            echo '<h3 class="text-success">' . $stats['bookings'] . '</h3>';
                            echo '<p class="mb-0">Total Bookings</p>';
                            echo '</div>';
                            echo '<div class="col-md-3">';
                            echo '<h3 class="text-info">' . $stats['equipment'] . '</h3>';
                            echo '<p class="mb-0">Equipment Items</p>';
                            echo '</div>';
                            echo '<div class="col-md-3">';
                            echo '<h3 class="text-warning">' . $stats['activities'] . '</h3>';
                            echo '<p class="mb-0">Lab Activities</p>';
                            echo '</div>';
                            echo '</div>';
                            
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">';
                            echo '<i class="fas fa-exclamation-triangle"></i> Error loading system statistics: ' . $e->getMessage();
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Installation Notes</h5>
                    <ul class="mb-0">
                        <li>Pastikan file <code>.env</code> sudah dikonfigurasi dengan benar</li>
                        <li>Run script SQL untuk membuat tabel yang diperlukan</li>
                        <li>Set permission folder uploads (chmod 755 atau 777)</li>
                        <li>Konfigurasi SMTP untuk email notifications</li>
                        <li>Test file upload functionality dengan file sample</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>