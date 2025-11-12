<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../controllers/cart_controller.php';
require_once '../settings/core.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$cart_items = get_user_cart_ctr($customer_id);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['product_price'] * $item['qty'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    /* Buttons */
    .btn { display:inline-flex; align-items:center; justify-content:center; border-radius:.5rem; padding:.5rem 1rem; font-weight:600; cursor:pointer; }
    .btn-primary { background:#16a34a; color:#fff; }
    .btn-primary:hover { background:#15803d; }
    .btn-outline { background:transparent; border:1px solid #d1d5db; color:#374151; }
    .btn-cancel { background:#9ca3af; color:#fff; }
    /* Modal (simple centering) */
    .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:50; }
    .modal-card { background:#fff; border-radius:1rem; padding:1.25rem; width:100%; max-width:28rem; box-shadow:0 10px 30px rgba(0,0,0,0.12); }
    /* Checkout message */
    #checkout-message { position:fixed; top:1rem; left:50%; transform:translateX(-50%); z-index:60; padding:.5rem 1rem; border-radius:.5rem; display:none; color:#fff; font-weight:600; }
    #checkout-message.success { background:#16a34a; }
    #checkout-message.error { background:#dc2626; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-6xl mx-auto p-6">
  <h1 class="text-3xl font-bold mb-8 text-center">💳 Checkout</h1>

  <?php if (empty($cart_items)): ?>
    <div class="bg-white p-10 rounded-2xl shadow-md text-center">
      <p class="text-gray-600 text-lg mb-4">Your cart is empty.</p>
      <a href="../index.php" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">Continue Shopping</a>
    </div>
  <?php else: ?>

    <!-- Cart Summary Table (wrapped so JS can hide it) -->
    <div id="cart-container">
      <div class="bg-white rounded-2xl shadow-md overflow-x-auto mb-6">
        <table class="w-full min-w-max table-auto text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 font-medium text-gray-700">Product</th>
              <th class="px-6 py-3 font-medium text-gray-700">Price</th>
              <th class="px-6 py-3 font-medium text-gray-700">Quantity</th>
              <th class="px-6 py-3 font-medium text-gray-700">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart_items as $item):
              $subtotal = $item['product_price'] * $item['qty']; ?>
              <tr class="border-b hover:bg-gray-50 transition duration-150">
                <td class="px-6 py-4 flex items-center space-x-4">
                  <img src="../uploads/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_title']); ?>" class="w-16 h-16 rounded-lg object-cover shadow-sm">
                  <span class="font-medium text-gray-800"><?php echo htmlspecialchars($item['product_title']); ?></span>
                </td>
                <td class="px-6 py-4 font-semibold text-gray-700">₵<?php echo number_format($item['product_price'], 2); ?></td>
                <td class="px-6 py-4 font-medium text-gray-700"><?php echo $item['qty']; ?></td>
                <td class="px-6 py-4 font-semibold text-gray-700">₵<?php echo number_format($subtotal, 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="flex justify-end px-6 py-4 bg-gray-100 rounded-b-2xl">
          <p class="text-xl font-bold text-gray-800">Total: ₵<?php echo number_format($total, 2); ?></p>
        </div>
      </div>

      <!-- Simulate Payment Button -->
      <div class="flex justify-center mb-6">
        <button id="checkout-btn" class="btn btn-primary">Simulate Payment</button>
      </div>
    </div>

    <!-- Confirmation container (hidden until successful) -->
    <div id="confirmation-container" style="display:none;">
      <div class="bg-white p-8 rounded-2xl shadow-md text-center">
        <h2 class="text-2xl font-bold mb-2">Thank you — your order is confirmed!</h2>
        <p class="mb-4">Order ID: <span id="order-id" class="font-semibold"></span></p>
        <a href="../index.php" class="btn btn-outline">Continue Shopping</a>
      </div>
    </div>

  <?php endif; ?>
</div>

<!-- Payment Modal (IDs match checkout.js) -->
<div id="payment-modal" class="modal-backdrop" style="display:none;">
  <div class="modal-card">
    <h2 class="text-2xl font-bold mb-4">Confirm Payment</h2>
    <p class="mb-6">Total: <span class="font-semibold">₵<?php echo number_format($total, 2); ?></span></p>
    <div class="flex justify-end space-x-3">
      <button id="cancel-payment" class="btn btn-cancel">Cancel</button>
      <button id="confirm-payment" class="btn btn-primary">Yes, I have Paid</button>
    </div>
  </div>
</div>

<!-- Placeholder for JS Response Messages -->
<div id="checkout-message" class=""></div>

<script src="../js/checkout.js"></script>
</body>
</html>
