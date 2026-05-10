<?php
/** @var array<string,mixed>|null $program */
/** @var list<array<string,mixed>> $linkedExercises */
/** @var list<array<string,mixed>> $allExercises */
/** @var list<string> $errors */
/** @var list<string> $types */
$isEdit = $program !== null && isset($program['id']) && (int) $program['id'] > 0;
$maxSlots = 15;
ob_start();
?>
<div class="row g-4">
    <div class="col-lg-10">
        <h1 class="h3 mb-2"><?= $isEdit ? 'Modifier mon programme' : 'Nouveau programme personnel' ?></h1>
        <p class="text-muted small mb-4">Les champs sont vérifiés sur le serveur uniquement. Choisissez les exercices dans l’ordre (ligne 1 = premier).</p>

        <?php foreach ($errors as $err) : ?>
            <p class="text-danger small mb-2"><?= e($err) ?></p>
        <?php endforeach; ?>

        <div class="card nf-card">
            <div class="card-body">
                <form method="post" action="<?= e(BASE_URL) ?>/index.php?action=user_program_save" novalidate>
                    <?php if ($isEdit) : ?>
                        <input type="hidden" name="id" value="<?= (int) $program['id'] ?>" />
                    <?php endif; ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">Nom du programme</label>
                            <input name="nom" class="form-control form-control-sm" value="<?= e((string) ($program['nom'] ?? '')) ?>" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Durée (semaines)</label>
                            <input name="duree_semaines" class="form-control form-control-sm" value="<?= e((string) ($program['duree_semaines'] ?? '')) ?>" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Type</label>
                            <select name="type_programme" class="form-select form-select-sm">
                                <?php foreach ($types as $t) : ?>
                                    <option value="<?= e($t) ?>" <?= (($program['type_programme'] ?? '') === $t) ? 'selected' : '' ?>><?= e($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <h2 class="h6">Enchaînement d’exercices</h2>
                    <p class="small text-muted mb-3">Sélectionnez au moins un exercice. « — vide — » ignore la ligne.</p>

                    <?php for ($i = 0; $i < $maxSlots; $i++) : ?>
                        <?php
                        $row = $linkedExercises[$i] ?? null;
                        $curId = $row ? (int) $row['id'] : 0;
                        $curRep = '';
                        if ($row && isset($row['repetitions_programme']) && $row['repetitions_programme'] !== null) {
                            $curRep = (string) $row['repetitions_programme'];
                        }
                        ?>
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-auto small text-muted"><?= $i + 1 ?>.</div>
                            <div class="col">
                                <select name="exercice_ids[]" class="form-select form-select-sm">
                                    <option value="">— vide —</option>
                                    <?php foreach ($allExercises as $ex) : ?>
                                        <option value="<?= (int) $ex['id'] ?>" <?= $curId === (int) $ex['id'] ? 'selected' : '' ?>>
                                            <?= e($ex['nom']) ?> (<?= e($ex['type_exercice']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input name="exercice_reps[]" class="form-control form-control-sm" placeholder="rép." value="<?= e($curRep) ?>" />
                            </div>
                        </div>
                    <?php endfor; ?>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-nf btn-sm">Enregistrer</button>
                        <a href="<?= e(BASE_URL) ?>/index.php?action=user_program_list" class="btn btn-outline-secondary btn-sm ms-2">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = $isEdit ? 'Modifier mon programme' : 'Nouveau programme';
require VIEW_PATH . '/front/layout.php';
