<?php
/** @var list<array<string,mixed>> $programs */
/** @var string $filterType */
/** @var string $searchQ */
/** @var string $message */
/** @var string $error */
ob_start();
?>
<div class="flex flex-wrap mt-6">
    <div class="w-full max-w-full px-3 mb-6">
        <div class="relative flex flex-col min-w-0 break-words bg-white shadow-xl rounded-2xl p-6">
            <h5 class="mb-4 font-bold dark:text-white">Programmes d’entraînement</h5>

            <?php if ($message !== '') : ?>
                <p class="mb-3 text-emerald-600 text-sm"><?= e($message) ?></p>
            <?php endif; ?>
            <?php if ($error !== '') : ?>
                <p class="mb-3 text-red-500 text-sm"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="get" action="<?= e(ADMIN_URL) ?>/index.php" class="mb-4 flex flex-wrap gap-2 items-end">
                <input type="hidden" name="action" value="program_list" />
                <div>
                    <label class="text-xs text-slate-500 block mb-1">Type</label>
                    <select name="type" class="border rounded-lg px-2 py-1 text-sm">
                        <option value="">— tous —</option>
                        <?php foreach (TYPES_ENTRAINEMENT as $t) : ?>
                            <option value="<?= e($t) ?>" <?= $filterType === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500 block mb-1">Nom contient</label>
                    <input name="q" value="<?= e($searchQ) ?>" class="border rounded-lg px-2 py-1 text-sm w-48" />
                </div>
                <button type="submit" class="bg-blue-500 text-white text-xs font-bold px-4 py-2 rounded-lg">Filtrer</button>
            </form>

            <a href="<?= e(ADMIN_URL) ?>/index.php?action=program_new" class="inline-block mb-4 text-sm text-blue-600 font-semibold">+ Nouveau programme</a>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2 text-left">Nom</th>
                            <th class="py-2 text-left">Type</th>
                            <th class="py-2 text-left">Semaines</th>
                            <th class="py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $p) : ?>
                            <tr class="border-b border-slate-100">
                                <td class="py-2"><?= e($p['nom']) ?></td>
                                <td class="py-2"><?= e($p['type_programme']) ?></td>
                                <td class="py-2"><?= (int) $p['duree_semaines'] ?></td>
                                <td class="py-2 space-x-2">
                                    <a href="<?= e(ADMIN_URL) ?>/index.php?action=program_edit&amp;id=<?= (int) $p['id'] ?>" class="text-blue-600">Modifier</a>
                                    <form action="<?= e(ADMIN_URL) ?>/index.php?action=program_delete" method="post" class="inline">
                                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                                        <button type="submit" class="text-red-500 bg-transparent border-0 cursor-pointer p-0 text-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = 'Programmes';
require VIEW_PATH . '/admin/layout.php';
