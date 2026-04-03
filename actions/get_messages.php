<?php
session_start();
header('Content-Type: application/json');
require_once("../controllers/chat_controller.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user1_id = $_SESSION['user_id'];
$user2_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;

if (!$user2_id) {
    echo json_encode(['success' => false, 'message' => 'Missing contact_id']);
    exit();
}

$messages = get_conversation_ctr($user1_id, $user2_id);
echo json_encode(['success' => true, 'messages' => $messages]);
?>
