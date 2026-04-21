<?php

require_once __DIR__ . '/controller/post.controller.php';
require_once __DIR__ . '/controller/reply.controller.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$postC = new PostC();
$replyC = new ReplyC();

// Suppression depuis le front-office (POST)
$flashMessage = '';
$flashError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (int) $_POST['delete_id'];
        if ($id > 0) {
            try {
                if (!$isAdmin) {
                    throw new Exception('Accès refusé.');
                }
                $postC->deletePost($id);
                header('Location: blog.php?deleted=1');
                exit;
            } catch (Exception $e) {
                $flashError = $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_reply_id'])) {
        $rid = (int) $_POST['delete_reply_id'];
        $postId = (int) ($_POST['reply_post_id'] ?? 0);
        if ($rid > 0) {
            try {
                if (!$isAdmin) {
                    throw new Exception('Accès refusé.');
                }
                $replyC->deleteReply($rid);
                header('Location: blog.php?reply_deleted=1' . ($postId > 0 ? '#post-' . $postId : ''));
                exit;
            } catch (Exception $e) {
                $flashError = $e->getMessage();
            }
        }
    }
}

if (isset($_GET['deleted'])) {
    $flashMessage = 'Article supprimé.';
} elseif (isset($_GET['reply_deleted'])) {
    $flashMessage = 'Réponse supprimée.';
}

try {
    $posts = $postC->listPost();
} catch (Exception $e) {
    http_response_code(500);
    $error = $e->getMessage();
    $posts = [];
}

