<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/allergie.php';
require_once __DIR__ . '/../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../Controller/traitement.Controller.php';

session_start();

$allergieController = new AllergieC();
$allergies = $allergieController->listAllergie();
$traitementController = new TraitementC();

function getAllergiesContext($allergies, $traitementController) {
    $context = "=== BASE DE CONNAISSANCES SUR LES ALLERGIES ===\n\n";
    
    if (empty($allergies)) {
        $context .= "Aucune allergie dans la base de données.\n";
        return $context;
    }
    
    foreach ($allergies as $allergie) {
        $context .= "📌 ALLERGIE: " . ($allergie['nom'] ?? 'Nom inconnu') . "\n";
        $context .= "   ⚠️ Gravité: " . ($allergie['gravite'] ?? 'Non spécifiée') . "\n";
        $context .= "   📝 Description: " . ($allergie['description'] ?? 'Aucune') . "\n";
        $context .= "   🤧 Symptômes: " . ($allergie['symptomes'] ?? 'Aucun') . "\n";
        
        $traitements = $traitementController->listTraitementByAllergie($allergie['id_allergie']);
        if (!empty($traitements) && is_array($traitements)) {
            $context .= "   💊 Traitements:\n";
            foreach ($traitements as $traitement) {
                $context .= "      - " . ($traitement['nom_traitement'] ?? 'Sans nom') . "\n";
                if (!empty($traitement['conseils'])) {
                    $context .= "        ✅ Conseils: " . $traitement['conseils'] . "\n";
                }
                if (!empty($traitement['interdiction'])) {
                    $context .= "        🚫 Interdictions: " . $traitement['interdiction'] . "\n";
                }
            }
        }
        $context .= "\n";
    }
    
    return $context;
}

