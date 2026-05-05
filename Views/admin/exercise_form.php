<?php
/** @var array<string,mixed>|null $exercise */
/** @var list<string> $errors */
/** @var list<string> $types */
/** @var array<int,string> $wgerMuscles */
$isEdit = $exercise !== null && isset($exercise['id']) && (int) $exercise['id'] > 0;
ob_start();
?>
<div class="flex flex-wrap mt-6">
    <div class="w-full max-w-full px-3 mb-6 lg:w-8/12">
        <div class="shadow-xl dark:bg-slate-850 relative flex flex-col min-w-0 break-words bg-white rounded-2xl p-6">
            <h5 class="font-bold dark:text-white mb-4"><?= $isEdit ? 'Modifier un exercice' : 'Nouvel exercice' ?></h5>

            <?php foreach ($errors as $err) : ?>
                <p class="text-red-500 text-sm mb-2"><?= e($err) ?></p>
            <?php endforeach; ?>

            <form method="post" action="<?= e(ADMIN_URL) ?>/index.php?action=exercise_save" class="space-y-3">
                <?php if ($isEdit) : ?>
                    <input type="hidden" name="id" value="<?= (int) $exercise['id'] ?>" />
                <?php endif; ?>

                <div>
                    <label class="text-xs text-slate-500">Nom (lettres uniquement + espaces/tiret/apostrophe)</label>
                    <input name="nom" class="w-full border rounded-lg px-3 py-2 text-sm"
                           value="<?= e((string) ($exercise['nom'] ?? '')) ?>" />
                </div>

                <div>
                    <label class="text-xs text-slate-500">Type</label>
                    <select name="type_exercice" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <?php foreach ($types as $t) : ?>
                            <option value="<?= e($t) ?>" <?= (($exercise['type_exercice'] ?? '') === $t) ? 'selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="text-xs text-slate-500">Étapes</label>
                    <textarea name="etapes" rows="5" class="w-full border rounded-lg px-3 py-2 text-sm"><?= e((string) ($exercise['etapes'] ?? '')) ?></textarea>
                </div>

                <div>
                    <label class="text-xs text-slate-500">Bienfaits</label>
                    <textarea name="benefices" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm"><?= e((string) ($exercise['benefices'] ?? '')) ?></textarea>
                </div>

                <div>
                    <label class="text-xs text-slate-500">URL image (http/https, laisser vide si aucune)</label>
                    <input name="url_image" class="w-full border rounded-lg px-3 py-2 text-sm"
                           value="<?= e((string) ($exercise['url_image'] ?? '')) ?>" />
                </div>

                <div>
                    <label class="text-xs text-slate-500">URL vidéo (http/https, optionnel)</label>
                    <input name="url_video" class="w-full border rounded-lg px-3 py-2 text-sm"
                           value="<?= e((string) ($exercise['url_video'] ?? '')) ?>" />
                </div>

                <div>
                    <label class="text-xs text-slate-500">Nombre de répétitions suggérées (chiffres)</label>
                    <input name="nb_repetitions_suggerees" class="w-full border rounded-lg px-3 py-2 text-sm"
                           value="<?= e((string) ($exercise['nb_repetitions_suggerees'] ?? '10')) ?>" />
                </div>

                <div>
                    <label class="text-xs text-slate-500">Muscle principal (API wger — pour bouton « je ne peux pas »)</label>
                    <select name="muscle_wger_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="">— non défini —</option>
                        <?php foreach ($wgerMuscles as $mid => $label) : ?>
                            <?php
                            $sel = isset($exercise['muscle_wger_id']) && (string) $exercise['muscle_wger_id'] === (string) $mid;
                            ?>
                            <option value="<?= (int) $mid ?>" <?= $sel ? 'selected' : '' ?>><?= e($label) ?> (<?= (int) $mid ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold px-6 py-2 rounded-lg">Enregistrer</button>
                <a href="<?= e(ADMIN_URL) ?>/index.php?action=exercise_list" class="ml-2 text-sm text-slate-600">Annuler</a>
            </form>
        </div>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = $isEdit ? 'Éditer exercice' : 'Nouvel exercice';
require VIEW_PATH . '/admin/layout.php';
