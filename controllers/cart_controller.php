<?php
// Include the cart class
include_once __DIR__ . '/../classes/cart_class.php';

/**
 * Cart Controller
 * Wraps cart_class methods for use in action scripts
 */

// Add product to cart
function add_to_cart_ctr($user_id, $product_id, $quantity)
{
    $cart = new cart_class();
    return $cart->add_to_cart($user_id, $product_id, $quantity);
}

// Update product quantity in cart
function update_cart_item_ctr($user_id, $product_id, $quantity)
{
    $cart = new cart_class();
    return $cart->update_cart_qty($user_id, $product_id, $quantity);
}

// Remove a product from the cart
function remove_from_cart_ctr($user_id, $product_id)
{
    $cart = new cart_class();
    return $cart->remove_from_cart($user_id, $product_id);
}

// Retrieve all items in a user's cart
function get_user_cart_ctr($user_id)
{
    $cart = new cart_class();
    return $cart->get_user_cart($user_id);
}

// Empty an entire cart for a user
function empty_cart_ctr($user_id)
{
    $cart = new cart_class();
    return $cart->empty_cart($user_id);
}

// Check if a product already exists in the cart
function check_product_in_cart_ctr($user_id, $product_id)
{
    $cart = new cart_class();
    return $cart->check_product_in_cart($user_id, $product_id);
}
?>
