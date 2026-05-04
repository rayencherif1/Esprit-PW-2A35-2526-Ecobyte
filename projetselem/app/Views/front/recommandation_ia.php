<?php
/** @var array<string,mixed>|null $suggestion */
/** @var string $message */
/** @var list<string> $errors */
/** @var array<string,string> $post */
ob_start();
?>
<h1 class="h3 mb-3">Assistant parcours (règles intelligentes)</h1>
<p class="text-muted">Ce module ne télécharge pas ChatGPT : il applique des <strong>règles</strong> selon vos réponses, puis propose un programme déjà présent en base MySQL. C’est suffisant pour une démo « IA » pédagogique transparente.</p>

<?php foreach ($errors as $err) : ?>
    <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card nf-card">
            <div class="card-body">
                <form method="post" action="<?= e(BASE_URL) ?>/index.php?action=recommandation_ia" class="vstack gap-3">
                    <div>
                        <label class="form-label small">Objectif principal</label>
                        <select name="objectif" class="form-select form-select-sm">
                            <?php
                            $o = (string) ($post['objectif'] ?? '');
                            ?>
                            <option value="perte_de_poids" <?= $o === 'perte_de_poids' ? 'selected' : '' ?>>Perte de poids</option>
                            <option value="musculation" <?= $o === 'musculation' ? 'selected' : '' ?>>Musculation</option>
                            <option value="cardio" <?= $o === 'cardio' ? 'selected' : '' ?>>Cardio / endurance</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small">Niveau</label>
                        <select name="niveau" class="form-select form-select-sm">
                            <?php $n = (string) ($post['niveau'] ?? ''); ?>
                            <option value="debutant" <?= $n === 'debutant' ? 'selected' : '' ?>>Débutant</option>
                            <option value="intermediaire" <?= $n === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
                            <option value="avance" <?= $n === 'avance' ? 'selected' : '' ?>>Avancé</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small">Jours par semaine (chiffre 1–7)</label>
                        <input name="jours_par_semaine" class="form-control form-control-sm" value="<?= e((string) ($post['jours_par_semaine'] ?? '')) ?>" />
                    </div>
                    <div>
                        <label class="form-label small">Lieu</label>
                        <select name="lieu" class="form-select form-select-sm">
                            <?php $l = (string) ($post['lieu'] ?? ''); ?>
                            <option value="maison" <?= $l === 'maison' ? 'selected' : '' ?>>Maison</option>
                            <option value="salle" <?= $l === 'salle' ? 'selected' : '' ?>>Salle</option>
                            <option value="mixte" <?= $l === 'mixte' ? 'selected' : '' ?>>Mixte</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-nf">Obtenir une suggestion</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card nf-card">
            <div class="card-body">
                <h2 class="h6">Résultat</h2>
                <?php if ($message !== '') : ?>
                    <p class="small"><?= e($message) ?></p>
                <?php endif; ?>
                <?php if ($suggestion !== null) : ?>
                    <h3 class="h5"><?= e($suggestion['nom']) ?></h3>
                    <p class="small text-muted"><?= e($suggestion['type_programme']) ?> · <?= (int) $suggestion['duree_semaines'] ?> sem.</p>
                    <a class="btn btn-nf btn-sm" href="<?= e(BASE_URL) ?>/index.php?action=program_start&amp;id=<?= (int) $suggestion['id'] ?>">Démarrer ce programme</a>
                <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $errors === []) : ?>
                    <p class="text-warning small mb-0">Aucun programme en base pour le type calculé.</p>
                <?php else : ?>
                    <p class="small text-muted mb-0">Remplissez le formulaire à gauche.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = 'Assistant parcours';
require VIEW_PATH . '/front/layout.php';
