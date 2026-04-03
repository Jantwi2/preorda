<?php
session_start();
require_once("../controllers/product_controller.php");
require_once("../controllers/user_controller.php");
require_once("../helpers/encryption.php");

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

if (!$is_vendor_storefront) {
    include 'store_not_found.php';
    exit();
}

if ($is_vendor_storefront && $vendor_id) {
    $products = get_vendor_products_ctr($vendor_id);
} else {
    include 'store_not_found.php';
    exit();
}

$categories = get_all_categories_ctr();
$brands = get_all_brands_ctr();
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Vendor theme vars with defaults
$v_primary   = htmlspecialchars($vendor_data['primary_color']    ?? '#0c0c0c');
$v_secondary = htmlspecialchars($vendor_data['secondary_color']  ?? '#1a1a1a');
$v_bg        = htmlspecialchars($vendor_data['background_color'] ?? '#faf8f5');
$v_accent    = htmlspecialchars($vendor_data['accent_color']     ?? '#0d9e8a');
$v_font      = htmlspecialchars($vendor_data['font_family']      ?? 'DM Sans');
$v_name      = htmlspecialchars($vendor_data['business_name']    ?? 'Store');
$v_tagline   = htmlspecialchars($vendor_data['tagline']          ?? 'Exclusive pre-order collection');
$v_logo      = htmlspecialchars($vendor_data['logo_url']         ?? '');
$v_wa        = htmlspecialchars($vendor_data['whatsapp_number']  ?? '');
$store_param = htmlspecialchars($_GET['store']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $v_name; ?> — PreOrda</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500&family=<?php echo urlencode($v_font); ?>:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    /* ── THEME: driven by vendor customisation ── */
    :root {
      --v-primary:   <?php echo $v_primary; ?>;
      --v-secondary: <?php echo $v_secondary; ?>;
      --v-bg:        <?php echo $v_bg; ?>;
      --v-accent:    <?php echo $v_accent; ?>;
      --v-font:      '<?php echo $v_font; ?>', 'DM Sans', sans-serif;

      /* System tokens (independent of vendor theme) */
      --ink:    #0c0c0c;
      --paper:  #f5f2ee;
      --cream:  #faf8f5;
      --sand:   #e8e2d9;
      --muted:  #6b6560;
      --wa:     #25d366;
      --wa-dk:  #128c5e;
      --r-sm:   8px;
      --r-md:   14px;
      --r-lg:   20px;
      --r-xl:   28px;
    }

    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }

    body {
      font-family: var(--v-font);
      background: var(--v-bg);
      color: var(--ink);
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    /* noise overlay */
    body::before {
      content: '';
      position: fixed; inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
      pointer-events: none; z-index: 9999; opacity: .5;
    }

    /* ══════════════════════════
       NAV
    ══════════════════════════ */
    .nav {
      position: sticky; top: 0; z-index: 200;
      background: rgba(250,248,245,0.88);
      backdrop-filter: blur(20px) saturate(1.4);
      -webkit-backdrop-filter: blur(20px) saturate(1.4);
      border-bottom: 1px solid transparent;
      transition: border-color .3s, box-shadow .3s;
      padding: 0 2.5rem;
      height: 68px;
      display: flex; align-items: center; justify-content: space-between;
    }

    .nav.scrolled {
      border-color: var(--sand);
      box-shadow: 0 2px 20px rgba(0,0,0,0.05);
    }

    .nav-logo {
      display: flex; align-items: center; gap: .6rem;
      text-decoration: none;
      font-family: 'Instrument Serif', serif;
      font-size: 1.45rem;
      color: var(--ink);
      letter-spacing: -.02em;
    }

    .nav-logo img {
      height: 36px; width: auto;
      object-fit: contain;
    }

    .nav-logo-text { line-height: 1; }

    .nav-right {
      display: flex; align-items: center; gap: 1.8rem;
    }

    .nav-link {
      font-family: 'Syne', sans-serif;
      font-size: .78rem; font-weight: 600;
      letter-spacing: .07em; text-transform: uppercase;
      color: var(--muted);
      text-decoration: none;
      transition: color .2s;
    }

    .nav-link:hover, .nav-link.active { color: var(--v-primary); }

    .cart-btn {
      position: relative;
      background: var(--v-primary);
      border: none; cursor: pointer;
      width: 42px; height: 42px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: white;
      transition: background .2s, transform .2s;
    }

    .cart-btn:hover { background: var(--v-accent); transform: scale(1.06); }

    .cart-badge {
      position: absolute; top: -3px; right: -3px;
      background: #ef4444; color: white;
      width: 18px; height: 18px;
      border-radius: 50%;
      font-size: .65rem; font-weight: 800;
      display: flex; align-items: center; justify-content: center;
      border: 2px solid var(--v-bg);
      font-family: 'Syne', sans-serif;
    }

    /* WA sticky */
    .wa-sticky {
      position: fixed; bottom: 1.8rem; right: 1.8rem; z-index: 300;
      background: var(--wa);
      border-radius: 50px;
      padding: .7rem 1.1rem .7rem .9rem;
      display: flex; align-items: center; gap: .55rem;
      box-shadow: 0 8px 28px rgba(37,211,102,.4);
      text-decoration: none;
      cursor: pointer;
      border: none;
      transition: transform .25s, box-shadow .25s;
      animation: waBounce .6s 1.5s cubic-bezier(0.34,1.56,0.64,1) both;
    }

    @keyframes waBounce {
      from { opacity: 0; transform: scale(.4) translateY(30px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .wa-sticky:hover {
      transform: translateY(-3px) scale(1.04);
      box-shadow: 0 12px 36px rgba(37,211,102,.5);
    }

    .wa-sticky svg { flex-shrink: 0; }
    .wa-label {
      font-family: 'Syne', sans-serif;
      font-size: .78rem; font-weight: 700;
      color: white; white-space: nowrap;
    }

    /* ══════════════════════════
       STORE HERO
    ══════════════════════════ */
    .store-hero {
      background: var(--v-primary);
      color: white;
      padding: 4rem 2.5rem 3rem;
      position: relative;
      overflow: hidden;
    }

    .store-hero::before {
      content: '';
      position: absolute; inset: 0;
      background:
        radial-gradient(ellipse 60% 80% at 80% -10%, rgba(255,255,255,.07) 0%, transparent 60%),
        radial-gradient(ellipse 40% 60% at -10% 110%, rgba(255,255,255,.05) 0%, transparent 60%);
      pointer-events: none;
    }

    /* dot grid */
    .store-hero::after {
      content: '';
      position: absolute; inset: 0;
      background-image: radial-gradient(circle, rgba(255,255,255,.12) 1px, transparent 1px);
      background-size: 28px 28px;
      pointer-events: none;
      opacity: .4;
    }

    .hero-inner {
      max-width: 1320px; margin: 0 auto;
      display: flex; align-items: flex-end;
      justify-content: space-between; gap: 2rem;
      position: relative; z-index: 2;
    }

    .hero-left { flex: 1; }

    .hero-store-badge {
      display: inline-flex; align-items: center; gap: .4rem;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 100px;
      padding: .35rem .9rem;
      font-family: 'Syne', sans-serif;
      font-size: .68rem; font-weight: 700;
      letter-spacing: .1em; text-transform: uppercase;
      margin-bottom: 1.2rem;
      animation: fadeUp .6s ease both;
    }

    .hero-store-badge::before {
      content: '';
      width: 6px; height: 6px;
      background: var(--wa);
      border-radius: 50%;
      animation: pulse 2s ease infinite;
    }

    @keyframes pulse {
      0%,100% { opacity:1; transform:scale(1); }
      50%      { opacity:.5; transform:scale(1.6); }
    }

    .hero-title {
      font-family: 'Instrument Serif', serif;
      font-size: clamp(2.4rem, 5vw, 4.5rem);
      line-height: 1.02;
      letter-spacing: -.03em;
      margin-bottom: 1rem;
      animation: fadeUp .6s .08s ease both;
    }

    .hero-tagline {
      font-size: 1.05rem;
      opacity: .75;
      font-weight: 300;
      max-width: 480px;
      line-height: 1.7;
      animation: fadeUp .6s .16s ease both;
    }

    .hero-right {
      display: flex; gap: 2.5rem;
      flex-shrink: 0;
      animation: fadeUp .6s .24s ease both;
    }

    .hero-stat { text-align: right; }

    .hero-stat-val {
      font-family: 'Instrument Serif', serif;
      font-size: 2.2rem;
      line-height: 1;
      opacity: .95;
    }

    .hero-stat-label {
      font-size: .72rem;
      opacity: .5;
      font-family: 'Syne', sans-serif;
      font-weight: 600;
      letter-spacing: .06em;
      text-transform: uppercase;
      margin-top: .15rem;
    }

    @keyframes fadeUp {
      from { opacity:0; transform:translateY(22px); }
      to   { opacity:1; transform:translateY(0); }
    }

    /* ══════════════════════════
       MAIN LAYOUT
    ══════════════════════════ */
    .page-body {
      max-width: 1320px; margin: 0 auto;
      padding: 2.5rem 2.5rem 5rem;
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 3rem;
    }

    /* ══════════════════════════
       SIDEBAR
    ══════════════════════════ */
    .sidebar {
      position: sticky; top: 88px;
      height: fit-content;
    }

    .sidebar-card {
      background: white;
      border-radius: var(--r-xl);
      border: 1.5px solid var(--sand);
      overflow: hidden;
    }

    .sidebar-head {
      padding: 1.2rem 1.4rem;
      border-bottom: 1px solid var(--sand);
      display: flex; justify-content: space-between; align-items: center;
    }

    .sidebar-head-title {
      font-family: 'Syne', sans-serif;
      font-size: .72rem; font-weight: 800;
      letter-spacing: .1em; text-transform: uppercase;
      color: var(--muted);
    }

    .clear-all-btn {
      background: none; border: none; cursor: pointer;
      font-family: 'Syne', sans-serif;
      font-size: .7rem; font-weight: 700;
      color: var(--v-accent);
      letter-spacing: .04em;
      transition: opacity .2s;
    }

    .clear-all-btn:hover { opacity: .7; }

    .filter-group {
      padding: 1.2rem 1.4rem;
      border-bottom: 1px solid var(--sand);
    }

    .filter-group:last-child { border-bottom: none; }

    .filter-group-title {
      font-family: 'Syne', sans-serif;
      font-size: .68rem; font-weight: 800;
      letter-spacing: .1em; text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 1rem;
    }

    .filter-opts {
      display: flex; flex-direction: column; gap: .5rem;
    }

    .filter-opt label {
      display: flex; align-items: center; gap: .7rem;
      cursor: pointer;
      font-size: .88rem;
      color: var(--ink);
      padding: .3rem .4rem;
      border-radius: var(--r-sm);
      transition: background .15s;
    }

    .filter-opt label:hover { background: var(--paper); }

    .filter-opt input[type="checkbox"] {
      width: 16px; height: 16px;
      accent-color: var(--v-primary);
      border-radius: 4px;
      cursor: pointer;
      flex-shrink: 0;
    }

    .filter-count {
      margin-left: auto;
      font-family: 'Syne', sans-serif;
      font-size: .65rem; font-weight: 700;
      color: var(--muted);
      background: var(--paper);
      padding: .1rem .45rem;
      border-radius: 20px;
    }

    .price-inputs {
      display: grid; grid-template-columns: 1fr auto 1fr; gap: .5rem;
      align-items: center;
    }

    .price-inp {
      width: 100%;
      padding: .6rem .8rem;
      border: 1.5px solid var(--sand);
      border-radius: var(--r-sm);
      font-family: var(--v-font);
      font-size: .88rem;
      color: var(--ink);
      background: var(--paper);
      outline: none;
      transition: border-color .2s;
    }

    .price-inp:focus { border-color: var(--v-primary); background: white; }
    .price-sep { color: var(--muted); font-size: .8rem; text-align: center; }

    /* ══════════════════════════
       CONTENT AREA
    ══════════════════════════ */
    .content-area {}

    /* controls bar */
    .controls {
      display: flex; align-items: center; gap: 1rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }

    .search-box {
      flex: 1; min-width: 220px;
      position: relative;
    }

    .search-box svg {
      position: absolute; left: .9rem; top: 50%;
      transform: translateY(-50%);
      color: var(--muted); pointer-events: none;
    }

    .search-inp {
      width: 100%;
      padding: .78rem .9rem .78rem 2.6rem;
      border: 1.5px solid var(--sand);
      border-radius: 100px;
      font-family: var(--v-font);
      font-size: .9rem;
      color: var(--ink);
      background: white;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .search-inp::placeholder { color: #bbb; }
    .search-inp:focus {
      border-color: var(--v-primary);
      box-shadow: 0 0 0 3px rgba(0,0,0,.05);
    }

    .sort-sel {
      padding: .78rem 2.2rem .78rem 1rem;
      border: 1.5px solid var(--sand);
      border-radius: 100px;
      font-family: 'Syne', sans-serif;
      font-size: .78rem; font-weight: 600;
      color: var(--ink);
      background: white;
      outline: none; cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='7' viewBox='0 0 10 7'%3E%3Cpath fill='%236b6560' d='M1 1l4 4 4-4'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right .8rem center;
      transition: border-color .2s;
    }

    .sort-sel:focus { border-color: var(--v-primary); }

    .result-pill {
      padding: .5rem 1rem;
      background: var(--paper);
      border-radius: 100px;
      font-family: 'Syne', sans-serif;
      font-size: .72rem; font-weight: 700;
      color: var(--muted);
      white-space: nowrap;
      letter-spacing: .04em;
    }

    .result-pill strong { color: var(--ink); }

    /* ── VIEW TOGGLE ── */
    .view-toggle {
      display: flex;
      background: var(--paper);
      border-radius: var(--r-sm);
      padding: 3px; gap: 2px;
    }

    .view-btn {
      background: none; border: none; cursor: pointer;
      width: 32px; height: 32px;
      border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      color: var(--muted);
      transition: background .2s, color .2s;
    }

    .view-btn.active {
      background: white;
      color: var(--ink);
      box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }

    /* ══════════════════════════
       PRODUCT GRID
    ══════════════════════════ */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 1.5rem;
    }

    .product-grid.list-view {
      grid-template-columns: 1fr;
    }

    /* ── PRODUCT CARD ── */
    .product-card {
      background: white;
      border-radius: var(--r-xl);
      border: 1.5px solid var(--sand);
      overflow: hidden;
      display: flex; flex-direction: column;
      cursor: pointer;
      transition: transform .3s cubic-bezier(.2,0,.2,1), box-shadow .3s, border-color .3s;
      position: relative;
      animation: cardIn .5s ease both;
    }

    @keyframes cardIn {
      from { opacity:0; transform:translateY(18px); }
      to   { opacity:1; transform:translateY(0); }
    }

    .product-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(0,0,0,.1);
      border-color: transparent;
    }

    /* image wrapper */
    .card-img-wrap {
      position: relative;
      width: 100%; height: 240px;
      background: var(--paper);
      overflow: hidden;
      flex-shrink: 0;
    }

    .card-img-wrap img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform .6s cubic-bezier(.2,0,.2,1);
    }

    .product-card:hover .card-img-wrap img { transform: scale(1.07); }

    .no-img {
      width: 100%; height: 100%;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      gap: .5rem;
      color: #ccc;
    }

    .no-img-icon { font-size: 2.8rem; }
    .no-img-label {
      font-family: 'Syne', sans-serif;
      font-size: .65rem; font-weight: 700;
      letter-spacing: .08em; text-transform: uppercase;
    }

    /* overlay actions */
    .card-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,.45) 0%, transparent 50%);
      opacity: 0;
      transition: opacity .3s;
      display: flex; align-items: flex-end;
      padding: 1rem;
    }

    .product-card:hover .card-overlay { opacity: 1; }

    .overlay-actions {
      display: flex; gap: .5rem; width: 100%;
    }

    .overlay-btn {
      flex: 1;
      background: white; border: none; cursor: pointer;
      border-radius: var(--r-md);
      padding: .65rem .8rem;
      font-family: 'Syne', sans-serif;
      font-size: .75rem; font-weight: 700;
      color: var(--ink);
      display: flex; align-items: center; justify-content: center; gap: .35rem;
      transition: background .2s;
      letter-spacing: .02em;
    }

    .overlay-btn:hover { background: var(--v-primary); color: white; }

    .overlay-btn.wa-btn {
      flex: 0;
      width: 40px;
      background: var(--wa);
      color: white;
      border-radius: var(--r-md);
    }

    .overlay-btn.wa-btn:hover { background: var(--wa-dk); }

    /* badges */
    .card-badges {
      position: absolute; top: .9rem; left: .9rem;
      display: flex; flex-direction: column; gap: .35rem;
      z-index: 2;
    }

    .badge {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .25rem .65rem;
      border-radius: 100px;
      font-family: 'Syne', sans-serif;
      font-size: .62rem; font-weight: 800;
      letter-spacing: .06em; text-transform: uppercase;
    }

    .badge-new  { background: var(--v-accent); color: white; }
    .badge-hot  { background: #ef4444; color: white; }
    .badge-cat  { background: rgba(255,255,255,.9); color: var(--ink); }

    /* wishlist */
    .wishlist-btn {
      position: absolute; top: .9rem; right: .9rem; z-index: 2;
      background: rgba(255,255,255,.9);
      border: none; cursor: pointer;
      width: 36px; height: 36px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      transition: all .25s;
      opacity: 0; transform: translateY(-6px);
    }

    .product-card:hover .wishlist-btn {
      opacity: 1; transform: translateY(0);
    }

    .wishlist-btn.active { opacity: 1; transform: translateY(0); color: #ef4444; }
    .wishlist-btn:hover { background: white; transform: scale(1.1) !important; }

    /* card body */
    .card-body {
      padding: 1.3rem 1.4rem 1.4rem;
      display: flex; flex-direction: column;
      flex: 1;
    }

    .card-meta {
      display: flex; align-items: center; gap: .5rem;
      margin-bottom: .7rem;
      flex-wrap: wrap;
    }

    .meta-cat {
      font-family: 'Syne', sans-serif;
      font-size: .65rem; font-weight: 800;
      letter-spacing: .08em; text-transform: uppercase;
      color: var(--v-accent);
    }

    .meta-sep { color: var(--sand); }

    .meta-brand {
      font-size: .78rem; color: var(--muted);
    }

    .card-name {
      font-family: 'Instrument Serif', serif;
      font-size: 1.15rem;
      line-height: 1.25;
      letter-spacing: -.01em;
      color: var(--ink);
      margin-bottom: .8rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-delivery {
      display: flex; align-items: center; gap: .4rem;
      font-size: .78rem; color: var(--muted);
      margin-bottom: 1rem;
    }

    .card-footer {
      margin-top: auto;
      display: flex; align-items: center; justify-content: space-between;
      gap: 1rem;
    }

    .card-price {
      font-family: 'Instrument Serif', serif;
      font-size: 1.5rem;
      letter-spacing: -.02em;
      color: var(--v-primary);
      line-height: 1;
    }

    .card-price-sub {
      font-size: .72rem;
      color: var(--muted);
      margin-top: .15rem;
      font-family: 'Syne', sans-serif;
      font-weight: 600;
      letter-spacing: .02em;
    }

    .preorder-btn {
      background: var(--v-primary);
      color: white;
      border: none; cursor: pointer;
      padding: .65rem 1.1rem;
      border-radius: var(--r-md);
      font-family: 'Syne', sans-serif;
      font-size: .78rem; font-weight: 700;
      letter-spacing: .03em;
      display: flex; align-items: center; gap: .4rem;
      transition: background .2s, transform .2s;
      white-space: nowrap;
    }

    .preorder-btn:hover {
      background: var(--v-accent);
      transform: translateY(-1px);
    }

    /* ── LIST VIEW card ── */
    .list-view .product-card {
      flex-direction: row;
      height: 140px;
    }

    .list-view .card-img-wrap {
      width: 140px; height: 140px;
      flex-shrink: 0;
      border-radius: 0;
    }

    .list-view .card-overlay { display: none; }

    .list-view .card-body {
      flex-direction: row;
      align-items: center;
      padding: 1.2rem 1.5rem;
      gap: 1.5rem;
    }

    .list-view .card-main {
      flex: 1;
    }

    .list-view .card-name { margin-bottom: .3rem; -webkit-line-clamp: 1; }
    .list-view .card-delivery { margin-bottom: 0; }

    .list-view .card-footer {
      flex-direction: column;
      align-items: flex-end;
      gap: .5rem;
      margin-top: 0;
    }

    /* ══════════════════════════
       EMPTY STATE
    ══════════════════════════ */
    .empty-state {
      grid-column: 1 / -1;
      text-align: center;
      padding: 6rem 2rem;
      background: white;
      border-radius: var(--r-xl);
      border: 1.5px dashed var(--sand);
    }

    .empty-icon {
      font-size: 3.5rem;
      margin-bottom: 1.2rem;
      animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
      0%,100% { transform:translateY(0); }
      50%      { transform:translateY(-12px); }
    }

    .empty-title {
      font-family: 'Instrument Serif', serif;
      font-size: 2rem;
      letter-spacing: -.025em;
      color: var(--ink);
      margin-bottom: .5rem;
    }

    .empty-sub {
      font-size: .9rem; color: var(--muted);
    }

    /* ══════════════════════════
       TOAST
    ══════════════════════════ */
    .toast {
      position: fixed; bottom: 6rem; left: 50%;
      transform: translateX(-50%) translateY(20px);
      background: var(--ink); color: white;
      padding: .75rem 1.5rem;
      border-radius: 100px;
      font-family: 'Syne', sans-serif;
      font-size: .82rem; font-weight: 700;
      box-shadow: 0 8px 30px rgba(0,0,0,.25);
      opacity: 0; pointer-events: none;
      transition: opacity .3s, transform .3s;
      z-index: 1000;
      white-space: nowrap;
    }

    .toast.show {
      opacity: 1; transform: translateX(-50%) translateY(0);
    }

    /* ══════════════════════════
       FOOTER
    ══════════════════════════ */
    .store-footer {
      background: var(--v-primary);
      color: rgba(255,255,255,.6);
      text-align: center;
      padding: 2rem;
      font-family: 'Syne', sans-serif;
      font-size: .78rem;
      font-weight: 600;
      letter-spacing: .04em;
    }

    .store-footer a {
      color: white; text-decoration: none;
      font-weight: 800;
    }

    /* ══════════════════════════
       RESPONSIVE
    ══════════════════════════ */
    @media (max-width: 1024px) {
      .page-body { grid-template-columns: 220px 1fr; gap: 2rem; }
    }

    @media (max-width: 860px) {
      .page-body { grid-template-columns: 1fr; }
      .sidebar { position: static; }
      .sidebar-card { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); }
      .sidebar-head { grid-column: 1/-1; }
      .hero-right { display: none; }
      .controls { flex-wrap: wrap; }
    }

    @media (max-width: 540px) {
      .nav { padding: 0 1rem; }
      .page-body { padding: 1.5rem 1rem 4rem; }
      .store-hero { padding: 2.5rem 1rem 2rem; }
      .product-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- ── NAV ── -->
<header class="nav" id="mainNav">
  <?php if ($v_logo): ?>
    <a href="products.php?store=<?php echo $store_param; ?>" class="nav-logo">
      <img src="<?php echo $v_logo; ?>" alt="<?php echo $v_name; ?>" />
    </a>
  <?php else: ?>
    <a href="products.php?store=<?php echo $store_param; ?>" class="nav-logo">
      <span style="width:28px;height:28px;background:var(--v-primary);border-radius:6px;display:flex;align-items:center;justify-content:center;color:white;font-size:1rem">◈</span>
      <span class="nav-logo-text"><?php echo $v_name; ?></span>
    </a>
  <?php endif; ?>

  <div class="nav-right">
    <a href="products.php?store=<?php echo $store_param; ?>" class="nav-link active">Products</a>
    <a href="my_orders.php" class="nav-link">My Orders</a>
    <button class="cart-btn" id="cartBtn" aria-label="Cart">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
      </svg>
      <span class="cart-badge" id="cartCount"><?php echo $cart_count; ?></span>
    </button>
  </div>
</header>

<!-- ── STORE HERO ── -->
<section class="store-hero">
  <div class="hero-inner">
    <div class="hero-left">
      <div class="hero-store-badge">Official Pre-Order Store</div>
      <h1 class="hero-title"><?php echo $v_name; ?></h1>
      <p class="hero-tagline"><?php echo $v_tagline; ?></p>
    </div>
    <div class="hero-right">
      <div class="hero-stat">
        <div class="hero-stat-val" id="heroProductCount"><?php echo count($products); ?></div>
        <div class="hero-stat-label">Products</div>
      </div>
      <?php if (!empty($vendor_data['total_orders'])): ?>
      <div class="hero-stat">
        <div class="hero-stat-val"><?php echo number_format($vendor_data['total_orders']); ?></div>
        <div class="hero-stat-label">Orders</div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── PAGE BODY ── -->
<div class="page-body">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-card">
      <div class="sidebar-head">
        <span class="sidebar-head-title">Filters</span>
        <button class="clear-all-btn" id="clearAllBtn">Clear all</button>
      </div>

      <?php if (!empty($categories)): ?>
      <div class="filter-group">
        <div class="filter-group-title">Category</div>
        <div class="filter-opts">
          <?php foreach ($categories as $cat): ?>
          <div class="filter-opt">
            <label>
              <input type="checkbox" class="cat-filter" value="<?php echo htmlspecialchars(strtolower($cat['name'])); ?>">
              <?php echo htmlspecialchars($cat['name']); ?>
              <span class="filter-count">—</span>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($brands)): ?>
      <div class="filter-group">
        <div class="filter-group-title">Brand</div>
        <div class="filter-opts">
          <?php foreach ($brands as $brand): ?>
          <div class="filter-opt">
            <label>
              <input type="checkbox" class="brand-filter" value="<?php echo htmlspecialchars(strtolower($brand['name'])); ?>">
              <?php echo htmlspecialchars($brand['name']); ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="filter-group">
        <div class="filter-group-title">Price (GH₵)</div>
        <div class="price-inputs">
          <input type="number" class="price-inp" id="minPrice" placeholder="Min" min="0">
          <span class="price-sep">—</span>
          <input type="number" class="price-inp" id="maxPrice" placeholder="Max" min="0">
        </div>
      </div>
    </div>
  </aside>

  <!-- CONTENT -->
  <main>
    <!-- controls -->
    <div class="controls">
      <div class="search-box">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" class="search-inp" id="searchInp" placeholder="Search products, brands…" />
      </div>

      <select class="sort-sel" id="sortSel">
        <option value="newest">Newest first</option>
        <option value="price-asc">Price: Low → High</option>
        <option value="price-desc">Price: High → Low</option>
        <option value="name-asc">Name A → Z</option>
      </select>

      <div class="result-pill" id="resultPill">
        <strong id="resultNum"><?php echo count($products); ?></strong> products
      </div>

      <div class="view-toggle">
        <button class="view-btn active" id="gridBtn" title="Grid view">
          <svg width="15" height="15" viewBox="0 0 15 15" fill="currentColor">
            <rect x="0" y="0" width="6" height="6" rx="1.5"/>
            <rect x="9" y="0" width="6" height="6" rx="1.5"/>
            <rect x="0" y="9" width="6" height="6" rx="1.5"/>
            <rect x="9" y="9" width="6" height="6" rx="1.5"/>
          </svg>
        </button>
        <button class="view-btn" id="listBtn" title="List view">
          <svg width="15" height="15" viewBox="0 0 15 15" fill="currentColor">
            <rect x="0" y="0" width="15" height="3" rx="1.5"/>
            <rect x="0" y="6" width="15" height="3" rx="1.5"/>
            <rect x="0" y="12" width="15" height="3" rx="1.5"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- grid -->
    <div class="product-grid" id="productGrid"></div>

    <!-- empty state -->
    <div class="empty-state" id="emptyState" style="display:none">
      <div class="empty-icon">🔍</div>
      <div class="empty-title">Nothing found</div>
      <p class="empty-sub">Try adjusting your filters or search terms</p>
    </div>
  </main>
</div>

<!-- ── WHATSAPP FLOAT ── -->
<?php if ($v_wa): ?>
<a class="wa-sticky" href="https://wa.me/<?php echo preg_replace('/[^0-9]/','',$v_wa); ?>?text=Hi! I'm browsing <?php echo urlencode($v_name); ?> on PreOrda." target="_blank" rel="noopener">
  <svg width="22" height="22" viewBox="0 0 24 24" fill="white">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
  </svg>
  <span class="wa-label">Chat with vendor</span>
</a>
<?php endif; ?>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<!-- FOOTER -->
<footer class="store-footer">
  Powered by <a href="../index.php">PreOrda</a> &nbsp;·&nbsp; © <?php echo date('Y'); ?> <?php echo $v_name; ?>
</footer>

<script>
  // ── DATA FROM PHP ──
  const ALL_PRODUCTS = <?php echo json_encode($products ?? []); ?>;
  const STORE_PARAM  = "<?php echo $store_param; ?>";
  const WA_NUMBER    = "<?php echo preg_replace('/[^0-9]/', '', $v_wa); ?>";
  const STORE_NAME   = "<?php echo addslashes($v_name); ?>";

  let filtered = [...ALL_PRODUCTS];
  let viewMode = 'grid'; // grid | list

  // ── NAV SCROLL ──
  const nav = document.getElementById('mainNav');
  window.addEventListener('scroll', () => nav.classList.toggle('scrolled', scrollY > 40), { passive: true });

  // ── RENDER ──
  function render() {
    const grid = document.getElementById('productGrid');
    const empty = document.getElementById('emptyState');

    if (!filtered.length) {
      grid.innerHTML = '';
      empty.style.display = '';
      return;
    }
    empty.style.display = 'none';

    grid.innerHTML = filtered.map((p, idx) => {
      const imgHtml = p.image_url
        ? `<img src="${p.image_url}" alt="${esc(p.name)}" loading="lazy" />`
        : `<div class="no-img"><div class="no-img-icon">📦</div><div class="no-img-label">No image</div></div>`;

      const detailUrl = `productdetails.php?id=${p.product_id}&store=${STORE_PARAM}`;
      const waMsg = encodeURIComponent(`Hi! I'm interested in "${p.name}" (GH₵${parseFloat(p.price).toLocaleString()}) from ${STORE_NAME} on PreOrda.`);
      const waUrl = WA_NUMBER ? `https://wa.me/${WA_NUMBER}?text=${waMsg}` : '#';

      const isNew = isWithin30Days(p.created_at);
      const badges = [
        p.category_name ? `<span class="badge badge-cat">${esc(p.category_name)}</span>` : '',
        isNew ? `<span class="badge badge-new">New</span>` : ''
      ].filter(Boolean).join('');

      if (viewMode === 'list') {
        return `
        <div class="product-card" onclick="navigate('${detailUrl}')" style="animation-delay:${idx * 0.04}s">
          <div class="card-img-wrap">${imgHtml}</div>
          <div class="card-body">
            <div class="card-main">
              <div class="card-meta">
                <span class="meta-cat">${esc(p.category_name || 'General')}</span>
                ${p.brand_name ? `<span class="meta-sep">·</span><span class="meta-brand">${esc(p.brand_name)}</span>` : ''}
              </div>
              <div class="card-name">${esc(p.name)}</div>
              <div class="card-delivery">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                ${esc(p.estimated_delivery_time || '3–5')} days delivery
              </div>
            </div>
            <div class="card-footer">
              <div>
                <div class="card-price">GH₵ ${parseFloat(p.price).toLocaleString()}</div>
                <div class="card-price-sub">Pre-order price</div>
              </div>
              <button class="preorder-btn" onclick="addToCart(event,${p.product_id})">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Pre-Order
              </button>
            </div>
          </div>
        </div>`;
      }

      return `
      <div class="product-card" onclick="navigate('${detailUrl}')" style="animation-delay:${idx * 0.04}s">
        <div class="card-img-wrap">
          ${imgHtml}
          <div class="card-badges">${badges}</div>
          <button class="wishlist-btn" onclick="toggleWish(event,this)" aria-label="Wishlist">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
          </button>
          <div class="card-overlay">
            <div class="overlay-actions">
              <button class="overlay-btn" onclick="addToCart(event,${p.product_id})">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Pre-Order
              </button>
              ${WA_NUMBER ? `<a class="overlay-btn wa-btn" href="${waUrl}" target="_blank" onclick="event.stopPropagation()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
              </a>` : ''}
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="card-meta">
            <span class="meta-cat">${esc(p.category_name || 'General')}</span>
            ${p.brand_name ? `<span class="meta-sep">·</span><span class="meta-brand">${esc(p.brand_name)}</span>` : ''}
          </div>
          <div class="card-name">${esc(p.name)}</div>
          <div class="card-delivery">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            ${esc(p.estimated_delivery_time || '3–5')} days delivery
          </div>
          <div class="card-footer">
            <div>
              <div class="card-price">GH₵ ${parseFloat(p.price).toLocaleString()}</div>
              <div class="card-price-sub">Pre-order price</div>
            </div>
            <button class="preorder-btn" onclick="addToCart(event,${p.product_id})">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
              Pre-Order
            </button>
          </div>
        </div>
      </div>`;
    }).join('');

    document.getElementById('resultNum').textContent = filtered.length;
    updateCounts();
  }

  // ── FILTER ──
  function applyFilters() {
    const cats    = [...document.querySelectorAll('.cat-filter:checked')].map(c => c.value);
    const brands  = [...document.querySelectorAll('.brand-filter:checked')].map(c => c.value);
    const minP    = parseFloat(document.getElementById('minPrice').value) || 0;
    const maxP    = parseFloat(document.getElementById('maxPrice').value) || Infinity;
    const q       = document.getElementById('searchInp').value.toLowerCase();
    const sort    = document.getElementById('sortSel').value;

    filtered = ALL_PRODUCTS.filter(p => {
      const cat   = (p.category_name || '').toLowerCase();
      const brand = (p.brand_name || '').toLowerCase();
      const name  = (p.name || '').toLowerCase();
      const desc  = (p.description || '').toLowerCase();
      const price = parseFloat(p.price);
      return (!cats.length   || cats.includes(cat))
          && (!brands.length || brands.includes(brand))
          && price >= minP && price <= maxP
          && (!q || name.includes(q) || desc.includes(q) || brand.includes(q));
    });

    filtered.sort((a,b) => {
      if (sort === 'price-asc')  return parseFloat(a.price) - parseFloat(b.price);
      if (sort === 'price-desc') return parseFloat(b.price) - parseFloat(a.price);
      if (sort === 'name-asc')   return a.name.localeCompare(b.name);
      return new Date(b.created_at || 0) - new Date(a.created_at || 0);
    });

    render();
  }

  // update count badges on sidebar
  function updateCounts() {
    document.querySelectorAll('.cat-filter').forEach(cb => {
      const count = ALL_PRODUCTS.filter(p =>
        (p.category_name || '').toLowerCase() === cb.value).length;
      const badge = cb.closest('label')?.querySelector('.filter-count');
      if (badge) badge.textContent = count;
    });
  }

  // ── CART ──
  function addToCart(e, productId) {
    e.stopPropagation();
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../actions/add_to_cart.php';
    const fields = { product_id: productId, quantity: 1, store: STORE_PARAM };
    Object.entries(fields).forEach(([k,v]) => {
      const inp = document.createElement('input');
      inp.type='hidden'; inp.name=k; inp.value=v;
      form.appendChild(inp);
    });
    document.body.appendChild(form);

    // Update badge optimistically
    const badge = document.getElementById('cartCount');
    badge.textContent = parseInt(badge.textContent||0) + 1;

    showToast('Added to cart!');
    setTimeout(() => form.submit(), 400);
  }

  // ── NAVIGATE ──
  function navigate(url) { window.location.href = url; }

  // ── WISHLIST ──
  function toggleWish(e, btn) {
    e.stopPropagation();
    btn.classList.toggle('active');
    const on = btn.classList.contains('active');
    btn.innerHTML = on
      ? `<svg width="18" height="18" fill="#ef4444" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>`
      : `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>`;
    showToast(on ? 'Saved to wishlist' : 'Removed from wishlist');
  }

  // ── VIEW TOGGLE ──
  document.getElementById('gridBtn').addEventListener('click', () => {
    viewMode = 'grid';
    document.getElementById('productGrid').classList.remove('list-view');
    document.getElementById('gridBtn').classList.add('active');
    document.getElementById('listBtn').classList.remove('active');
    render();
  });

  document.getElementById('listBtn').addEventListener('click', () => {
    viewMode = 'list';
    document.getElementById('productGrid').classList.add('list-view');
    document.getElementById('listBtn').classList.add('active');
    document.getElementById('gridBtn').classList.remove('active');
    render();
  });

  // ── CLEAR ──
  document.getElementById('clearAllBtn').addEventListener('click', () => {
    document.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false);
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('searchInp').value = '';
    document.getElementById('sortSel').value = 'newest';
    applyFilters();
  });

  // ── EVENTS ──
  document.querySelectorAll('.cat-filter, .brand-filter').forEach(c => c.addEventListener('change', applyFilters));
  document.getElementById('minPrice').addEventListener('input', applyFilters);
  document.getElementById('maxPrice').addEventListener('input', applyFilters);
  document.getElementById('searchInp').addEventListener('input', applyFilters);
  document.getElementById('sortSel').addEventListener('change', applyFilters);

  // ── TOAST ──
  let toastTimer;
  function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 2200);
  }

  // ── HELPERS ──
  function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function isWithin30Days(dateStr) {
    if (!dateStr) return false;
    return (Date.now() - new Date(dateStr)) < 30 * 24 * 3600 * 1000;
  }

  // ── INIT ──
  render();
</script>
</body>
</html>