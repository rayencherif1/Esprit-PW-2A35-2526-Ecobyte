<?php
/**
 * Chargement automatique des classes (Models, Controllers) sans Composer.
 * Quand PHP voit "new ExerciseModel()", il cherche le fichier correspondant.
 */

declare(strict_types=1);

spl_autoload_register(
    /**
     * @param string $className Nom de la classe demandée (ex: ExerciseModel)
     */
    static function (string $className): void {
        $base = APP_PATH; // Dossier racine des classes

        $candidates = [
            $base . '/Models/' . $className . '.php', // Modèles PDO
            $base . '/Controllers/' . $className . '.php', // Contrôleurs front
            $base . '/Controllers/Admin/' . $className . '.php', // Contrôleurs admin
            $base . '/Core/' . $className . '.php', // Utilitaires (View, etc.)
        ];

        foreach ($candidates as $file) {
            if (is_file($file)) {
                require_once $file; // Charge la classe une seule fois
                return;
            }
        }
    }
);

require_once APP_PATH . '/helpers.php'; // Fonctions e(), redirect(), etc.
