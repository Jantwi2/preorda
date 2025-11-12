<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// session_start();
include_once '../controllers/cart_controller.php';
include_once '../settings/core.php';

// Ensure user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['customer_id'];
$cart_items = get_user_cart_ctr($user_id);
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

  <div class="max-w-6xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">🛒 Your Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
      <div class="bg-white p-10 rounded-2xl shadow-md text-center">
        <p class="text-gray-600 text-lg mb-4">Your cart is currently empty.</p>
        <a href="../index.php" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">Continue Shopping</a>
      </div>
    <?php else: ?>
      <div class="bg-white rounded-2xl shadow-md overflow-x-auto">
        <table class="w-full min-w-max table-auto text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 font-medium text-gray-700">Product</th>
              <th class="px-6 py-3 font-medium text-gray-700">Price</th>
              <th class="px-6 py-3 font-medium text-gray-700">Quantity</th>
              <th class="px-6 py-3 font-medium text-gray-700">Subtotal</th>
              <th class="px-6 py-3 font-medium text-gray-700 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart_items as $item): ?>
              <?php $subtotal = $item['product_price'] * $item['qty']; $total += $subtotal; ?>
                <tr class="border-b hover:bg-gray-50 transition duration-150">
                <!-- Product -->
                <td class="px-6 py-4 flex items-center space-x-4">
                    <img src="../uploads/<?php echo htmlspecialchars($item['product_image']); ?>" 
                        alt="<?php echo htmlspecialchars($item['product_title']); ?>" 
                        class="w-16 h-16 rounded-lg object-cover shadow-sm">
                    <span class="font-medium text-gray-800"><?php echo htmlspecialchars($item['product_title']); ?></span>
                </td>

                <!-- Price -->
                <td class="px-6 py-4 font-semibold text-gray-700">
                    ₵<?php echo number_format($item['product_price'], 2); ?>
                </td>

                <!-- Quantity -->
                <!-- Quantity -->
                <td class="px-6 py-4">
                    <input type="number" name="qty" value="<?php echo $item['qty']; ?>" min="1"
                        class="quantity-input w-16 border rounded-lg p-1 text-center focus:ring-1 focus:ring-indigo-500"
                        data-product-id="<?php echo $item['p_id']; ?>">
                     <button class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600 transition text-sm update-btn"
                             data-product-id="<?php echo $item['p_id']; ?>">
                     Update
                     </button>
                </td>
                <!-- Subtotal -->
                <td class="px-6 py-4 font-semibold text-gray-700 text-center">
                    ₵<?php echo number_format($item['product_price'] * $item['qty'], 2); ?>
                </td>

                <!-- Actions (Remove) -->
                <td class="px-6 py-4 text-center">
                    <button class="text-red-500 hover:text-red-700 font-semibold text-sm transition remove-btn"
                            data-product-id="<?php echo $item['p_id']; ?>">
                    Remove
                    </button>
                </td>
                </tr>

            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-4 bg-gray-100 mt-4 rounded-b-2xl space-y-3 sm:space-y-0">
          <form method="POST" action="../actions/empty_cart_action.php">
            <button type="submit" class="text-gray-600 hover:text-gray-900 font-medium flex items-center space-x-1 transition">
              <span>🗑 Empty Cart</span>
            </button>
          </form>
          <br>

          <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-3">
            <a href="all_product.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">Continue Shopping</a>
            <a href="checkout.php" class="inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">Proceed to Checkout</a>
          </div>
          <div class="text-right sm:ml-auto mt-2 sm:mt-0">
            <p class="text-2xl font-bold text-gray-800">Total: ₵<?php echo number_format($total, 2); ?></p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <script src="../js/cart.js"></script>

</body>
</html>
