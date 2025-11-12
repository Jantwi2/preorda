<?php
// ...existing code...
include_once '../controllers/cart_controller.php';
include_once '../settings/core.php';

// Ensure session customer id exists
if (!isset($_SESSION['customer_id'])) {
    echo 'error';
    exit();
}

$user_id = $_SESSION['customer_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

if ($product_id <= 0 || $quantity < 1) {
    echo 'error';
    exit();
}

$result = update_cart_item_ctr($user_id, $product_id, $quantity);
echo $result ? 'success' : 'error';
?>
