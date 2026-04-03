<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Your Store — PreOrda</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet" />
  <style>
    :root {
      --ink: #0c0c0c;
      --paper: #f5f2ee;
      --cream: #faf8f5;
      --accent: #c8ff00;
      --teal: #0d9e8a;
      --sand: #e8e2d9;
      --muted: #6b6560;
      --error: #dc2626;
      --wa: #25d366;
      --wa-dark: #128c5e;
    }

    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--ink);
      min-height: 100vh;
      display: grid;
      grid-template-columns: 44% 56%;
    }

    /* ── NOISE ── */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 9999;
      opacity: 0.45;
    }

    /* ══════════════════════════════
       LEFT PANEL
    ══════════════════════════════ */
    .left-panel {
      background: var(--ink);
      color: var(--paper);
      padding: 3rem 3.5rem;
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow: hidden;
    }

    /* glow blobs */
    .left-panel::before {
      content: '';
      position: absolute;
      top: -20%;
      left: -20%;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(200,255,0,0.08) 0%, transparent 65%);
      pointer-events: none;
    }

    .left-panel::after {
      content: '';
      position: absolute;
      bottom: -10%;
      right: -20%;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(13,158,138,0.12) 0%, transparent 65%);
      pointer-events: none;
    }

    /* logo */
    .logo {
      font-family: 'Instrument Serif', serif;
      font-size: 1.7rem;
      letter-spacing: -0.02em;
      color: var(--paper);
      display: flex;
      align-items: center;
      gap: 0.45rem;
      text-decoration: none;
      margin-bottom: 3rem;
      position: relative;
      z-index: 2;
    }

    .logo-mark {
      width: 28px; height: 28px;
      background: var(--accent);
      border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.9rem;
    }

    /* headline */
    .left-headline {
      font-family: 'Instrument Serif', serif;
      font-size: clamp(2rem, 3vw, 3.2rem);
      line-height: 1.05;
      letter-spacing: -0.025em;
      color: var(--paper);
      margin-bottom: 1.2rem;
      position: relative;
      z-index: 2;
    }

    .left-headline em {
      font-style: italic;
      color: var(--accent);
    }

    .left-sub {
      font-size: 0.92rem;
      color: rgba(245,242,238,0.5);
      line-height: 1.7;
      font-weight: 300;
      max-width: 340px;
      margin-bottom: 3rem;
      position: relative;
      z-index: 2;
    }

    /* feature list */
    .feat-list {
      display: flex;
      flex-direction: column;
      gap: 1.4rem;
      position: relative;
      z-index: 2;
      flex: 1;
    }

    .feat-row {
      display: flex;
      gap: 1rem;
      align-items: flex-start;
    }

    .feat-dot {
      width: 36px; height: 36px;
      border-radius: 10px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      font-size: 1rem;
    }

    .feat-text h4 {
      font-family: 'Syne', sans-serif;
      font-size: 0.9rem;
      font-weight: 700;
      color: var(--paper);
      margin-bottom: 0.2rem;
    }

    .feat-text p {
      font-size: 0.8rem;
      color: rgba(245,242,238,0.45);
      line-height: 1.5;
    }

    /* store preview card */
    .preview-card {
      margin-top: auto;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      padding: 1.2rem 1.4rem;
      position: relative;
      z-index: 2;
    }

    .preview-label {
      font-family: 'Syne', sans-serif;
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: rgba(245,242,238,0.3);
      margin-bottom: 0.7rem;
    }

    .preview-url {
      font-family: 'Instrument Serif', serif;
      font-size: 1.15rem;
      color: var(--paper);
      letter-spacing: -0.01em;
    }

    .preview-url span {
      color: var(--accent);
    }

    .preview-stats {
      display: flex;
      gap: 1.5rem;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid rgba(255,255,255,0.07);
    }

    .p-stat-val {
      font-family: 'Instrument Serif', serif;
      font-size: 1.4rem;
      color: var(--paper);
    }

    .p-stat-label {
      font-size: 0.7rem;
      color: rgba(245,242,238,0.35);
      margin-top: -0.1rem;
    }

    /* footer note */
    .left-footer {
      font-size: 0.75rem;
      color: rgba(245,242,238,0.2);
      margin-top: 1.2rem;
      position: relative;
      z-index: 2;
    }

    /* ══════════════════════════════
       RIGHT PANEL
    ══════════════════════════════ */
    .right-panel {
      background: var(--cream);
      padding: 3rem 4rem 3rem 3.5rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      min-height: 100vh;
    }

    .form-wrap {
      width: 100%;
      max-width: 480px;
    }

    /* step indicator */
    .steps-bar {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 2.5rem;
    }

    .step-pip {
      height: 3px;
      border-radius: 2px;
      background: var(--sand);
      flex: 1;
      transition: background 0.4s;
    }

    .step-pip.active { background: var(--ink); }
    .step-pip.done { background: var(--teal); }

    .step-count {
      font-family: 'Syne', sans-serif;
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--muted);
      letter-spacing: 0.06em;
      white-space: nowrap;
    }

    /* header */
    .form-header {
      margin-bottom: 2.2rem;
      animation: fadeUp 0.5s ease both;
    }

    .form-eyebrow {
      font-family: 'Syne', sans-serif;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--teal);
      margin-bottom: 0.6rem;
    }

    .form-title {
      font-family: 'Instrument Serif', serif;
      font-size: 2.4rem;
      line-height: 1.05;
      letter-spacing: -0.025em;
      color: var(--ink);
      margin-bottom: 0.5rem;
    }

    .form-title em { font-style: italic; }

    .form-desc {
      font-size: 0.9rem;
      color: var(--muted);
      font-weight: 300;
    }

    /* form */
    form { animation: fadeUp 0.5s 0.1s ease both; }

    .field {
      margin-bottom: 1.3rem;
    }

    .field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    label {
      display: block;
      font-family: 'Syne', sans-serif;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 0.5rem;
    }

    input, select {
      width: 100%;
      padding: 0.85rem 1rem;
      border: 1.5px solid var(--sand);
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem;
      color: var(--ink);
      background: white;
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
    }

    input::placeholder { color: #bbb; }

    input:hover, select:hover { border-color: #ccc; }

    input:focus, select:focus {
      border-color: var(--ink);
      box-shadow: 0 0 0 3px rgba(12,12,12,0.06);
    }

    input.error-state { border-color: var(--error); }
    input.error-state:focus { box-shadow: 0 0 0 3px rgba(220,38,38,0.08); }
    input.ok-state { border-color: var(--teal); }

    select {
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%230c0c0c' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 1rem center;
      padding-right: 2.5rem;
    }

    /* store URL preview */
    .url-field {
      position: relative;
    }

    .url-field input {
      padding-left: 1rem;
    }

    .url-preview-strip {
      display: flex;
      align-items: center;
      gap: 0;
      margin-top: 0.5rem;
      background: var(--paper);
      border: 1.5px solid var(--sand);
      border-radius: 8px;
      padding: 0.5rem 0.85rem;
      font-size: 0.82rem;
      transition: border-color 0.2s;
    }

    .url-domain {
      font-family: 'DM Sans', sans-serif;
      color: var(--muted);
    }

    .url-slug {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      color: var(--teal);
    }

    .url-copy-btn {
      margin-left: auto;
      background: none;
      border: none;
      cursor: pointer;
      color: var(--muted);
      font-size: 0.75rem;
      font-family: 'Syne', sans-serif;
      font-weight: 600;
      letter-spacing: 0.04em;
      padding: 0.15rem 0.5rem;
      border-radius: 4px;
      transition: background 0.2s, color 0.2s;
    }

    .url-copy-btn:hover {
      background: var(--sand);
      color: var(--ink);
    }

    /* password strength */
    .strength-bar {
      display: flex;
      gap: 4px;
      margin-top: 0.5rem;
      height: 3px;
    }

    .strength-seg {
      flex: 1;
      border-radius: 2px;
      background: var(--sand);
      transition: background 0.3s;
    }

    .s-weak .strength-seg:nth-child(1) { background: var(--error); }
    .s-fair .strength-seg:nth-child(1),
    .s-fair .strength-seg:nth-child(2) { background: #f59e0b; }
    .s-good .strength-seg:nth-child(1),
    .s-good .strength-seg:nth-child(2),
    .s-good .strength-seg:nth-child(3) { background: var(--teal); }
    .s-strong .strength-seg { background: var(--teal); }

    .strength-label {
      font-family: 'Syne', sans-serif;
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      margin-top: 0.3rem;
      color: var(--muted);
    }

    /* field errors */
    .field-error {
      font-size: 0.75rem;
      color: var(--error);
      margin-top: 0.4rem;
      display: none;
      font-weight: 500;
    }

    .field-error.show { display: block; }

    /* helper */
    .helper {
      font-size: 0.75rem;
      color: var(--muted);
      margin-top: 0.35rem;
    }

    /* success banner */
    .success-banner {
      background: #f0fdf4;
      border: 1.5px solid #bbf7d0;
      border-radius: 10px;
      padding: 0.9rem 1rem;
      font-size: 0.88rem;
      color: #15803d;
      display: none;
      align-items: center;
      gap: 0.6rem;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }

    .success-banner.show { display: flex; }

    /* submit */
    .submit-btn {
      width: 100%;
      padding: 1rem;
      background: var(--ink);
      color: var(--cream);
      border: none;
      border-radius: 10px;
      font-family: 'Syne', sans-serif;
      font-size: 0.95rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      cursor: pointer;
      margin-top: 1.8rem;
      transition: all 0.25s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      position: relative;
      overflow: hidden;
    }

    .submit-btn::before {
      content: '';
      position: absolute;
      inset: 0;
      background: var(--teal);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform 0.35s ease;
      z-index: 0;
    }

    .submit-btn:hover::before { transform: scaleX(1); }
    .submit-btn span { position: relative; z-index: 1; }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(13,158,138,0.25); }
    .submit-btn:active { transform: translateY(0); }

    /* divider */
    .divider {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin: 1.5rem 0;
      color: var(--muted);
      font-size: 0.8rem;
      font-family: 'Syne', sans-serif;
      font-weight: 600;
      letter-spacing: 0.04em;
    }

    .divider::before, .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--sand);
    }

    /* terms */
    .terms-note {
      font-size: 0.78rem;
      color: var(--muted);
      text-align: center;
      margin-top: 1rem;
      line-height: 1.6;
    }

    .terms-note a {
      color: var(--ink);
      font-weight: 600;
      text-decoration: underline;
      text-underline-offset: 2px;
    }

    /* login link */
    .login-row {
      text-align: center;
      font-size: 0.88rem;
      color: var(--muted);
      margin-top: 1.5rem;
    }

    .login-row a {
      color: var(--ink);
      font-weight: 700;
      text-decoration: none;
      font-family: 'Syne', sans-serif;
      border-bottom: 1.5px solid var(--ink);
      padding-bottom: 1px;
      transition: color 0.2s, border-color 0.2s;
    }

    .login-row a:hover {
      color: var(--teal);
      border-color: var(--teal);
    }

    /* wa note */
    .wa-note {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      background: #f0fdf6;
      border: 1px solid #c3f4d7;
      border-radius: 8px;
      padding: 0.7rem 0.9rem;
      font-size: 0.8rem;
      color: var(--wa-dark);
      font-weight: 500;
      margin-top: 1.2rem;
    }

    .wa-note svg { flex-shrink: 0; }

    /* animations */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* eye toggle */
    .pw-wrap {
      position: relative;
    }

    .pw-toggle {
      position: absolute;
      right: 0.9rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: var(--muted);
      padding: 0.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .pw-toggle:hover { color: var(--ink); }

    /* ── MOBILE ── */
    @media (max-width: 900px) {
      body { grid-template-columns: 1fr; }
      .left-panel { display: none; }
      .right-panel { padding: 2.5rem 1.5rem; justify-content: flex-start; padding-top: 3rem; }
    }

    @media (max-width: 540px) {
      .field-row { grid-template-columns: 1fr; }
      .form-title { font-size: 2rem; }
    }
  </style>
</head>
<body>

  <!-- ── LEFT PANEL ── -->
  <div class="left-panel">
    <a href="../index.php" class="logo">
      <div class="logo-mark">◈</div>
      PreOrda
    </a>

    <div>
      <h1 class="left-headline">Your store.<br>Your <em>link.</em><br>Ready now.</h1>
      <p class="left-sub">Set up a personalised pre-order storefront in minutes. No tech skills. No spreadsheets. Just sales.</p>
    </div>

    <div class="feat-list">
      <div class="feat-row">
        <div class="feat-dot">🔗</div>
        <div class="feat-text">
          <h4>Unique store URL</h4>
          <p>Share https://preorda.page.gd/your-store on any platform</p>
        </div>
      </div>
      <div class="feat-row">
        <div class="feat-dot" style="background:rgba(37,211,102,0.12);border-color:rgba(37,211,102,0.2)">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="#25d366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        </div>
        <div class="feat-text">
          <h4>WhatsApp chat on every product</h4>
          <p>Customers land straight in your DMs — deal closed</p>
        </div>
      </div>
      <div class="feat-row">
        <div class="feat-dot">💳</div>
        <div class="feat-text">
          <h4>Mobile Money built-in</h4>
          <p>MoMo, card, bank — your customers pay how they want</p>
        </div>
      </div>
      <div class="feat-row">
        <div class="feat-dot">📊</div>
        <div class="feat-text">
          <h4>Orders &amp; analytics dashboard</h4>
          <p>Track every order, payment, and shipment in one place</p>
        </div>
      </div>
    </div>

    <div class="preview-card">
      <div class="preview-label">Your live store will look like</div>
      <div class="preview-url">https://preorda.page.gd/view/products.php?store=<span id="livePreviewSlug">your-store</span></div>
      <div class="preview-stats">
        <div>
          <div class="p-stat-val">0</div>
          <div class="p-stat-label">Orders today</div>
        </div>
        <div>
          <div class="p-stat-val">GH₵ 0</div>
          <div class="p-stat-label">Revenue</div>
        </div>
        <div>
          <div class="p-stat-val">∞</div>
          <div class="p-stat-label">Potential</div>
        </div>
      </div>
    </div>

    <div class="left-footer">Trusted by vendors across Accra, Kumasi & beyond · © 2025 PreOrda</div>
  </div>

  <!-- ── RIGHT PANEL ── -->
  <div class="right-panel">
    <div class="form-wrap">

      <!-- step bar -->
      <div class="steps-bar">
        <div class="step-pip active" id="pip1"></div>
        <div class="step-pip" id="pip2"></div>
        <div class="step-pip" id="pip3"></div>
        <div class="step-count" id="stepCount">Step 1 of 3</div>
      </div>

      <!-- form header -->
      <div class="form-header">
        <div class="form-eyebrow">Create account</div>
        <h1 class="form-title">Set up your<br><em>store</em></h1>
        <p class="form-desc">A few details and you're live — takes less than 2 minutes.</p>
      </div>

      <!-- success banner -->
      <div class="success-banner" id="successBanner">
        ✓ &nbsp;Store created! Redirecting to your dashboard…
      </div>

      <form id="signupForm" novalidate>

        <!-- STORE NAME -->
        <div class="field">
          <label for="storeName">Store Name</label>
          <div class="url-field">
            <input type="text" id="storeName" placeholder="e.g. Afia Luxe Imports" autocomplete="organization" />
          </div>
          <div class="url-preview-strip" id="urlPreview">
            <span class="url-domain">https://preorda.page.gd/view/products.php?store=</span><span class="url-slug" id="urlSlug">your-store</span>
            <button type="button" class="url-copy-btn" id="copyBtn" onclick="copyUrl()">Copy</button>
          </div>
          <div class="field-error" id="storeNameErr">Please enter a store name</div>
        </div>

        <!-- NAME + PHONE -->
        <div class="field-row">
          <div class="field">
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" placeholder="Afia Mensah" autocomplete="name" />
            <div class="field-error" id="fullNameErr">Full name required</div>
          </div>
          <div class="field">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" placeholder="+233 XX XXX XXXX" autocomplete="tel" />
            <div class="field-error" id="phoneErr">Valid phone number required</div>
          </div>
        </div>

        <!-- EMAIL -->
        <div class="field">
          <label for="email">Email Address</label>
          <input type="email" id="email" placeholder="you@example.com" autocomplete="email" />
          <div class="field-error" id="emailErr">Please enter a valid email</div>
        </div>

        <!-- BUSINESS TYPE -->
        <div class="field">
          <label for="bizType">Business Category</label>
          <select id="bizType">
            <option value="">Select your category…</option>
            <option value="fashion">Fashion &amp; Apparel</option>
            <option value="electronics">Electronics &amp; Gadgets</option>
            <option value="beauty">Beauty &amp; Cosmetics</option>
            <option value="home">Home &amp; Living</option>
            <option value="food">Food &amp; Beverages</option>
            <option value="accessories">Accessories &amp; Jewellery</option>
            <option value="other">Other</option>
          </select>
          <div class="field-error" id="bizTypeErr">Please select a category</div>
        </div>

        <!-- PASSWORD -->
        <div class="field-row">
          <div class="field">
            <label for="password">Password</label>
            <div class="pw-wrap">
              <input type="password" id="password" placeholder="Min. 8 characters" autocomplete="new-password" />
              <button type="button" class="pw-toggle" onclick="togglePw('password', this)" aria-label="Show password">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="strength-bar" id="strengthBar">
              <div class="strength-seg"></div>
              <div class="strength-seg"></div>
              <div class="strength-seg"></div>
              <div class="strength-seg"></div>
            </div>
            <div class="strength-label" id="strengthLabel"></div>
            <div class="field-error" id="passwordErr">Minimum 8 characters</div>
          </div>
          <div class="field">
            <label for="confirmPw">Confirm Password</label>
            <div class="pw-wrap">
              <input type="password" id="confirmPw" placeholder="Repeat password" autocomplete="new-password" />
              <button type="button" class="pw-toggle" onclick="togglePw('confirmPw', this)" aria-label="Show confirm password">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="field-error" id="confirmPwErr">Passwords do not match</div>
          </div>
        </div>

        <button type="submit" class="submit-btn">
          <span>Create My Store →</span>
        </button>

        <p class="terms-note">
          By creating an account you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
        </p>

        <div class="wa-note">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="#128c5e"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
          Your store gets a WhatsApp chat button on every product — automatically.
        </div>

        <div class="divider">already a vendor?</div>

        <div class="login-row">
          Have an account? <a href="login.php">Sign in here</a>
        </div>

      </form>
    </div>
  </div>

<script>
  // ── SLUG GENERATOR
  function toSlug(val) {
    return val.toLowerCase().trim()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .slice(0, 32) || 'your-store';
  }

  const storeInput = document.getElementById('storeName');
  const slugEl = document.getElementById('urlSlug');
  const liveSlug = document.getElementById('livePreviewSlug');

  storeInput.addEventListener('input', () => {
    const slug = toSlug(storeInput.value);
    slugEl.textContent = slug;
    if (liveSlug) liveSlug.textContent = slug;
    // step indicator update
    updateSteps();
  });

  // ── COPY URL
  function copyUrl() {
    const url = `https://preorda.page.gd/view/products.php?store=${slugEl.textContent}`;
    navigator.clipboard?.writeText(url);
    const btn = document.getElementById('copyBtn');
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = 'Copy', 1800);
  }

  // ── PASSWORD TOGGLE
  function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.innerHTML = isText
      ? `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
      : `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
  }

  // ── PASSWORD STRENGTH
  const pwInput = document.getElementById('password');
  const strengthBar = document.getElementById('strengthBar');
  const strengthLabel = document.getElementById('strengthLabel');

  pwInput.addEventListener('input', () => {
    const v = pwInput.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    const levels = ['', 's-weak', 's-fair', 's-good', 's-strong'];
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    strengthBar.className = `strength-bar ${v ? levels[score] || 's-weak' : ''}`;
    strengthLabel.textContent = v ? labels[score] || 'Weak' : '';
    updateSteps();
  });

  // ── STEP INDICATOR
  function updateSteps() {
    const f1 = storeInput.value.trim();
    const f2 = document.getElementById('fullName').value.trim() && document.getElementById('email').value.trim();
    const f3 = document.getElementById('password').value.length >= 8;

    const pips = [document.getElementById('pip1'), document.getElementById('pip2'), document.getElementById('pip3')];
    const stepCount = document.getElementById('stepCount');

    if (f3) {
      pips.forEach(p => { p.classList.remove('active'); p.classList.add('done'); });
      stepCount.textContent = 'Ready to go!';
    } else if (f2) {
      pips[0].classList.remove('active'); pips[0].classList.add('done');
      pips[1].classList.remove('active'); pips[1].classList.add('done');
      pips[2].classList.add('active');
      stepCount.textContent = 'Step 3 of 3';
    } else if (f1) {
      pips[0].classList.remove('active'); pips[0].classList.add('done');
      pips[1].classList.add('active');
      stepCount.textContent = 'Step 2 of 3';
    }
  }

  // ── VALIDATION HELPERS
  function err(id, msg) {
    const el = document.getElementById(id);
    const input = el.previousElementSibling?.tagName === 'INPUT' || el.previousElementSibling?.tagName === 'SELECT'
      ? el.previousElementSibling
      : el.closest('.field')?.querySelector('input, select');
    if (input) input.classList.add('error-state');
    el.textContent = msg || el.textContent;
    el.classList.add('show');
    return false;
  }

  function ok(inputId, errId) {
    const inp = document.getElementById(inputId);
    const errEl = document.getElementById(errId);
    inp.classList.remove('error-state');
    inp.classList.add('ok-state');
    errEl.classList.remove('show');
  }

  // live validation
  ['storeName','fullName','phone','email','confirmPw'].forEach(id => {
    document.getElementById(id).addEventListener('blur', () => validateField(id));
  });

  function validateField(id) {
    const v = document.getElementById(id).value.trim();
    if (id === 'storeName' && v) ok('storeName','storeNameErr');
    if (id === 'fullName' && v) ok('fullName','fullNameErr');
    if (id === 'phone' && v) ok('phone','phoneErr');
    if (id === 'email' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) ok('email','emailErr');
    if (id === 'confirmPw') {
      if (v === document.getElementById('password').value && v) ok('confirmPw','confirmPwErr');
    }
    updateSteps();
  }

  // ── FORM SUBMIT
  document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;

    const storeName = document.getElementById('storeName').value.trim();
    const fullName = document.getElementById('fullName').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const email = document.getElementById('email').value.trim();
    const bizType = document.getElementById('bizType').value;
    const pw = document.getElementById('password').value;
    const cpw = document.getElementById('confirmPw').value;

    if (!storeName) { err('storeNameErr', 'Please enter a store name'); valid = false; }
    else ok('storeName','storeNameErr');

    if (!fullName) { err('fullNameErr', 'Full name is required'); valid = false; }
    else ok('fullName','fullNameErr');

    if (!phone) { err('phoneErr', 'Phone number required'); valid = false; }
    else ok('phone','phoneErr');

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { err('emailErr', 'Please enter a valid email'); valid = false; }
    else ok('email','emailErr');

    if (!bizType) { err('bizTypeErr', 'Please select a category'); valid = false; }
    else { document.getElementById('bizType').classList.add('ok-state'); document.getElementById('bizTypeErr').classList.remove('show'); }

    if (pw.length < 8) { err('passwordErr', 'Minimum 8 characters'); valid = false; }

    if (pw !== cpw) { err('confirmPwErr', 'Passwords do not match'); valid = false; }
    else if (cpw) ok('confirmPw','confirmPwErr');

    if (!valid) return;

    const btn = this.querySelector('.submit-btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span>Creating your store…</span>';
    btn.disabled = true;

    const data = {
        storeName: storeName,
        fullName: fullName,
        phone: phone,
        email: email,
        businessType: bizType,
        password: pw
    };

    fetch('../actions/register_vendor.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(async res => {
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Server response was not JSON:', text);
            throw new Error('Server returned an invalid response. This usually means a database connection error or PHP failure.');
        }
    })
    .then(result => {
        if (result.status === 'otp_sent') {
            if (result.otp) {
                alert(`Email service not configured.\n\nYour verification code is: ${result.otp}\n\nPlease enter this code on the next page.`);
            }
            window.location.href = 'verify_otp.php';
        } else if (result.status === 'success') {
            document.getElementById('successBanner').classList.add('show');
            btn.innerHTML = '<span>✓ Store created!</span>';
            btn.style.background = 'var(--teal)';
            setTimeout(() => window.location.href = '../vendor/dashboard.php', 2000);
        } else if (result.status === 'duplicate') {
            alert(result.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        } else {
            alert(result.message || 'Registration failed. Please try again.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        alert(error.message || 'Network error. Please try again later.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
  });
</script>
</body>
</html>
