<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../controllers/cart_controller.php';

// Ensure user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'] ?? 1;

// Step 1: Check if product already exists in cart
$existing_cart_item = check_product_in_cart_ctr($customer_id, $product_id);

if ($existing_cart_item) {
    // Step 2: If product exists, increment quantity
    $new_qty = $existing_cart_item['qty'] + $quantity;
    $result = update_cart_item_ctr($customer_id, $product_id, $new_qty);
} else {
    // Step 3: Otherwise, add new product
    $result = add_to_cart_ctr($customer_id, $product_id, $quantity);
}

if ($result) {
    header("Location: ../view/cart.php?success=1");
    exit();
} else {
    header("Location: ../view/cart.php?error=1");
    exit();
}
?>
