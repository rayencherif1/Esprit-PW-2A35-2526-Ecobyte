<?php
/**
 * Chargement automatique des classes (MVC + services/core) sans Composer.
 * Quand PHP voit "new ExerciseModel()", il cherche le fichier correspondant.
 */

declare(strict_types=1);

spl_autoload_register(
    /**
     * @param string $className Nom de la classe demandée (ex: ExerciseModel)
     */
    static function (string $className): void {
        $candidates = [
            MODEL_PATH . '/' . $className . '.php', // Modèles MVC
            CONTROLLER_PATH . '/' . $className . '.php', // Contrôleurs MVC
            APP_PATH . '/Core/' . $className . '.php', // Utilitaires (View, EnvLoader, etc.)
            APP_PATH . '/Services/' . $className . '.php', // Clients externes (Ollama, etc.)
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
