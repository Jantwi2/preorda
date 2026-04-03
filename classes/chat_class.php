<?php
require_once('../settings/db_class.php');

class chat_class extends db_connection {

    // Send a message
    public function send_message($sender_id, $receiver_id, $message) {
        // Basic escaping
        $message = str_replace("'", "''", $message);
        
        $sql = "INSERT INTO chats (sender_id, receiver_id, message) 
                VALUES ('$sender_id', '$receiver_id', '$message')";
        return $this->db_query($sql);
    }

    // Get messages between two users
    public function get_conversation($user1_id, $user2_id) {
        $sql = "SELECT * FROM chats 
                WHERE (sender_id = '$user1_id' AND receiver_id = '$user2_id')
                   OR (sender_id = '$user2_id' AND receiver_id = '$user1_id')
                ORDER BY sent_at ASC";
        return $this->db_fetch_all($sql);
    }

    // Get a list of users that the current user has chatted with
    public function get_user_conversations($user_id) {
        // Returns the last message per contact
        $sql = "
            SELECT 
                IF(sender_id = '$user_id', receiver_id, sender_id) as contact_id,
                MAX(sent_at) as last_activity,
                (SELECT message FROM chats c2 WHERE (c2.sender_id = contact_id OR c2.receiver_id = contact_id) AND (c2.sender_id = '$user_id' OR c2.receiver_id = '$user_id') ORDER BY c2.sent_at DESC LIMIT 1) as last_message,
                u.first_name, u.last_name, u.role, v.business_name
            FROM chats c
            JOIN users u ON u.user_id = IF(c.sender_id = '$user_id', c.receiver_id, c.sender_id)
            LEFT JOIN vendors v ON v.user_id = u.user_id
            WHERE sender_id = '$user_id' OR receiver_id = '$user_id'
            GROUP BY contact_id
            ORDER BY last_activity DESC
        ";
        return $this->db_fetch_all($sql);
    }
}
?>
