<?php
// Ensure a session only if none exists
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

// convert PHP warnings/notices to exceptions so we can return JSON on failure
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    include_once("../controllers/product_controller.php");

    // ensure user is authenticated
    $user_id = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated', 'data' => []]);
        exit;
    }

    $products = get_all_products_ctr($user_id);
    if ($products === false || $products === null) $products = [];

    echo json_encode(['success' => true, 'data' => $products]);
} catch (Throwable $e) {
    http_response_code(500);
    // optionally log to file:
    error_log("fetch_product_action error: " . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Server error', 'details' => $e->getMessage()]);
}
restore_error_handler();
?>