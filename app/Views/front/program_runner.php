<?php
/** @var array<string,mixed> $program */
/** @var list<array<string,mixed>> $exercises */
ob_start();
?>
<div class="mb-3">
    <a href="<?= e(BASE_URL) ?>/index.php?action=home" class="small text-decoration-none">← Retour aux programmes</a>
</div>
<h1 class="h3"><?= e($program['nom']) ?></h1>
<p class="text-muted"><?= e($program['type_programme']) ?> · <?= (int) $program['duree_semaines'] ?> sem.</p>

<?php foreach ($exercises as $idx => $ex) : ?>
    <?php
    $muscle = isset($ex['muscle_wger_id']) && $ex['muscle_wger_id'] !== null ? (int) $ex['muscle_wger_id'] : 0;
    $reps = $ex['repetitions_programme'] !== null ? (int) $ex['repetitions_programme'] : (int) $ex['nb_repetitions_suggerees'];
    // Lien de recherche YouTube : pas besoin de clé API, on ouvre les résultats avec le nom de l’exercice.
    $ytUrl = 'https://www.youtube.com/results?search_query=' . rawurlencode($ex['nom'] . ' exercice démonstration');
    ?>
    <section class="card nf-card mb-4" data-exercise-block="<?= (int) $idx ?>">
        <div class="row g-0">
            <?php if (!empty($ex['url_image'])) : ?>
                <div class="col-md-4">
                    <img src="<?= e($ex['url_image']) ?>" alt="" class="img-fluid rounded-start h-100 object-fit-cover" style="min-height:160px;object-fit:cover;" />
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h2 class="h5"><?= e($ex['nom']) ?></h2>
                        <p class="small mb-1"><strong>Répétitions suggérées :</strong> <?= $reps ?></p>
                        <p class="small text-muted"><?= nl2br(e($ex['etapes'])) ?></p>
                        <p class="small"><strong>Bienfaits :</strong> <?= nl2br(e($ex['benefices'])) ?></p>
                        <?php if (!empty($ex['url_video'])) : ?>
                            <p class="small mb-1"><a href="<?= e($ex['url_video']) ?>" target="_blank" rel="noopener">Voir la vidéo</a></p>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a class="btn btn-outline-danger btn-sm" href="<?= e($ytUrl) ?>" target="_blank" rel="noopener" title="Ouvre YouTube dans un nouvel onglet">YouTube</a>
                            <button type="button" class="btn btn-outline-warning btn-sm nf-cant-btn" data-muscle="<?= $muscle ?>" data-block="<?= (int) $idx ?>">
                                Je ne peux pas faire cet exercice — autres idées
                            </button>
                        </div>
                        <div class="nf-alt mt-3 small text-muted d-none border rounded p-3 bg-white shadow-sm" id="nf-alt-<?= (int) $idx ?>" style="min-height:14rem;">
                            <p class="nf-alt-loading mb-2 d-none fw-semibold text-secondary">Recherche d’exercices du même groupe musculaire…</p>
                            <div class="nf-alt-list overflow-auto pe-1" style="max-height:30rem;"></div>
                            <p class="nf-alt-error text-danger mb-0 d-none"></p>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="col-12">
                    <div class="card-body">
                        <h2 class="h5"><?= e($ex['nom']) ?></h2>
                        <p class="small mb-1"><strong>Répétitions suggérées :</strong> <?= $reps ?></p>
                        <p class="small text-muted"><?= nl2br(e($ex['etapes'])) ?></p>
                        <p class="small"><strong>Bienfaits :</strong> <?= nl2br(e($ex['benefices'])) ?></p>
                        <?php if (!empty($ex['url_video'])) : ?>
                            <p class="small mb-1"><a href="<?= e($ex['url_video']) ?>" target="_blank" rel="noopener">Voir la vidéo</a></p>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a class="btn btn-outline-danger btn-sm" href="<?= e($ytUrl) ?>" target="_blank" rel="noopener" title="Ouvre YouTube dans un nouvel onglet">YouTube</a>
                            <button type="button" class="btn btn-outline-warning btn-sm nf-cant-btn" data-muscle="<?= $muscle ?>" data-block="<?= (int) $idx ?>">
                                Je ne peux pas faire cet exercice — autres idées
                            </button>
                        </div>
                        <div class="nf-alt mt-3 small text-muted d-none border rounded p-3 bg-white shadow-sm" id="nf-alt-<?= (int) $idx ?>" style="min-height:14rem;">
                            <p class="nf-alt-loading mb-2 d-none fw-semibold text-secondary">Recherche d’exercices du même groupe musculaire…</p>
                            <div class="nf-alt-list overflow-auto pe-1" style="max-height:30rem;"></div>
                            <p class="nf-alt-error text-danger mb-0 d-none"></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>

<script src="<?= e(BASE_URL) ?>/js/training-api.js"></script>
<?php
$slot = ob_get_clean();
$pageTitle = 'Séance — ' . $program['nom'];
require VIEW_PATH . '/front/layout.php';
