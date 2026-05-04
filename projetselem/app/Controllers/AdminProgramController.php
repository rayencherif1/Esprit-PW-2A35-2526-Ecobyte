<?php
/**
 * Contrôleur admin — CRUD programmes + liaison N:N avec exercices.
 */

declare(strict_types=1);

final class AdminProgramController
{
    private ProgramModel $programModel;
    private ExerciseModel $exerciseModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->exerciseModel = new ExerciseModel();
    }

    public function dispatch(string $action): void
    {
        switch ($action) {
            case 'program_list':
                $this->listAction();
                break;
            case 'program_new':
                $this->formAction(null);
                break;
            case 'program_edit':
                $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                $this->formAction($id > 0 ? $id : null);
                break;
            case 'program_save':
                $this->saveAction();
                break;
            case 'program_delete':
                $this->deleteAction();
                break;
            default:
                $this->listAction();
        }
    }

    private function listAction(): void
    {
        $type = isset($_GET['type']) ? (string) $_GET['type'] : '';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $typeFilter = in_array($type, TYPES_ENTRAINEMENT, true) ? $type : null;

        $rows = $this->programModel->findAll($typeFilter, $q !== '' ? $q : null);

        $message = isset($_GET['msg']) ? (string) $_GET['msg'] : '';
        $error = isset($_GET['err']) ? (string) $_GET['err'] : '';

        View::render('admin/program_list', [
            'programs' => $rows,
            'filterType' => $type,
            'searchQ' => $q,
            'message' => $message,
            'error' => $error,
        ]);
    }

    private function formAction(?int $id): void
    {
        $program = null;
        $linked = [];
        $errors = [];

        $allExercises = $this->exerciseModel->findAll(); // Pour les cases à cocher / sélection

        if ($id !== null) {
            $bundle = $this->programModel->findWithExercises($id);
            $program = $bundle['program'];
            $linked = $bundle['exercises'];
            if ($program === null) {
                $errors[] = 'Programme introuvable.';
            }
        }

        View::render('admin/program_form', [
            'program' => $program,
            'linkedExercises' => $linked,
            'allExercises' => $allExercises,
            'errors' => $errors,
            'types' => TYPES_ENTRAINEMENT,
        ]);
    }

    private function saveAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(ADMIN_URL . '/index.php?action=program_list&err=' . rawurlencode('Méthode invalide.'));
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $nom = isset($_POST['nom']) ? trim((string) $_POST['nom']) : '';
        $type = isset($_POST['type_programme']) ? (string) $_POST['type_programme'] : '';
        $dureeRaw = isset($_POST['duree_semaines']) ? trim((string) $_POST['duree_semaines']) : '';

        $idsEx = isset($_POST['exercice_ids']) && is_array($_POST['exercice_ids']) ? $_POST['exercice_ids'] : [];
        $reps = isset($_POST['exercice_reps']) && is_array($_POST['exercice_reps']) ? $_POST['exercice_reps'] : [];

        $exerciseIds = [];
        $repsClean = [];

        foreach ($idsEx as $i => $raw) {
            $eid = (int) $raw;
            if ($eid > 0) {
                $exerciseIds[] = $eid;
                $r = isset($reps[$i]) ? trim((string) $reps[$i]) : '';
                $repsClean[] = $r === '' ? null : $r;
            }
        }

        $errors = $this->validateProgram($nom, $type, $dureeRaw, $exerciseIds, $repsClean);

        if ($id > 0 && $this->programModel->findById($id) === null) {
            $errors[] = 'Programme inexistant.';
        }

        if ($errors !== []) {
            View::render('admin/program_form', [
                'program' => [
                    'id' => $id,
                    'nom' => $nom,
                    'type_programme' => $type,
                    'duree_semaines' => $dureeRaw,
                ],
                'linkedExercises' => $this->buildFakeLinked($exerciseIds, $repsClean),
                'allExercises' => $this->exerciseModel->findAll(),
                'errors' => $errors,
                'types' => TYPES_ENTRAINEMENT,
            ]);
            return;
        }

        $duree = (int) $dureeRaw;

        if ($id > 0) {
            $this->programModel->update($id, $nom, $duree, $type, $exerciseIds, $repsClean);
        } else {
            $this->programModel->insert($nom, $duree, $type, $exerciseIds, $repsClean);
        }

        redirect(ADMIN_URL . '/index.php?action=program_list&msg=' . rawurlencode('Programme enregistré.'));
    }

    /**
     * @param list<int> $exerciseIds
     * @param list<string|null> $repsClean
     * @return list<array<string,mixed>>
     */
    private function buildFakeLinked(array $exerciseIds, array $repsClean): array
    {
        $out = [];
        foreach ($exerciseIds as $i => $eid) {
            $row = $this->exerciseModel->findById($eid);
            if ($row === null) {
                continue;
            }
            $row['ordre'] = $i + 1;
            $row['repetitions_programme'] = $repsClean[$i] ?? null;
            $out[] = $row;
        }
        return $out;
    }

    /**
     * @param list<int> $exerciseIds
     * @param list<string|null> $repsClean
     * @return list<string>
     */
    private function validateProgram(
        string $nom,
        string $type,
        string $dureeRaw,
        array $exerciseIds,
        array $repsClean
    ): array {
        $errors = [];

        if ($nom === '') {
            $errors[] = 'Le nom du programme est obligatoire.';
        } elseif (!preg_match('/^[\p{L} \-\'’]+$/u', $nom)) {
            $errors[] = 'Le nom doit contenir uniquement des lettres (espaces, tiret et apostrophe autorisés).';
        }

        if (!in_array($type, TYPES_ENTRAINEMENT, true)) {
            $errors[] = 'Le type de programme est invalide.';
        }

        if ($dureeRaw === '' || !ctype_digit($dureeRaw)) {
            $errors[] = 'La durée en semaines doit être un nombre entier (chiffres uniquement).';
        } else {
            $d = (int) $dureeRaw;
            if ($d < 1 || $d > 520) {
                $errors[] = 'La durée doit être entre 1 et 520 semaines.';
            }
        }

        if ($exerciseIds === []) {
            $errors[] = 'Sélectionnez au moins un exercice pour ce programme (jointure obligatoire).';
        }

        foreach ($exerciseIds as $eid) {
            if ($this->exerciseModel->findById($eid) === null) {
                $errors[] = 'Un des exercices choisis n’existe plus en base.';
                break;
            }
        }

        foreach ($repsClean as $r) {
            if ($r === null || $r === '') {
                continue;
            }
            if (!ctype_digit((string) $r)) {
                $errors[] = 'Les répétitions par exercice doivent être des entiers ou laissées vides.';
                break;
            }
            $ri = (int) $r;
            if ($ri < 1 || $ri > 9999) {
                $errors[] = 'Les répétitions doivent être entre 1 et 9999.';
                break;
            }
        }

        return $errors;
    }

    private function deleteAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(ADMIN_URL . '/index.php?action=program_list&err=' . rawurlencode('Suppression refusée.'));
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0 || $this->programModel->findById($id) === null) {
            redirect(ADMIN_URL . '/index.php?action=program_list&err=' . rawurlencode('Programme introuvable.'));
        }

        $this->programModel->delete($id);

        redirect(ADMIN_URL . '/index.php?action=program_list&msg=' . rawurlencode('Programme supprimé.'));
    }
}
