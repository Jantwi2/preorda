<?php
require_once("../settings/core.php");

// Get tracking parameter
$reference = $_GET['ref'] ?? '';
$order_id  = $_GET['order_id'] ?? '';

// Fetch order details
$order = null;
if ($order_id) {
    require_once("../classes/order_class.php");
    $order_obj = new order_class();
    $order = $order_obj->db_fetch_one("SELECT * FROM orders WHERE order_id = '$order_id'");
} elseif ($reference) {
    require_once("../classes/payment_class.php");
    $payment_obj = new Payment();
    $payment = $payment_obj->db_fetch_one("SELECT * FROM payments WHERE transaction_id LIKE '%$reference%' LIMIT 1");
    if ($payment) {
        require_once("../classes/order_class.php");
        $order_obj = new order_class();
        $order = $order_obj->db_fetch_one("SELECT * FROM orders WHERE order_id = '{$payment['order_id']}'");
    }
}

// Build timeline steps
$statuses = ['pending', 'confirmed', 'shipped', 'delivered'];
$status_index = $order ? array_search($order['status'], $statuses) : -1;

$timeline_steps = [
    ['icon' => '📋', 'label' => 'Order Placed',     'desc' => 'Your order has been received.'],
    ['icon' => '✅', 'label' => 'Order Confirmed',   'desc' => 'Vendor has confirmed your order.'],
    ['icon' => '🚚', 'label' => 'Shipped',           'desc' => 'Your order is on the way.'],
    ['icon' => '🎉', 'label' => 'Delivered',         'desc' => 'Successfully delivered!'],
];

