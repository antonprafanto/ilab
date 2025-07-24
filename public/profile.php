<?php
/**
 * User Profile - Website Integrated Laboratory UNMUL
 * Halaman profil user untuk edit informasi personal
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';

// Require login
require_login();

$db = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        // Get form data
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $institution = sanitize_input($_POST['institution']);
        $department = sanitize_input($_POST['department']);
        $address = sanitize_input($_POST['address']);
        
        // Validate required fields
        if (empty($name) || empty($email)) {
            throw new Exception('Nama dan email harus diisi');
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }
        
        // Check if email is already used by another user
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            throw new Exception('Email sudah digunakan oleh user lain');
        }
        
        // Handle password change
        $password_update = '';
        $params = [$name, $email, $phone, $institution, $department, $address, $user_id];
        
        if (!empty($_POST['new_password'])) {
            // Verify current password
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $current_user = $stmt->fetch();
            
            if (!password_verify($_POST['current_password'], $current_user['password'])) {
                throw new Exception('Password lama tidak benar');
            }
            
            // Validate new password
            if (strlen($_POST['new_password']) < 6) {
                throw new Exception('Password baru minimal 6 karakter');
            }
            
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                throw new Exception('Konfirmasi password tidak sama');
            }
            
            $password_update = ', password = ?';
            array_splice($params, -1, 0, [password_hash($_POST['new_password'], PASSWORD_DEFAULT)]);
        }
        
        // Update user data
        $sql = "UPDATE users SET 
                    name = ?, 
                    email = ?, 
                    phone = ?, 
                    institution = ?, 
                    department = ?, 
                    address = ?
                    {$password_update}
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // Update session data
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        
        // Log activity
        log_activity($user_id, 'profile_updated', 'User updated profile information');
        
        $message = 'Profil berhasil diperbarui';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get current user data
try {
    $stmt = $db->prepare("
        SELECT u.*, ur.role_name 
        FROM users u 
        LEFT JOIN user_roles ur ON u.role_id = ur.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User tidak ditemukan');
    }
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - ILab UNMUL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Page Header -->
                <div class="d-flex align-items-center mb-4">
                    <a href="dashboard.php" class="btn btn-outline-primary me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-user-edit text-primary me-2"></i>Profil Saya
                        </h2>
                        <p class="text-muted mb-0">Kelola informasi personal Anda</p>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Profile Form -->
                <div class="card shadow">
                    <div class="card-body">
                        <form method="POST" id="profileForm">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            
                            <!-- User Info Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded">
                                        <h6 class="text-muted mb-1">Username</h6>
                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($user['username']) ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-3 rounded">
                                        <h6 class="text-muted mb-1">Role</h6>
                                        <p class="mb-0">
                                            <span class="badge bg-primary">
                                                <?= htmlspecialchars($user['role_name'] ?? 'User') ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-user me-2"></i>Informasi Personal
                            </h5>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">No. Telepon</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="institution" class="form-label">Institusi</label>
                                    <input type="text" class="form-control" id="institution" name="institution" 
                                           value="<?= htmlspecialchars($user['institution'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="department" class="form-label">Departemen/Jurusan</label>
                                    <input type="text" class="form-control" id="department" name="department" 
                                           value="<?= htmlspecialchars($user['department'] ?? '') ?>">
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <!-- Password Change Section -->
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-lock me-2"></i>Ubah Password
                                <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small>
                            </h5>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="current_password" class="form-label">Password Lama</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Minimal 6 karakter</div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Info -->
                            <div class="bg-light p-3 rounded mb-4">
                                <h6 class="mb-2">Informasi Akun</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Bergabung sejak: <?= date('d M Y', strtotime($user['created_at'])) ?>
                                </small>
                                <?php if (!empty($user['last_login'])): ?>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Login terakhir: <?= date('d M Y H:i', strtotime($user['last_login'])) ?>
                                    </small>
                                <?php endif; ?>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                <h6>Booking Saya</h6>
                                <a href="my-bookings.php" class="btn btn-sm btn-primary">Lihat Booking</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-tachometer-alt fa-2x text-success mb-2"></i>
                                <h6>Dashboard</h6>
                                <a href="dashboard.php" class="btn btn-sm btn-success">Ke Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                button.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                button.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Reset form to original values
        function resetForm() {
            if (confirm('Yakin ingin mereset semua perubahan?')) {
                document.getElementById('profileForm').reset();
            }
        }

        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;
            
            // If changing password, validate
            if (newPassword) {
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Password lama harus diisi untuk mengubah password');
                    return;
                }
                
                if (newPassword.length < 6) {
                    e.preventDefault();
                    alert('Password baru minimal 6 karakter');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Konfirmasi password tidak sama');
                    return;
                }
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
</body>
</html>