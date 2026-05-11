<?php
/**
 * Point d’entrée BACK OFFICE (admin) — CRUD exercices & programmes + tableau de bord.
 * Routage : GET action=dashboard | exercise_* | program_*
 */

declare(strict_types=1);

require dirname(dirname(__DIR__)) . '/config/config.php';
require dirname(dirname(__DIR__)) . '/app/bootstrap.php';

$action = isset($_GET['action']) ? (string) $_GET['action'] : 'dashboard'; // Défaut : stats

if ($action === 'dashboard') {
    (new AdminDashboardController())->dispatch(); // Graphique types d’exercices
} elseif (strpos($action, 'exercise_') === 0) {
    (new AdminExerciseController())->dispatch($action); // Liste / formulaire / save / delete
} elseif (strpos($action, 'program_') === 0) {
    (new AdminProgramController())->dispatch($action); // Programmes + jointure
} else {
    (new AdminDashboardController())->dispatch(); // Action inconnue → dashboard
}
