<?php
/** @var list<array<string,mixed>> $programs */
/** @var string $filterType */
/** @var string $searchQ */
/** @var string $message */
/** @var string $error */
/** @var list<string> $types */
ob_start();
?>
<div class="row g-4">
    <div class="col-12">
        <h1 class="h3 mb-2">Mes programmes</h1>
        <p class="text-muted small mb-4">Créez vos enchaînements à partir du catalogue d’exercices. Modification et suppression réservées à cette page.</p>

        <?php if ($message !== '') : ?>
            <p class="text-success small"><?= e($message) ?></p>
        <?php endif; ?>
        <?php if ($error !== '') : ?>
            <p class="text-danger small"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="get" action="<?= e(BASE_URL) ?>/index.php" class="row g-2 align-items-end mb-4">
            <input type="hidden" name="action" value="user_program_list" />
            <div class="col-md-4">
                <label class="form-label small text-muted">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <?php foreach ($types as $t) : ?>
                        <option value="<?= e($t) ?>" <?= $filterType === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small text-muted">Nom contient</label>
                <input name="q" class="form-control form-control-sm" value="<?= e($searchQ) ?>" />
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-nf btn-sm w-100">Filtrer</button>
            </div>
        </form>

        <p class="mb-3">
            <a class="btn btn-outline-secondary btn-sm" href="<?= e(BASE_URL) ?>/index.php?action=home">Voir tout le catalogue</a>
        </p>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Semaines</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $p) : ?>
                        <tr>
                            <td><?= e($p['nom']) ?></td>
                            <td><?= e($p['type_programme']) ?></td>
                            <td><?= (int) $p['duree_semaines'] ?></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-outline-primary btn-sm" href="<?= e(BASE_URL) ?>/index.php?action=user_program_edit&amp;id=<?= (int) $p['id'] ?>">Modifier</a>
                                <form action="<?= e(BASE_URL) ?>/index.php?action=user_program_delete" method="post" class="d-inline">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($programs) === 0) : ?>
            <p class="text-muted small">Aucun programme personnel pour l’instant — ajoutez-en un avec le bouton ci-dessus.</p>
        <?php endif; ?>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = 'Mes programmes';
require VIEW_PATH . '/front/layout.php';