$status_colors = [
    'pending'   => ['bg' => '#fef3c7', 'color' => '#92400e'],
    'confirmed' => ['bg' => '#dbeafe', 'color' => '#1e40af'],
    'shipped'   => ['bg' => '#e0e7ff', 'color' => '#4338ca'],
    'delivered' => ['bg' => '#dcfce7', 'color' => '#166534'],
    'cancelled' => ['bg' => '#fee2e2', 'color' => '#991b1b'],
];
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order - PreOrda</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #1a202c;
            --bg-main: #f8f9fa;
            --accent: #3498db;
            --font-main: 'Outfit', sans-serif;
            --text-dark: #1a202c;
            --text-gray: #718096;
            --border: #e2e8f0;
            --white: #ffffff;
            --success: #16a34a;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
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
        nav { max-width:1400px; margin:0 auto; padding:0 30px; display:flex; justify-content:space-between; align-items:center; }
        .logo { font-size:1.5rem; font-weight:700; color:var(--primary); text-decoration:none; display:flex; align-items:center; gap:10px; transition:transform 0.2s; }
        .logo:hover { transform:scale(1.02); }
        .logo img { height:40px; }
        .nav-links { display:flex; gap:2.5rem; list-style:none; align-items:center; }
        .nav-links a { color:var(--text-dark); text-decoration:none; font-weight:500; font-size:0.95rem; position:relative; transition:var(--transition); }
        .nav-links a::after { content:''; position:absolute; width:0; height:2px; bottom:-4px; left:0; background-color:var(--accent); transition:width 0.3s; }
        .nav-links a:hover::after { width:100%; }
        .nav-links a:hover { color:var(--accent); }
        .cart-icon { position:relative; cursor:pointer; color:var(--text-dark); padding:8px; border-radius:50%; transition:var(--transition); }
        .cart-icon:hover { color:var(--accent); background:rgba(0,0,0,0.03); }
        .cart-badge { position:absolute; top:0; right:0; background:#e53e3e; color:white; border-radius:50%; width:18px; height:18px; font-size:0.7rem; font-weight:700; display:flex; align-items:center; justify-content:center; border:2px solid white; }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 70px 0;
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
        .hero-inner { position:relative; }
        .hero h1 { font-size:2.8rem; font-weight:800; letter-spacing:-0.02em; margin-bottom:10px; }
        .hero p { font-size:1.1rem; opacity:0.85; font-weight:300; }

        /* ── Container ── */
        .container { max-width:860px; margin:0 auto; padding:50px 30px 80px; flex:1; width:100%; }

        /* ── Search Box ── */
        .search-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            padding: 35px 40px;
            margin-bottom: 35px;
        }
        .search-card h2 { font-size:1.1rem; font-weight:700; color:var(--secondary); margin-bottom:18px; }
        .search-row { display:flex; gap:12px; }
        .search-row input {
            flex: 1;
            padding: 14px 20px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-family: inherit;
            transition: var(--transition);
            background: var(--bg-main);
            color: var(--text-dark);
        }
        .search-row input:focus { outline:none; border-color:var(--primary); background:var(--white); box-shadow:0 0 0 3px rgba(44,62,80,0.1); }
        .search-row input::placeholder { color: var(--text-gray); }
        .track-btn {
            padding: 14px 32px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(44,62,80,0.2);
        }
        .track-btn:hover { background:var(--secondary); transform:translateY(-2px); box-shadow:0 8px 20px rgba(44,62,80,0.25); }

        /* ── Result Card ── */
        .result-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        /* Order Header */
        .result-header {
            padding: 28px 35px;
            background: linear-gradient(to right, var(--bg-main), var(--white));
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .order-number { font-size: 1.4rem; font-weight: 800; color: var(--secondary); }
        .order-number span { font-weight: 400; color: var(--text-gray); font-size: 1rem; }
        .status-pill {
            display: inline-block;
            padding: 8px 22px;
            border-radius: 9999px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        /* Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0;
            border-bottom: 1px solid var(--border);
        }
        .detail-cell {
            padding: 22px 35px;
            border-right: 1px solid var(--border);
        }
        .detail-cell:last-child { border-right: none; }
        .detail-cell-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-gray); font-weight: 600; margin-bottom: 6px; }
        .detail-cell-value { font-size: 1rem; font-weight: 700; color: var(--secondary); }

        /* ── Timeline ── */
        .timeline-section { padding: 35px 35px 40px; }
        .timeline-section h3 { font-size: 1rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-gray); margin-bottom: 35px; }

        .stepper {
            display: flex;
            justify-content: space-between;
            position: relative;
        }
        /* connector line */
        .stepper::before {
            content: '';
            position: absolute;
            top: 28px;
            left: calc(28px / 2);
            right: calc(28px / 2);
            height: 2px;
            background: var(--border);
            z-index: 0;
        }
        .stepper-fill {
            position: absolute;
            top: 28px;
            left: calc(28px / 2);
            height: 2px;
            background: var(--success);
            z-index: 1;
            transition: width 0.8s ease;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            flex: 1;
            position: relative;
            z-index: 2;
        }
        .step-dot {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            border: 3px solid var(--border);
            background: var(--white);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .step.done .step-dot {
            background: var(--success);
            border-color: var(--success);
            box-shadow: 0 0 0 6px rgba(22,163,74,0.15);
        }
        .step.active .step-dot {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 0 6px rgba(44,62,80,0.12);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(44,62,80,0.3); }
            70%  { box-shadow: 0 0 0 12px rgba(44,62,80,0); }
            100% { box-shadow: 0 0 0 0 rgba(44,62,80,0); }
        }
        .step-label { font-size: 0.85rem; font-weight: 700; color: var(--text-gray); text-align: center; line-height: 1.3; max-width: 80px; }
        .step.done .step-label, .step.active .step-label { color: var(--secondary); }
        .step-desc { font-size: 0.75rem; color: var(--text-gray); text-align: center; max-width: 90px; display: none; }
        .step.done .step-desc, .step.active .step-desc { display: block; }

        /* ── Empty / Not Found ── */
        .empty-state {
            text-align: center;
            padding: 80px 30px;
        }
        .empty-state .empty-icon { font-size: 4.5rem; display: block; margin-bottom: 20px; opacity: 0.5; }
        .empty-state h3 { font-size: 1.6rem; color: var(--secondary); margin-bottom: 10px; font-weight: 700; }
        .empty-state p { color: var(--text-gray); font-size: 1rem; }

        /* ── Footer ── */
        footer { background-color: var(--secondary); color: white; text-align: center; padding: 25px 20px; margin-top: auto; }
        footer p { margin: 0; font-size: 0.9rem; opacity: 0.85; }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .search-row { flex-direction: column; }
            .hero h1 { font-size: 2rem; }
            .result-header { flex-direction: column; align-items: flex-start; }
            .detail-cell { border-right: none; border-bottom: 1px solid var(--border); padding: 18px 25px; }
            .detail-cell:last-child { border-bottom: none; }
            .timeline-section { padding: 25px 20px 30px; }
            .search-card { padding: 25px; }
            .stepper { gap: 5px; }
            .step-dot { width: 44px; height: 44px; font-size: 1.1rem; }
        }
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
                <li><a href="my_orders.php" style="color: var(--accent);">My Orders</a></li>
                <li>
                    <div class="cart-icon">
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
            <h1>📦 Track Your Order</h1>
            <p>Enter your Order ID or payment reference to see live status.</p>
        </div>
    </div>

    <div class="container">

        <!-- Search -->
        <div class="search-card">
            <h2>Enter Tracking Info</h2>
            <div class="search-row">
                <input type="text" id="trackingInput"
                       placeholder="Order ID or Payment Reference…"
                       value="<?php echo htmlspecialchars($reference ?: $order_id); ?>">
                <button class="track-btn" onclick="trackOrder()">Track Order</button>
            </div>
        </div>

        <?php if ($order): ?>
            <?php
                $sc = $status_colors[$order['status']] ?? $status_colors['pending'];
                // Calculate filled width percentage
                $fill_pct = $status_index >= 0 ? ($status_index / (count($statuses) - 1)) * 100 : 0;
            ?>
            <div class="result-card">
                <!-- Order Header -->
                <div class="result-header">
                    <div class="order-number">
                        <span>Order</span> #<?php echo $order['order_id']; ?>
                    </div>
                    <div class="status-pill" style="background:<?php echo $sc['bg']; ?>; color:<?php echo $sc['color']; ?>;">
                        <?php echo ucfirst($order['status']); ?>
                    </div>
                </div>

                <!-- Details -->
                <div class="details-grid">
                    <div class="detail-cell">
                        <div class="detail-cell-label">Order Date</div>
                        <div class="detail-cell-value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                    </div>
                    <div class="detail-cell">
                        <div class="detail-cell-label">Total Amount</div>
                        <div class="detail-cell-value">GH₵ <?php echo number_format($order['total_price'], 2); ?></div>
                    </div>
                    <div class="detail-cell">
                        <div class="detail-cell-label">Shipping Address</div>
                        <div class="detail-cell-value"><?php echo htmlspecialchars($order['shipping_address'] ?? 'N/A'); ?></div>
                    </div>
                    <?php if (!empty($order['tracking_number'])): ?>
                    <div class="detail-cell">
                        <div class="detail-cell-label">Tracking Number</div>
                        <div class="detail-cell-value"><?php echo htmlspecialchars($order['tracking_number']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Timeline -->
                <div class="timeline-section">
                    <h3>Order Progress</h3>
                    <div class="stepper" id="stepper">
                        <div class="stepper-fill" id="stepperFill" style="width: 0%;"></div>
                        <?php foreach ($timeline_steps as $i => $step): ?>
                            <?php
                                $state = '';
                                if ($i < $status_index) $state = 'done';
                                elseif ($i === $status_index) $state = 'active';
                            ?>
                            <div class="step <?php echo $state; ?>">
                                <div class="step-dot">
                                    <?php if ($state === 'done'): ?>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?php else: ?>
                                        <?php echo $step['icon']; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="step-label"><?php echo $step['label']; ?></div>
                                <div class="step-desc"><?php echo $step['desc']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
                // Animate the progress fill
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(() => {
                        document.getElementById('stepperFill').style.width = '<?php echo $fill_pct; ?>%';
                    }, 200);
                });
            </script>

        <?php elseif ($reference || $order_id): ?>
            <!-- Order not found -->
            <div class="result-card">
                <div class="empty-state">
                    <span class="empty-icon">🔍</span>
                    <h3>No Order Found</h3>
                    <p>We couldn't find an order matching <strong><?php echo htmlspecialchars($reference ?: $order_id); ?></strong>.<br>Please double-check and try again.</p>
                </div>
            </div>

        <?php else: ?>
            <!-- Idle state -->
            <div class="result-card">
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    <h3>Enter your Order ID above</h3>
                    <p>You'll find your Order ID in your confirmation email or on the My Orders page.</p>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <footer>
        <p>Powered by <strong>PreOrda</strong> &nbsp;·&nbsp; &copy; <?php echo date('Y'); ?> All rights reserved.</p>
    </footer>

    <script>
        function trackOrder() {
            const value = document.getElementById('trackingInput').value.trim();
            if (value) {
                window.location.href = 'track.php?ref=' + encodeURIComponent(value);
            }
        }
        document.getElementById('trackingInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') trackOrder();
        });
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
