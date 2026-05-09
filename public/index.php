<?php
/**
 * Point d’entrée FRONT OFFICE — toutes les pages passent par ce fichier.
 * Routage : paramètre GET « action » (ex. index.php?action=home).
 */

declare(strict_types=1);

require dirname(__DIR__) . '/config/config.php'; // Constantes BDD + URLs
require dirname(__DIR__) . '/app/bootstrap.php'; // Autoload + helpers

$action = isset($_GET['action']) ? (string) $_GET['action'] : 'home'; // Action demandée

if (str_starts_with($action, 'user_program_')) {
    (new UserProgramController())->dispatch($action);
} elseif (str_starts_with($action, 'front_program_')) {
    (new FrontProgramManageController())->dispatch($action);
} else {
    (new FrontController())->dispatch($action);
}
