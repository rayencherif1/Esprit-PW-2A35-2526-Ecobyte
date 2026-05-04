<?php
/**
 * Fonctions utilitaires globales (échappement HTML, redirection HTTP).
 */

declare(strict_types=1);

/**
 * Échappe une chaîne pour l’affichage dans du HTML (prévention XSS).
 *
 * @param string|null $value Texte brut venant de la base ou du formulaire
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Redirige le navigateur vers une URL interne du site.
 *
 * @param string $path Chemin relatif (ex: /admin/index.php?action=dashboard)
 */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit; // Stoppe le script après l’en-tête de redirection
}
