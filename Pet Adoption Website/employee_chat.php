<?php
require_once 'config.php';
require_login();

// Check if user is an employee
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Chat - Streeties</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .chat-container {
            max-width: 1000px;
            margin: 80px auto 20px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .chat-list {
            width: 30%;
            float: left;
            border-right: 1px solid #eee;
            height: 600px;
            overflow-y: auto;
        }
        .chat-messages {
            width: 70%;
            float: right;
            height: 600px;
            display: flex;
            flex-direction: column;
        }
        .message-container {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
            max-width: 70%;
        }
        .message.sent {
            background: #007bff;
            color: white;
            margin-left: auto;
        }
        .message.received {
            background: #f1f1f1;
            margin-right: auto;
        }
        .message-input {
            padding: 20px;
            border-top: 1px solid #eee;
        }
        .message-input form {
            display: flex;
            gap: 10px;
        }
        .message-input input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .message-input button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .chat-user {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-user:hover {
            background: #f5f5f5;
        }
        .chat-user.active {
            background: #e9ecef;
        }
        .chat-user .unread-count {
            background: #e67e22;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8em;
        }
        .chat-user .user-info {
            display: flex;
            flex-direction: column;
        }
        .chat-user .user-name {
            font-weight: bold;
        }
        .chat-user .last-message {
            font-size: 0.8em;
            color: #666;
            margin-top: 3px;
        }
        .message-time {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        .message.sent .message-time {
            text-align: right;
        }
        .typing-indicator {
            color: #666;
            font-style: italic;
            padding: 5px 10px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo-container">
                <a href="index.html">
                    <img src="logo.png" alt="Streeties Logo" class="logo">
                </a>
            </div>
            <nav class="nav-links">
                <a href="index.html">Home</a>
                <a href="about.html">About</a>
                <a href="adopt.html">Adopt</a>
                <a href="contact.html">Contact</a>
                <a href="employee_chat.php" class="active chat-btn"><i class="fas fa-comments"></i> Employee Chat</a>
            </nav>
            <div class="auth-buttons">
                <a href="logout.php" class="login-btn">Logout</a>
            </div>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <div class="chat-container">
        <div class="chat-list">
            <h3>Active Chats</h3>
            <div id="usersList">
                <!-- Users will be loaded here -->
            </div>
        </div>
        <div class="chat-messages">
            <div class="message-container" id="messageContainer">
                <!-- Messages will be loaded here -->
            </div>
            <div class="message-input">
                <form id="messageForm">
                    <input type="hidden" id="currentChat" value="">
                    <input type="text" id="messageInput" placeholder="Type your message..." required>
                    <button type="submit">Send</button>
                </form>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <script>
        let currentChatUser = null;

        // Load active chats
        function loadActiveChats() {
            fetch('get_active_chats.php')
                .then(response => response.json())
                .then(users => {
                    const usersList = document.getElementById('usersList');
                    usersList.innerHTML = '';
                    users.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'chat-user';
                        div.innerHTML = `
                            <div class="user-info">
                                <span class="user-name">${user.name}</span>
                                <span class="last-message">${user.last_message}</span>
                            </div>
                            <div class="unread-count">${user.unread_count}</div>
                        `;
                        div.onclick = () => loadChat(user.id);
                        usersList.appendChild(div);
                    });
                });
        }

        // Load chat messages
        function loadChat(userId) {
            currentChatUser = userId;
            document.getElementById('currentChat').value = userId;
            
            // Update active chat highlighting
            document.querySelectorAll('.chat-user').forEach(el => el.classList.remove('active'));
            document.querySelector(`.chat-user[data-id="${userId}"]`)?.classList.add('active');

            fetch(`get_messages.php?user_id=${userId}`)
                .then(response => response.json())
                .then(messages => {
                    const container = document.getElementById('messageContainer');
                    container.innerHTML = '';
                    messages.forEach(msg => {
                        const div = document.createElement('div');
                        div.className = `message ${msg.sender_id === <?php echo $_SESSION['user_id']; ?> ? 'sent' : 'received'}`;
                        div.textContent = msg.message;
                        container.appendChild(div);
                    });
                    container.scrollTop = container.scrollHeight;
                });
        }

        // Send message
        document.getElementById('messageForm').onsubmit = function(e) {
            e.preventDefault();
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            const userId = document.getElementById('currentChat').value;

            if (!message || !userId) return;

            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}&receiver_id=${userId}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    input.value = '';
                    loadChat(userId);
                }
            });
        };

        // Initial load
        loadActiveChats();

        // Refresh chats every 5 seconds
        setInterval(loadActiveChats, 5000);
        setInterval(() => {
            if (currentChatUser) {
                loadChat(currentChatUser);
            }
        }, 3000);
    </script>
</body>
</html> 