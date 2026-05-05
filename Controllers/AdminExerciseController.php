<?php
/**
 * Contrôleur admin — CRUD exercices.
 * Toute validation des entrées utilisateur se fait ICI (pas dans le modèle, pas en HTML5).
 */

declare(strict_types=1);

final class AdminExerciseController
{
    /** Modèle pour l’accès base de données */
    private ExerciseModel $exerciseModel;

    public function __construct()
    {
        $this->exerciseModel = new ExerciseModel(); // Injection simple sans framework
    }

    /**
     * Point d’entrée : exécute l’action demandée par $_GET['action'].
     */
    public function dispatch(string $action): void
    {
        switch ($action) {
            case 'exercise_list':
                $this->listAction(); // Liste + filtres
                break;
            case 'exercise_new':
                $this->formAction(null); // Formulaire vide
                break;
            case 'exercise_edit':
                $id = isset($_GET['id']) ? (int) $_GET['id'] : 0; // ID depuis l’URL
                $this->formAction($id > 0 ? $id : null); // Édition si id valide
                break;
            case 'exercise_save':
                $this->saveAction(); // POST création / mise à jour
                break;
            case 'exercise_delete':
                $this->deleteAction(); // POST suppression
                break;
            default:
                $this->listAction(); // Action inconnue → liste
        }
    }

    /**
     * Affiche la liste avec recherche (GET) et messages d’erreur éventuels (session flash simple via query).
     */
    private function listAction(): void
    {
        $type = isset($_GET['type']) ? (string) $_GET['type'] : ''; // Filtre type
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : ''; // Recherche nom

        $typeFilter = in_array($type, TYPES_ENTRAINEMENT, true) ? $type : null; // Sécurise la valeur

        $rows = $this->exerciseModel->findAll($typeFilter, $q !== '' ? $q : null); // Requête préparée côté modèle

        $message = isset($_GET['msg']) ? (string) $_GET['msg'] : ''; // Message positif après save
        $error = isset($_GET['err']) ? (string) $_GET['err'] : ''; // Message d’erreur courte

        View::render('admin/exercise_list', [
            'exercises' => $rows,
            'filterType' => $type,
            'searchQ' => $q,
            'message' => $message,
            'error' => $error,
        ]);
    }

    /**
     * Formulaire création (id null) ou édition (id entier).
     */
    private function formAction(?int $id): void
    {
        $row = null;
        $errors = [];

        if ($id !== null) {
            $row = $this->exerciseModel->findById($id); // Charge la ligne existante
            if ($row === null) {
                $errors[] = 'Exercice introuvable.'; // ID invalide
            }
        }

        View::render('admin/exercise_form', [
            'exercise' => $row,
            'errors' => $errors,
            'types' => TYPES_ENTRAINEMENT,
            'wgerMuscles' => WGER_MUSCLES,
        ]);
    }

    /**
     * Enregistre les données POST après validation.
     */
    private function saveAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(ADMIN_URL . '/index.php?action=exercise_list&err=' . rawurlencode('Méthode invalide.'));
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0; // 0 = création
        $nom = isset($_POST['nom']) ? trim((string) $_POST['nom']) : '';
        $type = isset($_POST['type_exercice']) ? (string) $_POST['type_exercice'] : '';
        $etapes = isset($_POST['etapes']) ? trim((string) $_POST['etapes']) : '';
        $benefices = isset($_POST['benefices']) ? trim((string) $_POST['benefices']) : '';
        $urlImage = isset($_POST['url_image']) ? trim((string) $_POST['url_image']) : '';
        $urlVideo = isset($_POST['url_video']) ? trim((string) $_POST['url_video']) : '';
        $nbRepRaw = isset($_POST['nb_repetitions_suggerees']) ? trim((string) $_POST['nb_repetitions_suggerees']) : '';
        $muscleRaw = isset($_POST['muscle_wger_id']) ? trim((string) $_POST['muscle_wger_id']) : '';

        $errors = $this->validateExerciseFields($nom, $type, $etapes, $benefices, $urlImage, $urlVideo, $nbRepRaw, $muscleRaw);

        if ($id > 0 && $this->exerciseModel->findById($id) === null) {
            $errors[] = 'Impossible de modifier : exercice inexistant.'; // Cohérence édition
        }

        if ($errors !== []) {
            View::render('admin/exercise_form', [
                'exercise' => $this->mockRowFromPost($id, $nom, $type, $etapes, $benefices, $urlImage, $urlVideo, $nbRepRaw, $muscleRaw),
                'errors' => $errors,
                'types' => TYPES_ENTRAINEMENT,
                'wgerMuscles' => WGER_MUSCLES,
            ]);
            return;
        }

