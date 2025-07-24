<?php
/**
 * Logout - Website Integrated Laboratory UNMUL
 * Handles user logout dengan secure session cleanup
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';

// Log activity if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        log_activity($_SESSION['user_id'], 'user_logout', 'User logged out');
        
        // Update last activity
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
    } catch (Exception $e) {
        error_log("Logout logging error: " . $e->getMessage());
    }
}

// Clear all session data
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Set logout message for next page load
session_start();
$_SESSION['logout_message'] = 'Anda telah berhasil logout';

// Redirect to login page
header('Location: login.php');
exit();
?>