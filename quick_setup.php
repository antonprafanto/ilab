<?php
/**
 * Quick Setup Script - ILab UNMUL Local Testing
 * Automated setup untuk testing environment
 */

// Set execution time limit
set_time_limit(300);

$setup_steps = [];
$errors = [];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Setup - ILab UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .step-success { color: #28a745; }
        .step-error { color: #dc3545; }
        .step-warning { color: #ffc107; }
        .log-output { background: #f8f9fa; padding: 1rem; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>ILab UNMUL - Quick Setup
                        </h3>
                        <p class="mb-0">Automated setup untuk local testing environment</p>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Memulai setup process... Mohon tunggu.
                            </div>
                            
                            <?php
                            // Step 1: Check PHP Requirements
                            echo "<h5><i class='fas fa-check-circle step-success'></i> Step 1: PHP Requirements Check</h5>";
                            
                            $php_version = phpversion();
                            if (version_compare($php_version, '7.4.0', '>=')) {
                                echo "<p class='step-success'>✅ PHP Version: $php_version (OK)</p>";
                            } else {
                                echo "<p class='step-error'>❌ PHP Version: $php_version (Requires 7.4+)</p>";
                                $errors[] = "PHP version too old";
                            }
                            
                            $required_extensions = ['mysqli', 'pdo', 'mbstring', 'json', 'session'];
                            foreach ($required_extensions as $ext) {
                                if (extension_loaded($ext)) {
                                    echo "<p class='step-success'>✅ Extension $ext: Loaded</p>";
                                } else {
                                    echo "<p class='step-error'>❌ Extension $ext: Not loaded</p>";
                                    $errors[] = "Missing extension: $ext";
                                }
                            }
                            
                            // Step 2: Database Connection Test
                            echo "<hr><h5><i class='fas fa-database'></i> Step 2: Database Connection</h5>";
                            
                            $db_config = [
                                'host' => $_POST['db_host'] ?? 'localhost',
                                'name' => $_POST['db_name'] ?? 'ilab',
                                'user' => $_POST['db_user'] ?? 'root',
                                'pass' => $_POST['db_pass'] ?? ''
                            ];
                            
                            try {
                                $dsn = "mysql:host={$db_config['host']};charset=utf8mb4";
                                $pdo = new PDO($dsn, $db_config['user'], $db_config['pass']);
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                echo "<p class='step-success'>✅ MySQL Connection: Success</p>";
                                
                                // Create database if not exists
                                $pdo->exec("CREATE DATABASE IF NOT EXISTS {$db_config['name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                                echo "<p class='step-success'>✅ Database '{$db_config['name']}': Created/Verified</p>";
                                
                                // Switch to database
                                $pdo->exec("USE {$db_config['name']}");
                                
                            } catch (PDOException $e) {
                                echo "<p class='step-error'>❌ Database Error: " . $e->getMessage() . "</p>";
                                $errors[] = "Database connection failed";
                            }
                            
                            // Step 3: Import Database Schema
                            if (empty($errors)) {
                                echo "<hr><h5><i class='fas fa-table'></i> Step 3: Database Schema Import</h5>";
                                
                                $sql_files = [
                                    'database_schema.sql' => 'Main Database Schema',
                                    'includes/email_templates.sql' => 'Email Templates'
                                ];
                                
                                foreach ($sql_files as $file => $description) {
                                    if (file_exists($file)) {
                                        try {
                                            $sql = file_get_contents($file);
                                            $statements = explode(';', $sql);
                                            
                                            foreach ($statements as $statement) {
                                                $statement = trim($statement);
                                                if (!empty($statement)) {
                                                    $pdo->exec($statement);
                                                }
                                            }
                                            
                                            echo "<p class='step-success'>✅ $description: Imported</p>";
                                        } catch (PDOException $e) {
                                            echo "<p class='step-warning'>⚠️ $description: " . $e->getMessage() . "</p>";
                                        }
                                    } else {
                                        echo "<p class='step-warning'>⚠️ $description: File not found</p>";
                                    }
                                }
                            }
                            
                            // Step 4: Create Admin User
                            if (empty($errors)) {
                                echo "<hr><h5><i class='fas fa-user-shield'></i> Step 4: Admin User Setup</h5>";
                                
                                try {
                                    // Check if admin exists
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
                                    $stmt->execute();
                                    
                                    if ($stmt->fetchColumn() == 0) {
                                        // Get staf_ilab role ID
                                        $stmt = $pdo->prepare("SELECT id FROM user_roles WHERE role_name = 'staf_ilab'");
                                        $stmt->execute();
                                        $role_id = $stmt->fetchColumn();
                                        
                                        if ($role_id) {
                                            // Create admin user
                                            $admin_password = password_hash('password', PASSWORD_DEFAULT);
                                            $stmt = $pdo->prepare("
                                                INSERT INTO users (username, email, password, name, role_id, is_active, created_at) 
                                                VALUES ('admin', 'admin@ilab.local', ?, 'System Administrator', ?, 1, NOW())
                                            ");
                                            $stmt->execute([$admin_password, $role_id]);
                                            
                                            echo "<p class='step-success'>✅ Admin User Created</p>";
                                            echo "<div class='alert alert-warning'>";
                                            echo "<strong>Admin Login Credentials:</strong><br>";
                                            echo "Username: <code>admin</code><br>";
                                            echo "Password: <code>password</code>";
                                            echo "</div>";
                                        } else {
                                            echo "<p class='step-error'>❌ Admin Role Not Found</p>";
                                        }
                                    } else {
                                        echo "<p class='step-success'>✅ Admin User Already Exists</p>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<p class='step-error'>❌ Admin Setup Error: " . $e->getMessage() . "</p>";
                                }
                            }
                            
                            // Step 5: File Permissions
                            echo "<hr><h5><i class='fas fa-folder-open'></i> Step 5: File Permissions</h5>";
                            
                            $directories = [
                                'public/uploads' => 'Upload Directory',
                                'logs' => 'Log Directory'
                            ];
                            
                            foreach ($directories as $dir => $description) {
                                if (!is_dir($dir)) {
                                    if (mkdir($dir, 0755, true)) {
                                        echo "<p class='step-success'>✅ $description: Created</p>";
                                    } else {
                                        echo "<p class='step-error'>❌ $description: Failed to create</p>";
                                    }
                                } else {
                                    echo "<p class='step-success'>✅ $description: Exists</p>";
                                }
                                
                                if (is_writable($dir)) {
                                    echo "<p class='step-success'>✅ $description: Writable</p>";
                                } else {
                                    echo "<p class='step-warning'>⚠️ $description: Not writable (may need manual chmod)</p>";
                                }
                            }
                            
                            // Step 6: Environment File
                            echo "<hr><h5><i class='fas fa-file-code'></i> Step 6: Environment Configuration</h5>";
                            
                            $env_content = "# ILab UNMUL Local Configuration\n";
                            $env_content .= "DB_HOST={$db_config['host']}\n";
                            $env_content .= "DB_NAME={$db_config['name']}\n";
                            $env_content .= "DB_USER={$db_config['user']}\n";
                            $env_content .= "DB_PASS={$db_config['pass']}\n\n";
                            $env_content .= "SITE_URL=http://localhost" . dirname($_SERVER['REQUEST_URI']) . "\n";
                            $env_content .= "SITE_NAME=\"Integrated Laboratory UNMUL\"\n\n";
                            $env_content .= "# Development Settings\n";
                            $env_content .= "APP_DEBUG=true\n";
                            $env_content .= "APP_ENV=development\n";
                            
                            if (file_put_contents('.env', $env_content)) {
                                echo "<p class='step-success'>✅ Environment File: Created</p>";
                            } else {
                                echo "<p class='step-error'>❌ Environment File: Failed to create</p>";
                            }
                            
                            // Final Status
                            echo "<hr><h4><i class='fas fa-flag-checkered'></i> Setup Complete!</h4>";
                            
                            if (empty($errors)) {
                                echo "<div class='alert alert-success'>";
                                echo "<h5>🎉 Setup Successful!</h5>";
                                echo "<p>Sistem ILab UNMUL berhasil di-setup untuk local testing.</p>";
                                echo "<p><strong>Next Steps:</strong></p>";
                                echo "<ol>";
                                echo "<li><a href='includes/integration_test.php' target='_blank'>Run Integration Test</a></li>";
                                echo "<li><a href='public/index.php' target='_blank'>Visit Homepage</a></li>";
                                echo "<li><a href='public/login.php' target='_blank'>Login as Admin</a> (admin/password)</li>";
                                echo "<li><a href='LOCAL_TESTING_GUIDE.md' target='_blank'>Follow Testing Guide</a></li>";
                                echo "</ol>";
                                echo "</div>";
                            } else {
                                echo "<div class='alert alert-danger'>";
                                echo "<h5>❌ Setup Issues Found</h5>";
                                echo "<ul>";
                                foreach ($errors as $error) {
                                    echo "<li>$error</li>";
                                }
                                echo "</ul>";
                                echo "<p>Please fix these issues and run setup again.</p>";
                                echo "</div>";
                            }
                            ?>
                            
                        <?php else: ?>
                            
                            <!-- Setup Form -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Script ini akan melakukan automated setup untuk testing ILab UNMUL di local environment.
                            </div>
                            
                            <form method="POST">
                                <h5><i class="fas fa-database me-2"></i>Database Configuration</h5>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="db_host" class="form-label">Database Host</label>
                                        <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="db_name" class="form-label">Database Name</label>
                                        <input type="text" class="form-control" id="db_name" name="db_name" value="ilab" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="db_user" class="form-label">Database User</label>
                                        <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="db_pass" class="form-label">Database Password</label>
                                        <input type="password" class="form-control" id="db_pass" name="db_pass" placeholder="(kosongkan jika tidak ada password)">
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Setup Process Will:</h6>
                                    <ul class="mb-0">
                                        <li>Check PHP requirements dan extensions</li>
                                        <li>Test database connection</li>
                                        <li>Create database dan import schema</li>
                                        <li>Create admin user (admin/password)</li>
                                        <li>Setup file permissions</li>
                                        <li>Generate .env configuration file</li>
                                    </ul>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-play me-2"></i>Start Quick Setup
                                    </button>
                                </div>
                            </form>
                            
                        <?php endif; ?>
                        
                    </div>
                    <div class="card-footer bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Estimated setup time: 2-3 minutes
                                </small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <i class="fas fa-code me-1"></i>
                                    ILab UNMUL v1.0
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Resources -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                <h6>Testing Guide</h6>
                                <p class="small text-muted">Comprehensive testing documentation</p>
                                <a href="LOCAL_TESTING_GUIDE.md" class="btn btn-sm btn-outline-primary">View Guide</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-vial fa-2x text-success mb-2"></i>
                                <h6>Integration Test</h6>
                                <p class="small text-muted">System verification tool</p>
                                <a href="includes/integration_test.php" class="btn btn-sm btn-outline-success">Run Test</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-rocket fa-2x text-warning mb-2"></i>
                                <h6>Deployment Guide</h6>
                                <p class="small text-muted">Production deployment steps</p>
                                <a href="DEPLOYMENT.md" class="btn btn-sm btn-outline-warning">View Guide</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>