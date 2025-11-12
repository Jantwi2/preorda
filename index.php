<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'settings/core.php';
include_once 'controllers/category_controller.php';
include_once 'controllers/brand_controller.php';

// Fetch all categories and brands for filters
$categories = get_all_categories_public_ctr();
$brands = get_all_brands_public_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #74ebd5, #9face6);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
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
    .nav .btn:active {
      transform: translateY(0);
    }

    .search-box {
      display: inline-block;
      margin: 0 10px;
    }

    .search-box input[type="text"] {
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
      width: 200px;
    }

    .search-box button {
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      background: #28a745;
      color: #fff;
      cursor: pointer;
    }

    .search-box button:hover {
      background: #218838;
    }

    .filter-box {
      display: inline-block;
      margin-left: 15px;
    }

    select {
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .card {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      text-align: center;
      width: 300px;
      margin-top: 120px;
    }

    h1 {
      margin-bottom: 10px;
      color: #333;
    }

    p {
      margin-bottom: 20px;
      color: #555;
    }
  </style>
</head>
<body>
  <nav class="nav">
    <a href="index.php" class="btn">Home</a>
    <a href="view/all_product.php" class="btn">All Products</a>

    <!-- Search Box -->
    <form id="search-form" class="search-box">
      <input type="text" id="search-input" name="search" placeholder="Search product..." required>
      <button type="submit">Search</button>
    </form>


    <!-- Filters -->
    <div class="filter-box">
      <form action="view/all_product.php" method="get" style="display:inline;">
        <select name="category" onchange="this.form.submit()">
          <option value="">Filter by Category</option>
          <?php if ($categories): foreach ($categories as $cat): ?>
            <option value="<?= $cat['cat_id']; ?>"><?= htmlspecialchars($cat['cat_name']); ?></option>
          <?php endforeach; endif; ?>
        </select>
      </form>

      <form action="view/all_product.php" method="get" style="display:inline;">
        <select name="brand" onchange="this.form.submit()">
          <option value="">Filter by Brand</option>
          <?php if ($brands): foreach ($brands as $brand): ?>
            <option value="<?= $brand['brand_id']; ?>"><?= htmlspecialchars($brand['brand_name']); ?></option>
          <?php endforeach; endif; ?>
        </select>
      </form>
    </div>

    <?php if (isAdmin()): ?>
      <a href="actions/logout.php" class="btn">Logout</a>
      <a href="admin/category.php" class="btn">Category</a>
      <a href="admin/brand.php" class="btn">Brand</a>
      <a href="admin/product.php" class="btn">Product</a>
    <?php elseif (!isLoggedIn()): ?>
      <a href="view/register.php" class="btn">Register</a>
      <a href="view/login.php" class="btn">Login</a>
    <?php else: ?>
      <a href="actions/logout.php" class="btn">Logout</a>
    <?php endif; ?>
  </nav>

  <div class="card">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Guest'); ?>!</h1>
  </div>
  <div id="search-results" style="margin-top: 20px; width: 80%; max-width: 1000px;"></div>
  <script>
document.addEventListener("DOMContentLoaded", function() {
  const form = document.getElementById("search-form");
  const input = document.getElementById("search-input");
  const resultsDiv = document.getElementById("search-results");

  form.addEventListener("submit", async function(e) {
    e.preventDefault(); // stop page reload
    const query = input.value.trim();
    if (!query) return;

    // show a loading message
    resultsDiv.innerHTML = "<p>Searching...</p>";

    try {
      // send AJAX request to backend
      const response = await fetch(`view/product_search_result.php?q=${encodeURIComponent(query)}`);
      const html = await response.text();

      // inject search result HTML into the page
      resultsDiv.innerHTML = html;
      window.scrollTo({ top: resultsDiv.offsetTop, behavior: "smooth" });
    } catch (error) {
      console.error(error);
      resultsDiv.innerHTML = "<p style='color:red;'>Error fetching results. Please try again.</p>";
    }
  });
});
</script>

</body>
</html>
