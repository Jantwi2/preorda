<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once '../controllers/cart_controller.php';
require_once '../controllers/order_controller.php';
$invoice_no = 'INV' . time() . rand(1000, 9999); // simple invoice number


// Check if user is logged in
$customer_id = $_SESSION['customer_id'] ?? null;
if (!$customer_id) {
    echo json_encode([
        'status' => 'error',
        'order_id' => null,
        'message' => 'User not logged in.'
    ]);
    exit();
}

// Get shipping address from POST

// Step 1: Get all items from the user's cart
$cart_items = get_user_cart_ctr($customer_id);

if (!$cart_items || count($cart_items) === 0) {
    echo json_encode([
        'status' => 'error',
        'order_id' => null,
        'message' => 'Your cart is empty.'
    ]);
    exit();
}

// Step 2: Calculate total amount
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['qty'] * $item['product_price'];
}

// Step 3: Create a new order
$order_id = create_order_ctr($customer_id, $total_amount, $invoice_no, 'Pending');
if (!$order_id) {
    echo json_encode([
        'status' => 'error',
        'order_id' => null,
        'message' => 'Failed to create order.'
    ]);
    exit();
}

// Step 4: Add each cart item to order details
foreach ($cart_items as $item) {
    $success = add_order_details_ctr($order_id, $item['p_id'], $item['qty'], $item['product_price']);
    if (!$success) {
        echo json_encode([
            'status' => 'error',
            'order_id' => $order_id,
            'message' => 'Failed to add order details.'
        ]);
        exit();
    }
}

// Step 5: Record simulated payment
$payment_success = record_payment_ctr($customer_id, $order_id, $total_amount, 'Simulated');
if (!$payment_success) {
    echo json_encode([
        'status' => 'error',
        'order_id' => $order_id,
        'message' => 'Failed to record payment.'
    ]);
    exit();
}

// Step 6: Empty the customer's cart
$empty_cart_success = empty_cart_ctr($customer_id);
if (!$empty_cart_success) {
    // Not fatal, continue but log warning
    error_log("Warning: Failed to empty cart for customer_id=$customer_id after checkout.");
}

// Step 7: Return success JSON response
echo json_encode([
    'status' => 'success',
    'order_id' => $order_id,
    'message' => 'Order placed successfully!'
]);
exit();
?>
