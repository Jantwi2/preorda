<?php
include_once '../controllers/cart_controller.php';
include_once '../settings/core.php';

$user_id = $_SESSION['customer_id'];
$product_id = $_POST['product_id'];

remove_from_cart_ctr($user_id, $product_id);
echo "success";
?>
