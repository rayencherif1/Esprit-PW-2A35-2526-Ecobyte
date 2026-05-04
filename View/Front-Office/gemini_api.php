<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('GEMINI_API_KEY', 'AIzaSyBGc0ZpFUnP27XV3m5ZCitTJ5KSWnWg2Mc');

// Fonction pour obtenir le modèle disponible
function getAvailableModel() {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . GEMINI_API_KEY;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['models'])) {
        foreach ($data['models'] as $model) {
            $name = $model['name'];
            // Chercher un modèle qui supporte generateContent
            if (strpos($name, 'gemini') !== false && 
                (strpos($name, 'flash') !== false || strpos($name, 'pro') !== false)) {
                return $name;
            }
        }
    }
    
    return 'models/gemini-1.5-flash'; // Valeur par défaut
}

function callGeminiAPI($prompt) {
    $model = getAvailableModel();
    $url = 'https://generativelanguage.googleapis.com/v1beta/' . $model . ':generateContent?key=' . GEMINI_API_KEY;
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Erreur API (HTTP ' . $httpCode . ')');
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return $result['candidates'][0]['content']['parts'][0]['text'];
    }
    
    throw new Exception('Réponse inattendue');
}

$input = json_decode(file_get_contents('php://input'), true);
$question = trim($input['question'] ?? '');
$context = $input['context'] ?? '';

if (empty($question)) {
    echo json_encode(['success' => false, 'error' => 'Question vide']);
    exit;
}

try {
    $response = callGeminiAPI("Réponds en français à: $question\n\nContexte: $context");
    
    echo json_encode([
        'success' => true,
        'response' => $response
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>