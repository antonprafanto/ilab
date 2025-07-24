<?php
/**
 * API untuk tracking download SOP
 */

session_start();
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';
require_once '../../includes/classes/SOPManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sop_id = intval($input['sop_id'] ?? 0);

if (!$sop_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid SOP ID']);
    exit;
}

try {
    $sopManager = new SOPManager();
    $user_id = $_SESSION['user_id'] ?? null;
    
    $result = $sopManager->recordDownload($sop_id, $user_id);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Download tracked successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to track download']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    error_log('Track download error: ' . $e->getMessage());
}
?>