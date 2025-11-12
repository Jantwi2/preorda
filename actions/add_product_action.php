<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
ob_start();

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    include_once("../controllers/product_controller.php");

    $user_id = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        ob_end_clean();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        exit;
    }

    $data = [];
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
    } elseif (!empty($_POST)) {
        $data = $_POST;
    }

    $title = trim($data['product_title'] ?? $data['title'] ?? '');
    $price = trim($data['product_price'] ?? $data['price'] ?? '');
    $description = trim($data['product_description'] ?? $data['description'] ?? '');
    $cat_id = $data['cat_id'] ?? null;
    $brand_id = $data['brand_id'] ?? null;
    $keyword = trim($data['product_keyword'] ?? $data['keyword'] ?? '');

    if ($title === '') throw new RuntimeException('Product title is required');
    if ($price === '' || !is_numeric($price)) throw new RuntimeException('Valid product price is required');
    if (empty($cat_id) || empty($brand_id)) throw new RuntimeException('Category and Brand are required');

    // --- Image Upload (move to /uploads) ---
    $image_name = null;
    $fileKey = !empty($_FILES['product_image']) ? 'product_image' : (!empty($_FILES['image']) ? 'image' : null);

    if ($fileKey && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES[$fileKey];
        if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Image upload error: ' . $file['error']);

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) throw new RuntimeException('Invalid image type');

        // ✅ Always use the existing uploads folder
        $destDir = __DIR__ . '/../uploads/';
        if (!is_dir($destDir)) {
            throw new RuntimeException('Uploads folder does not exist');
        }

        $safeBase = preg_replace('/[^a-z0-9_\-\.]/i', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $image_name = time() . '_' . bin2hex(random_bytes(5)) . '_' . $safeBase . '.' . $ext;

        $targetPath = $destDir . $image_name;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded image');
        }
    }

    if ($image_name === null && !empty($data['image_name'])) {
        $image_name = basename($data['image_name']);
    }

    $result = add_product_ctr($title, $price, $description, $cat_id, $brand_id, $image_name, $keyword, $user_id);

    ob_end_clean();
    echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Product added successfully' : 'Failed to add product']);
    exit;

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    error_log("add_product_action error: " . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Server error', 'details' => $e->getMessage()]);
} finally {
    restore_error_handler();
}
?>