?>
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Blog</title>
    <style>
      :root { color-scheme: light; }
      * { box-sizing: border-box; }
      body {
        font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        margin: 0;
        background:
          radial-gradient(900px 450px at 20% -10%, rgba(99, 102, 241, 0.16), transparent 60%),
          radial-gradient(900px 450px at 80% -10%, rgba(16, 185, 129, 0.12), transparent 60%),
          #f6f7fb;
        color: #0f172a;
      }
      a { color: #2563eb; text-decoration: none; }
      a:hover { text-decoration: underline; }

      header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(148, 163, 184, 0.35);
      }

      .container { max-width: 1060px; margin: 0 auto; padding: 22px; }

      .brand { display: flex; align-items: center; gap: 12px; }
      .logo {
        width: 36px; height: 36px; border-radius: 12px;
        background: linear-gradient(135deg, #4f46e5, #22c55e);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
      }
      .brand h1 { font-size: 16px; margin: 0; letter-spacing: 0.2px; }
      .brand p { margin: 2px 0 0; color: #64748b; font-size: 13px; }

      .nav { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
      .nav a {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 9px 12px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.9);
        color: #0f172a;
        font-weight: 650;
        font-size: 14px;
        text-decoration: none;
      }
      .nav a:hover { background: #fff; text-decoration: none; }
      .nav a.primary { background: #2563eb; border-color: #2563eb; color: #fff; }
      .nav a.primary:hover { background: #1d4ed8; border-color: #1d4ed8; }

      .grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 18px; margin-top: 16px; }
      @media (min-width: 860px) {
        .card { grid-column: span 6; }
      }

      .card {
        grid-column: span 12;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
      }
      .card-inner { padding: 16px 16px 14px; }

      .cover {
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        display: block;
        background: #e2e8f0;
      }

      .title { font-size: 18px; font-weight: 800; margin: 0; line-height: 1.25; letter-spacing: 0.1px; }
      .meta { color: #64748b; font-size: 13px; margin-top: 8px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
      .badge {
        display: inline-flex; align-items: center;
        font-size: 12px; padding: 3px 10px;
        border-radius: 999px;
        background: rgba(99, 102, 241, 0.12);
        color: #3730a3;
        border: 1px solid rgba(99, 102, 241, 0.18);
        font-weight: 700;
      }
      .content { margin-top: 12px; color: #334155; line-height: 1.6; font-size: 14.5px; }

      .flash { margin: 0 0 14px; border-radius: 14px; padding: 12px 14px; border: 1px solid transparent; }
      .error { background: rgba(254, 226, 226, 0.9); border-color: rgba(239, 68, 68, 0.25); color: #991b1b; }
      .ok { background: rgba(220, 252, 231, 0.9); border-color: rgba(34, 197, 94, 0.25); color: #166534; }

      .actions {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid rgba(148, 163, 184, 0.25);
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
        align-items: center;
      }
      .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(148, 163, 184, 0.4);
        background: #ffffff;
        border-radius: 12px;
        padding: 8px 12px;
        font-weight: 750;
        font-size: 13px;
        cursor: pointer;
        color: #0f172a;
        text-decoration: none;
        transition: transform 120ms ease, background 120ms ease, border-color 120ms ease;
      }
      .btn:hover { background: #f8fafc; border-color: rgba(148, 163, 184, 0.55); text-decoration: none; transform: translateY(-1px); }
      .btn:active { transform: translateY(0); }
      .btn-danger { border-color: rgba(244, 63, 94, 0.35); background: rgba(255, 241, 242, 0.9); color: #9f1239; }
      .btn-danger:hover { background: rgba(255, 228, 230, 1); border-color: rgba(244, 63, 94, 0.45); }

      .replies { margin-top: 14px; }
      .replies-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
      .replies-title { margin: 0; font-size: 13px; font-weight: 800; color: #0f172a; letter-spacing: 0.2px; }
      .reply {
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: rgba(248, 250, 252, 0.8);
        border-radius: 14px;
        padding: 12px 12px 10px;
        margin-top: 10px;
      }
      .reply-meta { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; color: #64748b; font-size: 12.5px; }
      .reply-content { margin-top: 8px; color: #334155; line-height: 1.55; font-size: 14px; }
      .reply-actions { margin-top: 10px; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap; }
      .btn-sm { padding: 7px 10px; font-size: 12.5px; border-radius: 10px; }
    </style>
  </head>
  <body>
    <header>
      <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:14px;">
        <div class="brand">
          <span class="logo" aria-hidden="true"></span>
          <div>
            <h1>Blog</h1>
            <p>Articles récents</p>
          </div>
        </div>
        <nav class="nav" aria-label="Navigation">
          <a class="primary" href="ajouter_post.php">Ajouter un article</a>
          <a href="view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/index.html">Retour au site</a>
        </nav>
      </div>
    </header>

    <main class="container">
      <?php if ($flashMessage !== '') { ?>
        <div class="flash ok">
          <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php } ?>
      <?php if ($flashError !== '') { ?>
        <div class="flash error">
          <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php } ?>
      <?php if (isset($error)) { ?>
        <div class="flash error">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php } ?>

      <div class="grid">
        <?php
        $hasAny = false;
        foreach ($posts as $row) {
            $hasAny = true;
            $id = (int) ($row['id'] ?? 0);
            $titre = $row['titre'] ?? '';
            $contenu = $row['contenu'] ?? '';
            $date = $row['datePublication'] ?? '';
            $categorie = $row['categorie'] ?? '';
            $image = $row['image'] ?? '';
            $replies = $id > 0 ? $replyC->listRepliesByPostId($id) : [];
        ?>
          <article class="card"<?= $id > 0 ? ' id="post-' . $id . '"' : '' ?>>
            <?php if ($image !== '' && $image !== null) { ?>
              <img class="cover" src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">
            <?php } ?>
            <div class="card-inner">
              <h2 class="title"><?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?></h2>
              <div class="meta">
                <?php if ($categorie !== '') { ?><span class="badge"><?= htmlspecialchars($categorie, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                <?php if ($date !== '') { ?><span><?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
              </div>
              <?php if ($contenu !== '') { ?>
                <div class="content">
                  <?= nl2br(htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8')) ?>
                </div>
              <?php } ?>
              <?php if ($id > 0 && $isAdmin) { ?>
                <div class="actions">
                  <a class="btn" href="modifier_post.php?id=<?= $id ?>">Modifier</a>
                  <form method="post" action="" style="display:inline;" onsubmit="return confirm('Supprimer cet article ?');">
                    <input type="hidden" name="delete_id" value="<?= $id ?>">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                  </form>
                </div>
              <?php } ?>

              <?php if ($id > 0) { ?>
                <div class="replies">
                  <div class="replies-head">
                    <p class="replies-title">Réponses (<?= (int) count($replies) ?>)</p>
                    <a class="btn btn-sm" href="ajouter_reply.php?post_id=<?= $id ?>">Répondre</a>
                  </div>

                  <?php if (count($replies) === 0) { ?>
                    <div class="reply">
                      <div class="reply-content">Aucune réponse pour le moment.</div>
                    </div>
                  <?php } else { ?>
                    <?php foreach ($replies as $reply) {
                        $rid = (int) ($reply['id'] ?? 0);
                        $rcontenu = (string) ($reply['contenu'] ?? '');
                        $rdate = (string) ($reply['datePublication'] ?? '');
                        ?>
                      <div class="reply"<?= $rid > 0 ? ' id="reply-' . $rid . '"' : '' ?>>
                        <div class="reply-meta">
                          <?php if ($rdate !== '') { ?><span><?= htmlspecialchars($rdate, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                        </div>
                        <div class="reply-content"><?= nl2br(htmlspecialchars($rcontenu, ENT_QUOTES, 'UTF-8')) ?></div>
                        <?php if ($rid > 0 && $isAdmin) { ?>
                          <div class="reply-actions">
                            <a class="btn btn-sm" href="modifier_reply.php?id=<?= $rid ?>">Modifier</a>
                            <form method="post" action="" style="display:inline;" onsubmit="return confirm('Supprimer cette réponse ?');">
                              <input type="hidden" name="delete_reply_id" value="<?= $rid ?>">
                              <input type="hidden" name="reply_post_id" value="<?= $id ?>">
                              <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                          </div>
                        <?php } ?>
                      </div>
                    <?php } ?>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
          </article>
        <?php } ?>

        <?php if (!$hasAny) { ?>
          <div class="card">
            <div class="card-inner">Aucun post pour le moment.</div>
          </div>
        <?php } ?>
      </div>
    </main>
  </body>
</html>
