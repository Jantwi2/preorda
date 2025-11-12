<?php
// connect to database class
include_once __DIR__ . '/../settings/db_class.php';

/**
 * Cart class
 * Handles all shopping cart operations
 */
class cart_class extends db_connection
{
    /**
     * Add a product to the cart
     * - If product already exists, increment its qty
     */
    public function add_to_cart($c_id, $p_id, $qty)
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $c_id = mysqli_real_escape_string($conn, $c_id);
        $p_id = mysqli_real_escape_string($conn, $p_id);
        $qty = mysqli_real_escape_string($conn, $qty);
        $ip_add = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR']);

        // Check if product already exists in cart
        $check_sql = "SELECT * FROM `cart` WHERE `c_id` = '$c_id' AND `p_id` = '$p_id'";
        $existing_item = $ndb->db_fetch_one($check_sql);

        if ($existing_item) {
            // If product exists, increment qty
            $new_qty = $existing_item['qty'] + $qty;
            $update_sql = "UPDATE `cart` 
                        SET `qty` = '$new_qty' 
                        WHERE `c_id` = '$c_id' AND `p_id` = '$p_id'";
            return $this->db_query($update_sql);
        } else {
            // Otherwise, insert new record including IP
            $insert_sql = "INSERT INTO `cart`(`c_id`, `p_id`, `qty`, `ip_add`) 
                        VALUES ('$c_id', '$p_id', '$qty', '$ip_add')";
            return $this->db_query($insert_sql);
        }
    }

    /**
     * Update the qty of a specific product in the cart
     */
    public function update_cart_qty($c_id, $p_id, $qty)
    {
        $ndb = new db_connection();
        $c_id = mysqli_real_escape_string($ndb->db_conn(), $c_id);
        $p_id = mysqli_real_escape_string($ndb->db_conn(), $p_id);
        $qty = mysqli_real_escape_string($ndb->db_conn(), $qty);

        $sql = "UPDATE `cart` 
                SET `qty` = '$qty' 
                WHERE `c_id` = '$c_id' AND `p_id` = '$p_id'";
        return $this->db_query($sql);
    }

    /**
     * Remove a product from the cart
     */
    public function remove_from_cart($c_id, $p_id)
    {
        $ndb = new db_connection();
        $c_id = mysqli_real_escape_string($ndb->db_conn(), $c_id);
        $p_id = mysqli_real_escape_string($ndb->db_conn(), $p_id);

        $sql = "DELETE FROM `cart` WHERE `c_id` = '$c_id' AND `p_id` = '$p_id'";
        return $this->db_query($sql);
    }

    /**
     * Retrieve all items in a user's cart
     * Includes product details via JOIN
     */
    public function get_user_cart($c_id)
    {
        $ndb = new db_connection();
        $c_id = mysqli_real_escape_string($ndb->db_conn(), $c_id);

        $sql = "SELECT c.*, p.product_title, p.product_price, p.product_image 
                FROM `cart` AS c
                JOIN `products` AS p ON c.p_id = p.product_id
                WHERE c.`c_id` = '$c_id'";
        return $ndb->db_fetch_all($sql);
    }

    /**
     * Empty a user's cart
     */
    public function empty_cart($c_id)
    {
        $ndb = new db_connection();
        $c_id = mysqli_real_escape_string($ndb->db_conn(), $c_id);

        $sql = "DELETE FROM `cart` WHERE `c_id` = '$c_id'";
        return $this->db_query($sql);
    }

    /**
     * Check if a product exists in the cart (returns true/false)
     */
    public function check_product_in_cart($c_id, $p_id)
    {
        $ndb = new db_connection();
        $c_id = mysqli_real_escape_string($ndb->db_conn(), $c_id);
        $p_id = mysqli_real_escape_string($ndb->db_conn(), $p_id);

        $sql = "SELECT * FROM `cart` WHERE `c_id` = '$c_id' AND `p_id` = '$p_id'";
        $result = $ndb->db_fetch_one($sql);

        // Ensure we return an array or false
        if ($result && is_array($result)) {
            return $result; // the cart row
        } else {
            return false; // no such item
        }
    }

}
?>
