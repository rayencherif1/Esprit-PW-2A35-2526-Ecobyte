<?php
/**
 * Point d’entrée FRONT OFFICE — toutes les pages passent par ce fichier.
 * Routage : paramètre GET « action » (ex. index.php?action=home).
 */

declare(strict_types=1);

require dirname(__DIR__) . '/config/config.php'; // Constantes BDD + URLs
require dirname(__DIR__) . '/app/bootstrap.php'; // Autoload + helpers

$action = isset($_GET['action']) ? (string) $_GET['action'] : 'home'; // Action demandée

$controller = new FrontController(); // Contrôleur dédié au site public
$controller->dispatch($action); // Délègue à home, program_start, etc.
