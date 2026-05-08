<?php

/**
 * Contrôleur pour les fonctionnalités IA de résumé automatique
 * Version GRATUITE - Utilise des algorithmes NLP intelligents
 */

// Charger le système de résumé gratuit
require_once __DIR__ . '/free_summarizer.php';

/**
 * Génère un résumé automatique GRATUIT du contenu d'un post
 * Utilise des algorithmes NLP intelligents pour extraire les points clés
 *
 * @param string $postContent Le contenu du post à résumer.
 * @return string Le résumé généré.
 */
function generateSummary(string $postContent): string
{
    return generateFreeSummary($postContent);
}