<?php
session_start();
require_once("../classes/user_class.php");
require_once("../controllers/chat_controller.php");

// Must be logged in and a vendor
if (!isset($_SESSION['vendor_id']) || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$customer_user_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

$customer_info = null;
if ($customer_user_id) {
    require_once("../settings/db_class.php");
    $db = new db_connection();
    $sql = "SELECT first_name, last_name, email FROM users WHERE user_id = '$customer_user_id'";
    $customer_info = $db->db_fetch_one($sql);
}

// Fetch all conversations
$conversations = get_user_conversations_ctr($user_id);

if (!$customer_user_id && !empty($conversations)) {
    $customer_user_id = $conversations[0]['contact_id'];
    $db = new db_connection();
    $sql = "SELECT first_name, last_name, email FROM users WHERE user_id = '$customer_user_id'";
    $customer_info = $db->db_fetch_one($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Messages - Vendor Dashboard</title>
    <!-- Use same fonts as dashboard -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Include the dashboard master layout CSS file, for now inline the structure -->
    <style>
        :root {
            --primary-color: #C8FF00;
            --primary-hover: #E1FF4D;
            --sidebar-bg: #0C0C0C;
            --sidebar-hover: #1A1A1A;
            --bg-color: #0C0C0C;
            --card-bg: #151515;
            --text-main: #f1f1f1;
            --text-muted: #a1a1aa;
            --border-color: rgba(255, 255, 255, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: var(--bg-color); color: var(--text-main); display: flex; min-height: 100vh; }

        /* Sidebar Styles */
        .sidebar {
            position: fixed; left: 0; top: 0;
            width: 260px; height: 100vh;
            background: #0C0C0C; color: white;
            padding: 20px; overflow-y: auto; z-index: 100;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }
        .logo { margin-bottom: 40px; display: flex; align-items: center; justify-content: center; }
        .logo img { max-width: 180px; height: auto; }

        .nav-item {
            padding: 12px 16px; margin-bottom: 8px; border-radius: 8px;
            display: flex; align-items: center; gap: 12px;
            transition: background 0.2s; text-decoration: none; color: white;
        }
        .nav-item:hover { background: #1A1A1A; color: #C8FF00; }
        .nav-item.active { background: #C8FF00; color: #0C0C0C; }
        .nav-icon { width: 20px; height: 20px; }

        /* Main Content */
        .main-content { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        
        .dashboard-header {
            background: #0C0C0C; padding: 15px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex; justify-content: space-between; align-items: center;
        }
        .vendor-info { display: flex; align-items: center; gap: 15px; }
        .vendor-name-text { font-size: 16px; font-weight: 600; color: #f1f1f1; }
        .profile-photo-wrapper {
            width: 40px; height: 40px; border-radius: 50%;
            background: #1A1A1A; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #C8FF00;
        }
        .profile-photo-initials { font-size: 16px; font-weight: 700; color: #C8FF00; }

        .action-icons { display: flex; gap: 20px; }
        .action-icon { width: 24px; height: 24px; color: #a1a1aa; cursor: pointer; transition: color 0.2s; }
        .action-icon:hover { color: #C8FF00; }

        /* Chat Specific Layout */
        .chat-container {
            flex: 1;
            display: flex;
            margin: 24px 32px;
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .chat-sidebar {
            width: 300px;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            background: #fafbfc;
        }

        .conversations-list { flex: 1; overflow-y: auto; }
        .contact-item { padding: 16px 20px; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: 0.2s; display: flex; gap: 12px; align-items: center; }
        .contact-item:hover { background: #f3f4f6; }
        .contact-item.active { background: #fff; border-left: 4px solid var(--primary-color); }

        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
        }

        .chat-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-messages {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: #0C0C0C;
        }

        .message {
            max-width: 60%;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
        }

        .message.received { background: var(--card-bg); border: 1px solid var(--border-color); align-self: flex-start; border-bottom-left-radius: 0; }
        .message.sent { background: var(--primary-color); color: #0C0C0C; align-self: flex-end; border-bottom-right-radius: 0; font-weight: 500; }
        .message-time { font-size: 11px; margin-top: 4px; opacity: 0.7; display: block; text-align: right; }

        .chat-input-area {
            padding: 20px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 12px;
            background: var(--card-bg);
        }

        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: inherit;
            outline: none;
        }
        .chat-input:focus { border-color: var(--primary-color); }

        .send-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 20px;
            cursor: pointer;
            font-weight: 500;
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="../images/logo_c.png" alt="PreOrda">
        </div>
        <a href="dashboard.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span>Overview</span>
        </a>
        <a href="products.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span>Products</span>
        </a>
        <a href="orders.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span>Orders</span>
        </a>
        <a href="customers.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span>Customers</span>
        </a>
        <a href="brandcatmgt.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <span>Brands & Categories</span>
        </a>
        <a href="chat.php" class="nav-item active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
            <span>Messages</span>
        </a>
        <a href="settings.php" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Settings</span>
        </a>
        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid #2d3748;">
            <a href="../actions/logout.php" class="nav-item" style="color: #fc8181;">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <header class="dashboard-header">
            <div class="vendor-info">
                <div class="profile-photo-wrapper">
                    <span class="profile-photo-initials"><?php 
                    $name_parts = explode(' ', $_SESSION['full_name'] ?? 'Vendor');
                    echo strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
                    ?></span>
                </div>
                <span class="vendor-name-text"><?php echo htmlspecialchars($_SESSION['business_name'] ?? 'My Store'); ?></span>
            </div>
            <div class="action-icons">
                <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.16 6 8.356 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <a href="settings.php" style="color: inherit;">
                    <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </a>
            </div>
        </header>
            <div class="header-title">Customer Messages</div>
            <div class="user-profile">
                <span style="font-size: 14px; font-weight: 500;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <div class="avatar" style="width: 35px; height: 35px; font-size: 14px; background: #1A1A1A; border: 1px solid var(--primary-color); color: var(--primary-color);">
                    <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                </div>
            </div>
        </header>

        <div class="chat-container">
            <!-- Sidebar -->
            <div class="chat-sidebar">
                <div style="padding: 16px 20px; font-weight: 600; border-bottom: 1px solid var(--border-color);">
                    Conversations
                </div>
                <div class="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <div style="padding: 30px 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                            No messages from customers yet.
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <div class="contact-item <?php echo $conv['contact_id'] == $customer_user_id ? 'active' : ''; ?>" 
                                 onclick="window.location.href='chat.php?customer_id=<?php echo $conv['contact_id']; ?>'">
                                <div class="avatar">
                                    <?php echo strtoupper(substr($conv['first_name'], 0, 1)); ?>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                                        <div style="font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']); ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            <?php echo date('M d', strtotime($conv['last_activity'])); ?>
                                        </div>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($conv['last_message']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Chat Area -->
            <?php if ($customer_user_id && $customer_info): ?>
                <div class="chat-main">
                    <div class="chat-header">
                        <div class="avatar">
                            <?php echo strtoupper(substr($customer_info['first_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h2 style="font-size: 16px; margin: 0; font-weight: 600;"><?php echo htmlspecialchars($customer_info['first_name'] . ' ' . $customer_info['last_name']); ?></h2>
                            <span style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($customer_info['email']); ?></span>
                        </div>
                    </div>

                    <div class="chat-messages" id="chatBox">
                        <!-- Loaded via AJAX -->
                    </div>

                    <div class="chat-input-area">
                        <input type="text" id="messageInput" class="chat-input" placeholder="Type a reply..." onkeypress="handleKeyPress(event)">
                        <button class="send-btn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-comments"></i>
                    <h3>Select a Conversation</h3>
                    <p style="font-size: 14px; margin-top: 8px;">Choose a customer from the left to start messaging.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const receiverId = <?php echo $customer_user_id ? $customer_user_id : 'null'; ?>;
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        const chatBox = document.getElementById('chatBox');
        let lastMessageCount = 0;

        async function fetchMessages() {
            if (!receiverId) return;

            try {
                const response = await fetch('../actions/get_messages.php?contact_id=' + receiverId);
                const data = await response.json();

                if (data.success && data.messages.length !== lastMessageCount) {
                    renderMessages(data.messages);
                    lastMessageCount = data.messages.length;
                    scrollToBottom();
                }
            } catch (error) {
                console.error("Failed to fetch messages", error);
            }
        }

        function renderMessages(messages) {
            if (!chatBox) return;
            chatBox.innerHTML = '';

            messages.forEach(msg => {
                const isSent = parseInt(msg.sender_id) === currentUserId;
                const timeStr = new Date(msg.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                const div = document.createElement('div');
                div.className = `message ${isSent ? 'sent' : 'received'}`;
                
                div.innerHTML = `
                    ${msg.message.replace(/</g, "&lt;").replace(/>/g, "&gt;")}
                    <span class="message-time">${timeStr}</span>
                `;
                
                chatBox.appendChild(div);
            });
        }

        function scrollToBottom() {
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        }

        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message || !receiverId) return;

            input.value = '';

            const timeStr = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const div = document.createElement('div');
            div.className = 'message sent';
            div.innerHTML = `${message.replace(/</g, "&lt;").replace(/>/g, "&gt;")}<span class="message-time">${timeStr}</span>`;
            chatBox.appendChild(div);
            scrollToBottom();
            lastMessageCount++;

            try {
                const response = await fetch('../actions/send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ receiver_id: receiverId, message: message })
                });
                
                const data = await response.json();
                if (!data.success) alert('Failed to send message.');
                else fetchMessages();
            } catch (error) {
                console.error('Error sending message:', error);
            }
        }

        function handleKeyPress(e) {
            if (e.key === 'Enter') sendMessage();
        }

        if (receiverId) {
            fetchMessages();
            setInterval(fetchMessages, 3000); // Polling every 3s
        }
    </script>
</body>
</html>
