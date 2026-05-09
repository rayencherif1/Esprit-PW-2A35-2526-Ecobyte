<?php
/**
 * Validation serveur des formulaires programme (admin + espace utilisateur).
 */

declare(strict_types=1);

final class ProgramValidator
{
    /**
     * @param list<int> $exerciseIds
     * @param list<string|null> $repsClean
     * @return list<string>
     */
    public static function validate(
        ExerciseModel $exerciseModel,
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
            if ($exerciseModel->findById($eid) === null) {
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
}
