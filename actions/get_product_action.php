<?php
// Include core files and controller
require_once('../controllers/product_controller.php');

// Set response header
header('Content-Type: application/json');

try {
    // Get the raw JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID not provided'
        ]);
        exit;
    }

    $product_id = intval($input['id']);

    // Get product from controller
    $product = get_product_by_id_ctr($product_id);

    if ($product) {
        echo json_encode([
            'success' => true,
            'data' => $product
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
    }

} catch (Exception $e) {
    // Catch any unexpected errors
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
