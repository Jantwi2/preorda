<?php
session_start();
header('Content-Type: application/json');
include("../controllers/brand_controller.php");

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

$brand_name = isset($data['name']) ? trim($data['name']) : (isset($data['brand_name']) ? trim($data['brand_name']) : '');
$cat_id = isset($data['cat_id']) ? trim($data['cat_id']) : (isset($data['category_id']) ? trim($data['category_id']) : '');
$cat_id_user_id = isset($data['cat_id_user_id']) ? trim($data['cat_id_user_id']) : '';

// require brand name
if (empty($brand_name)) {
    echo json_encode(['success' => false, 'message' => 'Brand name is required']);
    exit;
}

// determine user id from session
$user_id = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// If the table uses a combined cat_id_user_id value and caller supplied it, use that as the category identifier.
// Otherwise require cat_id separately.
if (!empty($cat_id_user_id)) {
    $cat = $cat_id_user_id;
} else {
    if (empty($cat_id)) {
        echo json_encode(['success' => false, 'message' => 'Category id is required']);
        exit;
    }
    $cat = $cat_id;
}

// Call controller to add brand
$result = add_brand_ctr($brand_name, $cat, $user_id);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Brand added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add brand']);
}
?>