<?php
/**
 * Registration Page - Website Integrated Laboratory UNMUL
 * Pendaftaran untuk 8 jenis stakeholder berdasarkan role types
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
$error_messages = [];
$success_message = '';

// Get available roles
$user_roles = $user->getUserRoles();

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_messages[] = 'Token keamanan tidak valid';
    } else {
        $registration_data = [
            'username' => sanitize_input($_POST['username'] ?? ''),
            'email' => sanitize_input($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'full_name' => sanitize_input($_POST['full_name'] ?? ''),
            'role_id' => intval($_POST['role_id'] ?? 0),
            'institution' => sanitize_input($_POST['institution'] ?? ''),
            'phone' => sanitize_input($_POST['phone'] ?? ''),
            'address' => sanitize_input($_POST['address'] ?? '')
        ];
        
        // Password confirmation check
        if ($registration_data['password'] !== $registration_data['confirm_password']) {
            $error_messages[] = 'Konfirmasi password tidak cocok';
        }
        
        if (empty($error_messages)) {
            $result = $user->register($registration_data);
            
            if ($result['success']) {
                $success_message = $result['message'];
                // Clear form data
                $_POST = [];
            } else {
                $error_messages = $result['errors'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Integrated Laboratory UNMUL</title>
    <meta name="description" content="Daftar akun untuk mengakses layanan Integrated Laboratory UNMUL - Penelitian, Pengujian, dan Pelatihan">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .register-container {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1200px;
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .role-card {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .role-card:hover {
            border-color: var(--primary-color);
            background: #f8fafc;
        }
        
        .role-card.selected {
            border-color: var(--primary-color);
            background: #eff6ff;
        }
        
        .role-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .internal-role .role-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .external-role .role-icon {
            background: linear-gradient(135deg, var(--accent-color), #f97316);
            color: white;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            font-weight: 600;
        }
        
        .step.active {
            background: var(--primary-color);
            color: white;
        }
        
        .step.completed {
            background: var(--secondary-color);
            color: white;
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="container">
            <div class="register-card mx-auto">
                <!-- Header -->
                <div class="register-header">
                    <img src="images/logo-unmul-white.png" alt="UNMUL" height="60" class="mb-3">
                    <h2 class="fw-bold mb-2">Daftar Akun ILab UNMUL</h2>
                    <p class="mb-0 lead">Bergabunglah dengan komunitas peneliti dan inovator di Kalimantan Timur</p>
                </div>
                
                <div class="p-4">
                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step active" id="step1">1</div>
                        <div class="step" id="step2">2</div>
                        <div class="step" id="step3">3</div>
                    </div>
                    
                    <?php if (!empty($error_messages)): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Terdapat kesalahan:</h6>
                            <ul class="mb-0">
                                <?php foreach ($error_messages as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success_message) ?>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-success">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="register.php" id="registrationForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="role_id" id="selectedRoleId" value="<?= $_POST['role_id'] ?? '' ?>">
                            
                            <!-- Step 1: Choose Role -->
                            <div class="form-section active" id="section1">
                                <h4 class="mb-4 text-center">Pilih Kategori Anda</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-university me-2"></i>Stakeholder Internal
                                        </h5>
                                        <?php foreach ($user_roles as $role): ?>
                                            <?php if ($role['role_type'] === 'internal'): ?>
                                                <div class="role-card internal-role" data-role-id="<?= $role['id'] ?>">
                                                    <div class="d-flex align-items-center">
                                                        <div class="role-icon">
                                                            <i class="fas fa-<?= getRoleIcon($role['role_name']) ?>"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars(ucfirst($role['role_name'])) ?></h6>
                                                            <small class="text-muted"><?= htmlspecialchars($role['description']) ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5 class="text-warning mb-3">
                                            <i class="fas fa-handshake me-2"></i>Stakeholder Eksternal
                                        </h5>
                                        <?php foreach ($user_roles as $role): ?>
                                            <?php if ($role['role_type'] === 'external'): ?>
                                                <div class="role-card external-role" data-role-id="<?= $role['id'] ?>">
                                                    <div class="d-flex align-items-center">
                                                        <div class="role-icon">
                                                            <i class="fas fa-<?= getRoleIcon($role['role_name']) ?>"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars(ucfirst($role['role_name'])) ?></h6>
                                                            <small class="text-muted"><?= htmlspecialchars($role['description']) ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-primary" id="nextStep1" disabled>
                                        Lanjutkan <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Account Information -->
                            <div class="form-section" id="section2">
                                <h4 class="mb-4 text-center">Informasi Akun</h4>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   placeholder="Username" required 
                                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                                            <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   placeholder="Email" required 
                                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                            <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Password" required>
                                            <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                        </div>
                                        <small class="text-muted">Minimal <?= PASSWORD_MIN_LENGTH ?> karakter</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   placeholder="Konfirmasi Password" required>
                                            <label for="confirm_password"><i class="fas fa-lock me-2"></i>Konfirmasi Password</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-outline-secondary me-2" id="prevStep2">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali
                                    </button>
                                    <button type="button" class="btn btn-primary" id="nextStep2">
                                        Lanjutkan <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Personal Information -->
                            <div class="form-section" id="section3">
                                <h4 class="mb-4 text-center">Informasi Pribadi</h4>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                                   placeholder="Nama Lengkap" required 
                                                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                                            <label for="full_name"><i class="fas fa-id-card me-2"></i>Nama Lengkap</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="institution" name="institution" 
                                                   placeholder="Institusi/Perusahaan" 
                                                   value="<?= htmlspecialchars($_POST['institution'] ?? '') ?>">
                                            <label for="institution"><i class="fas fa-building me-2"></i>Institusi/Perusahaan</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   placeholder="Nomor Telepon" 
                                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                            <label for="phone"><i class="fas fa-phone me-2"></i>Nomor Telepon</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="address" name="address" 
                                                      placeholder="Alamat" style="height: 100px"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                            <label for="address"><i class="fas fa-map-marker-alt me-2"></i>Alamat</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" id="terms_agreement" required>
                                    <label class="form-check-label" for="terms_agreement">
                                        Saya menyetujui <a href="terms.php" target="_blank">syarat dan ketentuan</a> 
                                        serta <a href="privacy.php" target="_blank">kebijakan privasi</a> ILab UNMUL
                                    </label>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-outline-secondary me-2" id="prevStep3">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <!-- Login Link -->
                    <div class="text-center mt-4 pt-4 border-top">
                        <p class="mb-0">Sudah memiliki akun?</p>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const roleCards = document.querySelectorAll('.role-card');
            const sections = document.querySelectorAll('.form-section');
            const steps = document.querySelectorAll('.step');
            let currentStep = 1;
            
            // Role selection
            roleCards.forEach(card => {
                card.addEventListener('click', function() {
                    roleCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    const roleId = this.dataset.roleId;
                    document.getElementById('selectedRoleId').value = roleId;
                    document.getElementById('nextStep1').disabled = false;
                });
            });
            
            // Step navigation
            document.getElementById('nextStep1').addEventListener('click', () => goToStep(2));
            document.getElementById('nextStep2').addEventListener('click', () => {
                if (validateStep2()) goToStep(3);
            });
            document.getElementById('prevStep2').addEventListener('click', () => goToStep(1));
            document.getElementById('prevStep3').addEventListener('click', () => goToStep(2));
            
            function goToStep(step) {
                sections[currentStep - 1].classList.remove('active');
                steps[currentStep - 1].classList.remove('active');
                steps[currentStep - 1].classList.add('completed');
                
                currentStep = step;
                
                sections[currentStep - 1].classList.add('active');
                steps[currentStep - 1].classList.add('active');
            }
            
            function validateStep2() {
                const username = document.getElementById('username').value.trim();
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (!username || !email || !password || !confirmPassword) {
                    showAlert('Semua field harus diisi', 'warning');
                    return false;
                }
                
                if (password !== confirmPassword) {
                    showAlert('Konfirmasi password tidak cocok', 'warning');
                    return false;
                }
                
                if (password.length < <?= PASSWORD_MIN_LENGTH ?>) {
                    showAlert('Password minimal <?= PASSWORD_MIN_LENGTH ?> karakter', 'warning');
                    return false;
                }
                
                return true;
            }
            
            // Form submission
            form.addEventListener('submit', function(e) {
                const fullName = document.getElementById('full_name').value.trim();
                const termsAgreed = document.getElementById('terms_agreement').checked;
                
                if (!fullName) {
                    e.preventDefault();
                    showAlert('Nama lengkap harus diisi', 'warning');
                    return;
                }
                
                if (!termsAgreed) {
                    e.preventDefault();
                    showAlert('Anda harus menyetujui syarat dan ketentuan', 'warning');
                    return;
                }
                
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
                submitBtn.disabled = true;
            });
            
            // Pre-select role if coming back from form submission
            const selectedRoleId = document.getElementById('selectedRoleId').value;
            if (selectedRoleId) {
                const selectedCard = document.querySelector(`[data-role-id="${selectedRoleId}"]`);
                if (selectedCard) {
                    selectedCard.classList.add('selected');
                    document.getElementById('nextStep1').disabled = false;
                }
            }
        });
        
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.register-card .p-4');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>

<?php
function getRoleIcon($role_name) {
    $icons = [
        'fakultas' => 'chalkboard-teacher',
        'mahasiswa' => 'graduation-cap',
        'peneliti_internal' => 'flask',
        'staf_ilab' => 'user-cog',
        'industri' => 'industry',
        'pemerintah' => 'building',
        'masyarakat' => 'users',
        'umkm' => 'store'
    ];
    
    return $icons[$role_name] ?? 'user';
}
?>