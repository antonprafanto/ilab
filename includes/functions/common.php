<?php
/**
 * Common Functions untuk ILab UNMUL Website
 * Fungsi-fungsi umum yang digunakan di seluruh aplikasi
 */

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generate_unique_code($prefix = '', $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = $prefix;
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Date and time functions
function format_indonesian_date($date) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = is_string($date) ? strtotime($date) : $date;
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

// Authentication functions
function require_login() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }
}

function require_role($required_roles) {
    require_login();
    
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    if (!isset($_SESSION['role_name']) || !in_array($_SESSION['role_name'], $required_roles)) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. Insufficient permissions.');
    }
}

function is_admin() {
    return isset($_SESSION['role_name']) && 
           in_array($_SESSION['role_name'], ['super_admin', 'staf_ilab', 'kepala_lab']);
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_role() {
    return $_SESSION['role_name'] ?? null;
}

// File handling functions
function get_file_icon($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $icons = [
        'pdf' => 'fas fa-file-pdf text-danger',
        'doc' => 'fas fa-file-word text-primary',
        'docx' => 'fas fa-file-word text-primary',
        'xlsx' => 'fas fa-file-excel text-success',
        'pptx' => 'fas fa-file-powerpoint text-warning',
        'jpg' => 'fas fa-file-image text-info',
        'jpeg' => 'fas fa-file-image text-info',
        'png' => 'fas fa-file-image text-info',
        'txt' => 'fas fa-file-alt text-secondary'
    ];
    
    return $icons[$extension] ?? 'fas fa-file text-muted';
}

function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Notification helper functions
function send_booking_notification($booking_id, $type = 'created') {
    try {
        require_once __DIR__ . '/../classes/NotificationSystem.php';
        $notification = new NotificationSystem();
        return $notification->sendBookingNotification($booking_id, $type);
    } catch (Exception $e) {
        error_log("Notification helper error: " . $e->getMessage());
        return false;
    }
}

function send_registration_notification($user_id) {
    try {
        require_once __DIR__ . '/../classes/NotificationSystem.php';
        $notification = new NotificationSystem();
        return $notification->sendRegistrationNotification($user_id);
    } catch (Exception $e) {
        error_log("Registration notification error: " . $e->getMessage());
        return false;
    }
}

// Activity logging functions
function log_activity($user_id, $action, $details = '') {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->execute([$user_id, $action, $details, $ip_address]);
        
        return true;
    } catch (Exception $e) {
        error_log("Activity logging error: " . $e->getMessage());
        return false;
    }
}

// Equipment availability functions
function check_equipment_availability($equipment_id, $date, $time_start, $time_end, $exclude_booking_id = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT COUNT(*) as conflicts
            FROM equipment_bookings eb
            JOIN facility_bookings fb ON eb.booking_id = fb.id
            WHERE eb.equipment_id = ?
            AND fb.booking_date = ?
            AND fb.status NOT IN ('cancelled', 'rejected')
            AND (
                (fb.time_start < ? AND fb.time_end > ?) OR
                (fb.time_start < ? AND fb.time_end > ?) OR
                (fb.time_start >= ? AND fb.time_end <= ?)
            )
        ";
        
        $params = [$equipment_id, $date, $time_end, $time_start, $time_end, $time_start, $time_start, $time_end];
        
        if ($exclude_booking_id) {
            $sql .= " AND fb.id != ?";
            $params[] = $exclude_booking_id;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['conflicts'] == 0;
        
    } catch (Exception $e) {
        error_log("Equipment availability check error: " . $e->getMessage());
        return false;
    }
}

