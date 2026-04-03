<?php
require_once('../classes/wishlist_class.php');

// Add item to wishlist
function add_to_wishlist_ctr($user_id, $product_id) {
    $wishlist = new wishlist_class();
    return $wishlist->add_to_wishlist($user_id, $product_id);
}

// Remove item from wishlist
function remove_from_wishlist_ctr($user_id, $product_id) {
    $wishlist = new wishlist_class();
    return $wishlist->remove_from_wishlist($user_id, $product_id);
}

// Check if item is in wishlist
function check_wishlist_ctr($user_id, $product_id) {
    $wishlist = new wishlist_class();
    return $wishlist->check_wishlist($user_id, $product_id);
}

// Get full wishlist details
function get_user_wishlist_ctr($user_id) {
    $wishlist = new wishlist_class();
    return $wishlist->get_user_wishlist($user_id);
}

// Get array of product IDs in wishlist
function get_user_wishlist_ids_ctr($user_id) {
    $wishlist = new wishlist_class();
    return $wishlist->get_user_wishlist_ids($user_id);
}
?>
