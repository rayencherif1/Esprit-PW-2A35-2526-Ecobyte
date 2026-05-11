<?php

/**
 * API de génération et récupération du résumé IA pour un post
 * 
 * Paramètres POST:
 * - post_id: ID du post pour lequel générer/récupérer le résumé
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../controller/ai_summary.php';
require_once __DIR__ . '/../controller/post.controller.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée. Utilisez POST.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $postId = (int) ($input['post_id'] ?? 0);

    if ($postId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID du post invalide.']);
        exit;
    }

    $postController = new PostC();
    $postData = $postController->getPostById($postId);

    if (!$postData) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Post non trouvé.']);
        exit;
    }

    // Vérifier si un résumé existe déjà
    $existingSummary = $postData['summary'] ?? null;

    // Si le résumé stocké est manifestement tronqué, régénérer un résumé complet
    if (!empty($existingSummary) && substr(trim($existingSummary), -3) !== '...') {
        http_response_code(200);
        echo json_encode(['success' => true, 'summary' => $existingSummary, 'cached' => true]);
        exit;
    }

    // Générer un nouveau résumé
    $summary = generateSummary($postData['contenu']);

    if (empty($summary)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Impossible de générer le résumé.']);
        exit;
    }

    // Sauvegarder le résumé dans la base de données
    try {
        $postController->updatePostSummary($postId, $summary);
    } catch (Exception $e) {
        // Log l'erreur mais continue (le résumé est quand même retourné)
        error_log('Erreur lors de la sauvegarde du résumé: ' . $e->getMessage());
    }

    http_response_code(200);
    echo json_encode(['success' => true, 'summary' => $summary, 'cached' => false]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
