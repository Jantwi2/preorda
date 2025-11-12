<?php
include_once("../classes/product_class.php");



// ------------------------------------------
// 1. ADD PRODUCT
// ------------------------------------------
function add_product_ctr($title, $price, $description, $cat_id, $brand_id, $image_name, $keyword, $user_id) {
    $prod = new product_class();
    return $prod->add_product($title, $price, $description, $cat_id, $brand_id, $image_name, $keyword, $user_id);
}

// ------------------------------------------
// 2. VIEW ALL PRODUCTS
// ------------------------------------------
function get_all_products_ctr($user_id = null) {
    $prod = new product_class();
    return $prod->view_all_products($user_id);
}

// ------------------------------------------
// 3. SEARCH PRODUCTS
// ------------------------------------------
function search_products_ctr($query, $user_id = null) {
    $prod = new product_class();
    return $prod->search_products($query, $user_id);
}

// ------------------------------------------
// 4. FILTER PRODUCTS BY CATEGORY
// ------------------------------------------
function filter_products_by_category_ctr($cat_id, $user_id = null) {
    $prod = new product_class();
    return $prod->filter_products_by_category($cat_id, $user_id);
}

// ------------------------------------------
// 5. FILTER PRODUCTS BY BRAND
// ------------------------------------------
function filter_products_by_brand_ctr($brand_id, $user_id = null) {
    $prod = new product_class();
    return $prod->filter_products_by_brand($brand_id, $user_id);
}

// ------------------------------------------
// 6. VIEW SINGLE PRODUCT
// ------------------------------------------
function get_product_by_id_ctr($product_id) {
    $prod = new product_class();
    return $prod->view_single_product($product_id);
}

// ------------------------------------------
// 7. UPDATE PRODUCT
// ------------------------------------------
function update_product_ctr($product_id, $title, $price, $description, $cat_id, $brand_id, $image_name = null, $keyword = null) {
    $prod = new product_class();
    return $prod->update_product($product_id, $title, $price, $description, $cat_id, $brand_id, $image_name, $keyword);
}

// ------------------------------------------
// 8. DELETE PRODUCT
// ------------------------------------------
function delete_product_ctr($product_id) {
    $prod = new product_class();
    return $prod->delete_product($product_id);
}

// ------------------------------------------
// 9. BULK IMAGE UPLOAD
// ------------------------------------------
function bulk_upload_images_ctr(array $files, $destDir = '../uploads/') {
    $prod = new product_class();
    return $prod->bulk_upload_images($files, $destDir);
}
?>
