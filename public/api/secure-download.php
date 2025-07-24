<?php
/**
 * Secure File Download API - iLab UNMUL
 * Handles secure file downloads dengan token validation
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/FileUploadSecurity.php';

// Require login for downloads
require_login();

if (!isset($_GET['token']) || empty($_GET['token'])) {
    http_response_code(400);
    die('Missing download token');
}

$token = $_GET['token'];

// Validate token
if (!isset($_SESSION['download_tokens'][$token])) {
    http_response_code(403);
    die('Invalid or expired download token');
}

$token_data = $_SESSION['download_tokens'][$token];

// Check if token is expired
if ($token_data['expires'] < time()) {
    unset($_SESSION['download_tokens'][$token]);
    http_response_code(403);
    die('Download token has expired');
}

$file_path = $token_data['file_path'];
$booking_id = $token_data['booking_id'];

// Initialize security class
$fileSecurity = new FileUploadSecurity();

// Get file info
$file_info = $fileSecurity->getFileInfo($file_path);
if (!$file_info) {
    http_response_code(404);
    die('File not found');
}

$full_path = realpath(__DIR__ . '/../../public/uploads/' . $file_path);

// Additional access control checks
if ($booking_id) {
    // Check if user has access to this booking
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT user_id FROM facility_bookings 
        WHERE id = ? AND (user_id = ? OR ? IN (
            SELECT id FROM users u 
            JOIN user_roles ur ON u.role_id = ur.id 
            WHERE ur.role_name = 'staf_ilab'
        ))
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id'], $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        die('Access denied to this file');
    }
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Set content type and disposition
$mime_type = $file_info['mime_type'];
$filename = $file_info['name'];

header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
header('Content-Length: ' . $file_info['size']);
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Log download activity
try {
    $stmt = $db->prepare("
        INSERT INTO download_logs (
            user_id, file_path, booking_id, download_time, ip_address
        ) VALUES (?, ?, ?, NOW(), ?)
    ");
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt->execute([$_SESSION['user_id'], $file_path, $booking_id, $ip_address]);
} catch (Exception $e) {
    error_log("Download logging error: " . $e->getMessage());
}

// Remove token after use (one-time use)
unset($_SESSION['download_tokens'][$token]);

// Stream the file
if (readfile($full_path) === false) {
    http_response_code(500);
    die('Error reading file');
}

exit;
?>