<?php

/**
 * API de traduction automatique
 * Utilise l'API MyMemory (gratuite, sans authentification)
 * 
 * Paramètres POST:
 * - text: texte à traduire
 * - target_lang: langue cible (ex: 'fr', 'en', 'es')
 */

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée. Utilisez POST.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $text = trim($input['text'] ?? '');
    $targetLang = trim($input['target_lang'] ?? 'fr');

    if (empty($text)) {
        http_response_code(400);
        echo json_encode(['error' => 'Le texte à traduire est obligatoire.']);
        exit;
    }

    if (strlen($text) > 5000) {
        http_response_code(400);
        echo json_encode(['error' => 'Le texte ne doit pas dépasser 5000 caractères.']);
        exit;
    }

    // Appel à l'API MyMemory
    $apiUrl = 'https://api.mymemory.translated.net/get';
    $params = [
        'q' => $text,
        'langpair' => 'en|' . strtolower($targetLang),
    ];

    $url = $apiUrl . '?' . http_build_query($params);

    // Utiliser cURL pour l'appel HTTP
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if (!$response || $curlError) {
        http_response_code(502);
        echo json_encode(['error' => 'Erreur lors de la connexion au service de traduction.']);
        exit;
    }

    $result = json_decode($response, true);

    if ($httpCode !== 200 || empty($result)) {
        http_response_code(502);
        echo json_encode(['error' => 'Le service de traduction n\'a pas répondu correctement.']);
        exit;
    }

    $translatedText = $result['responseData']['translatedText'] ?? null;
    $responseStatus = $result['responseStatus'] ?? 0;

    if ($responseStatus !== 200 || !$translatedText) {
        http_response_code(400);
        echo json_encode(['error' => 'La traduction a échoué. Vérifiez les paramètres.']);
        exit;
    }

    // Réponse réussie
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'original' => $text,
        'translated' => $translatedText,
        'target_lang' => $targetLang,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
