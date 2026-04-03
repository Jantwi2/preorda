<?php
require_once('../classes/chat_class.php');

// Send message
function send_message_ctr($sender_id, $receiver_id, $message) {
    if (empty(trim($message)) || !$sender_id || !$receiver_id) {
        return false;
    }
    $chat = new chat_class();
    return $chat->send_message($sender_id, $receiver_id, $message);
}

// Get full conversation
function get_conversation_ctr($user1_id, $user2_id) {
    $chat = new chat_class();
    return $chat->get_conversation($user1_id, $user2_id);
}

// Get user conversations list
function get_user_conversations_ctr($user_id) {
    $chat = new chat_class();
    return $chat->get_user_conversations($user_id);
}
?>
