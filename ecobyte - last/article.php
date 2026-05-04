<?php

require_once __DIR__ . '/controller/post.controller.php';
require_once __DIR__ . '/controller/reply.controller.php';
require_once __DIR__ . '/controller/image_utils.php';
require_once __DIR__ . '/model/reply.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$postId = (int) ($_GET['id'] ?? 0);
if ($postId <= 0) {
    header('Location: blog.php');
    exit;
}

$postC = new PostC();
$replyC = new ReplyC();

$post = $postC->getPostById($postId);
if (!$post) {
    http_response_code(404);
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Article introuvable</title></head><body><p>Article introuvable.</p><p><a href="blog.php">Retour au blog</a></p></body></html>';
    exit;
}

$nutritionRaw = $post['nutrition'] ?? '';
$nutritionData = null;
if (is_string($nutritionRaw) && $nutritionRaw !== '') {
    $decodedNutrition = json_decode($nutritionRaw, true);
    if (is_array($decodedNutrition) && (
        (isset($decodedNutrition['success']) && $decodedNutrition['success']) ||
        (isset($decodedNutrition['nutrition']) && is_array($decodedNutrition['nutrition']))
    )) {
        $nutritionData = $decodedNutrition;
    }
}

$image = $post['image'] ?? '';
$title = $post['titre'] ?? '';
$content = $post['contenu'] ?? '';
$date = $post['datePublication'] ?? '';
$category = $post['categorie'] ?? '';
$replies = $replyC->listRepliesByPostId($postId);

?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — Ecobyte</title>
    <style>
      :root { color-scheme: light; }
      * { box-sizing: border-box; }
      body { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: #f8fafc; color: #0f172a; }
      .container { max-width: 860px; margin: 0 auto; padding: 22px; }
      .top { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
      .top a { color: #2563eb; text-decoration: none; font-weight: 600; }
      .card { background: #fff; border: 1px solid rgba(148, 163, 184, 0.35); border-radius: 20px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); overflow: hidden; }
      .cover { width: 100%; height: auto; display: block; background: #e2e8f0; }
      .card-inner { padding: 24px; }
      .title { margin: 0 0 12px; font-size: 2rem; line-height: 1.05; }
      .meta { color: #64748b; font-size: 0.95rem; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 20px; }
      .badge { display: inline-flex; align-items: center; font-size: 12px; padding: 4px 11px; border-radius: 999px; background: rgba(99, 102, 241, 0.12); color: #3730a3; border: 1px solid rgba(99, 102, 241, 0.18); font-weight: 700; }
      .content { color: #334155; line-height: 1.8; font-size: 1rem; }
      .generated-box { margin-top: 18px; padding: 18px 20px; border-radius: 18px; background: rgba(220, 252, 231, 0.95); border: 1px solid rgba(34, 197, 94, 0.35); color: #14532d; font-size: 0.95rem; line-height: 1.7; }
      .generated-box strong { display: block; margin-bottom: 10px; }
      .nutrition-box { margin-top: 24px; padding: 18px 20px; border-radius: 18px; background: rgba(220, 252, 231, 0.9); border: 1px solid rgba(34, 197, 94, 0.25); color: #14532d; font-size: 0.95rem; line-height: 1.75; }
      .section-title { margin: 38px 0 16px; font-size: 1.15rem; font-weight: 700; }
      .reply { margin-top: 14px; padding: 16px; border-radius: 16px; background: rgba(248, 250, 252, 0.9); border: 1px solid rgba(148, 163, 184, 0.25); }
      .reply .reply-meta { color: #64748b; font-size: 0.9rem; margin-bottom: 8px; }
      .reply-content { color: #334155; line-height: 1.6; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="top">
        <a href="blog.php">← Retour au blog</a>
        <a href="view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/index.html">Accueil du site</a>
      </div>
      <article class="card">
        <?php if ($image !== '' && $image !== null) { ?>
          <img class="cover" src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
        <?php } ?>
        <div class="card-inner">
          <h1 class="title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
          <div class="meta">
            <?php if ($category !== '') { ?><span class="badge"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
            <?php if ($date !== '') { ?><span><?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
          </div>
          <?php if ($nutritionData !== null) { ?>
            <div class="generated-box">
              <strong>📝 Article généré automatiquement :</strong>
              Article généré automatiquement autour de l’aliment détecté : <b><?= htmlspecialchars((string) ($nutritionData['food_name'] ?? 'Inconnu'), ENT_QUOTES, 'UTF-8') ?></b>.<br>
              Catégorie : <b><?= htmlspecialchars($category !== '' ? $category : 'Santé & Nutrition', ENT_QUOTES, 'UTF-8') ?></b>.
            </div>
            <div class="nutrition-box">
              <strong>🍎 Analyse nutritionnelle :</strong><br>
              Aliment : <b><?= htmlspecialchars((string) ($nutritionData['food_name'] ?? 'Inconnu'), ENT_QUOTES, 'UTF-8') ?></b><br>
              Calories (100g) : <b><?= htmlspecialchars((string) ($nutritionData['nutrition']['calories'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></b> kcal<br>
              Protéines : <b><?= htmlspecialchars((string) ($nutritionData['nutrition']['protein'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></b>g, Lipides : <b><?= htmlspecialchars((string) ($nutritionData['nutrition']['fat'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></b>g, Glucides : <b><?= htmlspecialchars((string) ($nutritionData['nutrition']['carbs'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></b>g
            </div>
          <?php } ?>
          <div class="content" style="margin-top:24px;"><?= nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) ?></div>
        </div>
      </article>
      <?php if (!empty($replies)) { ?>
        <h2 class="section-title">Réponses (<?= count($replies) ?>)</h2>
        <?php foreach ($replies as $reply) { ?>
          <div class="reply">
            <div class="reply-meta">
              <?= htmlspecialchars($reply['author'] ?? 'Visiteur', ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($reply['datepublication'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="reply-content"><?= nl2br(htmlspecialchars($reply['contenu'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  </body>
</html>
