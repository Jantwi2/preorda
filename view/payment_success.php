<?php
require_once '../controllers/cart_controller.php';
require_once '../settings/core.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get order details (you may pass order_id via GET or session)
$order_id = $_GET['order_id'] ?? null;
$customer_id = $_SESSION['customer_id'];

if (!$order_id) {
    echo "No order found.";
    exit();
}

// Fetch order info
$order = get_order_by_id_ctr($order_id);
if (!$order || $order['c_id'] != $customer_id) {
    echo "Invalid order.";
    exit();
}

// Fetch payment info
$payment = get_payment_by_order_ctr($order_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex items-center justify-center min-h-screen p-6">
    <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full text-center">
        <h1 class="text-3xl font-bold text-green-600 mb-4">✅ Payment Successful!</h1>
        <p class="text-gray-700 mb-4">Thank you for your order.</p>

        <div class="bg-gray-100 p-4 rounded-lg mb-4 text-left">
            <p><span class="font-semibold">Order Reference:</span> <?php echo htmlspecialchars($order['order_id']); ?></p>
            <p><span class="font-semibold">Amount Paid:</span> ₵<?php echo number_format($payment['amt'], 2); ?></p>
            <p><span class="font-semibold">Currency:</span> <?php echo htmlspecialchars($payment['currency']); ?></p>
            <p><span class="font-semibold">Payment Date:</span> <?php echo date("d M Y, H:i", strtotime($payment['payment_date'])); ?></p>
        </div>

        <a href="../index.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
            Continue Shopping
        </a>
    </div>
</div>

</body>
</html>
