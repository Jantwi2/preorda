<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once '../settings/core.php';


// Instead include the controllers and fetch categories server-side
include_once '../controllers/category_controller.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../view/login.php");
    exit();
}

// get categories for the select (server-side)
$user_id = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? null;
$categories = [];
if ($user_id) {
    $categories = get_all_cat_ctr($user_id) ?: [];
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Brand Management</title>
  <style>
    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f7f9fb;
      color: #333;
      margin: 0;
      padding: 0;
    }

    h1 {
      text-align: center;
      background-color: #004aad;
      color: white;
      padding: 1rem;
      margin: 4rem;
    }

    section {
      background-color: white;
      max-width: 800px;
      margin: 2rem auto;
      padding: 1.5rem 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    h2 {
      border-bottom: 2px solid #004aad;
      padding-bottom: 0.5rem;
      color: #004aad;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      margin-top: 1rem;
    }

    label {
      font-weight: 500;
      display: flex;
      flex-direction: column;
    }

    input[type="text"],
    select {
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    input[type="text"]:focus,
    select:focus {
      border-color: #004aad;
      outline: none;
    }

    button {
      background-color: #004aad;
      color: white;
      border: none;
      padding: 0.7rem 1.2rem;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s;
      font-size: 1rem;
      align-self: flex-start;
    }

    button:hover {
      background-color: #0063da;
    }

    #brandsContainer {
      margin-top: 1rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }

    .brand-card {
      background: #f3f6fa;
      padding: 1rem;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .brand-card h4 {
      margin: 0;
      color: #004aad;
    }

    .brand-actions {
      margin-top: 0.5rem;
      display: flex;
      justify-content: space-between;
    }

    .brand-actions button {
      background-color: #ccc;
      color: #333;
      font-size: 0.9rem;
      padding: 0.4rem 0.8rem;
    }

    .brand-actions button.edit {
      background-color: #ffc107;
    }

    .brand-actions button.delete {
      background-color: #dc3545;
      color: white;
    }

    /* Modal */
    #editModal {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    #editModal.active {
      display: flex;
    }

    #editModal form {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 400px;
    }

    #editModal button[type="submit"] {
      background-color: #28a745;
    }

    #cancelEdit {
      background-color: #6c757d;
      margin-left: 0.5rem;
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
  </style>
</head>
<body>
        <nav class="nav">
        <?php if (isAdmin()): ?>
        <a href="../actions/logout.php" class="btn">Logout</a>
        <a href="category.php" class="btn">Category</a>
        <a href="product.php" class="btn">Product</a>
        <?php elseif (!isLoggedIn()): ?>
        <a href="view/register.php" class="btn">Register</a>
        <a href="view/login.php" class="btn">Login</a>
        <?php else: ?>
        <a href="actions/logout.php" class="btn">Logout</a>
        <?php endif; ?>
  </nav>
  <h1>Brand Management</h1>

  <section id="brand-form">
    <h2>Add Brand</h2>
    <form id="addBrandForm">
      <label>Brand name:
        <input type="text" name="brand_name" id="brand_name" required>
      </label>
      <label>Category:
        <select name="cat_id" id="cat_id" required>
          <option value="">--Select category--</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?=htmlspecialchars($c['cat_id'])?>">
              <?=htmlspecialchars($c['cat_name'])?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <button type="submit">Add Brand</button>
    </form>
  </section>

  <section id="brand-list">
    <h2>Your Brands</h2>
    <div id="brandsContainer">Loading...</div>
  </section>

  <div id="editModal">
    <form id="editBrandForm">
      <h3>Edit Brand</h3>
      <input type="hidden" id="edit_brand_id" name="brand_id">
      <label>Brand name:
        <input type="text" id="edit_brand_name" name="brand_name" required>
      </label>
      <div style="margin-top:1rem;">
        <button type="submit">Save</button>
        <button type="button" id="cancelEdit">Cancel</button>
      </div>
    </form>
  </div>

  <script src="../js/brand.js"></script>
</body>
</html>
