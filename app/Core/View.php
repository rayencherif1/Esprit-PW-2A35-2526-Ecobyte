<?php
/**
 * Couche View : inclut un fichier PHP dans Views/ en injectant des variables.
 * Aucune requête SQL ici — uniquement de la présentation.
 */

declare(strict_types=1);

final class View
{
    /**
     * Affiche une vue en extrayant le tableau $data en variables locales.
     *
     * @param string $template Chemin relatif depuis Views/ (ex: 'front/home')
     * @param array<string,mixed> $data Données passées à la vue (clé => valeur)
     */
    public static function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP); // Crée $programs, $errors, etc. à partir des clés

        $file = VIEW_PATH . '/' . $template . '.php'; // Chemin absolu du fichier vue

        if (!is_file($file)) {
            http_response_code(500);
            echo 'Vue introuvable : ' . e($template);
            return;
        }

        require $file; // Le fichier utilise les variables extraites
    }
}
