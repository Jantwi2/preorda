<?php
session_start();
require_once("../controllers/wishlist_controller.php");

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?redirect=wishlist");
    exit();
}

$user_id = $_SESSION['user_id'];
$wishlist_items = get_user_wishlist_ctr($user_id);
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - PreOrda</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #1a202c;
            --bg-main: #f8f9fa;
            --accent: #e53e3e;
            --font-main: 'Outfit', sans-serif;
            --text-dark: #1a202c;
            --text-gray: #718096;
            --border: #e2e8f0;
            --white: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --radius-lg: 20px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* ── Header ── */
        header {
            background-color: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(226,232,240,0.8);
        }
        nav {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size:1.5rem; font-weight:700; color:var(--primary); text-decoration:none; display:flex; align-items:center; gap:10px; transition:transform 0.2s; }
        .logo:hover { transform: scale(1.02); }
        .logo img { height:40px; }
        
        .nav-links { display:flex; gap:2.5rem; list-style:none; align-items:center; }
        .nav-links a { color:var(--text-dark); text-decoration:none; font-weight:500; font-size:0.95rem; position:relative; transition:var(--transition); }
        .nav-links a::after { content:''; position:absolute; width:0; height:2px; bottom:-4px; left:0; background-color:var(--accent); transition:width 0.3s; }
        .nav-links a:hover::after { width:100%; }
        .nav-links a:hover { color:var(--accent); }
        .nav-links a.active { color: var(--accent); }
        .nav-links a.active::after { width: 100%; }
        
        .cart-icon { position:relative; cursor:pointer; color:var(--text-dark); padding:8px; border-radius:50%; transition:var(--transition); }
        .cart-icon:hover { background-color:rgba(0,0,0,0.03); color:var(--primary); }
        .cart-badge { position:absolute; top:0; right:0; background:#e53e3e; color:white; border-radius:50%; width:18px; height:18px; font-size:0.7rem; font-weight:700; display:flex; align-items:center; justify-content:center; border:2px solid var(--white); }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle cx="1" cy="1" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }
        .hero-inner { position: relative; }
        .hero h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 10px; }
        .hero p { font-size: 1.1rem; opacity: 0.85; font-weight: 300; }

        /* ── Layout ── */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 30px 80px;
            flex: 1;
        }

        /* ── Wishlist Grid ── */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--border);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            cursor: pointer;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }

        .product-image-wrapper {
            position: relative;
            width: 100%;
            padding-top: 100%; /* 1:1 Aspect Ratio */
            background: #f1f5f9;
        }
        .product-image {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
        }
        .no-image-placeholder {
            position: absolute; top:0; left:0; right:0; bottom:0;
            display: flex; align-items: center; justify-content: center;
            font-size: 3rem; color: #cbd5e0;
        }

        .remove-btn {
            position: absolute;
            top: 12px; right: 12px;
            width: 36px; height: 36px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: none;
            transition: var(--transition);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            color: var(--accent);
            z-index: 10;
        }
        .remove-btn:hover { background: var(--accent); color: white; transform: rotate(90deg); }

        .product-info { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .product-meta { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .product-category { color: var(--primary); }
        .product-brand { color: var(--text-gray); }
        .product-name { font-size: 1.1rem; font-weight: 700; color: var(--secondary); margin-bottom: 8px; line-height: 1.3; }
        
        .vendor-info { font-size: 0.85rem; color: var(--text-gray); margin-bottom: 12px; display: flex; align-items: center; gap: 5px; }

        .price-row { margin-top: auto; display: flex; align-items: center; justify-content: space-between; font-weight: 800; color: var(--text-dark); font-size: 1.25rem; }

        /* ── Empty State ── */
        .empty-wishlist {
            text-align: center;
            padding: 80px 20px;
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px dashed var(--border);
            box-shadow: var(--shadow-sm);
        }
        .empty-icon { font-size: 5rem; display: block; margin-bottom: 20px; opacity: 0.4; }
        .empty-wishlist h2 { font-size: 2rem; color: var(--secondary); margin-bottom: 12px; }
        .empty-wishlist p { color: var(--text-gray); font-size: 1.05rem; margin-bottom: 30px; }
        .btn-shop {
            display: inline-block;
            padding: 14px 35px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(44,62,80,0.2);
        }
        .btn-shop:hover { background: var(--secondary); transform: translateY(-2px); }

        /* ── Footer ── */
        footer { background-color: var(--secondary); color: white; text-align: center; padding: 25px 20px; margin-top: auto; }
        footer p { margin: 0; font-size: 0.9rem; opacity: 0.85; }
    </style>

    <!-- PWA Setup -->
    <link rel="manifest" href="/capstone/manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <link rel="apple-touch-icon" href="/capstone/images/logo_c.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <a href="../index.php" class="logo">
                <img src="../images/logo_c.png" alt="PreOrda Logo">
            </a>
            <ul class="nav-links">
                <li><a href="products.php">Products</a></li>
                <li><a href="wishlist.php" class="active">Wishlist</a></li>
                <li><a href="my_orders.php">My Orders</a></li>
                <li>
                    <div class="cart-icon" onclick="window.location.href='cart.php'">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    </div>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Hero -->
    <div class="hero">
        <div class="hero-inner">
            <h1>❤️ My Wishlist</h1>
            <p><?php echo count($wishlist_items); ?> saved item<?php echo count($wishlist_items) !== 1 ? 's' : ''; ?></p>
        </div>
    </div>

    <div class="container">
        <?php if (empty($wishlist_items)): ?>
            <div class="empty-wishlist">
                <span class="empty-icon">💔</span>
                <h2>Your wishlist is empty</h2>
                <p>Find something you love and save it for later!</p>
                <a href="products.php" class="btn-shop">Explore Products</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid" id="wishlistGrid">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="product-card" id="wishlist-item-<?php echo $item['product_id']; ?>" onclick="window.location.href='productdetails.php?id=<?php echo $item['product_id']; ?>'">
                        <div class="product-image-wrapper">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-image">
                            <?php else: ?>
                                <div class="no-image-placeholder">📦</div>
                            <?php endif; ?>
                            
                            <button class="remove-btn" onclick="event.stopPropagation(); removeWishlist(<?php echo $item['product_id']; ?>)" title="Remove from Wishlist">
                                ✕
                            </button>
                        </div>
                        <div class="product-info">
                            <div class="product-meta">
                                <span class="product-category"><?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?></span>
                                <span class="product-brand"><?php echo htmlspecialchars($item['brand_name'] ?? ''); ?></span>
                            </div>
                            <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="vendor-info">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <?php echo htmlspecialchars($item['business_name'] ?? 'PreOrda Global'); ?>
                            </div>
                            <div class="price-row">
                                GH₵ <?php echo number_format($item['price'], 2); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>Powered by <strong>PreOrda</strong> &nbsp;·&nbsp; &copy; <?php echo date('Y'); ?> All rights reserved.</p>
    </footer>

    <script>
        async function removeWishlist(id) {
            if (!confirm('Remove this item from your wishlist?')) return;

            try {
                const response = await fetch('../actions/toggle_wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: id })
                });
                
                const data = await response.json();
                if (data.success) {
                    // Remove from DOM
                    const card = document.getElementById(`wishlist-item-${id}`);
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        // Check if empty
                        const grid = document.getElementById('wishlistGrid');
                        if (grid && grid.children.length === 0) {
                            location.reload(); // Reload to show empty state
                        }
                    }, 300);
                } else {
                    alert(data.message || 'Could not remove item.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Connection error. Please try again.');
            }
        }
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/capstone/service-worker.js')
                    .then(reg => console.log('PreOrda Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed: ', err));
            });
        }
    </script>
</body>
</html>
