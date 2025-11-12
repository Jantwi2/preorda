<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once "../controllers/customer_controller.php"; 
require_once "../settings/db_class.php"; 

// Collect inputs
$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$country  = trim($_POST['country'] ?? '');
$city     = trim($_POST['city'] ?? '');
$contact  = trim($_POST['contact'] ?? '');
$user_role= trim($_POST['role'] ?? '');

// Validate
if (!$fullname || !$email || !$password || !$country || !$city || !$contact || !$user_role) {
    echo json_encode(["success" => false, "error" => "All fields are required"]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Invalid email"]);
    exit;
}
if (!preg_match('/^[0-9\-\+\s\(\)]+$/', $contact)) {
    echo json_encode(["success" => false, "error" => "Invalid phone number"]);
    exit;
}

// Call controller
$result = add_customer_ctr($fullname, $email, $contact, $password, $country, $city, $user_role);

if ($result === "duplicate") {
    echo json_encode(["success" => false, "error" => "Email already exists"]);
} elseif ($result) {
    echo json_encode(["success" => true, 
                      "redirect" => "../view/login.php"]);
} else {
    echo json_encode(["success" => false, "error" => "DB insert failed"]);
}

?>