<?php
/**
 * Contrôleur front-office : page d’accueil + lancement d’un programme.
 */

declare(strict_types=1);

final class FrontController
{
    private ProgramModel $programModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
    }

    /**
     * Routeur interne du front : $_GET['action'].
     */
    public function dispatch(string $action): void
    {
        switch ($action) {
            case 'home':
                $this->homeAction();
                break;
            case 'program_start':
                $this->programStartAction();
                break;
            default:
                $this->homeAction();
        }
    }

    /**
     * Page d’accueil : liste des programmes + formulaire de recherche (GET).
     */
    private function homeAction(): void
    {
        $type = isset($_GET['type']) ? (string) $_GET['type'] : '';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $typeFilter = in_array($type, TYPES_ENTRAINEMENT, true) ? $type : null;

        $programs = $this->programModel->findAll($typeFilter, $q !== '' ? $q : null);

        View::render('front/home', [
            'programs' => $programs,
            'filterType' => $type,
            'searchQ' => $q,
            'types' => TYPES_ENTRAINEMENT,
        ]);
    }

    /**
     * Page « Démarrer » : affiche les exercices du programme pour l’utilisateur.
     */
    private function programStartAction(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $bundle = $this->programModel->findWithExercises($id);

        if ($bundle['program'] === null) {
            View::render('front/program_not_found', []);
            return;
        }

        View::render('front/program_runner', [
            'program' => $bundle['program'],
            'exercises' => $bundle['exercises'],
        ]);
    }
}
