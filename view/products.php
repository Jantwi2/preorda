<?php
session_start();
require_once("../controllers/product_controller.php");
require_once("../controllers/user_controller.php");
require_once("../controllers/wishlist_controller.php");
require_once("../helpers/encryption.php");

// Check if this is a vendor-specific storefront
$is_vendor_storefront = false;
$vendor_data = null;
$vendor_id = null;

if (isset($_GET['store'])) {
    $encrypted_slug = $_GET['store'];
    $vendor_slug = decrypt_slug($encrypted_slug);
    
    if ($vendor_slug) {
        $vendor_data = get_vendor_by_slug_ctr($vendor_slug);
        if ($vendor_data) {
            $is_vendor_storefront = true;
            $vendor_id = $vendor_data['vendor_id'];
        }
    }
}

// Enforce store parameter
if (!$is_vendor_storefront) {
    include 'store_not_found.php';
    exit();
}

// Fetch data based on storefront type
if ($is_vendor_storefront && $vendor_id) {
    $products = get_vendor_products_ctr($vendor_id);
} else {
    // Should not reach here due to check above, but for safety
    include 'store_not_found.php';
    exit();
}

// Fetch vendor phone number for WhatsApp
$vendor_phone = '';
if ($vendor_data && isset($vendor_data['user_id'])) {
    require_once('../settings/db_class.php');
    $db = new db_connection();
    $uid = intval($vendor_data['user_id']);
    $phone_result = $db->db_fetch_one("SELECT phone FROM users WHERE user_id = '$uid' LIMIT 1");
    if ($phone_result && !empty($phone_result['phone'])) {
        // Strip non-numeric except leading +
        $vendor_phone = preg_replace('/[^0-9]/', '', $phone_result['phone']);
    }
}

$categories = get_all_categories_ctr();
$brands = get_all_brands_ctr();

