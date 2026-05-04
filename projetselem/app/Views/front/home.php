<?php
/** @var list<array<string,mixed>> $programs */
/** @var string $filterType */
/** @var string $searchQ */
/** @var list<string> $types */
ob_start();
?>
<div class="row g-4">
    <div class="col-lg-8">
        <h1 class="h3 mb-3">Programmes d’entraînement</h1>
        <p class="text-muted">Recherchez par nom ou par type, puis lancez une séance. Les exercices viennent de votre base MySQL.</p>

        <form method="get" action="<?= e(BASE_URL) ?>/index.php" class="row g-2 align-items-end mb-4">
            <input type="hidden" name="action" value="home" />
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
                <label class="form-label small text-muted">Nom du programme</label>
                <input name="q" class="form-control form-control-sm" value="<?= e($searchQ) ?>" />
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-nf btn-sm w-100">Rechercher</button>
            </div>
        </form>

        <div class="row g-3">
            <?php foreach ($programs as $p) : ?>
                <div class="col-md-6">
                    <div class="card nf-card h-100">
                        <div class="card-body">
                            <span class="badge text-bg-light border mb-2"><?= e($p['type_programme']) ?></span>
                            <h2 class="h5 card-title"><?= e($p['nom']) ?></h2>
                            <p class="card-text small text-muted"><?= (int) $p['duree_semaines'] ?> semaine(s)</p>
                            <a class="btn btn-nf btn-sm" href="<?= e(BASE_URL) ?>/index.php?action=program_start&amp;id=<?= (int) $p['id'] ?>">Démarrer</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($programs) === 0) : ?>
                <p class="text-warning">Aucun programme ne correspond — ajoutez-en dans l’admin ou élargissez la recherche.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card nf-card mb-3">
            <div class="card-body">
                <h2 class="h6">Conseil du moment</h2>
                <p id="nf-quote" class="small text-muted mb-2">Chargement…</p>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="nf-quote-btn">Autre conseil</button>
            </div>
        </div>
        <div class="card nf-card mb-3">
            <div class="card-body">
                <h2 class="h6">IMC (API)</h2>
                <p class="small text-muted">Taille en cm, poids en kg — calcul via API distante.</p>
                <div class="mb-2">
                    <label class="form-label small">Taille (cm)</label>
                    <input id="nf-bmi-h" class="form-control form-control-sm" />
                </div>
                <div class="mb-2">
                    <label class="form-label small">Poids (kg)</label>
                    <input id="nf-bmi-w" class="form-control form-control-sm" />
                </div>
                <button type="button" class="btn btn-nf btn-sm" id="nf-bmi-btn">Calculer</button>
                <p id="nf-bmi-out" class="small mt-2 mb-0 text-muted"></p>
                <p id="nf-bmi-err" class="small mt-1 mb-0 text-danger"></p>
            </div>
        </div>
        <div class="card nf-card">
            <div class="card-body">
                <h2 class="h6">Assistant parcours</h2>
                <p class="small text-muted">Questionnaire simple pour suggérer un programme (règles métier).</p>
                <a href="<?= e(BASE_URL) ?>/index.php?action=recommandation_ia" class="btn btn-outline-success btn-sm">Ouvrir</a>
            </div>
        </div>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = 'Accueil — programmes';
$footerScripts = '<script src="' . e(BASE_URL) . '/js/training-api.js"></script>';
require VIEW_PATH . '/front/layout.php';
