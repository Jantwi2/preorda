<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../settings/core.php';
include_once '../controllers/product_controller.php';

// Validate product_id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Product ID");
}

$product_id = $_GET['id'];
$product = get_product_by_id_ctr($product_id);

if (!$product) {
    die("Product not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['product_title']); ?> | Product Details</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #74ebd5, #9face6);
      margin: 0;
      padding: 0;
      color: #333;
    }

    .nav {
      width: 100%;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);
      padding: 16px 0;
      position: fixed;
      top: 0;
      left: 0;
      text-align: center;
      z-index: 100;
    }
    .nav .btn {
      display: inline-block;
      padding: 10px 20px;
      margin: 0 8px;
      border: none;
      border-radius: 6px;
      background-color: #007BFF;
      color: white;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
      text-decoration: none;
    }
    .nav .btn:hover {
      background-color: #0056b3;
      transform: translateY(-2px);
    }

    .container {
      max-width: 900px;
      margin: 120px auto;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      overflow: hidden;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
    }

    .image-container {
      flex: 1 1 45%;
      text-align: center;
      padding: 20px;
      background: #f9f9f9;
    }

    .image-container img {
      width: 100%;
      max-width: 400px;
      border-radius: 12px;
      transition: transform 0.3s ease;
    }

    .image-container img:hover {
      transform: scale(1.05);
    }

    .details {
      flex: 1 1 55%;
      padding: 30px;
    }

    .details h1 {
      font-size: 28px;
      color: #333;
      margin-bottom: 10px;
    }

    .price {
      font-size: 22px;
      color: #007BFF;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .meta {
      margin-bottom: 15px;
      font-size: 15px;
      color: #666;
    }

    .meta span {
      display: inline-block;
      margin-right: 15px;
      background: #eef;
      padding: 6px 12px;
      border-radius: 6px;
    }

    .description {
      font-size: 16px;
      color: #555;
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .add-cart {
      display: inline-block;
      padding: 12px 24px;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      background: #28a745;
      color: #fff;
      cursor: pointer;
      transition: background 0.3s ease;
      text-decoration: none;
    }

    .add-cart:hover {
      background: #218838;
    }

    .keywords {
      margin-top: 20px;
      font-size: 14px;
      color: #777;
    }

    .keywords span {
      background: #f1f1f1;
      padding: 6px 10px;
      border-radius: 5px;
      margin-right: 6px;
      display: inline-block;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        margin: 100px 20px;
      }
      .details {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <nav class="nav">
    <a href="../index.php" class="btn">Home</a>
    <a href="all_product.php" class="btn">All Products</a>
    <a href="#" class="btn">Cart</a>
  </nav>

  <div class="container">
    <div class="image-container">
      <img src="../uploads/<?= htmlspecialchars($product['product_image']); ?>" alt="<?= htmlspecialchars($product['product_title']); ?>">
    </div>

    <div class="details">
      <h1><?= htmlspecialchars($product['product_title']); ?></h1>
      <div class="price">₵<?= number_format($product['product_price'], 2); ?></div>

      <div class="meta">
        <span><strong>Category:</strong> <?= htmlspecialchars($product['category_name']); ?></span>
        <span><strong>Brand:</strong> <?= htmlspecialchars($product['brand_name']); ?></span>
      </div>

      <p class="description"><?= nl2br(htmlspecialchars($product['product_desc'])); ?></p>

      <a href="#" class="add-cart">🛒 Add to Cart</a>

      <div class="keywords">
        <strong>Keywords:</strong>
        <?php 
          $keywords = explode(',', $product['product_keywords']);
          foreach ($keywords as $kw): ?>
            <span><?= htmlspecialchars(trim($kw)); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</body>
</html>