// Get cart count
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Fetch user wishlist if logged in
$user_wishlist_ids = [];
if (isset($_SESSION['user_id'])) {
    $user_wishlist_ids = get_user_wishlist_ids_ctr($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <?php if ($is_vendor_storefront && $vendor_data): ?>
    <title><?php echo htmlspecialchars($vendor_data['business_name']); ?> - PreOrda</title>
    <?php else: ?>
    <title>Discover Products - PreOrda</title>
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
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-glow: 0 0 30px rgba(37, 99, 235, 0.3);
            --radius-lg: 20px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --ease: cubic-bezier(0.25, 1, 0.5, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-main);
            color: var(--text-dark);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        /* Header (Dark & Sleek) */
        header {
            background-color: var(--header-bg);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        nav {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }
        
        .logo img {
            height: 32px;
            border-radius: 4px;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .nav-links a.active, .nav-links a:hover {
            color: var(--white);
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s var(--ease);
        }

        .cart-icon:hover {
            transform: translateY(-2px);
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--accent);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #000;
        }

        /* Hero Section - Sleek & High Impact */
        .hero {
            position: relative;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            padding: 120px 40px 160px;
            text-align: center;
            overflow: hidden;
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
            z-index: 1;
        }

        /* Ambient grid behind hero */
        .hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
            opacity: 0.5;
        }

        .hero h1 {
            font-size: 5rem;
            font-weight: 900;
            color: var(--white);
            letter-spacing: -0.04em;
            line-height: 1;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .hero p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.7);
            max-width: 600px;
            margin: 0 auto;
            font-weight: 400;
        }

        /* Floating Search Bar */
        .search-container {
            max-width: 800px;
            margin: -40px auto 60px; /* Pull it up into the hero */
            position: relative;
            z-index: 10;
            padding: 0 20px;
        }

        .search-input-wrapper {
            display: flex;
            align-items: center;
            background: var(--white);
            border-radius: 100px;
            padding: 10px 15px 10px 30px;
            box-shadow: var(--shadow-lg), var(--shadow-glow);
            transition: all 0.4s var(--ease);
        }

        .search-input-wrapper:focus-within {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25), 0 0 40px rgba(37, 99, 235, 0.4);
        }

        .search-icon {
            color: var(--accent);
            margin-right: 15px;
        }

        .search-input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 1.1rem;
            font-weight: 500;
            font-family: inherit;
            color: var(--text-dark);
            padding: 15px 0;
            outline: none;
        }

        .search-input::placeholder {
            color: var(--text-light);
            font-weight: 400;
        }

        .sort-select {
            appearance: none;
            background: var(--bg-main);
            border: 1px solid var(--border);
            padding: 12px 24px;
            border-radius: 100px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
            cursor: pointer;
            outline: none;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .sort-select:hover {
            background: #e5e7eb;
        }

        /* Main Content Layout */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 40px 100px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 60px;
        }

        /* Sidebar Filters */
        .sidebar {
            position: sticky;
            top: 120px;
            height: fit-content;
            background: var(--white);
            padding: 30px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .filter-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border);
        }

        .filter-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .filter-section h3 {
            font-size: 0.9rem;
            margin-bottom: 15px;
            color: var(--text-dark);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filter-option {
            margin-bottom: 12px;
        }

        .filter-option label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-gray);
            transition: color 0.2s ease;
        }
        
        .filter-option label:hover {
            color: var(--primary);
        }

        .filter-option input[type="checkbox"] {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--accent);
            border-radius: 4px;
        }

        /* Price Filter Inputs */
        .price-range {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .price-range input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            background: var(--bg-main);
        }
        
        .price-range input:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--white);
        }

        .clear-filters {
            width: 100%;
            padding: 15px;
            background: #111827;
            color: var(--white);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 700;
            margin-top: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .clear-filters:hover {
            background-color: var(--accent);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Product Grid Layering */
        .results-meta {
            margin-bottom: 30px;
            font-size: 1rem;
            color: var(--text-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
        }
        
        .results-meta strong {
            color: var(--text-dark);
            font-weight: 800;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 40px;
            row-gap: 50px;
        }

        /* The Bold Card Structure */
        .product-card {
            display: flex;
            flex-direction: column;
            cursor: pointer;
            background: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            overflow: hidden;
            transition: all 0.4s var(--ease);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .product-image-container {
            position: relative;
            width: 100%;
            aspect-ratio: 4/5;
            background: #f1f5f9;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s var(--ease);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        /* Hover Overlay UI */
        .product-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            opacity: 0;
            transition: all 0.3s var(--ease);
            z-index: 2;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .btn-reserve {
            background: var(--accent);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: 100px;
            font-size: 0.95rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            transform: translateY(20px);
        }

        .product-card:hover .btn-reserve {
            transform: translateY(0);
        }

        .btn-reserve:hover {
            background: #1d4ed8;
            transform: translateY(-2px) !important;
        }

        .btn-wa {
            width: 46px;
            height: 46px;
            background: #25D366;
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            transform: translateY(20px);
        }

        .product-card:hover .btn-wa {
            transform: translateY(0);
        }

        .btn-wa:hover {
            background: #128C7E;
            transform: scale(1.1) !important;
        }

        /* Wishlist Floating Button */
        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--white);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 3;
            color: var(--text-gray);
        }

        .wishlist-btn:hover {
            transform: scale(1.1);
            color: #ef4444;
        }

        .wishlist-btn.active {
            color: #ef4444;
        }

        /* Product Details Text */
        .product-info {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .product-brand {
            font-size: 0.8rem;
            color: var(--text-gray);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .product-category {
            font-size: 0.75rem;
            color: var(--accent);
            background: rgba(37, 99, 235, 0.1);
            padding: 4px 10px;
            border-radius: 100px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .product-name {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 15px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--text-dark);
            margin-top: auto;
        }

        .delivery-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: var(--text-gray);
            font-weight: 600;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        /* No Results UI */
        .no-results {
            text-align: center;
            padding: 120px 20px;
            grid-column: 1 / -1;
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 2px dashed var(--border);
        }
        
        .no-results-icon {
            font-size: 3.5rem;
            margin-bottom: 24px;
            opacity: 0.4;
        }

        .no-results h3 {
            font-size: 2rem;
            margin-bottom: 12px;
            color: var(--text-dark);
            font-weight: 800;
        }

        .no-results p {
            color: var(--text-gray);
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            }
        }

        @media (max-width: 1024px) {
            .hero h1 {
                font-size: 4rem;
            }
            .container {
                grid-template-columns: 240px 1fr;
            }
        }

        @media (max-width: 800px) {
            .hero h1 {
                font-size: 3rem;
            }
            .container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                position: static;
                margin-bottom: 40px;
            }
        }

        @media (max-width: 600px) {
            .hero {
                padding: 100px 20px 120px;
            }
            .search-input-wrapper {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
                border-radius: 16px;
            }
            .sort-select {
                width: 100%;
            }
            .nav-links {
                display: none;
            }
            .product-overlay {
                opacity: 1; /* Always show buttons on mobile */
                background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
                align-items: flex-end;
            }
            .btn-reserve, .btn-wa {
                transform: translateY(0);
            }
        }
        
        /* Staggered Animation Class */
        .fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            opacity: 0;
            transform: translateY(40px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- PWA Setup -->
    <link rel="manifest" href="/capstone/manifest.json">
    <meta name="theme-color" content="#000000">
    <link rel="apple-touch-icon" href="/capstone/images/logo_c.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>
<body>
    <!-- Header -->
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
                <li><a href="products.php" class="active">Storefront</a></li>
                <li><a href="wishlist.php">Wishlist</a></li>
                <li><a href="my_orders.php">Orders</a></li>
                <li>
                    <a href="cart.php" style="text-decoration:none;">
                    <div class="cart-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span class="cart-badge" id="cartCount"><?php echo $cart_count; ?></span>
                    </div>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <?php if ($is_vendor_storefront && $vendor_data): ?>
            <h1><?php echo htmlspecialchars($vendor_data['business_name']); ?></h1>
            <p><?php echo htmlspecialchars($vendor_data['tagline'] ?? 'Curated selections for the modern lifestyle. Pre-order exclusive pieces today.'); ?></p>
        <?php else: ?>
            <h1>Curated Excellence</h1>
            <p>Discover independent brands and reserve limited-edition releases before they disappear.</p>
        <?php endif; ?>
    </section>

    <!-- Search / Filter overlap -->
    <div class="search-container">
        <div class="search-input-wrapper">
            <svg class="search-icon" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" class="search-input" id="searchInput" placeholder="Search premium products or brands...">
            
            <select class="sort-select" id="sortSelect">
                <option value="newest">Latest Arrivals</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
                <option value="name-asc">Alphabetical</option>
            </select>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Sidebar Filters -->
        <aside class="sidebar">
            <div class="filter-section">
                <h3>Categories</h3>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="filter-option">
                            <label>
                                <input type="checkbox" class="category-filter" value="<?php echo htmlspecialchars($cat['name']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size:0.9rem; color:var(--text-light)">No categories</p>
                <?php endif; ?>
            </div>

            <div class="filter-section">
                <h3>Brands</h3>
                <?php if (!empty($brands)): ?>
                    <?php foreach ($brands as $brand): ?>
                        <div class="filter-option">
                            <label>
                                <input type="checkbox" class="brand-filter" value="<?php echo htmlspecialchars($brand['name']); ?>">
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="filter-section">
                <h3>Price (GH₵)</h3>
                <div class="price-range">
                    <input type="number" id="minPrice" placeholder="Min" min="0">
                    <input type="number" id="maxPrice" placeholder="Max" min="0">
                </div>
            </div>

            <button class="clear-filters" id="clearFilters">Reset Filters</button>
        </aside>

        <!-- Product View -->
        <main class="main-content">
            <div class="results-meta">
                <span>Displaying <strong id="resultCount">0</strong> products</span>
            </div>

            <div class="product-grid" id="productGrid">
                <!-- Products dynamically inserted here -->
            </div>

            <!-- No Results -->
            <div class="no-results" id="noResults" style="display: none;">
                <div class="no-results-icon">
                    <svg width="80" height="80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3>No Items Found</h3>
                <p>Adjust your search criteria or reset filters to explore our catalog.</p>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer style="background-color: #111827; color: white; text-align: center; padding: 60px 20px;">
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -1px;">PREORDA</h2>
        <p style="margin: 10px 0 0 0; font-size: 0.95rem; color: #9ca3af; font-weight: 500;">&copy; <?php echo date('Y'); ?> The Ultimate Pre-Order Marketplace. All rights reserved.</p>
    </footer>

    <script>
        const products = <?php echo json_encode($products); ?>;
        const currentStore = "<?php echo isset($_GET['store']) ? addslashes(htmlspecialchars($_GET['store'])) : ''; ?>";
        const vendorPhone = "<?php echo $vendor_phone; ?>";
        const vendorName = "<?php echo isset($vendor_data['business_name']) ? addslashes($vendor_data['business_name']) : ''; ?>";
        
        let filteredProducts = [...products];
        let wishlist = <?php echo json_encode($user_wishlist_ids); ?>;
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        document.addEventListener('DOMContentLoaded', () => {
            renderProducts(filteredProducts);
            updateResultCount();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.querySelectorAll('.category-filter, .brand-filter').forEach(cb => {
                cb.addEventListener('change', applyFilters);
            });
            document.getElementById('minPrice').addEventListener('input', applyFilters);
            document.getElementById('maxPrice').addEventListener('input', applyFilters);
            document.getElementById('searchInput').addEventListener('input', applyFilters);
            document.getElementById('sortSelect').addEventListener('change', applyFilters);
            
            document.getElementById('clearFilters').addEventListener('click', () => {
                document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                document.getElementById('minPrice').value = '';
                document.getElementById('maxPrice').value = '';
                document.getElementById('searchInput').value = '';
                document.getElementById('sortSelect').value = 'newest';
                applyFilters();
            });
        }

        function applyFilters() {
            const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(cb => cb.value.toLowerCase());
            const selectedBrands = Array.from(document.querySelectorAll('.brand-filter:checked')).map(cb => cb.value.toLowerCase());
            const minPrice = parseFloat(document.getElementById('minPrice').value) || 0;
            const maxPrice = parseFloat(document.getElementById('maxPrice').value) || Infinity;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const sortValue = document.getElementById('sortSelect').value;

            filteredProducts = products.filter(product => {
                const cat = (product.category_name || '').toLowerCase();
                const brand = (product.brand_name || '').toLowerCase();
                const name = (product.name || '').toLowerCase();
                const desc = (product.description || '').toLowerCase();
                const price = parseFloat(product.price);

                const matchesCat = selectedCategories.length === 0 || selectedCategories.includes(cat);
                const matchesBrand = selectedBrands.length === 0 || selectedBrands.includes(brand);
                const matchesPrice = price >= minPrice && price <= maxPrice;
                const matchesSearch = name.includes(searchTerm) || desc.includes(searchTerm) || brand.includes(searchTerm);
                
                return matchesCat && matchesBrand && matchesPrice && matchesSearch;
            });

            sortProducts(sortValue);
            renderProducts(filteredProducts);
            updateResultCount();
        }

        function sortProducts(sortValue) {
            filteredProducts.sort((a, b) => {
                if (sortValue === 'price-low') return parseFloat(a.price) - parseFloat(b.price);
                if (sortValue === 'price-high') return parseFloat(b.price) - parseFloat(a.price);
                if (sortValue === 'name-asc') return a.name.localeCompare(b.name);
                if (sortValue === 'newest') return new Date(b.created_at) - new Date(a.created_at);
                return 0;
            });
        }

        function renderProducts(productsToRender) {
            const grid = document.getElementById('productGrid');
            const noResults = document.getElementById('noResults');

            if (productsToRender.length === 0) {
                grid.innerHTML = '';
                noResults.style.display = 'block';
                return;
            }

            noResults.style.display = 'none';
            grid.innerHTML = ''; 

            productsToRender.forEach((product, index) => {
                const imgHtml = product.image_url 
                    ? `<img src="${product.image_url}" alt="${product.name}" class="product-image" loading="lazy">`
                    : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;background:#f3f4f6;color:#cbd5e1;">📦</div>`;

                let productUrl = `productdetails.php?id=${product.product_id}`;
                if (currentStore) productUrl += `&store=${currentStore}`;

                const isWishlisted = wishlist.includes(parseInt(product.product_id));
                const heartPath = isWishlisted 
                    ? `<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />`
                    : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>`;

                const delay = (index * 0.05) + 's';

                const cardHtml = `
                    <div class="product-card fade-in-up" style="animation-delay: ${delay}" onclick="window.location.href='${productUrl}'">
                        <div class="product-image-container">
                            ${imgHtml}
                            <button class="wishlist-btn ${isWishlisted ? 'active' : ''}" onclick="event.stopPropagation(); toggleWishlist(${product.product_id}, this)" title="Add to Wishlist">
                                <svg width="20" height="20" ${isWishlisted ? 'fill="currentColor"' : 'fill="none" stroke="currentColor"'} viewBox="0 0 24 24">
                                    ${heartPath}
                                </svg>
                            </button>
                            
                            <div class="product-overlay">
                                <button class="btn-reserve" onclick="event.stopPropagation(); addToCart(${product.product_id})">
                                    Add to Cart
                                </button>
                                ${vendorPhone ? `
                                <a href="${buildWhatsAppLink(product)}" class="btn-wa" onclick="event.stopPropagation()" target="_blank" title="Enquire via WhatsApp">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.570-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </a>` : ''}
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <div class="product-meta">
                                <span class="product-brand">${product.brand_name || ''}</span>
                                <span class="product-category">${product.category_name || 'General'}</span>
                            </div>
                            <h3 class="product-name">${product.name}</h3>
                            <div class="product-price">GH₵ ${(parseFloat(product.price)).toLocaleString()}</div>
                            <div class="delivery-pill">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Est. ${product.estimated_delivery_time || '3-5'} days
                            </div>
                        </div>
                    </div>
                `;
                grid.insertAdjacentHTML('beforeend', cardHtml);
            });
        }

        function buildWhatsAppLink(product) {
            const price = parseFloat(product.price).toLocaleString();
            const msg = `Hi ${vendorName}! I am interested in *${product.name}* (GH₵ ${price}). Could you provide more details?`;
            return `https://wa.me/${vendorPhone}?text=${encodeURIComponent(msg)}`;
        }

        function updateResultCount() {
            document.getElementById('resultCount').textContent = filteredProducts.length;
        }

        async function toggleWishlist(id, btn) {
            if (!isLoggedIn) {
                alert('Please sign in to save pieces to your wishlist.');
                window.location.href = 'login.html';
                return;
            }

            try {
                const response = await fetch('../actions/toggle_wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: id })
                });
                const data = await response.json();
                
                if (data.success) {
                    btn.classList.toggle('active');
                    if (data.action === 'added') {
                        wishlist.push(parseInt(id));
                        btn.innerHTML = `<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" /></svg>`;
                    } else {
                        wishlist = wishlist.filter(itemId => itemId !== parseInt(id));
                        btn.innerHTML = `<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>`;
                    }
                } else {
                    alert(data.message || 'Error updating wishlist');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Connection error. Please try again.');
            }
        }

        function addToCart(productId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../actions/add_to_cart.php';
            
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="product_id" value="${productId}">`);
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="quantity" value="1">`);
            
            if (currentStore) {
                form.insertAdjacentHTML('beforeend', `<input type="hidden" name="store" value="${currentStore}">`);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/capstone/service-worker.js')
                    .catch(e => console.log('SW reg failed: ', e));
            });
        }
    </script>
</body>
</html>