<?php
/** @var list<array<string,mixed>> $programs_catalog */
/** @var list<string> $errors */
/** @var array<string,mixed> $post */
/** @var list<array<string,mixed>> $suggestions */
/** @var string $ai_reason */
/** @var string $api_error */
/** @var bool $ai_ready */
/** @var bool $local_fallback */
/** @var array<string,mixed> $profile_signals */
$local_fallback = $local_fallback ?? false;
$ai_ready = $ai_ready ?? false;
$suggestions = $suggestions ?? [];
$profile_signals = $profile_signals ?? [];
ob_start();
?>
<h1 class="h3 mb-3">Suggestion de programme (Ollama)</h1>
<p class="text-muted small">
    Pas un chatbot : un envoi envoie votre profil et la liste des programmes au modèle <strong>sur votre PC</strong> (Ollama) avec consignes système + message utilisateur ; réponse JSON avec <code>2 programmes classés</code>.
    Installez <a href="https://ollama.com" target="_blank" rel="noopener">Ollama</a>, puis <code>ollama pull llama3.2</code> et laissez <code>ollama serve</code> actif. Réglages : <code>.env</code> (<code>OLLAMA_MODEL</code>, <code>OLLAMA_BASE_URL</code>). Ce n’est pas un avis médical.
</p>

<?php if (!function_exists('curl_init')) : ?>
    <div class="alert alert-danger py-2 small">
        Activez <code>extension=curl</code> dans le <code>php.ini</code> d’Apache (XAMPP), puis redémarrez Apache.
    </div>
<?php endif; ?>

<details class="mb-3 small text-muted">
    <summary class="text-body">Ollama ne répond pas ?</summary>
    <ul class="mb-0 mt-2 ps-3">
        <li>Terminal : <code>ollama serve</code> (ou l’app Windows en arrière-plan).</li>
        <li>Modèle téléchargé : <code>ollama pull llama3.2</code> (ou le nom dans votre <code>.env</code>).</li>
        <li>URL : par défaut <code>http://127.0.0.1:11434</code> — si vous changez le port, mettez <code>OLLAMA_BASE_URL</code> dans <code>.env</code>.</li>
    </ul>
</details>

<?php if (!$ai_ready) : ?>
    <div class="alert alert-warning">
        <strong>Ollama non configuré.</strong> Dans <code>.env</code> à la racine du projet : <code>OLLAMA_MODEL=llama3.2</code> (voir <code>.env.example</code>) puis vérifiez qu’Ollama tourne.
    </div>
<?php endif; ?>

<?php foreach ($errors as $err) : ?>
    <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<?php if ($local_fallback && $suggestions !== []) : ?>
    <div class="alert alert-info py-2 small mb-0">
        Ollama n’a pas répondu correctement : suggestion automatique locale (règles simples).
    </div>
<?php endif; ?>

<?php if ($api_error !== '') : ?>
    <div class="alert alert-danger py-2"><?= e($api_error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card nf-card">
            <div class="card-body">
                <form method="post" action="<?= e(BASE_URL) ?>/index.php?action=recommend_ai" class="vstack gap-3">
                    <?php if ($ai_ready && defined('OLLAMA_MODEL')) : ?>
                        <p class="small text-muted mb-0">Modèle : <code><?= e((string) OLLAMA_MODEL) ?></code> (depuis <code>.env</code>).</p>
                    <?php endif; ?>
                    <div>
                        <label class="form-label small">Poids (kg) — facultatif</label>
                        <input name="poids_kg" class="form-control form-control-sm"
                            value="<?= e((string) ($post['poids_kg'] ?? '')) ?>" placeholder="ex. 72" />
                    </div>
                    <div>
                        <label class="form-label small">Taille (cm) — facultatif</label>
                        <input name="taille_cm" class="form-control form-control-sm"
                            value="<?= e((string) ($post['taille_cm'] ?? '')) ?>" placeholder="ex. 175" />
                    </div>
                    <div>
                        <label class="form-label small">Expérience</label>
                        <?php $ex = (string) ($post['experience'] ?? 'debutant'); ?>
                        <select name="experience" class="form-select form-select-sm">
                            <option value="debutant" <?= $ex === 'debutant' ? 'selected' : '' ?>>Débutant</option>
                            <option value="intermediaire" <?= $ex === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
                            <option value="avance" <?= $ex === 'avance' ? 'selected' : '' ?>>Avancé</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small">Objectif principal</label>
                        <?php $o = (string) ($post['objectif'] ?? 'musculation'); ?>
                        <select name="objectif" class="form-select form-select-sm">
                            <?php foreach (TYPES_ENTRAINEMENT as $t) : ?>
                                <option value="<?= e($t) ?>" <?= $o === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small">Lieu</label>
                        <?php $l = (string) ($post['lieu'] ?? 'mixte'); ?>
                        <select name="lieu" class="form-select form-select-sm">
                            <option value="maison" <?= $l === 'maison' ? 'selected' : '' ?>>Maison</option>
                            <option value="salle" <?= $l === 'salle' ? 'selected' : '' ?>>Salle</option>
                            <option value="mixte" <?= $l === 'mixte' ? 'selected' : '' ?>>Mixte</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small">Notes (facultatif, max 500 car.)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" maxlength="500"><?= e((string) ($post['notes'] ?? '')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-nf" <?= $ai_ready ? '' : 'disabled' ?>>Demander une suggestion</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card nf-card">
            <div class="card-body">
                <h2 class="h6">Résultat</h2>
                <?php if ($suggestions !== []) : ?>
                    <?php if ($profile_signals !== []) : ?>
                        <p class="small text-muted mb-2">Poids/taille pris en compte (ex. IMC/profil corporel) pour affiner la sélection.</p>
                    <?php endif; ?>
                    <?php foreach ($suggestions as $idx => $sg) : ?>
                        <h3 class="h6 mb-1"><?= $idx === 0 ? 'Choix 1 (meilleur)' : 'Choix 2 (alternative)' ?> — <?= e((string) $sg['nom']) ?></h3>
                        <p class="small text-muted"><?= e((string) $sg['type_programme']) ?> · <?= (int) $sg['duree_semaines'] ?> sem.</p>
                        <a class="btn btn-nf btn-sm mb-3" href="<?= e(BASE_URL) ?>/index.php?action=program_start&amp;id=<?= (int) $sg['id'] ?>">Démarrer ce programme</a>
                    <?php endforeach; ?>
                    <?php if ($ai_reason !== '') : ?>
                        <p class="small border-top pt-2"><?= nl2br(e($ai_reason)) ?></p>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="small text-muted mb-0">Remplissez le formulaire et envoyez. Programmes en base : <?= count($programs_catalog) ?>.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$slot = ob_get_clean();
$pageTitle = 'Suggestion IA';
require VIEW_PATH . '/front/layout.php';
