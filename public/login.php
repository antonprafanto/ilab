<?php
/**
 * Login Page - Website Integrated Laboratory UNMUL
 * Role-based authentication untuk 8 jenis stakeholder
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/User.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('/dashboard.php');
}

$user = new User();
$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Token keamanan tidak valid';
    } else {
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);
        
        if (empty($username) || empty($password)) {
            $error_message = 'Username dan password harus diisi';
        } else {
            $result = $user->login($username, $password, $remember_me);
            
            if ($result['success']) {
                redirect($result['redirect_url']);
            } else {
                $error_message = $result['error'];
            }
        }
    }
}

// Handle logout message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = 'Anda telah berhasil logout';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Login ke sistem Integrated Laboratory UNMUL untuk akses fasilitas penelitian dan pengujian">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }
        
        .login-left {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .login-right {
            padding: 3rem;
        }
        
        .stakeholder-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }
        
        .stakeholder-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stakeholder-list i {
            margin-right: 0.5rem;
            width: 20px;
        }
        
        .form-floating .form-control {
            border-radius: 10px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
        
        .institutional-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="login-card">
                <div class="row g-0">
                    <!-- Left Side - Information -->
                    <div class="col-lg-6 login-left">
                        <div>
                            <div class="mb-4">
                                <img src="images/logo-unmul-white.png" alt="UNMUL" height="60" class="mb-3">
                                <h2 class="fw-bold">Integrated Laboratory UNMUL</h2>
                                <p class="lead">Pusat Penelitian dan Pengujian Terkemuka di Kalimantan Timur</p>
                            </div>
                            
                            <div class="stakeholder-access">
                                <h5 class="mb-3">Akses untuk Stakeholder:</h5>
                                <ul class="stakeholder-list">
                                    <li><i class="fas fa-graduation-cap"></i> Mahasiswa & Dosen</li>
                                    <li><i class="fas fa-flask"></i> Peneliti Internal</li>
                                    <li><i class="fas fa-industry"></i> Industri & Perusahaan</li>
                                    <li><i class="fas fa-building"></i> Instansi Pemerintah</li>
                                    <li><i class="fas fa-users"></i> Masyarakat & UMKM</li>
                                </ul>
                            </div>
                            
                            <div class="institutional-info">
                                <h6>Informasi Kontak</h6>
                                <p class="mb-1"><i class="fas fa-map-marker-alt"></i> Samarinda, Kalimantan Timur</p>
                                <p class="mb-1"><i class="fas fa-phone"></i> <?= INSTITUTION_PHONE ?></p>
                                <p class="mb-0"><i class="fas fa-envelope"></i> <?= INSTITUTION_EMAIL ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Login Form -->
                    <div class="col-lg-6 login-right">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-primary">Login ke Sistem</h3>
                            <p class="text-muted">Masuk untuk mengakses layanan ILab UNMUL</p>
                        </div>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= htmlspecialchars($success_message) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="login.php" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Username atau Email" required 
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                                <label for="username"><i class="fas fa-user me-2"></i>Username atau Email</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Password" required>
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Ingat saya selama 30 hari
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <div class="mb-3">
                                <a href="forgot-password.php" class="text-decoration-none">
                                    <i class="fas fa-key me-1"></i>Lupa Password?
                                </a>
                            </div>
                            
                            <hr>
                            
                            <p class="mb-0">Belum memiliki akun?</p>
                            <a href="register.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                            </a>
                        </div>
                        
                        <div class="mt-4 pt-4 border-top">
                            <div class="text-center text-muted">
                                <small>
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Sistem keamanan terlindungi<br>
                                    Support: <a href="mailto:<?= ADMIN_EMAIL ?>"><?= ADMIN_EMAIL ?></a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Access Information -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="text-center text-white">
                        <h5 class="mb-3">Layanan ILab UNMUL</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="service-quick">
                                    <i class="fas fa-flask fa-2x mb-2"></i>
                                    <h6>Penelitian & Pengujian</h6>
                                    <small>Saintek, Kedokteran, Sosial</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="service-quick">
                                    <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                    <h6>Pelatihan & Magang</h6>
                                    <small>Teknis & Metodologi</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="service-quick">
                                    <i class="fas fa-certificate fa-2x mb-2"></i>
                                    <h6>Kalibrasi KAN</h6>
                                    <small>Terakreditasi Nasional</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="service-quick">
                                    <i class="fas fa-calendar fa-2x mb-2"></i>
                                    <h6>Booking Online</h6>
                                    <small>Fasilitas Modern</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;
                
                if (!username || !password) {
                    e.preventDefault();
                    showAlert('Username dan password harus diisi', 'danger');
                    return;
                }
                
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
                submitBtn.disabled = true;
            });
            
            // Auto-focus on username field
            document.getElementById('username').focus();
            
            // Show/hide password
            const passwordField = document.getElementById('password');
            const showPasswordBtn = document.createElement('button');
            showPasswordBtn.type = 'button';
            showPasswordBtn.className = 'btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-3';
            showPasswordBtn.innerHTML = '<i class="fas fa-eye"></i>';
            showPasswordBtn.style.border = 'none';
            showPasswordBtn.style.background = 'transparent';
            
            passwordField.parentNode.style.position = 'relative';
            passwordField.parentNode.appendChild(showPasswordBtn);
            
            showPasswordBtn.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    passwordField.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        });
        
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const form = document.querySelector('form');
            form.parentNode.insertBefore(alertDiv, form);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>