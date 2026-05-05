<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// VOTRE CLÉ API GROQ
$api_key = '';

$input = json_decode(file_get_contents('php://input'), true);
$question = trim($input['question'] ?? '');

if (empty($question)) {
    echo json_encode(['success' => false, 'error' => 'Question vide']);
    exit;
}

// Pas de contexte, l'IA répond librement comme ChatGPT
$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => 'llama-3.1-8b-instant',
    'messages' => [
        ['role' => 'system', 'content' => 'Tu es AllergieBot, un assistant IA amical et utile. Tu réponds à toutes les questions en français, de manière naturelle et conviviale.'],
        ['role' => 'user', 'content' => $question]
    ],
    'temperature' => 0.8,
    'max_tokens' => 500
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['success' => false, 'error' => 'Erreur: ' . $curlError]);
    exit;
}

if ($httpCode == 200) {
    $result = json_decode($response, true);
    $reply = $result['choices'][0]['message']['content'];
    echo json_encode(['success' => true, 'response' => $reply]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erreur API (HTTP ' . $httpCode . ')']);
}
?>