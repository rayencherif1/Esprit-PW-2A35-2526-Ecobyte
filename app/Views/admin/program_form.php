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
<div class="flex flex-wrap mt-6">
    <div class="w-full max-w-full px-3 mb-6">
        <div class="bg-white shadow-xl rounded-2xl p-6">
            <h5 class="font-bold mb-2"><?= $isEdit ? 'Modifier le programme' : 'Nouveau programme' ?></h5>
            <p class="text-xs text-slate-500 mb-4">Choisissez les exercices dans l’ordre (ligne 1 = premier exercice). Laissez « — vide — » pour ignorer une ligne.</p>

            <?php foreach ($errors as $err) : ?>
                <p class="text-red-500 text-sm mb-2"><?= e($err) ?></p>
            <?php endforeach; ?>

            <form method="post" action="<?= e(ADMIN_URL) ?>/index.php?action=program_save" class="space-y-4">
                <?php if ($isEdit) : ?>
                    <input type="hidden" name="id" value="<?= (int) $program['id'] ?>" />
                <?php endif; ?>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500">Nom du programme</label>
                        <input name="nom" class="w-full border rounded-lg px-3 py-2 text-sm" value="<?= e((string) ($program['nom'] ?? '')) ?>" />
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Durée (semaines, chiffres)</label>
                        <input name="duree_semaines" class="w-full border rounded-lg px-3 py-2 text-sm" value="<?= e((string) ($program['duree_semaines'] ?? '')) ?>" />
                    </div>
                    <div>
                        <label class="text-xs text-slate-500">Type</label>
                        <select name="type_programme" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <?php foreach ($types as $t) : ?>
                                <option value="<?= e($t) ?>" <?= (($program['type_programme'] ?? '') === $t) ? 'selected' : '' ?>><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="border rounded-xl p-4 bg-slate-50">
                    <h6 class="text-sm font-bold mb-3">Enchaînement d’exercices (jointure)</h6>
                    <?php for ($i = 0; $i < $maxSlots; $i++) : ?>
                        <?php
                        $row = $linkedExercises[$i] ?? null;
                        $curId = $row ? (int) $row['id'] : 0;
                        $curRep = '';
                        if ($row && isset($row['repetitions_programme']) && $row['repetitions_programme'] !== null) {
                            $curRep = (string) $row['repetitions_programme'];
                        }
                        ?>
                        <div class="flex flex-wrap gap-2 items-center mb-2">
                            <span class="text-xs w-6 text-slate-400"><?= $i + 1 ?>.</span>
                            <select name="exercice_ids[]" class="border rounded-lg px-2 py-1 text-sm flex-1 min-w-[200px]">
                                <option value="">— vide —</option>
                                <?php foreach ($allExercises as $ex) : ?>
                                    <option value="<?= (int) $ex['id'] ?>" <?= $curId === (int) $ex['id'] ? 'selected' : '' ?>>
                                        <?= e($ex['nom']) ?> (<?= e($ex['type_exercice']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input name="exercice_reps[]" class="border rounded-lg px-2 py-1 text-sm w-24" placeholder="rép." value="<?= e($curRep) ?>" />
                        </div>
                    <?php endfor; ?>
                </div>

                <button type="submit" class="bg-blue-500 text-white text-sm font-bold px-6 py-2 rounded-lg">Enregistrer</button>
                <a href="<?= e(ADMIN_URL) ?>/index.php?action=program_list" class="ml-2 text-sm">Annuler</a>
            </form>
        </div>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = $isEdit ? 'Éditer programme' : 'Nouveau programme';
require VIEW_PATH . '/admin/layout.php';
