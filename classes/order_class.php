<?php
// connect to database class
include_once __DIR__ . '/../settings/db_class.php';

class order_class extends db_connection
{
    /**
     * Create a new order
     * Returns the inserted order_id
     */
    public function create_order($customer_id, $total_amount, $invoice_no, $order_status = 'Pending')
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $customer_id = mysqli_real_escape_string($conn, $customer_id);
        $total_amount = mysqli_real_escape_string($conn, $total_amount);
        $order_status = mysqli_real_escape_string($conn, $order_status);
        if (empty($invoice_no)) {
            $invoice_no = 'INV' . time() . rand(1000, 9999);
        }
        $invoice_no = mysqli_real_escape_string($conn, $invoice_no);

        $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, total_amount, order_status) 
                    VALUES ('$customer_id', '$invoice_no', NOW(), '$total_amount', '$order_status')";

            // run on the same $conn so mysqli_insert_id() returns correct id
            if (mysqli_query($conn, $sql)) {
                return mysqli_insert_id($conn);
            }

            error_log('create_order failed: ' . mysqli_error($conn) . ' -- SQL: ' . $sql);
            return false;
    }

    /**
     * Add order details (products) for a given order
     */
    public function add_order_details($order_id, $product_id, $quantity, $price)
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $order_id = mysqli_real_escape_string($conn, $order_id);
        $product_id = mysqli_real_escape_string($conn, $product_id);
        $quantity = mysqli_real_escape_string($conn, $quantity);
        $price = mysqli_real_escape_string($conn, $price);

        $sql = "INSERT INTO orderdetails (order_id, product_id, qty, price)
                VALUES ('$order_id', '$product_id', '$quantity', '$price')";

        return $this->db_query($sql);
    }

    /**
     * Record a payment for a given order
     */
    /**
     * Record a payment for a given order
     */
    public function record_payment($customer_id, $order_id, $amount, $currency = 'Simulated')
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $order_id = mysqli_real_escape_string($conn, $order_id);
        $amount = mysqli_real_escape_string($conn, $amount);

        // Adjust column names to your schema; here we store method and status too.
        $sql = "INSERT INTO payment (customer_id, order_id, amt, currency, payment_date)
                VALUES ('$customer_id', '$order_id', '$amount', '$currency', NOW())";

        if ($this->db_query($sql)) {
            return true;
        }
        error_log('record_payment failed: ' . mysqli_error($conn));
        return false;
    }
 // ...existing code...

    /**
     * Get all past orders for a user
     */
    public function get_user_orders($customer_id)
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $customer_id = mysqli_real_escape_string($conn, $customer_id);

        $sql = "
            SELECT o.*, 
                   GROUP_CONCAT(CONCAT(od.product_id, ':', od.quantity, ':', od.price) SEPARATOR '|') AS products
            FROM orders o
            LEFT JOIN orderdetails od ON o.order_id = od.order_id
            WHERE o.customer_id = '$customer_id'
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
        ";

        return $this->db_fetch_all($sql);
    }

    /**
     * Get details of a specific order
     */
    public function get_order_details($order_id)
    {
        $ndb = new db_connection();
        $conn = $ndb->db_conn();

        $order_id = mysqli_real_escape_string($conn, $order_id);

        $sql = "
            SELECT od.*, p.product_title, p.product_price, p.product_image
            FROM orderdetails od
            INNER JOIN products p ON od.product_id = p.product_id
            WHERE od.order_id = '$order_id'
        ";

        return $this->db_fetch_all($sql);
    }
}
?>
