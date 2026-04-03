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
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --sidebar-bg: #1e1e2d;
            --sidebar-hover: #2b2b40;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-main); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar (Matching dashboard.php) */
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 24px; font-size: 24px; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 12px; }
        .sidebar-header i { color: var(--primary-color); }
        .nav-menu { padding: 20px 0; flex-grow: 1; }
        .nav-item { padding: 12px 24px; display: flex; align-items: center; gap: 12px; color: #a1a1aa; text-decoration: none; transition: all 0.3s; }
        .nav-item:hover, .nav-item.active { background-color: var(--sidebar-hover); color: white; }
        .nav-item i { width: 20px; text-align: center; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        
        .header { background-color: var(--card-bg); padding: 16px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .header-title { font-size: 20px; font-weight: 600; }
        .user-profile { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: #e0e7ff; color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px; }

        /* Chat Specific Layout */
        .chat-container {
            flex: 1;
            display: flex;
            margin: 24px 32px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
            background: #f8fafc;
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
        .message.sent { background: var(--primary-color); color: white; align-self: flex-end; border-bottom-right-radius: 0; }
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
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-store"></i>
            <span>PreOrda</span>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
            <a href="products.php" class="nav-item"><i class="fas fa-box"></i> Products</a>
            <a href="brandcatmgt.php" class="nav-item"><i class="fas fa-tags"></i> Brands/Categories</a>
            <a href="orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="chat.php" class="nav-item active"><i class="fas fa-comments"></i> Messages</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <div class="header-title">Customer Messages</div>
            <div class="user-profile">
                <span style="font-size: 14px; font-weight: 500;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <div class="avatar" style="width: 35px; height: 35px; font-size: 14px;">
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
