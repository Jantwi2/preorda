<?php
//connect to database class
include_once __DIR__ . '/../settings/db_class.php';

/**
 * Brand class - CRUD for brands table
 *
 * NOTE: This implementation assumes your brands table columns are:
 *   brand_id, brand_name, cat_id, user_id
 *
 * If your table uses a single combined column named `cat_id_user_id`,
 * replace the queries/comments below accordingly (or tell me the exact
 * schema and I will adapt the class).
 */

class brand_class extends db_connection
{
    // Insert a new brand
    public function add_brand($brand_name, $cat_id, $user_id)
    {
        $ndb = new db_connection();
        $brand_name = mysqli_real_escape_string($ndb->db_conn(), $brand_name);
        $cat_id = mysqli_real_escape_string($ndb->db_conn(), $cat_id);
        $user_id = mysqli_real_escape_string($ndb->db_conn(), $user_id);

        $sql = "INSERT INTO `brands` (`brand_name`, `cat_id`, `user_id`) VALUES ('$brand_name', '$cat_id', '$user_id')";
        return $this->db_query($sql);
    }

    // Get all brands for a given user
    public function view_all_brands($user_id)
    {
        $ndb = new db_connection();
        $user_id = mysqli_real_escape_string($ndb->db_conn(), $user_id);
        $sql = "
            SELECT b.*, c.cat_name AS category_name
            FROM `brands` AS b
            LEFT JOIN `categories` AS c ON b.cat_id = c.cat_id
            WHERE b.user_id = $user_id
            ORDER BY b.brand_id DESC
        ";
        return $ndb->db_fetch_all($sql);
    }

    public function view_all_brands_public()
    {
        $sql = "SELECT * FROM `brands` ORDER BY brand_name ASC";
        return $this->db_fetch_all($sql);
    }




    

    // Get brands for a specific category (optionally scoped to a user)
    public function view_brands_by_category($cat_id, $user_id = null)
    {
        $ndb = new db_connection();
        $cat_id = mysqli_real_escape_string($ndb->db_conn(), $cat_id);
        if ($user_id !== null) {
            $user_id = mysqli_real_escape_string($ndb->db_conn(), $user_id);
            $sql = "SELECT * FROM `brands` WHERE `cat_id` = $cat_id AND `user_id` = $user_id";
        } else {
            $sql = "SELECT * FROM `brands` WHERE `cat_id` = $cat_id";
        }
        return $ndb->db_fetch_all($sql);
    }

    // Update a brand name
    public function update_brand($brand_id, $new_name)
    {
        $ndb = new db_connection();
        $brand_id = mysqli_real_escape_string($ndb->db_conn(), $brand_id);
        $new_name = mysqli_real_escape_string($ndb->db_conn(), $new_name);

        $sql = "UPDATE `brands` SET `brand_name` = '$new_name' WHERE `brand_id` = '$brand_id'";
        return $this->db_query($sql);
    }

    // Delete a brand
    public function delete_brand($brand_id)
    {
        $ndb = new db_connection();
        $brand_id = mysqli_real_escape_string($ndb->db_conn(), $brand_id);

        $sql = "DELETE FROM `brands` WHERE `brand_id` = '$brand_id'";
        return $this->db_query($sql);
    }
}
?>