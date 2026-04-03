<?php
session_start();
require_once("../controllers/product_controller.php");
require_once("../controllers/user_controller.php");
require_once("../helpers/encryption.php");

// Check if this is a vendor-specific storefront
$is_vendor_storefront = false;
$vendor_data = null;

if (isset($_GET['store'])) {
    $encrypted_slug = $_GET['store'];
    $vendor_slug = decrypt_slug($encrypted_slug);
    
    if ($vendor_slug) {
        $vendor_data = get_vendor_by_slug_ctr($vendor_slug);
        if ($vendor_data) {
            $is_vendor_storefront = true;
        }
    }
}

// Enforce store parameter
if (!$is_vendor_storefront) {
    include 'store_not_found.php';
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $store_param = isset($_GET['store']) ? '?store=' . htmlspecialchars($_GET['store']) : '';
        
        switch ($_POST['action']) {
            case 'update':
                $product_id = intval($_POST['product_id']);
                $quantity = intval($_POST['quantity']);
                if ($quantity > 0) {
                    $_SESSION['cart'][$product_id] = $quantity;
                } else {
                    unset($_SESSION['cart'][$product_id]);
                }
                break;
            
            case 'remove':
                $product_id = intval($_POST['product_id']);
                unset($_SESSION['cart'][$product_id]);
                break;
            
            case 'clear':
                $_SESSION['cart'] = [];
                break;
        }
        header('Location: cart.php' . $store_param);
        exit();
    }
}

// Fetch cart products
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $all_products = get_all_products_ctr();
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        foreach ($all_products as $product) {
            if ($product['product_id'] == $product_id) {
                $item = $product;
                $item['quantity'] = $quantity;
                $item['item_total'] = $product['price'] * $quantity;
                $cart_items[] = $item;
                $subtotal += $item['item_total'];
                break;
            }
        }
    }
}

