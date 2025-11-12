<?php
require_once '../settings/core.php';
include_once '../controllers/product_controller.php';
include_once '../controllers/category_controller.php';
include_once '../controllers/brand_controller.php';

// Get search keyword
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$brand = $_GET['brand'] ?? '';

// Fetch categories and brands for filter dropdowns
$categories = get_all_categories_public_ctr();
$brands = get_all_brands_public_ctr();

// Fetch search results
$products = search_products_ctr($search, $category, $brand);
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results for "<?= htmlspecialchars($search); ?>"</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #74ebd5, #9face6);
      margin: 0;
      padding: 0;
      color: #333;
    }

    /* Navigation Bar */
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

    .search-container {
      margin-top: 100px;
      text-align: center;
    }

    .search-container h2 {
      color: #333;
    }

    .filter-form {
      margin: 20px auto;
      text-align: center;
    }

    .filter-form select, 
    .filter-form input[type="text"] {
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
      margin: 0 6px;
      font-size: 14px;
    }

    .filter-form button {
      padding: 8px 14px;
      background: #28a745;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .filter-form button:hover {
      background: #218838;
    }

    /* Product Grid */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      padding: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .product-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      padding-bottom: 15px;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .product-card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      border-bottom: 1px solid #eee;
    }

    .product-card h3 {
      margin: 15px 0 5px;
      font-size: 18px;
      color: #333;
    }

    .price {
      color: #007BFF;
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 8px;
    }

    .meta {
      font-size: 14px;
      color: #666;
      margin-bottom: 10px;
    }

    .meta span {
      display: inline-block;
      background: #eef;
      padding: 5px 10px;
      border-radius: 6px;
      margin: 3px;
    }

    .add-cart {
      background: #28a745;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 10px 20px;
      text-decoration: none;
      font-size: 15px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .add-cart:hover {
      background: #218838;
    }

    .no-results {
      text-align: center;
      font-size: 18px;
      color: #555;
      margin-top: 60px;
    }

    /* Pagination */
    .pagination {
      text-align: center;
      margin: 30px 0;
    }

    .pagination a {
      display: inline-block;
      padding: 8px 14px;
      background: #007BFF;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      margin: 0 4px;
      transition: background 0.3s;
    }

    .pagination a:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>

  <nav class="nav">
    <a href="../index.php" class="btn">Home</a>
    <a href="all_product.php" class="btn">All Products</a>
  </nav>

  <div class="search-container">
    <h2>Search Results for "<?= htmlspecialchars($search); ?>"</h2>

    <form action="product_search_result.php" method="get" class="filter-form">
      <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search product...">

      <select name="category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['cat_id']; ?>" <?= $category == $cat['cat_id'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($cat['cat_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="brand">
        <option value="">All Brands</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= $b['brand_id']; ?>" <?= $brand == $b['brand_id'] ? 'selected' : ''; ?>>
            <?= htmlspecialchars($b['brand_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Filter</button>
    </form>
  </div>

  <?php if ($products && count($products) > 0): ?>
    <div class="product-grid">
      <?php foreach ($products as $prod): ?>
        <div class="product-card">
          <img src="../uploads/<?= htmlspecialchars($prod['product_image']); ?>" alt="<?= htmlspecialchars($prod['product_title']); ?>">
          <h3><a href="single_product.php?pid=<?= $prod['product_id']; ?>" style="text-decoration:none; color:#333;">
            <?= htmlspecialchars($prod['product_title']); ?>
          </a></h3>
          <div class="price">₵<?= number_format($prod['product_price'], 2); ?></div>
          <div class="meta">
            <span><?= htmlspecialchars($prod['cat_name']); ?></span>
            <span><?= htmlspecialchars($prod['brand_name']); ?></span>
          </div>
          <a href="#" class="add-cart">🛒 Add to Cart</a>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination Placeholder -->
    <?php if (count($products) > 10): ?>
      <div class="pagination">
        <a href="#">&laquo; Prev</a>
        <a href="#">Next &raquo;</a>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="no-results">
      No products found for "<strong><?= htmlspecialchars($search); ?></strong>"
    </div>
  <?php endif; ?>
</body>
</html>
