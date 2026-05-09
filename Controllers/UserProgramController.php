<?php
/**
 * Espace utilisateur (front) — CRUD sur les programmes personnels uniquement.
 * Validation serveur uniquement (pas d’attributs HTML5).
 */

declare(strict_types=1);

final class UserProgramController
{
    private ProgramModel $programModel;
    private ExerciseModel $exerciseModel;

    public function __construct()
    {
        AppSession::userProgramOwnerToken();
        $this->programModel = new ProgramModel();
        $this->exerciseModel = new ExerciseModel();
    }

    public function dispatch(string $action): void
    {
        switch ($action) {
            case 'user_program_list':
                $this->listAction();
                break;
            case 'user_program_new':
                $this->formAction(null);
                break;
            case 'user_program_edit':
                $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                $this->formAction($id > 0 ? $id : null);
                break;
            case 'user_program_save':
                $this->saveAction();
                break;
            case 'user_program_delete':
                $this->deleteAction();
                break;
            default:
                $this->listAction();
        }
    }

    private function ownerToken(): string
    {
        return AppSession::userProgramOwnerToken();
    }

    private function listAction(): void
    {
        $token = $this->ownerToken();
        $type = isset($_GET['type']) ? (string) $_GET['type'] : '';
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $typeFilter = in_array($type, TYPES_ENTRAINEMENT, true) ? $type : null;

        $rows = $this->programModel->findAllOwnedByUser($token, $typeFilter, $q !== '' ? $q : null);

        $message = isset($_GET['msg']) ? (string) $_GET['msg'] : '';
        $error = isset($_GET['err']) ? (string) $_GET['err'] : '';

        View::render('front/user_program_list', [
            'programs' => $rows,
            'filterType' => $type,
            'searchQ' => $q,
            'message' => $message,
            'error' => $error,
            'types' => TYPES_ENTRAINEMENT,
        ]);
    }

    private function formAction(?int $id): void
    {
        $token = $this->ownerToken();
        $program = null;
        $linked = [];
        $errors = [];

        $allExercises = $this->exerciseModel->findAll();

        if ($id !== null) {
            if (!$this->programModel->isOwnedByUser($id, $token)) {
                redirect(BASE_URL . '/index.php?action=user_program_list&err=' . rawurlencode('Ce programme ne fait pas partie de vos créations.'));
            }
            $bundle = $this->programModel->findWithExercises($id);
            $program = $bundle['program'];
            $linked = $bundle['exercises'];
            if ($program === null) {
                $errors[] = 'Programme introuvable.';
            }
        }

        View::render('front/user_program_form', [
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
            redirect(BASE_URL . '/index.php?action=user_program_list&err=' . rawurlencode('Méthode invalide.'));
        }

        $token = $this->ownerToken();

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

        $errors = ProgramValidator::validate(
            $this->exerciseModel,
            $nom,
            $type,
            $dureeRaw,
            $exerciseIds,
            $repsClean
        );

        if ($id > 0 && !$this->programModel->isOwnedByUser($id, $token)) {
            $errors[] = 'Vous ne pouvez pas modifier ce programme.';
        }

        if ($errors !== []) {
            View::render('front/user_program_form', [
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
            $this->programModel->insert($nom, $duree, $type, $exerciseIds, $repsClean, $token);
        }

        redirect(BASE_URL . '/index.php?action=user_program_list&msg=' . rawurlencode('Programme enregistré.'));
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

    private function deleteAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/index.php?action=user_program_list&err=' . rawurlencode('Suppression refusée.'));
        }

        $token = $this->ownerToken();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0 || !$this->programModel->isOwnedByUser($id, $token)) {
            redirect(BASE_URL . '/index.php?action=user_program_list&err=' . rawurlencode('Programme introuvable ou non autorisé.'));
        }

        $this->programModel->delete($id);

        redirect(BASE_URL . '/index.php?action=user_program_list&msg=' . rawurlencode('Programme supprimé.'));
    }
}
