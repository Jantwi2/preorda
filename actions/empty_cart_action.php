<?php
include_once '../controllers/cart_controller.php';
include_once '../settings/core.php';

$user_id = $_SESSION['customer_id'];
empty_cart_ctr($user_id);
echo "success";
?>
