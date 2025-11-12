<?php
// ...existing code...
require_once '../settings/core.php';

// remove these lines (don't include action endpoints):
// require_once '../actions/fetch_category_action.php';
// require_once '../actions/fetch_brand_action.php';

// include controllers and get arrays server-side
include_once '../controllers/category_controller.php';
include_once '../controllers/brand_controller.php';

$user_id = $_SESSION['customer_id'] ?? null;
if (!$user_id || !isLoggedIn() || !isAdmin()) {
    header("Location: ../view/login.php");
    exit();
}

$categories = get_all_cat_ctr($user_id) ?: [];
$brands  = get_all_brands_ctr($user_id) ?: [];



?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Product Management</title>
  <style>
    /* lightweight styles (you can move to app.css) */
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:#f6f8fb; color:#222; margin:0; padding:0; }
    header { background:#004aad; color:#fff; padding:1rem; text-align:center; margin: 4rem}
    .container { max-width:1100px; margin:2rem auto; padding:0 1rem; }
    .card { background:white; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.08); padding:1.25rem; }
    form { display:grid; grid-template-columns: 1fr 1fr; gap:1rem; }
    form .full { grid-column: 1 / -1; }
    label { display:block; font-weight:600; margin-bottom:0.35rem; font-size:0.95rem; color:#333; }
    input[type="text"], input[type="number"], select, textarea { width:100%; padding:0.6rem; border:1px solid #dcdfe6; border-radius:6px; font-size:0.95rem; }
    textarea { min-height:100px; resize:vertical; }
    .actions { display:flex; gap:0.5rem; align-items:center; }
    .btn { background:#004aad; color:white; padding:0.6rem 0.9rem; border-radius:6px; border:none; cursor:pointer; }
    .btn.ghost { background:#f0f2f7; color:#333; }
    .products-grid { margin-top:1rem; display:grid; grid-template-columns: repeat(auto-fill, minmax(250px,1fr)); gap:1rem; }
    .product-tile { background:#fff; padding:1rem; border-radius:8px; border:1px solid #eef2f6; display:flex; flex-direction:column; gap:0.5rem; }
    .product-tile img { width:100%; height:160px; object-fit:cover; border-radius:6px; }
    @media (max-width:800px) { form { grid-template-columns: 1fr; } }
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
  </style>
</head>
<body>
    <nav class="nav">
        <?php if (isAdmin()): ?>
        <a href="../actions/logout.php" class="btn">Logout</a>
        <a href="brand.php" class="btn">Brand</a>
        <a href="category.php" class="btn">Category</a>
        <?php elseif (!isLoggedIn()): ?>
        <a href="view/register.php" class="btn">Register</a>
        <a href="view/login.php" class="btn">Login</a>
        <?php else: ?>
        <a href="actions/logout.php" class="btn">Logout</a>
        <?php endif; ?>
  </nav>
  <header><h1>Product Management</h1></header>
  <div class="container">
    <div class="card">
      <h2>Add / Edit Product</h2>
      <form id="productForm" enctype="multipart/form-data">
        <input type="hidden" name="product_id" id="product_id" value="">
        <div>
          <label for="cat_id">Category</label>
          <select id="cat_id" name="cat_id" required>
            <option value="">-- Select category --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?=htmlspecialchars($c['cat_id'])?>"><?=htmlspecialchars($c['cat_name'])?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="brand_id">Brand</label>
          <select id="brand_id" name="brand_id" required>
            <option value="">-- Select brand --</option>
            <?php foreach ($brands as $b): ?>
              <option value="<?=htmlspecialchars($b['brand_id'])?>" data-cat="<?=htmlspecialchars($b['cat_id'])?>"><?=htmlspecialchars($b['brand_name'])?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="title">Product Title</label>
          <input id="title" name="title" type="text" required>
        </div>
        <div>
          <label for="price">Price (GHS)</label>
          <input id="price" name="price" type="number" step="0.01" min="0" required>
        </div>

        <div class="full">
          <label for="keyword">Keyword</label>
          <input id="keyword" name="keyword" type="text">
        </div>

        <div class="full">
          <label for="description">Description</label>
          <textarea id="description" name="description"></textarea>
        </div>

        <div>
          <label for="image">Upload Image (single)</label>
          <input id="image" name="image" type="file" accept="image/*">
        </div>
        <div>
          <label for="images">Bulk Upload Images (multiple)</label>
          <input id="images" name="images[]" type="file" accept="image/*" multiple>
        </div>

        <input type="hidden" id="image_path" name="image_path">
        <input type="hidden" id="image_paths" name="image_paths"> <!-- JSON string -->

        <div class="full actions">
          <button class="btn" type="submit" id="saveBtn">Save Product</button>
          <button class="btn ghost" type="button" id="resetBtn">Reset</button>
        </div>
      </form>
    </div>

    <div class="card" style="margin-top:1rem;">
      <h2>Your Products</h2>
      <div id="productsContainer" class="products-grid">Loading...</div>
    </div>
  </div>

  <script src="../js/product.js"></script>
</body>
</html>
