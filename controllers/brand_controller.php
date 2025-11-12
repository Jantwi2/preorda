<?php

// connect to the brand class
include_once __DIR__ . '/../classes/brand_class.php';
// Create a brand
function add_brand_ctr($brand_name, $cat_id, $user_id){
    $brand = new brand_class();
    return $brand->add_brand($brand_name, $cat_id, $user_id);
}

// Get all brands for a user
function get_all_brands_ctr($user_id){
    $brand = new brand_class();
    return $brand->view_all_brands($user_id);
}

function get_all_brands_public_ctr() {
    $brand = new brand_class();
    return $brand->view_all_brands_public();
}

// Get brands by category (optionally scoped to a user)
function get_brands_by_category_ctr($cat_id, $user_id = null){
    $brand = new brand_class();
    return $brand->view_brands_by_category($cat_id, $user_id);
}

// Update a brand name
function update_brand_ctr($brand_id, $new_name){
    $brand = new brand_class();
    return $brand->update_brand($brand_id, $new_name);
}

// Delete a brand
function delete_brand_ctr($brand_id){
    $brand = new brand_class();
    return $brand->delete_brand($brand_id);
}
?>