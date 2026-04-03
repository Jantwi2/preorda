<?php
session_start();
require_once("../controllers/product_controller.php");
require_once("../controllers/user_controller.php");
require_once("../helpers/encryption.php");

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

if (!$is_vendor_storefront) {
    include 'store_not_found.php';
    exit();
}

$store_qs = isset($_GET['store']) ? '?store=' . htmlspecialchars($_GET['store']) : '';

if (empty($_SESSION['cart'])) {
    header('Location: products.php' . $store_qs);
    exit();
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>Checkout - <?php echo htmlspecialchars($vendor_data['business_name']); ?></title>
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
            --border: #e5e7eb;
            --white: #ffffff;
            --radius-lg: 24px;
            --radius-md: 12px;
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.05);
            --ease: cubic-bezier(0.25, 1, 0.5, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-main);
            background-color: var(--bg-main);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: var(--header-bg);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        nav { max-width: 1400px; margin: 0 auto; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size:1.5rem; font-weight:900; color:var(--white); text-decoration:none; display:flex; align-items:center; gap:12px; }
        .logo img { height:32px; border-radius: 4px; }
        .nav-links { display:flex; gap:2.5rem; list-style:none; }
        .nav-links a { color:rgba(255,255,255,0.7); text-decoration:none; font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; }
        .nav-links a:hover { color:var(--white); }

        .container {
            max-width: 1400px;
            margin: 60px auto;
            padding: 0 40px;
            flex: 1;
        }

        .checkout-title {
            font-size: 3rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -1px;
            margin-bottom: 40px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 60px;
            align-items: start;
        }

        .form-panel {
            background: var(--white);
            padding: 40px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
        }

        .form-section {
            margin-bottom: 40px;
        }
        .form-section:last-child { margin-bottom: 0; }

        .form-section h3 {
            font-size: 1.25rem;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 2px solid var(--text-dark);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: 1 / -1; }

        .form-label {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-gray);
            letter-spacing: 0.5px;
        }

        .form-input {
            padding: 16px;
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            background: var(--bg-main);
            transition: all 0.3s var(--ease);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .payment-method {
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            padding: 20px;
            text-align: center;
            font-weight: 800;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .payment-method.active, .payment-method:hover {
            border-color: var(--text-dark);
            background: var(--text-dark);
            color: var(--white);
        }

        .summary-panel {
            background: #000000;
            color: var(--white);
            padding: 40px;
            border-radius: var(--radius-lg);
            position: sticky;
            top: 120px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        .summary-panel h3 {
            font-size: 1.5rem;
            font-weight: 900;
            text-transform: uppercase;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            padding-bottom: 20px;
            margin-bottom: 30px;
            color: var(--white);
        }

        .summary-items {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 30px;
            padding-right: 15px;
        }

        .summary-items::-webkit-scrollbar { width: 6px; }
        .summary-items::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

        .summary-item {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .item-thumb {
            width: 60px;
            height: 75px;
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius-md);
            object-fit: cover;
        }

        .item-details { display: flex; flex-direction: column; justify-content: center; }
        .item-name { font-weight: 800; font-size: 1rem; line-height: 1.2; margin-bottom: 5px; }
        .item-meta { font-size: 0.85rem; color: rgba(255,255,255,0.6); font-weight: 600; }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
        }

        .summary-row.total {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px dashed rgba(255,255,255,0.2);
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--white);
        }

        .btn-pay {
            width: 100%;
            padding: 20px;
            background: var(--white);
            color: #000000;
            border: none;
            border-radius: 100px;
            font-family: inherit;
            font-size: 1.1rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-pay:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(255,255,255,0.15);
        }

        .btn-pay.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .error-message {
            background: #fef2f2;
            color: #ef4444;
            padding: 15px;
            border-radius: var(--radius-md);
            font-weight: 700;
            margin-bottom: 25px;
            display: none;
            border: 1px solid #fecaca;
        }

        footer { background-color: #111827; color: white; text-align: center; padding: 60px 20px; margin-top: auto; }
        footer h2 { margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -1px; }

        @media (max-width: 1024px) {
            .checkout-grid { grid-template-columns: 1fr; }
            .summary-panel { position: static; margin-bottom: 40px; order: -1; }
        }

        @media (max-width: 600px) {
            .container { padding: 0 20px; margin-top: 30px; }
            .form-grid { grid-template-columns: 1fr; }
            .checkout-title { font-size: 2.2rem; }
            .form-panel { padding: 25px; }
            .payment-methods { grid-template-columns: 1fr; }
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
                <li><a href="cart.php<?php echo $store_qs; ?>">← Back to Cart</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 class="checkout-title">Finalize Order</h1>

        <div class="checkout-grid">
            <div class="form-panel">
                <div id="errorMessage" class="error-message"></div>
                <form id="checkoutForm">
                    <div class="form-section">
                        <h3>Customer Profile</h3>
                        <div class="form-group full">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-input" required placeholder="contact@example.com">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Delivery Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-input" required>
                            </div>
                            <div class="form-group full">
                                <label class="form-label">Street Address</label>
                                <input type="text" class="form-input" required placeholder="Apartment, Studio, or Floor">
                            </div>
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-input" required placeholder="+233...">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Payment Configuration</h3>
                        <div class="payment-methods">
                            <div class="payment-method active" onclick="selectPayment(this)">Mobile Money</div>
                            <div class="payment-method" onclick="selectPayment(this)">Credit Card</div>
                        </div>
                        <div class="form-group full">
                            <label class="form-label">Authorization Token / Number</label>
                            <input type="text" class="form-input" required placeholder="Enter primary payment details">
                        </div>
                    </div>
                </form>
            </div>

            <div class="summary-panel">
                <h3>Order Summary</h3>
                <div class="summary-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="summary-item">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="product" class="item-thumb">
                            <?php else: ?>
                                <div class="item-thumb" style="display:flex;align-items:center;justify-content:center;">📦</div>
                            <?php endif; ?>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-meta">Qty: <?php echo $item['quantity']; ?> × GH₵ <?php echo number_format($item['price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>GH₵ <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>GH₵ <?php echo number_format($shipping, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total Due</span>
                    <span>GH₵ <?php echo number_format($total, 2); ?></span>
                </div>

                <button type="submit" form="checkoutForm" class="btn-pay">Authorize & Pay</button>
            </div>
        </div>
    </div>

    <footer>
        <h2>PREORDA</h2>
    </footer>

    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        function selectPayment(element) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
        }

        const paymentForm = document.getElementById('checkoutForm');
        paymentForm.addEventListener("submit", payWithPaystack, false);

        function payWithPaystack(e) {
            e.preventDefault();

            const email = document.querySelector('input[type="email"]').value;
            const inputs = document.querySelectorAll('input[type="text"]');
            const firstName = inputs[0].value;
            const lastName = inputs[1].value;
            const address = inputs[2].value;
            const city = inputs[3].value;
            const shippingAddress = address + ', ' + city;
            const phone = document.querySelector('input[type="tel"]').value;
            const amount = <?php echo $total * 100; ?>;

            if (!email || !firstName || !lastName) {
                showError('All fields are required to process checkout.');
                return;
            }

            const handler = PaystackPop.setup({
                key: 'pk_test_1e1399c94c952ee54bbacfd2dcc4e1bbbcdd61f8',
                email: email,
                amount: amount,
                currency: 'GHS',
                firstname: firstName,
                lastname: lastName,
                metadata: { custom_fields: [{ display_name: "Mobile Number", variable_name: "mobile_number", value: phone }] },
                callback: function(response) {
                    verifyTransaction(response.reference, email, amount, shippingAddress, firstName, lastName, phone);
                }
            });

            handler.openIframe();
        }

        function showError(msg) {
            const err = document.getElementById('errorMessage');
            err.textContent = msg;
            err.style.display = 'block';
            window.scrollTo({top:0, behavior:'smooth'});
            setTimeout(() => err.style.display = 'none', 5000);
        }

        function verifyTransaction(ref, email, amount, addr, fName, lName, phone) {
            const btn = document.querySelector('.btn-pay');
            btn.classList.add('loading');
            btn.textContent = 'Processing...';

            const fd = new FormData();
            fd.append('reference', ref);
            fd.append('email', email);
            fd.append('amount', amount);
            fd.append('shipping_address', addr);
            fd.append('payment_method', 'paystack');
            fd.append('first_name', fName);
            fd.append('last_name', lName);
            fd.append('phone', phone);
            
            fetch('../actions/process_payment.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'payment_success.php?ref=' + ref + '<?php echo $store_qs ? "&store=" . urlencode($_GET["store"]) : ""; ?>';
                } else {
                    btn.classList.remove('loading');
                    btn.textContent = 'Authorize & Pay';
                    showError('Transaction authorized but system error occurred: ' + data.message);
                }
            })
            .catch(err => {
                btn.classList.remove('loading');
                btn.textContent = 'Authorize & Pay';
                showError('Network error confirming payment.');
            });
        }
        
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/capstone/service-worker.js').catch(e => console.log(e));
            });
        }
    </script>
</body>
</html>
