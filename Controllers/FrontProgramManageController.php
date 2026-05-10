<?php
/**
 * Front-office — ajout de programmes au catalogue public (sans admin).
 * Validation serveur uniquement (pas de validation HTML5).
 */

declare(strict_types=1);

final class FrontProgramManageController
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
            case 'front_program_new':
                $this->formAction();
                break;
            case 'front_program_save':
                $this->saveAction();
                break;
            default:
                redirect(BASE_URL . '/index.php?action=home');
        }
    }

    private function formAction(): void
    {
        View::render('front/front_program_form', [
            'program' => null,
            'linkedExercises' => [],
            'allExercises' => $this->exerciseModel->findAll(),
            'errors' => [],
            'types' => TYPES_ENTRAINEMENT,
        ]);
    }

    private function saveAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(BASE_URL . '/index.php?action=home&err=' . rawurlencode('Méthode invalide.'));
        }

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

        if ($errors !== []) {
            View::render('front/front_program_form', [
                'program' => [
                    'id' => 0,
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
        // Insertion dans le catalogue public => utilisateur_token = NULL
        $this->programModel->insert($nom, $duree, $type, $exerciseIds, $repsClean, null);

        redirect(BASE_URL . '/index.php?action=home&msg=' . rawurlencode('Programme ajouté au catalogue.'));
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
}

