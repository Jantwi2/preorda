<?php
require_once('../controllers/product_controller.php');

// Set JSON response header
header('Content-Type: application/json');

try {
    // Parse the incoming JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Product ID not provided'
        ]);
        exit;
    }

    $product_id = intval($input['id']);

    // Call the delete controller
    $deleted = delete_product_ctr($product_id);

    if ($deleted) {
        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete product. It may not exist.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