        $nbRep = (int) $nbRepRaw; // Déjà validé comme entier
        $muscleId = $muscleRaw === '' ? null : (int) $muscleRaw; // NULL si vide

        $fields = [
            'nom' => $nom,
            'type_exercice' => $type,
            'etapes' => $etapes,
            'benefices' => $benefices,
            'url_image' => $urlImage,
            'url_video' => $urlVideo,
            'nb_repetitions_suggerees' => $nbRep,
            'muscle_wger_id' => $muscleId,
        ];

        if ($id > 0) {
            $this->exerciseModel->update($id, $fields); // UPDATE préparé
        } else {
            $this->exerciseModel->insert($fields); // INSERT préparé
        }

        redirect(ADMIN_URL . '/index.php?action=exercise_list&msg=' . rawurlencode('Enregistrement réussi.'));
    }

    /**
     * Suppression confirmée par formulaire POST (pas par simple lien GET).
     */
    private function deleteAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(ADMIN_URL . '/index.php?action=exercise_list&err=' . rawurlencode('Suppression refusée (POST requis).'));
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0 || $this->exerciseModel->findById($id) === null) {
            redirect(ADMIN_URL . '/index.php?action=exercise_list&err=' . rawurlencode('Exercice introuvable.'));
        }

        $this->exerciseModel->delete($id); // DELETE préparé

        redirect(ADMIN_URL . '/index.php?action=exercise_list&msg=' . rawurlencode('Exercice supprimé.'));
    }

    /**
     * Règles métier : messages en français affichés dans la vue (pas d’alert() JS).
     *
     * @return list<string>
     */
    private function validateExerciseFields(
        string $nom,
        string $type,
        string $etapes,
        string $benefices,
        string $urlImage,
        string $urlVideo,
        string $nbRepRaw,
        string $muscleRaw
    ): array {
        $errors = [];

        if ($nom === '') {
            $errors[] = 'Le nom est obligatoire.';
        } elseif (!preg_match('/^[\p{L} \-\'’]+$/u', $nom)) {
            $errors[] = 'Le nom doit contenir uniquement des lettres (espaces, tiret et apostrophe autorisés).';
        }

        if (!in_array($type, TYPES_ENTRAINEMENT, true)) {
            $errors[] = 'Le type d’exercice est invalide.';
        }

        if ($etapes === '') {
            $errors[] = 'Les étapes sont obligatoires.';
        }

        if ($benefices === '') {
            $errors[] = 'Les bienfaits sont obligatoires.';
        }

        if ($urlImage !== '' && !$this->isValidHttpUrl($urlImage)) {
            $errors[] = 'L’URL de l’image doit commencer par http:// ou https:// et être une URL valide.';
        }

        if ($urlVideo !== '' && !$this->isValidHttpUrl($urlVideo)) {
            $errors[] = 'L’URL de la vidéo doit commencer par http:// ou https:// et être une URL valide.';
        }

        if ($nbRepRaw === '' || !ctype_digit($nbRepRaw)) {
            $errors[] = 'Le nombre de répétitions doit être un entier positif (chiffres uniquement, sans décimale).';
        } else {
            $n = (int) $nbRepRaw;
            if ($n < 1 || $n > 9999) {
                $errors[] = 'Le nombre de répétitions doit être compris entre 1 et 9999.';
            }
        }

        if ($muscleRaw !== '') {
            if (!ctype_digit($muscleRaw) || !isset(WGER_MUSCLES[(int) $muscleRaw])) {
                $errors[] = 'Le muscle pour l’API de remplacement est invalide.';
            }
        }

        return $errors;
    }

    /**
     * Vérifie une URL http(s) pour les champs image / vidéo.
     */
    private function isValidHttpUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME)); // http ou https

        return $scheme === 'http' || $scheme === 'https';
    }

    /**
     * Reconstruit un tableau « faux exercice » pour ré-afficher le formulaire après erreur.
     *
     * @return array<string,mixed>
     */
    private function mockRowFromPost(
        int $id,
        string $nom,
        string $type,
        string $etapes,
        string $benefices,
        string $urlImage,
        string $urlVideo,
        string $nbRepRaw,
        string $muscleRaw
    ): array {
        return [
            'id' => $id,
            'nom' => $nom,
            'type_exercice' => $type,
            'etapes' => $etapes,
            'benefices' => $benefices,
            'url_image' => $urlImage,
            'url_video' => $urlVideo,
            'nb_repetitions_suggerees' => $nbRepRaw,
            'muscle_wger_id' => $muscleRaw === '' ? null : (int) $muscleRaw,
        ];
    }
}
