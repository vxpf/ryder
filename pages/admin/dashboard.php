<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
}

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_email = $_SESSION['admin_email'] ?? 'admin@rydr.nl';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Rydr</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f6f7f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #1a202c;
            color: white;
            padding: 20px 0;
            flex-shrink: 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #2d3748;
            margin-bottom: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .logo .dot {
            color: #ff3b58;
        }
        
        .admin-label {
            font-size: 12px;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #2d3748;
            color: white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 700;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-name {
            font-weight: 600;
        }
        
        .user-role {
            font-size: 12px;
            color: #666;
        }
        
        .logout-btn {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            color: #333;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background-color: #e9ecef;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
        }
        
        .card-btn {
            background-color: #3563e9;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .card-btn:hover {
            background-color: #2954d4;
        }
        
        .card-btn.secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .card-btn.secondary:hover {
            background-color: #e9ecef;
        }
        
        .chat-area {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            height: 600px;
        }
        
        .chat-list {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .chat-list-header {
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }
        
        .chat-list-content {
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .chat-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chat-item:hover {
            background-color: #f8f9fa;
        }
        
        .chat-item.active {
            background-color: #ebf5ff;
            border-left: 3px solid #3563e9;
        }
        
        .chat-item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .chat-item-name {
            font-weight: 600;
        }
        
        .chat-item-time {
            font-size: 12px;
            color: #666;
        }
        
        .chat-item-preview {
            font-size: 14px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .chat-window {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chat-window-header {
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chat-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #3563e9;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .chat-user-name {
            font-weight: 600;
        }
        
        .chat-user-status {
            font-size: 12px;
            color: #28a745;
        }
        
        .chat-window-actions {
            display: flex;
            gap: 10px;
        }
        
        .chat-window-content {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background-color: #f5f7fb;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message {
            max-width: 80%;
        }
        
        .system-message {
            align-self: center;
            background-color: #f0f0f0;
            padding: 8px 12px;
            border-radius: 15px;
            font-size: 13px;
            color: #666;
            text-align: center;
            width: auto;
            max-width: 90%;
        }
        
        .user-message {
            align-self: flex-end;
            background-color: #3563e9;
            color: white;
            padding: 10px 15px;
            border-radius: 18px 18px 0 18px;
        }
        
        .agent-message {
            align-self: flex-start;
            display: flex;
            gap: 10px;
        }
        
        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .message-content {
            background-color: white;
            padding: 10px 15px;
            border-radius: 18px 18px 18px 0;
            border: 1px solid #e0e0e0;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .message-name {
            font-weight: 600;
            color: #333;
        }
        
        .message-time {
            color: #999;
        }
        
        .message p {
            margin: 0;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .chat-window-input {
            padding: 15px;
            display: flex;
            gap: 10px;
            border-top: 1px solid #e9ecef;
        }
        
        .chat-window-input input {
            flex-grow: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-family: inherit;
            font-size: 14px;
        }
        
        .chat-window-input input:focus {
            outline: none;
            border-color: #3563e9;
            box-shadow: 0 0 0 2px rgba(53, 99, 233, 0.2);
        }
        
        .chat-window-input button {
            background-color: #3563e9;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chat-window-input button:hover {
            background-color: #2954d4;
        }
        
        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
        }
        
        .no-chat-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #a0aec0;
        }
        
        @media (max-width: 992px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 10px 0;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .chat-area {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .chat-list {
                height: 300px;
            }
            
            .chat-window {
                height: 500px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">Rydr<span class="dot">.</span></div>
                <div class="admin-label">Admin Dashboard</div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="/admin/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#"><i class="fas fa-comments"></i> Live Chats</a></li>
                <li><a href="#"><i class="fas fa-car"></i> Auto's</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Gebruikers</a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Reserveringen</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Instellingen</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="page-title">Dashboard</div>
                
                <div class="user-info">
                    <div>
                        <div class="user-name"><?= htmlspecialchars($admin_name) ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="/admin/logout" class="logout-btn">Uitloggen</a>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Actieve Chats</div>
                        <div class="card-actions">
                            <button class="card-btn">Alle chats bekijken</button>
                        </div>
                    </div>
                    <div>
                        <p>Er zijn momenteel <strong>2</strong> actieve chats.</p>
                        <p>Gemiddelde wachttijd: <strong>1 minuut</strong></p>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Statistieken</div>
                        <div class="card-actions">
                            <button class="card-btn secondary">Vernieuwen</button>
                        </div>
                    </div>
                    <div>
                        <p>Chats vandaag: <strong>12</strong></p>
                        <p>Gemiddelde chat duur: <strong>8 minuten</strong></p>
                        <p>Klanttevredenheid: <strong>4.8/5</strong></p>
                    </div>
                </div>
                
                <div class="chat-area">
                    <div class="chat-list">
                        <div class="chat-list-header">
                            Actieve Chats (2)
                        </div>
                        <div class="chat-list-content">
                            <div class="chat-item active" onclick="selectChat(1)">
                                <div class="chat-item-header">
                                    <div class="chat-item-name">Bezoeker #1</div>
                                    <div class="chat-item-time">Nu</div>
                                </div>
                                <div class="chat-item-preview">Ik heb een vraag over het huren van een auto...</div>
                            </div>
                            <div class="chat-item" onclick="selectChat(2)">
                                <div class="chat-item-header">
                                    <div class="chat-item-name">Bezoeker #2</div>
                                    <div class="chat-item-time">3 min</div>
                                </div>
                                <div class="chat-item-preview">Wat zijn de kosten voor een weekendhuur?</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chat-window">
                        <div class="chat-window-header">
                            <div class="chat-user-info">
                                <div class="chat-user-avatar">B1</div>
                                <div>
                                    <div class="chat-user-name">Bezoeker #1</div>
                                    <div class="chat-user-status">Online</div>
                                </div>
                            </div>
                            <div class="chat-window-actions">
                                <button class="card-btn secondary" onclick="endChat()">Chat beëindigen</button>
                            </div>
                        </div>
                        
                        <div class="chat-window-content" id="chat-messages">
                            <div class="message system-message">
                                <p>Chat gestart om <?= date('H:i') ?></p>
                            </div>
                            <div class="message user-message">
                                <p>Hallo! Ik heb een vraag over het huren van een auto voor het weekend.</p>
                            </div>
                            <div class="message agent-message">
                                <div class="message-avatar">
                                    <img src="/assets/images/team/brian-mensah.png" alt="Agent">
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <span class="message-name"><?= htmlspecialchars($admin_name) ?></span>
                                        <span class="message-time">Nu</span>
                                    </div>
                                    <p>Hallo! Natuurlijk, ik help u graag. Welk type auto zoekt u voor het weekend?</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="chat-window-input">
                            <input type="text" id="admin-message-input" placeholder="Typ een bericht...">
                            <button id="admin-send-message"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageInput = document.getElementById('admin-message-input');
            const sendButton = document.getElementById('admin-send-message');
            const chatMessages = document.getElementById('chat-messages');
            
            if (sendButton && messageInput) {
                sendButton.addEventListener('click', sendMessage);
                messageInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        sendMessage();
                    }
                });
            }
            
            function sendMessage() {
                const message = messageInput.value.trim();
                if (message) {
                    // Add agent message
                    const agentMessage = document.createElement('div');
                    agentMessage.className = 'message agent-message';
                    
                    agentMessage.innerHTML = `
                        <div class="message-avatar">
                            <img src="/assets/images/team/brian-mensah.png" alt="Agent">
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-name"><?= htmlspecialchars($admin_name) ?></span>
                                <span class="message-time">Nu</span>
                            </div>
                            <p>${escapeHtml(message)}</p>
                        </div>
                    `;
                    chatMessages.appendChild(agentMessage);
                    
                    // Clear input
                    messageInput.value = '';
                    
                    // Scroll to bottom
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    
                    // Simulate user response after delay
                    setTimeout(() => {
                        // Add user response
                        const userMessage = document.createElement('div');
                        userMessage.className = 'message user-message';
                        
                        // Generate a response based on the agent's message
                        let response = getSimulatedResponse(message);
                        
                        userMessage.innerHTML = `<p>${response}</p>`;
                        chatMessages.appendChild(userMessage);
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 2000);
                }
            }
            
            function getSimulatedResponse(message) {
                message = message.toLowerCase();
                
                if (message.includes('weekend') || message.includes('type auto')) {
                    return 'Ik zoek een middenklasse auto, liefst een SUV. Is dat mogelijk voor komend weekend?';
                } else if (message.includes('suv') || message.includes('middenklasse')) {
                    return 'Dat klinkt goed! Wat zijn de kosten voor een SUV voor het hele weekend?';
                } else if (message.includes('prijs') || message.includes('kosten') || message.includes('tarief')) {
                    return 'Bedankt voor de informatie. Is het mogelijk om de auto vrijdagmiddag op te halen en maandagochtend terug te brengen?';
                } else if (message.includes('ophalen') || message.includes('terugbrengen') || message.includes('vrijdag')) {
                    return 'Perfect! Hoe kan ik de reservering maken? Moet ik naar jullie kantoor komen of kan het online?';
                } else if (message.includes('reservering') || message.includes('boeken') || message.includes('online')) {
                    return 'Geweldig, bedankt voor je hulp! Ik ga nu direct de reservering maken via de website.';
                } else if (message.includes('bedankt') || message.includes('dank')) {
                    return 'Nog een laatste vraag: is er een borg die ik moet betalen?';
                } else {
                    return 'Bedankt voor de informatie. Kunt u mij meer vertellen over de beschikbaarheid voor komend weekend?';
                }
            }
            
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
        
        function selectChat(chatId) {
            // In a real app, this would load the chat history for the selected chat
            const chatItems = document.querySelectorAll('.chat-item');
            chatItems.forEach(item => {
                item.classList.remove('active');
            });
            
            event.currentTarget.classList.add('active');
            
            // Update chat window header
            document.querySelector('.chat-user-name').textContent = `Bezoeker #${chatId}`;
            document.querySelector('.chat-user-avatar').textContent = `B${chatId}`;
            
            // Clear chat messages and add initial messages
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';
            
            const systemMessage = document.createElement('div');
            systemMessage.className = 'message system-message';
            systemMessage.innerHTML = `<p>Chat gestart om ${new Date().getHours()}:${String(new Date().getMinutes()).padStart(2, '0')}</p>`;
            chatMessages.appendChild(systemMessage);
            
            if (chatId === 1) {
                const userMessage = document.createElement('div');
                userMessage.className = 'message user-message';
                userMessage.innerHTML = '<p>Hallo! Ik heb een vraag over het huren van een auto voor het weekend.</p>';
                chatMessages.appendChild(userMessage);
            } else {
                const userMessage = document.createElement('div');
                userMessage.className = 'message user-message';
                userMessage.innerHTML = '<p>Wat zijn de kosten voor een weekendhuur?</p>';
                chatMessages.appendChild(userMessage);
            }
        }
        
        function endChat() {
            if (confirm('Weet u zeker dat u deze chat wilt beëindigen?')) {
                const chatMessages = document.getElementById('chat-messages');
                
                const systemMessage = document.createElement('div');
                systemMessage.className = 'message system-message';
                systemMessage.innerHTML = '<p>Chat beëindigd</p>';
                chatMessages.appendChild(systemMessage);
                
                // In a real app, you would mark the chat as ended in the database
                // and remove it from the active chats list
                setTimeout(() => {
                    alert('Chat is beëindigd. In een echte applicatie zou deze chat nu worden gearchiveerd.');
                }, 500);
            }
        }
    </script>
</body>
</html> 