$allergiesContext = getAllergiesContext($allergies, $traitementController);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AllergieBot - Assistant IA spécialisé</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        
        .chat-container { height: calc(100vh - 200px); display: flex; flex-direction: column; }
        .messages-area { flex: 1; overflow-y: auto; padding: 1rem; }
        .message { animation: slideIn 0.3s ease; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .typing-indicator { display: inline-flex; align-items: center; gap: 4px; padding: 8px 12px; }
        .typing-indicator span { width: 8px; height: 8px; border-radius: 50%; background-color: #9CA3AF; animation: typing 1.4s infinite; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-10px); opacity: 1; }
        }
        
        .suggestion-chip { transition: all 0.2s ease; cursor: pointer; }
        .suggestion-chip:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .message-content { line-height: 1.5; }
        .message-content ul, .message-content ol { margin-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-purple-50">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🤖</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">AllergieBot</h1>
                        <p class="text-xs opacity-90">Assistant IA spécialisé en allergies</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="clearChat()" class="bg-white/20 hover:bg-white/30 rounded-lg px-3 py-2 text-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Effacer
                    </button>
                    <a href="allergie.php" class="bg-white/20 hover:bg-white/30 rounded-lg px-4 py-2 text-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="container mx-auto px-4 mt-4">
        <div class="bg-white rounded-lg shadow-md p-3 flex justify-around items-center">
            <div class="text-center"><div class="text-2xl font-bold text-blue-600"><?= count($allergies) ?></div><div class="text-xs text-gray-500">Allergies</div></div>
            <div class="h-8 w-px bg-gray-300"></div>
            <div class="text-center"><div class="text-2xl font-bold text-green-600">🤖</div><div class="text-xs text-gray-500">IA Active</div></div>
            <div class="h-8 w-px bg-gray-300"></div>
            <div class="text-center"><div class="text-2xl font-bold text-purple-600">✅</div><div class="text-xs text-gray-500">Prêt</div></div>
        </div>
    </div>

    <!-- Chat Container -->
    <div class="container mx-auto px-4 py-4">
        <div class="chat-container bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-200">
            <div id="messagesArea" class="messages-area bg-gradient-to-b from-gray-50 to-white">
                <div class="message mb-4 flex justify-start">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl rounded-tl-none px-5 py-3 max-w-[85%] shadow-md">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-lg">🤖</span>
                            <span class="font-semibold">AllergieBot</span>
                            <span class="text-xs opacity-75">● En ligne</span>
                        </div>
                        <div class="message-content text-sm">
                            <p>Bonjour ! 👋 Je suis votre assistant médical spécialisé dans les allergies.</p>
                            <p class="mt-2">Je peux vous renseigner sur :</p>
                            <ul class="mt-1"><li>✅ Les différents types d'allergies</li><li>✅ Les symptômes associés</li><li>✅ Les traitements disponibles</li><li>✅ Les conseils et précautions</li></ul>
                            <p class="mt-2 font-semibold">Posez-moi toutes vos questions ! 🌿</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Suggestions -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <button onclick="sendSuggestion('Quels sont les types d\'allergies les plus courants ?')" class="suggestion-chip bg-blue-100 hover:bg-blue-200 rounded-full px-4 py-2 text-xs font-medium whitespace-nowrap transition shadow-sm">🤧 Types d'allergies</button>
                    <button onclick="sendSuggestion('Comment reconnaître les symptômes d\'une allergie ?')" class="suggestion-chip bg-green-100 hover:bg-green-200 rounded-full px-4 py-2 text-xs font-medium whitespace-nowrap transition shadow-sm">🩺 Symptômes</button>
                    <button onclick="sendSuggestion('Quels traitements existent contre les allergies ?')" class="suggestion-chip bg-yellow-100 hover:bg-yellow-200 rounded-full px-4 py-2 text-xs font-medium whitespace-nowrap transition shadow-sm">💊 Traitements</button>
                    <button onclick="sendSuggestion('Comment réagir en cas de choc anaphylactique ?')" class="suggestion-chip bg-red-100 hover:bg-red-200 rounded-full px-4 py-2 text-xs font-medium whitespace-nowrap transition shadow-sm">🚨 Urgences</button>
                    <button onclick="sendSuggestion('Quelles précautions prendre pour éviter les allergies ?')" class="suggestion-chip bg-purple-100 hover:bg-purple-200 rounded-full px-4 py-2 text-xs font-medium whitespace-nowrap transition shadow-sm">🛡️ Prévention</button>
                </div>
            </div>
            
            <!-- Input -->
            <div class="bg-white border-t border-gray-200 p-4">
                <form id="chatForm" class="flex gap-2">
                    <input type="text" id="userInput" placeholder="Écrivez votre question sur les allergies..." class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" autocomplete="off">
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all font-semibold hover:scale-105">
                        <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Envoyer
                    </button>
                </form>
                <p class="text-xs text-gray-400 mt-2 text-center">🤖 AllergieBot utilise l'IA Gemini pour répondre à vos questions</p>
            </div>
        </div>
    </div>

    <script>
        const messagesArea = document.getElementById('messagesArea');
        const chatForm = document.getElementById('chatForm');
        const userInput = document.getElementById('userInput');
        let isWaiting = false;
        const allergiesContext = <?= json_encode($allergiesContext) ?>;
        const API_URL = 'gemini_api.php';
        
        function addMessage(role, content, isMarkdown = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message mb-4 flex ${role === 'user' ? 'justify-end' : 'justify-start'}`;
            
            if (role === 'user') {
                messageDiv.innerHTML = `<div class="bg-blue-600 text-white rounded-2xl rounded-tr-none px-5 py-3 max-w-[85%] shadow-md"><div class="flex items-center gap-2 mb-1"><span class="text-sm">👤</span><span class="font-semibold text-xs">Vous</span></div><p class="text-sm">${escapeHtml(content)}</p></div>`;
            } else {
                let formattedContent = content;
                if (isMarkdown && typeof marked !== 'undefined') {
                    formattedContent = marked.parse(content);
                } else {
                    formattedContent = `<p>${escapeHtml(content)}</p>`;
                }
                messageDiv.innerHTML = `<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl rounded-tl-none px-5 py-3 max-w-[85%] shadow-md"><div class="flex items-center gap-2 mb-2"><span class="text-sm">🤖</span><span class="font-semibold text-xs">AllergieBot</span></div><div class="message-content text-sm">${formattedContent}</div></div>`;
            }
            messagesArea.appendChild(messageDiv);
            scrollToBottom();
        }
        
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.className = 'message mb-4 flex justify-start';
            typingDiv.innerHTML = `<div class="bg-gray-200 text-gray-700 rounded-2xl rounded-tl-none px-5 py-3 shadow-md"><div class="typing-indicator"><span></span><span></span><span></span><span class="ml-2 text-xs text-gray-500">AllergieBot réfléchit...</span></div></div>`;
            messagesArea.appendChild(typingDiv);
            scrollToBottom();
        }
        
        function hideTypingIndicator() { const indicator = document.getElementById('typingIndicator'); if (indicator) indicator.remove(); }
        function scrollToBottom() { messagesArea.scrollTop = messagesArea.scrollHeight; }
        function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }
        
        function clearChat() {
            if (confirm('Voulez-vous vraiment effacer la conversation ?')) {
                const welcomeMessage = messagesArea.querySelector('.message');
                messagesArea.innerHTML = '';
                if (welcomeMessage) messagesArea.appendChild(welcomeMessage);
                else location.reload();
                scrollToBottom();
            }
        }
        
        async function sendMessage(question) {
            if (isWaiting || !question.trim()) return;
            addMessage('user', question);
            userInput.value = '';
            isWaiting = true;
            userInput.disabled = true;
            showTypingIndicator();
            
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ question: question, context: allergiesContext })
                });
                
                if (!response.ok) throw new Error('Erreur serveur');
                const data = await response.json();
                hideTypingIndicator();
                
                if (data.success) addMessage('assistant', data.response, true);
                else addMessage('assistant', "❌ " + (data.error || 'Erreur inconnue'), false);
            } catch (error) {
                hideTypingIndicator();
                addMessage('assistant', "❌ Erreur: " + error.message, false);
            } finally {
                isWaiting = false;
                userInput.disabled = false;
                userInput.focus();
            }
        }
        
        function sendSuggestion(question) { if (!isWaiting) sendMessage(question); }
        chatForm.addEventListener('submit', (e) => { e.preventDefault(); sendMessage(userInput.value); });
        userInput.addEventListener('keypress', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(userInput.value); } });
        setTimeout(scrollToBottom, 100);
        userInput.focus();
    </script>
</body>
</html>