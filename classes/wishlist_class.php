<?php
require_once('../settings/db_class.php');

class wishlist_class extends db_connection {

    // Add to wishlist
    public function add_to_wishlist($user_id, $product_id) {
        $sql = "INSERT IGNORE INTO wishlists (user_id, product_id) VALUES ('$user_id', '$product_id')";
        return $this->db_query($sql);
    }

    // Remove from wishlist
    public function remove_from_wishlist($user_id, $product_id) {
        $sql = "DELETE FROM wishlists WHERE user_id = '$user_id' AND product_id = '$product_id'";
        return $this->db_query($sql);
    }

    // Check if product is in user's wishlist
    public function check_wishlist($user_id, $product_id) {
        $sql = "SELECT * FROM wishlists WHERE user_id = '$user_id' AND product_id = '$product_id'";
        return $this->db_fetch_one($sql);
    }

    // Get all items in a user's wishlist
    public function get_user_wishlist($user_id) {
        $sql = "SELECT p.*, w.added_at, v.business_name, c.category_name, b.brand_name
                FROM wishlists w 
                JOIN products p ON w.product_id = p.product_id 
                LEFT JOIN vendors v ON p.vendor_id = v.vendor_id
                LEFT JOIN categories c ON p.category_id = c.category_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                WHERE w.user_id = '$user_id'
                ORDER BY w.added_at DESC";
        return $this->db_fetch_all($sql);
    }

    // Get simple array of wishlisted product IDs for a user
    public function get_user_wishlist_ids($user_id) {
        $sql = "SELECT product_id FROM wishlists WHERE user_id = '$user_id'";
        $result = $this->db_fetch_all($sql);
        if ($result) {
            return array_column($result, 'product_id');
        }
        return [];
    }
}
?>