// Validation functions
function validate_time_slot($date, $time_start, $time_end) {
    // Check if date is not in the past
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        return ['valid' => false, 'message' => 'Cannot book for past dates'];
    }
    
    // Check if start time is before end time
    if (strtotime($time_start) >= strtotime($time_end)) {
        return ['valid' => false, 'message' => 'Start time must be before end time'];
    }
    
    // Check minimum booking duration (30 minutes)
    $duration = (strtotime($time_end) - strtotime($time_start)) / 60;
    if ($duration < 30) {
        return ['valid' => false, 'message' => 'Minimum booking duration is 30 minutes'];
    }
    
    // Check maximum booking duration (8 hours)
    if ($duration > 480) {
        return ['valid' => false, 'message' => 'Maximum booking duration is 8 hours'];
    }
    
    // Check operating hours (7:00 - 18:00)
    $start_hour = (int)date('H', strtotime($time_start));
    $end_hour = (int)date('H', strtotime($time_end));
    
    if ($start_hour < 7 || $end_hour > 18) {
        return ['valid' => false, 'message' => 'Booking hours must be between 07:00 - 18:00'];
    }
    
    return ['valid' => true];
}

// Status badge helper
function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'approved' => '<span class="badge bg-success">Approved</span>',
        'rejected' => '<span class="badge bg-danger">Rejected</span>',
        'cancelled' => '<span class="badge bg-secondary">Cancelled</span>',
        'completed' => '<span class="badge bg-primary">Completed</span>',
        'in_progress' => '<span class="badge bg-info">In Progress</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

// Priority badge helper
function get_priority_badge($priority) {
    $badges = [
        'low' => '<span class="badge bg-secondary">Low</span>',
        'normal' => '<span class="badge bg-primary">Normal</span>',
        'high' => '<span class="badge bg-warning">High</span>',
        'urgent' => '<span class="badge bg-danger">Urgent</span>'
    ];
    
    return $badges[$priority] ?? '<span class="badge bg-primary">Normal</span>';
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'baru saja';
    if ($time < 3600) return floor($time/60) . ' menit yang lalu';
    if ($time < 86400) return floor($time/3600) . ' jam yang lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari yang lalu';
    if ($time < 31536000) return floor($time/2592000) . ' bulan yang lalu';
    
    return floor($time/31536000) . ' tahun yang lalu';
}

// File handling functions
function upload_file($file, $directory, $allowed_types = []) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!empty($allowed_types) && !in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'error' => 'File size too large'];
    }
    
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = UPLOAD_PATH . $directory . '/' . $filename;
    
    if (!is_dir(dirname($upload_path))) {
        mkdir(dirname($upload_path), 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'success' => true, 
            'filename' => $filename,
            'path' => $upload_path,
            'url' => SITE_URL . '/public/uploads/' . $directory . '/' . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to upload file'];
}

// Validation functions
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_phone($phone) {
    return preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $phone);
}

function validate_password($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH;
}

// Business process functions
function get_process_step_name($step_number, $process_type = 'text_based_8step') {
    $steps = [
        'text_based_8step' => [
            1 => 'Permohonan Penggunaan Fasilitas',
            2 => 'Verifikasi Permohonan',
            3 => 'Penjadwalan',
            4 => 'Persiapan Sampel',
            5 => 'Pengujian dan Analisis',
            6 => 'Pelaporan Hasil',
            7 => 'Pembayaran',
            8 => 'Evaluasi'
        ],
        'flowchart_7step' => [
            1 => 'Mengajukan surat permohonan',
            2 => 'Menginformasikan surat permohonan',
            3 => 'Wakil Direktur menginformasikan kepada Kepala Lab',
            4 => 'Persiapan dan Penggunaan Fasilitas',
            5 => 'Penggunaan Aset/Fasilitas iLab',
            6 => 'Peritaan atau penggunaan aset',
            7 => 'Pengembalian fasilitas'
        ]
    ];
    
    return $steps[$process_type][$step_number] ?? 'Unknown Step';
}

