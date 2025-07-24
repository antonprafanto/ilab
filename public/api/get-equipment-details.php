<?php
/**
 * API Endpoint untuk Equipment Details
 * Returns detailed equipment information in JSON format
 */

header('Content-Type: application/json');
require_once '../../includes/config/database.php';
require_once '../../includes/functions/common.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid equipment ID']);
    exit;
}

$equipment_id = (int)$_GET['id'];
$db = Database::getInstance()->getConnection();

try {
    $stmt = $db->prepare("
        SELECT 
            e.*,
            ec.category_name,
            ec.description as category_description
        FROM equipment e
        JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.id = ?
    ");
    
    $stmt->execute([$equipment_id]);
    $equipment = $stmt->fetch();
    
    if ($equipment) {
        echo json_encode([
            'success' => true,
            'equipment' => $equipment
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Equipment not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>