<?php
session_start();
header('Content-Type: application/json');
require_once("../controllers/wishlist_controller.php");

// Respond with JSON error
function respondError($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    respondError('You must be logged in to add to wishlist.');
}

$user_id = $_SESSION['user_id'];

// Get posted data (handle both Form POST and JSON POST)
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id'])) {
    respondError('Product ID is required.');
}

$product_id = intval($data['product_id']);

// Check if already in wishlist
$exists = check_wishlist_ctr($user_id, $product_id);

if ($exists) {
    // Remove it
    $result = remove_from_wishlist_ctr($user_id, $product_id);
    if ($result) {
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist.']);
    } else {
        respondError('Failed to remove from wishlist.');
    }
} else {
    // Add it
    $result = add_to_wishlist_ctr($user_id, $product_id);
    if ($result) {
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist.']);
    } else {
        respondError('Failed to add to wishlist.');
    }
}
?>
