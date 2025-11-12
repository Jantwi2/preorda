<?php
header('Content-Type: application/json');

include("../controllers/brand_controller.php");

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);
$brand_id = isset($data['id']) ? trim($data['id']) : '';

if (empty($brand_id)) {
    echo json_encode(['success' => false, 'message' => 'Brand ID is required']);
    exit;
}

// Call the controller function
$result = delete_brand_ctr($brand_id);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Brand deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete brand']);
}
exit;

?>
