<?php
session_start();
require_once("../classes/user_class.php");
require_once("../controllers/chat_controller.php");

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?redirect=chat");
    exit();
}

$user_id = $_SESSION['user_id'];
$vendor_user_id = isset($_GET['vendor_id']) ? intval($_GET['vendor_id']) : 0;

// If we have a vendor_id, fetch their info to display in the header
$vendor_info = null;
if ($vendor_user_id) {
    require_once("../settings/db_class.php");
    $db = new db_connection();
    $sql = "SELECT u.first_name, u.last_name, v.business_name 
            FROM users u 
            LEFT JOIN vendors v ON u.user_id = v.user_id 
            WHERE u.user_id = '$vendor_user_id'";
    $vendor_info = $db->db_fetch_one($sql);
}

// Fetch all conversations for the sidebar
$conversations = get_user_conversations_ctr($user_id);

// If no vendor is selected but we have conversations, select the first one
if (!$vendor_user_id && !empty($conversations)) {
    $vendor_user_id = $conversations[0]['contact_id'];
    // Re-fetch info
    $db = new db_connection();
    $sql = "SELECT u.first_name, u.last_name, v.business_name 
            FROM users u 
            LEFT JOIN vendors v ON u.user_id = v.user_id 
            WHERE u.user_id = '$vendor_user_id'";
    $vendor_info = $db->db_fetch_one($sql);
}

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - PreOrda</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #1a202c;
            --bg-main: #f8f9fa;
            --accent: #3498db;
            --font-main: 'Outfit', sans-serif;
            --text-dark: #1a202c;
            --text-gray: #718096;
            --border: #e2e8f0;
            --white: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --radius-lg: 20px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-main);
            color: var(--text-dark);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Prevent body scroll, handle scroll internally */
        }

        /* ── Header ── */
        header {
            background-color: var(--white);
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
            z-index: 10;
        }
        nav { max-width:1400px; margin:0 auto; padding:0 30px; display:flex; justify-content:space-between; align-items:center; }
        .logo { font-size:1.5rem; font-weight:700; color:var(--primary); text-decoration:none; display:flex; align-items:center; gap:10px; }
        .logo img { height:40px; }
        
        .nav-links { display:flex; gap:2.5rem; list-style:none; align-items:center; }
        .nav-links a { color:var(--text-dark); text-decoration:none; font-weight:500; font-size:0.95rem; }
        .nav-links a:hover { color:var(--accent); }
        
        /* ── Chat Layout ── */
        .chat-container {
            display: flex;
            flex: 1;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            background: var(--white);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            overflow: hidden;
        }

        /* Sidebar */
        .chat-sidebar {
            width: 320px;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            background: #fafbfc;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            background: var(--white);
            font-weight: 700;
            color: var(--secondary);
            font-size: 1.1rem;
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }

        .contact-item {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .contact-item:hover { background: var(--white); }
        .contact-item.active { background: var(--white); border-left: 4px solid var(--accent); }

        .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .contact-info { flex: 1; min-width: 0; }
        .contact-name { font-weight: 700; color: var(--secondary); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .contact-preview { font-size: 0.85rem; color: var(--text-gray); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .contact-time { font-size: 0.75rem; color: #a0aec0; }

        /* Main Chat Window */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--white);
            position: relative;
        }

        .chat-header {
            padding: 20px 30px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 15px;
            background: var(--white);
            z-index: 5;
        }
        .chat-header h2 { font-size: 1.2rem; margin: 0; color: var(--secondary); }
        .chat-header p { margin: 0; font-size: 0.85rem; color: var(--text-gray); }

        .chat-messages {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: var(--bg-main);
        }

        .message {
            max-width: 70%;
            padding: 14px 18px;
            border-radius: 18px;
            font-size: 0.95rem;
            line-height: 1.5;
            position: relative;
            word-wrap: break-word;
        }

        .message.received {
            background: var(--white);
            color: var(--text-dark);
            border: 1px solid var(--border);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            box-shadow: var(--shadow-sm);
        }

        .message.sent {
            background: var(--primary);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 6px rgba(44, 62, 80, 0.2);
        }

        .message-time {
            font-size: 0.7rem;
            margin-top: 5px;
            opacity: 0.7;
            text-align: right;
            display: block;
        }

        .chat-input-area {
            padding: 20px 30px;
            background: var(--white);
            border-top: 1px solid var(--border);
            display: flex;
            gap: 15px;
        }

        .chat-input {
            flex: 1;
            padding: 16px 20px;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            font-family: inherit;
            font-size: 1rem;
            resize: none;
            outline: none;
            transition: var(--transition);
            background: var(--bg-main);
        }
        .chat-input:focus { border-color: var(--primary); background: var(--white); }

        .send-btn {
            width: 55px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .send-btn:hover { background: var(--secondary); }

        .empty-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-gray);
            background: var(--bg-main);
        }
        .empty-chat svg { width: 80px; height: 80px; margin-bottom: 20px; opacity: 0.2; }
    </style>

    <!-- PWA Setup -->
    <link rel="manifest" href="/capstone/manifest.json">
    <meta name="theme-color" content="#2c3e50">
    <link rel="apple-touch-icon" href="/capstone/images/logo_c.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <a href="../index.php" class="logo">
                <img src="../images/logo_c.png" alt="PreOrda Logo">
            </a>
            <ul class="nav-links">
                <li><a href="products.php">Products</a></li>
                <li><a href="wishlist.php">Wishlist</a></li>
                <li><a href="my_orders.php">My Orders</a></li>
                <li><a href="cart.php" style="font-weight:700;">Cart (<?php echo $cart_count; ?>)</a></li>
            </ul>
        </nav>
    </header>

    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                Messages
            </div>
            <div class="conversations-list">
                <?php if (empty($conversations)): ?>
                    <div style="padding: 30px 20px; text-align: center; color: var(--text-gray); font-size: 0.9rem;">
                        No conversations yet.<br>Go to a product and click "Chat with Vendor" to start one.
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="contact-item <?php echo $conv['contact_id'] == $vendor_user_id ? 'active' : ''; ?>" 
                             onclick="window.location.href='chat.php?vendor_id=<?php echo $conv['contact_id']; ?>'">
                            <div class="avatar">
                                <?php echo strtoupper(substr($conv['business_name'] ?: $conv['first_name'], 0, 1)); ?>
                            </div>
                            <div class="contact-info">
                                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                    <div class="contact-name"><?php echo htmlspecialchars($conv['business_name'] ?: $conv['first_name'] . ' ' . $conv['last_name']); ?></div>
                                    <div class="contact-time"><?php echo date('M d', strtotime($conv['last_activity'])); ?></div>
                                </div>
                                <div class="contact-preview"><?php echo htmlspecialchars($conv['last_message']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Chat Area -->
        <?php if ($vendor_user_id && $vendor_info): ?>
            <div class="chat-main">
                <div class="chat-header">
                    <div class="avatar" style="width: 45px; height: 45px; font-size: 1.2rem;">
                        <?php echo strtoupper(substr($vendor_info['business_name'] ?: $vendor_info['first_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h2><?php echo htmlspecialchars($vendor_info['business_name'] ?: $vendor_info['first_name'] . ' ' . $vendor_info['last_name']); ?></h2>
                        <p>Typically replies within an hour</p>
                    </div>
                </div>

                <div class="chat-messages" id="chatBox">
                    <!-- Messages will be loaded here via AJAX -->
                </div>

                <div class="chat-input-area">
                    <input type="text" id="messageInput" class="chat-input" placeholder="Type your message here..." onkeypress="handleKeyPress(event)">
                    <button class="send-btn" onclick="sendMessage()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                <h3>Your Messages</h3>
                <p>Select a conversation from the sidebar to view.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const receiverId = <?php echo $vendor_user_id ? $vendor_user_id : 'null'; ?>;
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        const chatBox = document.getElementById('chatBox');
        let lastMessageCount = 0;

        async function fetchMessages() {
            if (!receiverId) return;

            try {
                const response = await fetch(`../actions/get_messages.php?contact_id=${receiverId}`);
                const data = await response.json();

                if (data.success) {
                    // Only re-render if we have new messages
                    if (data.messages.length !== lastMessageCount) {
                        renderMessages(data.messages);
                        lastMessageCount = data.messages.length;
                        scrollToBottom();
                    }
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
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message || !receiverId) return;

            // Clear input immediately for better UX
            input.value = '';

            // Optimistically append message
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
                if (!data.success) {
                    alert('Failed to send message: ' + data.message);
                } else {
                    // Refresh completely to ensure sync
                    fetchMessages();
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        }

        function handleKeyPress(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        }

        // Start polling if we have an active chat
        if (receiverId) {
            fetchMessages();
            setInterval(fetchMessages, 3000); // Check every 3 seconds
        }
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/capstone/service-worker.js')
                    .then(reg => console.log('PreOrda Service Worker registered'))
                    .catch(err => console.log('Service Worker registration failed: ', err));
            });
        }
    </script>
</body>
</html>
