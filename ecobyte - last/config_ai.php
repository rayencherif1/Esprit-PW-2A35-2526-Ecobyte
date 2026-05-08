<?php

/**
 * Configuration pour les services IA externes
 * À compléter avec vos clés API
 */

// ===== OpenAI Configuration =====
// Obtenir votre clé: https://platform.openai.com/account/api-keys
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: 'sk-your-api-key-here');

// Model à utiliser (gpt-3.5-turbo, gpt-4, etc.)
define('OPENAI_MODEL', 'gpt-3.5-turbo');

// Timeout pour les appels API (en secondes)
define('API_TIMEOUT', 30);

// ===== Configuration du résumé IA =====
// Nombre maximum de tokens pour le résumé
define('SUMMARY_MAX_TOKENS', 150);

// Température de génération (0.0 = déterministe, 1.0 = créatif)
define('SUMMARY_TEMPERATURE', 0.5);

// Longueur minimale du contenu pour générer un résumé (caractères)
define('SUMMARY_MIN_CONTENT_LENGTH', 50);

// Longueur maximale du contenu à envoyer à l'API (caractères)
define('SUMMARY_MAX_CONTENT_LENGTH', 2000);

// ===== Fonction utilitaire =====
function getAIConfig($key, $default = null) {
    $config = [
        'api_key' => OPENAI_API_KEY,
        'model' => OPENAI_MODEL,
        'timeout' => API_TIMEOUT,
        'summary_max_tokens' => SUMMARY_MAX_TOKENS,
        'summary_temperature' => SUMMARY_TEMPERATURE,
        'summary_min_length' => SUMMARY_MIN_CONTENT_LENGTH,
        'summary_max_length' => SUMMARY_MAX_CONTENT_LENGTH,
    ];
    
    return $config[$key] ?? $default;
}
