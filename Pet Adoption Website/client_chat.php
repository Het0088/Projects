<?php
require_once 'config.php';
require_login();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Support - Streeties</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
        .status {
            text-align: center;
            padding: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="status" id="status">
            Connecting to support...
        </div>
        <div class="message-container" id="messageContainer">
            <!-- Messages will be loaded here -->
        </div>
        <div class="message-input">
            <form id="messageForm">
                <input type="text" id="messageInput" placeholder="Type your message..." required>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <script>
        // Get available employee or wait for one
        function findEmployee() {
            fetch('find_employee.php')
                .then(response => response.json())
                .then(result => {
                    if (result.employee_id) {
                        document.getElementById('status').style.display = 'none';
                        loadMessages(result.employee_id);
                    } else {
                        setTimeout(findEmployee, 5000);
                    }
                });
        }

        // Load messages
        function loadMessages() {
            fetch('get_client_messages.php')
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

            if (!message) return;

            fetch('send_client_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    input.value = '';
                    loadMessages();
                }
            });
        };

        // Initial load
        findEmployee();

        // Refresh messages every 3 seconds
        setInterval(loadMessages, 3000);
    </script>
</body>
</html> 