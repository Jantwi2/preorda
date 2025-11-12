<?php
session_start();
header('Content-Type: application/json');

include_once("../controllers/brand_controller.php");


$brands = get_all_brands_ctr($_SESSION['customer_id']);
if ($brands === false || $brands === null) $brands = [];

echo json_encode(['success' => true, 'data' => $brands]);
?>