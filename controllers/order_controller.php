<?php
include_once dirname(__FILE__) . '/../classes/order_class.php';

/**
 * Create a new order
 * Returns the order_id or false on failure
 */
function create_order_ctr($customer_id, $total_amount, $invoice_no, $order_status = 'Pending') {
    $order = new order_class();
    return $order->create_order($customer_id, $total_amount, $invoice_no, $order_status);
}

/**
 * Add an item to order details
 */
function add_order_details_ctr($order_id, $product_id, $quantity, $price) {
    $order = new order_class();
    return $order->add_order_details($order_id, $product_id, $quantity, $price);
}

/**
 * Record a payment
 */
function record_payment_ctr($customer_id, $order_id, $amount, $currency = 'Simulated') {
    $order = new order_class();
    return $order->record_payment($customer_id, $order_id, $amount, $currency);
}

/**
 * Get all past orders for a user
 */
function get_user_orders_ctr($customer_id) {
    $order = new order_class();
    return $order->get_user_orders($customer_id);
}

/**
 * Get details of a specific order
 */
function get_order_details_ctr($order_id) {
    $order = new order_class();
    return $order->get_order_details($order_id);
}

/**
 * Checkout helper: creates order, adds details, records payment
 * $cart_items is an array of cart items, each with keys: product_id, qty, price
 */
function process_checkout_ctr($customer_id, $cart_items, $shipping_address = null) {
    if (empty($cart_items)) return false;

    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['qty'] * $item['price'];
    }

    // Step 1: Create order
    $order_id = create_order_ctr($customer_id, $total_amount, 'Pending', $shipping_address);
    if (!$order_id) return false;

    // Step 2: Add order details
    foreach ($cart_items as $item) {
        add_order_details_ctr($order_id, $item['product_id'], $item['qty'], $item['price']);
    }

    // Step 3: Record payment (simulated)
    record_payment_ctr($order_id, $total_amount, 'Simulated', 'Paid');

    return $order_id;
}
?>