$cart_count = count($_SESSION['cart']);
$shipping = 50;
$total = $subtotal + $shipping;
$store_qs = isset($_GET['store']) ? '?store=' . htmlspecialchars($_GET['store']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <?php if ($is_vendor_storefront && $vendor_data): ?>
    <title><?php echo htmlspecialchars($vendor_data['business_name']); ?> - Exclusive Cart</title>
    <?php else: ?>
    <title>Shopping Cart - PreOrda</title>
    <?php endif; ?>
    <style>
        :root {
            <?php if ($is_vendor_storefront && $vendor_data): ?>
            --primary: <?php echo htmlspecialchars($vendor_data['primary_color'] ?? '#000000'); ?>;
            --secondary: <?php echo htmlspecialchars($vendor_data['secondary_color'] ?? '#333333'); ?>;
            --bg-main: <?php echo htmlspecialchars($vendor_data['background_color'] ?? '#f8f9fa'); ?>;
            --accent: <?php echo htmlspecialchars($vendor_data['accent_color'] ?? '#2563eb'); ?>;
            --header-bg: <?php echo htmlspecialchars($vendor_data['header_color'] ?? '#000000'); ?>;
            --font-main: "<?php echo htmlspecialchars($vendor_data['font_family'] ?? 'Montserrat'); ?>", sans-serif;
            <?php else: ?>
            --primary: #000000;
            --secondary: #333333;
            --bg-main: #f8f9fa;
            --accent: #2563eb;
            --header-bg: #000000;
            --font-main: 'Montserrat', sans-serif;
            <?php endif; ?>
            --text-dark: #111827;
            --text-gray: #4b5563;
            --text-light: #9ca3af;
            --border: #e5e7eb;
            --white: #ffffff;
            --danger: #ef4444;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.025);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 8px;
            --ease: cubic-bezier(0.25, 1, 0.5, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-main);
            color: var(--text-dark);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header (Sleek) */
        header {
            background-color: var(--header-bg);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        nav {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size:1.5rem; font-weight:800; color:var(--white); text-decoration:none; display:flex; align-items:center; gap:12px; letter-spacing: -0.5px; }
        .logo img { height:32px; border-radius: 4px; }
        .nav-links { display:flex; gap:2.5rem; list-style:none; align-items:center; }
        .nav-links a { color:rgba(255,255,255,0.7); text-decoration:none; font-weight:600; font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; transition:all 0.3s ease; }
        .nav-links a:hover { color:var(--white); }
        .cart-icon { position:relative; cursor:pointer; color:var(--white); display: flex; align-items: center; justify-content: center; }
        .cart-badge { position:absolute; top:-8px; right:-8px; background:var(--accent); color:white; border-radius:50%; width:20px; height:20px; font-size:0.7rem; font-weight:800; display:flex; align-items:center; justify-content:center; border:2px solid var(--header-bg); }

        /* Container Layout */
        .page-container {
            max-width: 1400px;
            margin: 60px auto;
            padding: 0 40px;
            flex: 1;
            width: 100%;
        }

        .cart-header {
            margin-bottom: 40px;
        }

        .cart-header h1 {
            font-size: 3rem;
            font-weight: 900;
            color: var(--text-dark);
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        .cart-header p {
            font-size: 1.1rem;
            color: var(--text-gray);
            font-weight: 500;
            margin-top: 5px;
        }

        .cart-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 60px;
            align-items: start;
        }

        /* Items List Detail */
        .cart-items-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            padding: 40px;
            box-shadow: var(--shadow-sm);
        }

        .items-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--text-dark);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .items-header-bar h2 {
            font-size: 1.2rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .clear-btn {
            background: none;
            border: none;
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.2s;
        }

        .clear-btn:hover { color: var(--danger); }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 30px;
            padding-bottom: 30px;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
            align-items: center;
        }

        .cart-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .item-image {
            width: 100px;
            height: 125px;
            background: #f1f5f9;
            border-radius: var(--radius-sm);
            object-fit: cover;
        }

        .item-placeholder {
            width: 100px;
            height: 125px;
            background: #f1f5f9;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-brand {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--text-light);
            letter-spacing: 2px;
        }

        .item-name {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .item-price-unit {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-gray);
        }

        .item-actions {
            display: flex;
            align-items: flex-end;
            flex-direction: column;
            gap: 20px;
        }

        .item-price-total {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--text-dark);
        }

        .qty-wrapper {
            display: flex;
            align-items: center;
            border: 2px solid var(--border);
            border-radius: 100px;
            padding: 5px 15px;
            gap: 15px;
        }

        .qty-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-dark);
            cursor: pointer;
            font-weight: 600;
        }

        .qty-value {
            font-weight: 800;
            font-size: 1.1rem;
            min-width: 20px;
            text-align: center;
        }

        .remove-btn {
            background: none;
            border: none;
            color: var(--danger);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Order Summary Panel */
        .summary-panel {
            background: #000000;
            color: var(--white);
            border-radius: var(--radius-lg);
            padding: 40px;
            position: sticky;
            top: 120px;
            box-shadow: var(--shadow-xl);
        }

        .summary-panel h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 30px;
            text-transform: uppercase;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            padding-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 1rem;
            font-weight: 500;
            color: rgba(255,255,255,0.7);
        }

        .summary-row span:last-child {
            color: var(--white);
            font-weight: 700;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px dashed rgba(255,255,255,0.2);
        }

        .summary-total .label {
            font-size: 1.2rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .summary-total .amount {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--white);
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 20px;
            background: var(--white);
            color: #000000;
            text-align: center;
            text-decoration: none;
            border-radius: 100px;
            font-weight: 800;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 40px;
            transition: all 0.3s var(--ease);
        }

        .checkout-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(255,255,255,0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 100px 0;
        }

        .empty-state h2 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .empty-state p {
            font-size: 1.2rem;
            color: var(--text-gray);
            margin-bottom: 40px;
        }

        .shop-btn {
            display: inline-block;
            padding: 18px 40px;
            background: #000000;
            color: var(--white);
            text-decoration: none;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 100px;
            transition: all 0.3s ease;
        }

        .shop-btn:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .cart-grid { grid-template-columns: 1fr; }
            .summary-panel { position: static; }
        }

        @media (max-width: 600px) {
            .cart-item { grid-template-columns: 80px 1fr; }
            .item-actions { grid-column: 1 / -1; flex-direction: row; justify-content: space-between; align-items: center; }
            .cart-header h1 { font-size: 2.2rem; }
            .page-container { padding: 0 20px; margin: 40px auto; }
            .cart-items-container { padding: 25px; }
            .summary-panel { padding: 30px; }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <?php if ($is_vendor_storefront && !empty($vendor_data['logo_url'])): ?>
                <a href="products.php?store=<?php echo htmlspecialchars($_GET['store']); ?>" class="logo">
                    <img src="<?php echo htmlspecialchars($vendor_data['logo_url']); ?>" alt="<?php echo htmlspecialchars($vendor_data['business_name']); ?>">
                    <span><?php echo htmlspecialchars($vendor_data['business_name']); ?></span>
                </a>
            <?php else: ?>
                <a href="../index.php" class="logo">
                    <img src="../images/logo_white.png" alt="PreOrda Logo" onerror="this.src='../images/logo_c.png'; this.style.filter='invert(1) brightness(2)'">
                    <span>PreOrda</span>
                </a>
            <?php endif; ?>
            <ul class="nav-links">
                <li><a href="products.php<?php echo $store_qs; ?>">Storefront</a></li>
                <li><a href="my_orders.php">Orders</a></li>
                <li>
                    <a href="cart.php<?php echo $store_qs; ?>" style="text-decoration:none;">
                    <div class="cart-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    </div>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="page-container">
        <?php if (empty($cart_items)): ?>
            <div class="empty-state">
                <h2>Cart is Empty</h2>
                <p>Curated selections await. Start adding pieces to your cart.</p>
                <a href="products.php<?php echo $store_qs; ?>" class="shop-btn">Return to Shop</a>
            </div>
        <?php else: ?>
            <div class="cart-header">
                <h1>Your Cart</h1>
                <p><?php echo $cart_count; ?> exclusive pieces reserved</p>
            </div>

            <div class="cart-grid">
                <div class="cart-items-container">
                    <div class="items-header-bar">
                        <h2>Reserved Items</h2>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="clear-btn" onclick="return confirm('Clear all items from your cart?')">
                                Clear Cart
                            </button>
                        </form>
                    </div>

                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="item-placeholder" style="display:none;">📦</div>
                            <?php else: ?>
                                <div class="item-placeholder">📦</div>
                            <?php endif; ?>

                            <div class="item-details">
                                <span class="item-brand"><?php echo htmlspecialchars($item['brand_name'] ?? 'PreOrda'); ?></span>
                                <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="item-price-unit">GH₵ <?php echo number_format($item['price'], 2); ?></div>
                            </div>

                            <div class="item-actions">
                                <div class="item-price-total">GH₵ <?php echo number_format($item['item_total'], 2); ?></div>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <div class="qty-wrapper">
                                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] - 1; ?>" class="qty-btn">−</button>
                                        <span class="qty-value"><?php echo $item['quantity']; ?></span>
                                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="qty-btn">+</button>
                                    </div>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" class="remove-btn">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-panel">
                    <h2>Cart Summary</h2>
                    
                    <div class="summary-row">
                        <span>Items Subtotal</span>
                        <span>GH₵ <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Standard Delivery</span>
                        <span>GH₵ <?php echo number_format($shipping, 2); ?></span>
                    </div>

                    <div class="summary-total">
                        <span class="label">Total</span>
                        <span class="amount">GH₵ <?php echo number_format($total, 2); ?></span>
                    </div>

                    <a href="checkout.php<?php echo $store_qs; ?>" class="checkout-btn">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer style="background-color: #111827; color: white; text-align: center; padding: 60px 20px;">
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -1px;">PREORDA</h2>
        <p style="margin: 10px 0 0 0; font-size: 0.95rem; color: #9ca3af; font-weight: 500;">&copy; <?php echo date('Y'); ?> The Ultimate Pre-Order Marketplace. All rights reserved.</p>
    </footer>
</body>
</html>
