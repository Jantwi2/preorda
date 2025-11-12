<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
include("../controllers/brand_controller.php");

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);
$brand_id = isset($data['id']) ? trim($data['id']) : '';
$new_name = isset($data['new_name']) ? trim($data['new_name']) : '';

if (empty($brand_id) || empty($new_name)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Call the controller function
$result = update_brand_ctr($brand_id, $new_name);
if ($result) {
    echo json_encode(['success' => true, 'message' => 'Brand updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update brand']);
}
exit; // 🚨 important to stop execution

?>