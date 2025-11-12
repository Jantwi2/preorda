<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../settings/core.php';
require_once '../actions/fetch_category_action.php';


// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../view/login.php");
    exit();
}



?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);}
        h2 { margin-bottom: 20px; }
        .error { color: red; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px;}
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left;}
        form.inline { display: inline; }
        input[type="text"] { padding: 6px; border-radius: 4px; border: 1px solid #ccc;}
        button, input[type="submit"] { padding: 6px 12px; border-radius: 4px; border: none; background: #007BFF; color: #fff; cursor: pointer;}
        button.delete { background: #dc3545; }
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
        <a href="product.php" class="btn">Product</a>        
        <?php elseif (!isLoggedIn()): ?>
        <a href="view/register.php" class="btn">Register</a>
        <a href="view/login.php" class="btn">Login</a>
        <?php else: ?>
        <a href="actions/logout.php" class="btn">Logout</a>
        <?php endif; ?>
  </nav>
<div class="container">
    <h2>Manage Categories</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- CREATE -->
    <form id="categoryForm">
        <input type="text" id="category_name" name="category_name" placeholder="New Category Name" required>
        <input type="submit" value="Add Category">
    </form>


    <!-- RETRIEVE, UPDATE, DELETE -->
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= $cat['cat_id'] ?></td>
                <td>
                <form class="updateCategoryForm inline" data-id="<?= $cat['cat_id'] ?>">
                    <input type="text" class="new_name" value="<?= htmlspecialchars($cat['cat_name']) ?>" required>
                    <input type="submit" value="Update">
                </form>

                </td>
                <td>
                    <form class="deleteCategoryForm inline" data-id="<?= $cat['cat_id'] ?>">
                        <button type="submit" class="delete">Delete</button>
                    </form>

                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<script src="../js/category.js"></script>
</body>
</html>