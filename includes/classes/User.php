<?php
/**
 * User Management Class untuk ILab UNMUL
 * Handles 8 role types berdasarkan stakeholder analysis
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Register new user dengan role validation
     */
    public function register($data) {
        try {
            // Validate input
            $errors = $this->validateRegistration($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Check if email/username already exists
            if ($this->emailExists($data['email'])) {
                return ['success' => false, 'errors' => ['Email sudah terdaftar']];
            }
            
            if ($this->usernameExists($data['username'])) {
                return ['success' => false, 'errors' => ['Username sudah terdaftar']];
            }
            
            // Hash password
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, full_name, role_id, institution, phone, address, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $password_hash,
                $data['full_name'],
                $data['role_id'],
                $data['institution'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null
            ]);
            
            if ($result) {
                $user_id = $this->db->lastInsertId();
                
                // Log registration activity
                log_activity($user_id, 'register', 'User registered successfully');
                
                return [
                    'success' => true, 
                    'user_id' => $user_id,
                    'message' => 'Registrasi berhasil. Silakan login.'
                ];
            }
            
            return ['success' => false, 'errors' => ['Gagal menyimpan data user']];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Terjadi kesalahan sistem']];
        }
    }
    
    /**
     * Login user dengan role-based authentication
     */
    public function login($username, $password, $remember_me = false) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, ur.role_name, ur.role_type, ur.permissions 
                FROM users u 
                JOIN user_roles ur ON u.role_id = ur.id 
                WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'error' => 'Username/email tidak ditemukan'];
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                // Log failed login attempt
                log_activity($user['id'], 'login_failed', 'Failed login attempt');
                return ['success' => false, 'error' => 'Password salah'];
            }
            
            // Create session
            $this->createSession($user);
            
            // Handle remember me
            if ($remember_me) {
                $this->createRememberToken($user['id']);
            }
            
            // Log successful login
            log_activity($user['id'], 'login', 'User logged in successfully');
            
            return [
                'success' => true, 
                'user' => $this->sanitizeUserData($user),
                'redirect_url' => $this->getRedirectUrl($user['role_name'])
            ];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Terjadi kesalahan sistem'];
        }
    }
    
    /**
     * Logout user dan cleanup session
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            log_activity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            $this->clearRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        session_start();
        
        return ['success' => true, 'message' => 'Logout berhasil'];
    }
    
    /**
     * Get user by ID dengan role information
     */
    public function getUserById($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, ur.role_name, ur.role_type, ur.permissions 
                FROM users u 
                JOIN user_roles ur ON u.role_id = ur.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            return $user ? $this->sanitizeUserData($user) : null;
            
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($user_id, $data) {
        try {
            $errors = $this->validateProfileUpdate($data, $user_id);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET full_name = ?, institution = ?, phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $data['full_name'],
                $data['institution'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $user_id
            ]);
            
            if ($result) {
                log_activity($user_id, 'profile_update', 'Profile updated successfully');
                return ['success' => true, 'message' => 'Profil berhasil diperbarui'];
            }
            
            return ['success' => false, 'errors' => ['Gagal memperbarui profil']];
            
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Terjadi kesalahan sistem']];
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($current_password, $user['password_hash'])) {
                return ['success' => false, 'error' => 'Password lama salah'];
            }
            
            // Validate new password
            if (!validate_password($new_password)) {
                return ['success' => false, 'error' => 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter'];
            }
            
            // Update password
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $result = $stmt->execute([$new_hash, $user_id]);
            
            if ($result) {
                log_activity($user_id, 'password_change', 'Password changed successfully');
                return ['success' => true, 'message' => 'Password berhasil diubah'];
            }
            
            return ['success' => false, 'error' => 'Gagal mengubah password'];
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Terjadi kesalahan sistem'];
        }
    }
    
    /**
     * Get all user roles untuk registration form
     */
    public function getUserRoles() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_roles ORDER BY role_type, role_name");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get roles error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check user permissions untuk role-based access
     */
    public function hasPermission($user_id, $permission) {
        try {
            $stmt = $this->db->prepare("
                SELECT ur.permissions 
                FROM users u 
                JOIN user_roles ur ON u.role_id = ur.id 
                WHERE u.id = ? AND u.is_active = 1
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !$user['permissions']) {
                return false;
            }
            
            $permissions = json_decode($user['permissions'], true);
            return in_array($permission, $permissions);
            
        } catch (Exception $e) {
            error_log("Permission check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get users by role type (internal/external)
     */
    public function getUsersByRoleType($role_type, $limit = 50, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, ur.role_name, ur.role_type 
                FROM users u 
                JOIN user_roles ur ON u.role_id = ur.id 
                WHERE ur.role_type = ? AND u.is_active = 1 
                ORDER BY u.created_at DESC 
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$role_type, $limit, $offset]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get users by role error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistration($data) {
        $errors = [];
        
        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors[] = 'Username minimal 3 karakter';
        }
        
        if (empty($data['email']) || !validate_email($data['email'])) {
            $errors[] = 'Email tidak valid';
        }
        
        if (empty($data['password']) || !validate_password($data['password'])) {
            $errors[] = 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter';
        }
        
        if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
            $errors[] = 'Nama lengkap minimal 2 karakter';
        }
        
        if (empty($data['role_id']) || !is_numeric($data['role_id'])) {
            $errors[] = 'Role harus dipilih';
        }
        
        if (!empty($data['phone']) && !validate_phone($data['phone'])) {
            $errors[] = 'Format nomor telepon tidak valid';
        }
        
        return $errors;
    }
    
    /**
     * Validate profile update data
     */
    private function validateProfileUpdate($data, $user_id) {
        $errors = [];
        
        if (empty($data['full_name']) || strlen($data['full_name']) < 2) {
            $errors[] = 'Nama lengkap minimal 2 karakter';
        }
        
        if (!empty($data['phone']) && !validate_phone($data['phone'])) {
            $errors[] = 'Format nomor telepon tidak valid';
        }
        
        return $errors;
    }
    
    /**
     * Check if email exists
     */
    private function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if username exists
     */
    private function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Create user session
     */
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role_name'];
        $_SESSION['user_role_type'] = $user['role_type'];
        $_SESSION['login_time'] = time();
        
        // Update last login
        $stmt = $this->db->prepare("UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$user['id']]);
    }
    
    /**
     * Create remember me token
     */
    private function createRememberToken($user_id) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Store token in database
        $stmt = $this->db->prepare("
            INSERT INTO remember_tokens (user_id, token, expires_at) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
        ");
        $stmt->execute([$user_id, $token, $expires, $token, $expires]);
        
        // Set cookie
        setcookie('remember_token', $token, strtotime('+30 days'), '/');
    }
    
    /**
     * Clear remember me token
     */
    private function clearRememberToken($token) {
        $stmt = $this->db->prepare("DELETE FROM remember_tokens WHERE token = ?");
        $stmt->execute([$token]);
    }
    
    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl($role_name) {
        $redirects = [
            'staf_ilab' => '/admin/dashboard.php',
            'fakultas' => '/dashboard.php',
            'mahasiswa' => '/dashboard.php',
            'peneliti_internal' => '/dashboard.php',
            'industri' => '/dashboard.php',
            'pemerintah' => '/dashboard.php',
            'masyarakat' => '/dashboard.php',
            'umkm' => '/dashboard.php'
        ];
        
        return $redirects[$role_name] ?? '/dashboard.php';
    }
    
    /**
     * Sanitize user data untuk output
     */
    private function sanitizeUserData($user) {
        unset($user['password_hash']);
        return $user;
    }
}
?>