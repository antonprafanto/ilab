<?php
/**
 * API Endpoint untuk Log Activity
 * Handles activity logging with file uploads
 */

header('Content-Type: application/json');
session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Verify CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();
    
    // Validate required fields
    $required_fields = ['title', 'category', 'activity_date', 'description'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Handle file uploads
    $attachments = [];
    if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
        $upload_dir = '../../public/uploads/activities/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
            if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['attachments']['name'][$i];
                $file_size = $_FILES['attachments']['size'][$i];
                $file_tmp = $_FILES['attachments']['tmp_name'][$i];
                
                // Validate file
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_types)) {
                    throw new Exception("File type '$file_ext' not allowed");
                }
                
                if ($file_size > $max_size) {
                    throw new Exception("File '$file_name' exceeds maximum size");
                }
                
                // Generate safe filename
                $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file_name, PATHINFO_FILENAME));
                $safe_filename = $safe_name . '_' . time() . '_' . $i . '.' . $file_ext;
                $file_path = 'uploads/activities/' . $safe_filename;
                
                if (move_uploaded_file($file_tmp, $upload_dir . $safe_filename)) {
                    $attachments[] = [
                        'name' => $file_name,
                        'path' => $file_path,
                        'size' => $file_size
                    ];
                }
            }
        }
    }
    
    // Insert activity
    $stmt = $db->prepare("
        INSERT INTO activities (
            user_id, booking_id, equipment_id, title, category, activity_date,
            duration_hours, description, results, samples_processed, 
            attachments, priority, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $_SESSION['user_id'],
        $_POST['booking_id'] ?: null,
        $_POST['equipment_id'] ?: null,
        sanitize_input($_POST['title']),
        sanitize_input($_POST['category']),
        $_POST['activity_date'],
        $_POST['duration_hours'] ?: null,
        sanitize_input($_POST['description']),
        sanitize_input($_POST['results'] ?? ''),
        sanitize_input($_POST['samples_processed'] ?? ''),
        !empty($attachments) ? json_encode($attachments) : null,
        sanitize_input($_POST['priority'] ?? 'normal'),
        sanitize_input($_POST['notes'] ?? '')
    ]);
    
    if (!$result) {
        throw new Exception('Failed to insert activity');
    }
    
    $activity_id = $db->lastInsertId();
    
    // Log the activity in system logs
    log_activity($_SESSION['user_id'], 'activity_logged', "Activity logged: " . $_POST['title']);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'activity_id' => $activity_id,
        'message' => 'Activity logged successfully'
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    error_log("Activity logging error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>