function get_booking_status_badge($status) {
    $badges = [
        'submitted' => '<span class="badge badge-primary">Diajukan</span>',
        'verified' => '<span class="badge badge-info">Diverifikasi</span>',
        'scheduled' => '<span class="badge badge-warning">Dijadwalkan</span>',
        'in_progress' => '<span class="badge badge-info">Sedang Berlangsung</span>',
        'testing' => '<span class="badge badge-warning">Pengujian</span>',
        'reporting' => '<span class="badge badge-info">Pelaporan</span>',
        'payment_pending' => '<span class="badge badge-warning">Menunggu Pembayaran</span>',
        'completed' => '<span class="badge badge-success">Selesai</span>',
        'cancelled' => '<span class="badge badge-danger">Dibatalkan</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

// Organization structure functions
function get_organization_level_name($level) {
    $levels = [
        1 => 'Direktur',
        2 => 'Wakil Direktur',
        3 => 'Kepala Lab/Unit',
        4 => 'Anggota Lab/Unit',
        5 => 'Laboran',
        6 => 'Sub Bagian Tata Usaha',
        7 => 'Sub Bagian Keuangan',
        8 => 'Staff'
    ];
    
    return $levels[$level] ?? 'Unknown Level';
}

// Stakeholder benefits functions
function get_stakeholder_category_name($category) {
    $categories = [
        'mahasiswa' => 'Mahasiswa',
        'dosen_peneliti' => 'Dosen dan Peneliti',
        'universitas' => 'Universitas Mulawarman',
        'fakultas' => 'Fakultas',
        'industri' => 'Industri dan Masyarakat',
        'pemerintah' => 'Pemerintah',
        'masyarakat' => 'Masyarakat Luas',
        'umkm' => 'UMKM'
    ];
    
    return $categories[$category] ?? 'Unknown Category';
}

// Equipment status functions
function get_equipment_status_badge($status) {
    $badges = [
        'available' => '<span class="badge badge-success">Tersedia</span>',
        'in_use' => '<span class="badge badge-warning">Digunakan</span>',
        'maintenance' => '<span class="badge badge-info">Maintenance</span>',
        'out_of_order' => '<span class="badge badge-danger">Rusak</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

// Pagination function
function paginate($total_records, $records_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_records / $records_per_page);
    $pagination = '';
    
    if ($total_pages > 1) {
        $pagination .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            $pagination .= "<li class='page-item'><a class='page-link' href='{$base_url}?page={$prev_page}'>Previous</a></li>";
        }
        
        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = ($i == $current_page) ? 'active' : '';
            $pagination .= "<li class='page-item {$active}'><a class='page-link' href='{$base_url}?page={$i}'>{$i}</a></li>";
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $next_page = $current_page + 1;
            $pagination .= "<li class='page-item'><a class='page-link' href='{$base_url}?page={$next_page}'>Next</a></li>";
        }
        
        $pagination .= '</ul></nav>';
    }
    
    return $pagination;
}

// Currency formatting
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Logging function
function log_activity($user_id, $action, $description, $ip_address = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $ip = $ip_address ?: $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt->execute([$user_id, $action, $description, $ip]);
        
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Session management
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

function has_role($required_roles) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'] ?? '';
    
    if (is_array($required_roles)) {
        return in_array($user_role, $required_roles);
    }
    
    return $user_role === $required_roles;
}

function require_role($required_roles) {
    if (!has_role($required_roles)) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied. Insufficient permissions.');
    }
}

// JSON response function
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Redirect function
function redirect($url, $permanent = false) {
    if ($permanent) {
        header('HTTP/1.1 301 Moved Permanently');
    }
    header("Location: $url");
    exit;
}

// Get current page for navigation
function get_current_page() {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    return $current_page;
}

// Generate breadcrumbs
function generate_breadcrumbs($items) {
    $breadcrumbs = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($items as $index => $item) {
        $is_last = ($index === count($items) - 1);
        
        if ($is_last) {
            $breadcrumbs .= '<li class="breadcrumb-item active" aria-current="page">' . $item['title'] . '</li>';
        } else {
            $breadcrumbs .= '<li class="breadcrumb-item"><a href="' . $item['url'] . '">' . $item['title'] . '</a></li>';
        }
    }
    
    $breadcrumbs .= '</ol></nav>';
    return $breadcrumbs;
}
?>