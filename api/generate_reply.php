<?php

/**
 * API de génération de réponse IA pour les questions nutritionnelles
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controller/ai_reply.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['post_content']) || !isset($input['question'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètres manquants: post_content et question requis.']);
        exit;
    }

    $postContent = trim($input['post_content']);
    $question = trim($input['question']);

    if (empty($postContent) || empty($question)) {
        http_response_code(400);
        echo json_encode(['error' => 'Contenu vide.']);
        exit;
    }

    $response = generateAiReplyText($postContent, $question);

    http_response_code(200);
    echo json_encode(['success' => true, 'response' => $response]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}