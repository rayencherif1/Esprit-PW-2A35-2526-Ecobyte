<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AllergieBot - Assistant IA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .chat-container {
            width: 90%;
            max-width: 800px;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .chat-header h1 { font-size: 24px; margin-bottom: 5px; }
        .chat-header p { font-size: 12px; opacity: 0.9; }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .message { margin-bottom: 15px; display: flex; }
        .message.user { justify-content: flex-end; }
        .message.bot { justify-content: flex-start; }
        .message-content {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
        }
        .user .message-content {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 4px;
        }
        .bot .message-content {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
        }
        .chat-input input:focus { border-color: #667eea; }
        .chat-input button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
        }
        .typing-indicator {
            display: flex;
            gap: 5px;
            padding: 10px 15px;
        }
        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-10px); opacity: 1; }
        }
        .suggestions {
            padding: 10px 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }
        .suggestion-btn {
            padding: 6px 12px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
        }
        .suggestion-btn:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>🤖 AllergieBot</h1>
            <p>Assistant IA - Réponses intelligentes et naturelles</p>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message bot">
                <div class="message-content">
                    👋 Bonjour ! Je suis AllergieBot, votre assistant IA.<br>
                    Je peux répondre à toutes vos questions sur les allergies ou autre chose !<br><br>
                    Que voulez-vous savoir ?
                </div>
            </div>
        </div>
        
        <div class="suggestions">
            <button class="suggestion-btn" onclick="sendMessage('Quels sont les symptômes d\'une allergie ?')">🤧 Symptômes</button>
            <button class="suggestion-btn" onclick="sendMessage('Comment soigner une allergie alimentaire ?')">💊 Traitements</button>
            <button class="suggestion-btn" onclick="sendMessage('Que faire en cas de choc anaphylactique ?')">🚨 Urgence</button>
            <button class="suggestion-btn" onclick="sendMessage('Salut, comment vas-tu ?')">👋 Dis bonjour</button>
        </div>
        
        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Posez votre question..." onkeypress="if(event.key==='Enter') sendMessage()">
            <button onclick="sendMessage()">Envoyer</button>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        
        function addMessage(text, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;
            messageDiv.innerHTML = `<div class="message-content" style="white-space: pre-line;">${escapeHtml(text)}</div>`;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function addTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = `<div class="message-content"><div class="typing-indicator"><span></span><span></span><span></span></div></div>`;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function removeTypingIndicator() {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();
        }
        
        async function sendMessage(question = null) {
            const message = question || messageInput.value.trim();
            if (!message) return;
            
            addMessage(message, true);
            messageInput.value = '';
            addTypingIndicator();
            
            try {
                const response = await fetch('groq_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ question: message })
                });
                
                const data = await response.json();
                removeTypingIndicator();
                
                if (data.success) {
                    addMessage(data.response);
                } else {
                    addMessage('❌ Désolé, une erreur est survenue. Veuillez réessayer.');
                }
            } catch (error) {
                removeTypingIndicator();
                addMessage('❌ Erreur de connexion. Vérifiez que le serveur fonctionne.');
            }
        }
        
        // Exposer sendMessage globalement
        window.sendMessage = sendMessage;
    </script>
</body>
</html>