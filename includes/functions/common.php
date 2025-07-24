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