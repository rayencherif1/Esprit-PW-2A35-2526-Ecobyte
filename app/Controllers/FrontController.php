<?php
/**
 * Contrôleur front-office : accueil, lancement programme, « IA » par règles.
 */

declare(strict_types=1);

final class FrontController
{
    private ProgramModel $programModel;
    private ExerciseModel $exerciseModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->exerciseModel = new ExerciseModel();
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
            case 'recommandation_ia':
                $this->recommandationAction();
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

    /**
     * « IA » pédagogique : formulaire + règles déterministes (pas d’appel réseau IA).
     * Le prof voit une fonctionnalité « intelligente » basée sur le profil saisi.
     */
    private function recommandationAction(): void
    {
        $suggestion = null; // Programme MySQL recommandé
        $message = ''; // Texte explicatif pour l’utilisateur
        $errors = []; // Erreurs de validation POST

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $objectif = isset($_POST['objectif']) ? (string) $_POST['objectif'] : '';
            $niveau = isset($_POST['niveau']) ? (string) $_POST['niveau'] : '';
            $joursRaw = isset($_POST['jours_par_semaine']) ? trim((string) $_POST['jours_par_semaine']) : '';
            $lieu = isset($_POST['lieu']) ? (string) $_POST['lieu'] : '';

            $errors = $this->validateIaForm($objectif, $niveau, $joursRaw, $lieu);

            if ($errors === []) {
                $typeProg = $this->inferProgramType($objectif, $niveau, (int) $joursRaw, $lieu); // Logique métier
                $suggestion = $this->programModel->findFirstByType($typeProg); // Premier programme du type
                $message = $this->buildIaMessage($typeProg, $niveau, (int) $joursRaw, $lieu, $suggestion !== null);
            }
        }

        View::render('front/recommandation_ia', [
            'suggestion' => $suggestion,
            'message' => $message,
            'errors' => $errors,
            'post' => $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [],
        ]);
    }

    /**
     * @return list<string>
     */
    private function validateIaForm(string $objectif, string $niveau, string $joursRaw, string $lieu): array
    {
        $errors = [];
        $objOk = ['perte_de_poids', 'musculation', 'cardio'];
        if (!in_array($objectif, $objOk, true)) {
            $errors[] = 'Objectif invalide.';
        }

        $nivOk = ['debutant', 'intermediaire', 'avance'];
        if (!in_array($niveau, $nivOk, true)) {
            $errors[] = 'Niveau invalide.';
        }

        if ($joursRaw === '' || !ctype_digit($joursRaw)) {
            $errors[] = 'Indiquez le nombre de jours par semaine avec des chiffres uniquement (1 à 7).';
        } else {
            $j = (int) $joursRaw;
            if ($j < 1 || $j > 7) {
                $errors[] = 'Le nombre de jours doit être entre 1 et 7.';
            }
        }

        $lieuOk = ['maison', 'salle', 'mixte'];
        if (!in_array($lieu, $lieuOk, true)) {
            $errors[] = 'Lieu d’entraînement invalide.';
        }

        return $errors;
    }

    /**
     * Déduit le type de programme MySQL à proposer (ENUM aligné sur la base).
     */
    private function inferProgramType(string $objectif, string $niveau, int $jours, string $lieu): string
    {
        if ($objectif === 'perte_de_poids') {
            return 'perte_de_poids';
        }

        if ($objectif === 'cardio') {
            return 'cardio';
        }

        if ($objectif === 'musculation') {
            if ($niveau === 'debutant' && $lieu === 'maison') {
                return 'perte_de_poids';
            }
            return 'musculation';
        }

        return 'musculation';
    }

    /**
     * Texte pédagogique affiché sous la suggestion (transparence : ce ne sont pas des modèles GPT).
     */
    private function buildIaMessage(string $typeProg, string $niveau, int $jours, string $lieu, bool $found): string
    {
        $base = 'Analyse basée sur vos réponses (règles métier, pas sur un modèle d’IA externe). ';
        $base .= 'Type cible : ' . $typeProg . '. ';
        $base .= 'Niveau ' . $niveau . ', ' . $jours . ' j/semaine, lieu : ' . $lieu . '. ';

        if (!$found) {
            $base .= 'Aucun programme de ce type en base pour l’instant — créez-en un dans l’admin.';
        } else {
            $base .= 'Voici un programme correspondant dans votre catalogue.';
        }

        return $base;
    }
}
