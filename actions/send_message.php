<?php
session_start();
header('Content-Type: application/json');
require_once("../controllers/chat_controller.php");

// Respond with JSON error
function respondError($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    respondError('Unauthorized access.');
}

$sender_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['receiver_id']) || !isset($data['message'])) {
    respondError('Missing required fields.');
}

$receiver_id = intval($data['receiver_id']);
$message = trim($data['message']);

if (empty($message)) {
    respondError('Message cannot be empty.');
}

$result = send_message_ctr($sender_id, $receiver_id, $message);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    respondError('Failed to send message.');
}
?>
