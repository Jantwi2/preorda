<?php
session_start();
require_once '../settings/core.php';

// Optionally, you can get an order reference if available
$order_id = $_GET['order_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex items-center justify-center min-h-screen p-6">
    <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full text-center">
        <h1 class="text-3xl font-bold text-red-600 mb-4">❌ Payment Failed</h1>
        <p class="text-gray-700 mb-4">Unfortunately, your payment could not be processed.</p>

        <?php if ($order_id): ?>
            <p class="text-gray-600 mb-4"><span class="font-semibold">Order Reference:</span> <?php echo htmlspecialchars($order_id); ?></p>
        <?php endif; ?>

        <p class="text-gray-700 mb-6">Please try again or contact support if the problem persists.</p>

        <a href="../checkout.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
            Retry Payment
        </a>
    </div>
</div>

</body>
</html>
