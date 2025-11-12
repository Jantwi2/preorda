<?php
include_once("../controllers/product_controller.php");
include_once("../settings/db_class.php");

// --- CONFIG ---
$limit = 10; // products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// --- FILTERS ---
$selected_cat = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : null;
$selected_brand = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : null;

// --- DATA FETCH ---
$products = [];
if ($search_query) {
    $products = search_products_ctr($search_query);
} elseif ($selected_cat) {
    $products = filter_products_by_category_ctr($selected_cat);
} elseif ($selected_brand) {
    $products = filter_products_by_brand_ctr($selected_brand);
} else {
    $products = get_all_products_ctr();
}

// Pagination slice (manual pagination since controller returns full list)
$total_products = count($products);
$total_pages = ceil($total_products / $limit);
$products = array_slice($products, $start, $limit);

// --- CATEGORY & BRAND DROPDOWNS ---
$db = new db_connection();
$cats = $db->db_fetch_all("SELECT * FROM categories ORDER BY cat_name ASC");
$brands = $db->db_fetch_all("SELECT * FROM brands ORDER BY brand_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Products</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #fafafa;
            margin: 0;
            padding: 0;
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
            width: 90%;
            margin: 5rem auto;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .filters {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        select, input[type="text"], button {
            padding: 0.6rem;
            font-size: 14px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
            padding: 1rem;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-bottom: 1px solid #eee;
        }

        .product-title {
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }

        .product-meta {
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .price {
            font-size: 16px;
            color: #27ae60;
            margin: 5px 0;
        }

        .btn-cart {
            display: inline-block;
            background: #ff7a00;
            color: #fff;
            text-decoration: none;
            padding: 0.6rem 1rem;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .btn-cart:hover {
            background: #e56c00;
        }

        .pagination {
            margin-top: 2rem;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 12px;
            background: #eee;
            color: #333;
            border-radius: 4px;
            text-decoration: none;
        }

        .pagination a.active {
            background: #333;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container">
      <nav class="nav">
        <a href="../index.php" class="btn">Home</a>
        <a href="cart.php" class="btn">Cart</a>
    </nav>
    <h1>All Products</h1>

    <!-- Filters -->
    <form method="GET" class="filters">
        <select name="cat_id">
            <option value="">-- Filter by Category --</option>
            <?php foreach ($cats as $cat): ?>
                <option value="<?= $cat['cat_id'] ?>" <?= $selected_cat == $cat['cat_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['cat_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="brand_id">
            <option value="">-- Filter by Brand --</option>
            <?php foreach ($brands as $brand): ?>
                <option value="<?= $brand['brand_id'] ?>" <?= $selected_brand == $brand['brand_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($brand['brand_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="search" placeholder="Search product..." value="<?= htmlspecialchars($search_query ?? '') ?>">
        <button type="submit">Apply</button>
        <a href="all_product.php" style="padding: 0.6rem; background: #ccc; text-decoration:none; color:#000; border-radius:4px;">Reset</a>
    </form>

    <!-- Product List -->
    <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $prod): ?>
                <div class="product-card">
                    <a href="single_product.php?id=<?= $prod['product_id'] ?>">
                        <img src="<?= htmlspecialchars($prod['product_image']) ?>" alt="<?= htmlspecialchars($prod['product_title']) ?>">
                    </a>
                    <div class="product-title"><?= htmlspecialchars($prod['product_title']) ?></div>
                    <div class="price">GHS <?= number_format($prod['product_price'], 2) ?></div>
                    <div class="product-meta">Category: <?= htmlspecialchars($prod['category_name'] ?? 'N/A') ?></div>
                    <div class="product-meta">Brand: <?= htmlspecialchars($prod['brand_name'] ?? 'N/A') ?></div>

                    <!-- Add to Cart Form -->
                    <form action="../actions/add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $prod['product_id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-cart">Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">No products found.</p>
        <?php endif; ?>
    </div>


    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?><?= $selected_cat ? "&cat_id=$selected_cat" : '' ?><?= $selected_brand ? "&brand_id=$selected_brand" : '' ?><?= $search_query ? "&search=" . urlencode($search_query) : '' ?>"
                   class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
