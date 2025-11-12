<?php
require_once("../settings/db_class.php");

class product_class extends db_connection
{
    // ==============================
    // 1. ADD PRODUCT
    // ==============================
    public function add_product($title, $price, $description, $cat_id, $brand_id, $image_name, $keyword, $user_id)
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $title = mysqli_real_escape_string($conn, $title);
        $price = mysqli_real_escape_string($conn, $price);
        $description = mysqli_real_escape_string($conn, $description);
        $cat_id = (int)$cat_id;
        $brand_id = (int)$brand_id;
        $image_path = $image_name ? mysqli_real_escape_string($conn, '../uploads/' . $image_name) : '';
        $keyword = mysqli_real_escape_string($conn, $keyword);
        $user_id = (int)$user_id;

        $sql = "INSERT INTO `products`
                (`product_title`, `product_price`, `product_desc`, `product_cat`, `product_brand`, `product_image`, `product_keywords`, `user_id`)
                VALUES ('$title', '$price', '$description', $cat_id, $brand_id, '$image_path', '$keyword', '$user_id')";
        return $this->db_query($sql);
    }

    // ==============================
    // 2. VIEW ALL PRODUCTS
    // ==============================
    public function view_all_products($user_id = null)
    {
        $ndb = new db_connection();
        $where = $user_id ? "WHERE p.user_id = " . (int)$user_id : "";

        $sql = "
            SELECT p.*, c.cat_name AS category_name, b.brand_name AS brand_name
            FROM `products` AS p
            LEFT JOIN `categories` AS c ON p.product_cat = c.cat_id
            LEFT JOIN `brands` AS b ON p.product_brand = b.brand_id
            $where
            ORDER BY p.product_id DESC
        ";
        return $ndb->db_fetch_all($sql);
    }

    // ==============================
    // 3. SEARCH PRODUCTS
    // ==============================
    public function search_products($query, $user_id = null)
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $query = mysqli_real_escape_string($conn, $query);
        $user_filter = $user_id ? "AND p.user_id = " . (int)$user_id : "";

        $sql = "
            SELECT p.*, c.cat_name AS category_name, b.brand_name AS brand_name
            FROM `products` AS p
            LEFT JOIN `categories` AS c ON p.product_cat = c.cat_id
            LEFT JOIN `brands` AS b ON p.product_brand = b.brand_id
            WHERE (p.product_title LIKE '%$query%'
                OR p.product_desc LIKE '%$query%'
                OR p.product_keywords LIKE '%$query%')
            $user_filter
            ORDER BY p.product_title ASC
        ";
        return $ndb->db_fetch_all($sql);
    }

    // ==============================
    // 4. FILTER BY CATEGORY
    // ==============================
    public function filter_products_by_category($cat_id, $user_id = null)
    {
        $ndb = new db_connection();
        $cat_id = (int)$cat_id;
        $user_filter = $user_id ? "AND p.user_id = " . (int)$user_id : "";

        $sql = "
            SELECT p.*, c.cat_name AS category_name, b.brand_name AS brand_name
            FROM `products` AS p
            LEFT JOIN `categories` AS c ON p.product_cat = c.cat_id
            LEFT JOIN `brands` AS b ON p.product_brand = b.brand_id
            WHERE p.product_cat = $cat_id
            $user_filter
            ORDER BY p.product_id DESC
        ";
        return $ndb->db_fetch_all($sql);
    }

    // ==============================
    // 5. FILTER BY BRAND
    // ==============================
    public function filter_products_by_brand($brand_id, $user_id = null)
    {
        $ndb = new db_connection();
        $brand_id = (int)$brand_id;
        $user_filter = $user_id ? "AND p.user_id = " . (int)$user_id : "";

        $sql = "
            SELECT p.*, c.cat_name AS category_name, b.brand_name AS brand_name
            FROM `products` AS p
            LEFT JOIN `categories` AS c ON p.product_cat = c.cat_id
            LEFT JOIN `brands` AS b ON p.product_brand = b.brand_id
            WHERE p.product_brand = $brand_id
            $user_filter
            ORDER BY p.product_id DESC
        ";
        return $ndb->db_fetch_all($sql);
    }

    // ==============================
    // 6. VIEW SINGLE PRODUCT
    // ==============================
    public function view_single_product($product_id)
    {
        return $this->view_product_by_id($product_id);
    }

    // Existing function retained
    public function view_product_by_id($product_id)
    {
        $ndb = new db_connection();
        $product_id = (int)$product_id;

        $sql = "
            SELECT p.*, c.cat_name AS category_name, b.brand_name AS brand_name
            FROM `products` AS p
            LEFT JOIN `categories` AS c ON p.product_cat = c.cat_id
            LEFT JOIN `brands` AS b ON p.product_brand = b.brand_id
            WHERE p.product_id = $product_id
            LIMIT 1
        ";
        $rows = $ndb->db_fetch_all($sql);
        return !empty($rows) ? $rows[0] : null;
    }

    // ==============================
    // 7. UPDATE PRODUCT
    // ==============================
    public function update_product($product_id, $title, $price, $description, $cat_id, $brand_id, $image_name = null, $keyword = null)
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $product_id = (int)$product_id;
        $title = mysqli_real_escape_string($conn, $title);
        $price = mysqli_real_escape_string($conn, $price);
        $description = mysqli_real_escape_string($conn, $description);
        $cat_id = (int)$cat_id;
        $brand_id = (int)$brand_id;

        $sets = [];
        $sets[] = "`product_title` = '$title'";
        $sets[] = "`product_price` = '$price'";
        $sets[] = "`product_desc` = '$description'";
        $sets[] = "`product_cat` = $cat_id";
        $sets[] = "`product_brand` = $brand_id";

        if ($keyword !== null) {
            $keyword = mysqli_real_escape_string($conn, $keyword);
            $sets[] = "`product_keywords` = '$keyword'";
        }

        if ($image_name) {
            $image_path = mysqli_real_escape_string($conn, '../uploads/' . $image_name);
            $sets[] = "`product_image` = '$image_path'";
        }

        $sql = "UPDATE `products` SET " . implode(', ', $sets) . " WHERE `product_id` = $product_id";
        return $this->db_query($sql);
    }

    // ==============================
    // 8. DELETE PRODUCT
    // ==============================
    public function delete_product($product_id)
    {
        $ndb = new db_connection();
        $product_id = (int)$product_id;

        $row = $this->view_product_by_id($product_id);
        $sql = "DELETE FROM `products` WHERE `product_id` = $product_id";
        $res = $this->db_query($sql);

        if ($res && !empty($row['product_image'])) {
            $imgPath = $row['product_image'];
            if (strpos($imgPath, '..') !== false && file_exists($imgPath)) {
                @unlink($imgPath);
            }
        }
        return $res;
    }

    // ==============================
    // 9. BULK IMAGE UPLOAD
    // ==============================
    public function bulk_upload_images(array $files, $destDir = '../uploads/')
    {
        $result = ['saved' => [], 'errors' => []];

        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0755, true)) {
                $result['errors'][] = "Failed to create directory: $destDir";
                return $result;
            }
        }

        $count = count($files['name'] ?? []);
        for ($i = 0; $i < $count; $i++) {
            $tmpName = $files['tmp_name'][$i] ?? null;
            $orig = $files['name'][$i] ?? '';
            $error = $files['error'][$i] ?? 1;

            if ($error !== UPLOAD_ERR_OK || !$tmpName) {
                $result['errors'][] = "Upload error for file: $orig (code $error)";
                continue;
            }

            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $result['errors'][] = "Invalid file type for $orig";
                continue;
            }

            $safeName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $target = rtrim($destDir, '/') . '/' . $safeName;

            if (move_uploaded_file($tmpName, $target)) {
                $result['saved'][] = $safeName;
            } else {
                $result['errors'][] = "Failed to move $orig";
            }
        }

        return $result;
    }
}
?>
