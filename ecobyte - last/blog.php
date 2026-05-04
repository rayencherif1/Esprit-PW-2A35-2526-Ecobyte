<?php

require_once __DIR__ . '/controller/post.controller.php';
require_once __DIR__ . '/controller/reply.controller.php';
require_once __DIR__ . '/controller/image_utils.php';
require_once __DIR__ . '/model/reply.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$postC = new PostC();
$replyC = new ReplyC();

// Suppression depuis le front-office (POST)
$flashMessage = '';
$flashError = '';
$replyError = '';
$pendingReplyPostId = 0;
$pendingReplyContent = '';
$pendingReplyPseudo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signal_reply_id'])) {
        $signalReplyId = (int) ($_POST['signal_reply_id'] ?? 0);
        $signalReason = trim((string) ($_POST['signal_reason'] ?? ''));

        if ($signalReplyId <= 0) {
            $flashError = 'Commentaire invalide.';
        } elseif ($signalReason === '') {
            $flashError = 'La raison du signalement est obligatoire.';
        } else {
            if ($replyC->signalReply($signalReplyId, $signalReason)) {
                header('Location: blog.php?reported=1#reply-' . $signalReplyId);
                exit;
            }
            $flashError = 'Impossible de signaler ce commentaire pour le moment.';
        }
    } elseif (isset($_POST['add_reply'])) {
        $postId = (int) ($_POST['post_id'] ?? 0);
        $contenu = trim((string) ($_POST['contenu'] ?? ''));
        $pseudo = trim((string) ($_POST['pseudo'] ?? ''));
        $parentReplyId = (int) ($_POST['parent_reply_id'] ?? 0);
        $pendingReplyPostId = $postId;
        $pendingReplyContent = (string) ($_POST['contenu'] ?? '');
        $pendingReplyPseudo = $pseudo;

        // Gestion de l'upload d'image
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Valider l'image d'abord
            $imageValidation = validateUploadedImage($_FILES['image']['tmp_name']);
            if (!$imageValidation['valid']) {
                $replyError = 'Image invalide: ' . $imageValidation['message'];
            } else {
                $uploadDir = __DIR__ . '/view/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $imagePath = 'view/uploads/' . $fileName;
                }
            }
        } else {
            // Debug: voir pourquoi l'upload ne fonctionne pas
            $uploadError = isset($_FILES['image']) ? $_FILES['image']['error'] : 'pas de fichier';
        }

        if ($flashError === '' && $replyError === '' && $postId <= 0) {
            $flashError = 'Article invalide.';
        } elseif ($flashError === '' && $replyError === '' && $pseudo === '') {
            $replyError = 'Votre nom est obligatoire.';
        } elseif ($flashError === '' && $replyError === '' && $contenu === '' && $imagePath === null) {
            $replyError = 'Le contenu ou une image est obligatoire.';
        } elseif ($flashError === '' && $replyError === '') {
            try {
                $contenu = nettoyerCommentaire($contenu);
                $userId = $replyC->getOrCreateUserByPseudo($pseudo);
                $reply = new Reply(null, $contenu, $imagePath, null, $postId, $userId, 'en_attente', null, 0, $parentReplyId);
                $replyC->addReply($reply);
                header('Location: blog.php?reply_pending=1#post-' . $postId);
                exit;
            } catch (Exception $e) {
                $flashError = $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_id'])) {
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
    } elseif (isset($_POST['like_reply_id'])) {
        $rid = (int) $_POST['like_reply_id'];
        $postId = (int) ($_POST['like_post_id'] ?? 0);
        if ($rid > 0) {
            try {
                $replyC->addLike($rid);
                header('Location: blog.php' . ($postId > 0 ? '?liked=1#reply-' . $rid : '?liked=1'));
                exit;
            } catch (Exception $e) {
                $flashError = $e->getMessage();
            }
        }
    } elseif (isset($_POST['unlike_reply_id'])) {
        $rid = (int) $_POST['unlike_reply_id'];
        $postId = (int) ($_POST['like_post_id'] ?? 0);
        if ($rid > 0) {
            try {
                $replyC->removeLike($rid);
                header('Location: blog.php' . ($postId > 0 ? '?unliked=1#reply-' . $rid : '?unliked=1'));
                exit;
            } catch (Exception $e) {
                $flashError = $e->getMessage();
            }
        }
    }
}

if (isset($_GET['deleted'])) {
    $flashMessage = 'Article supprimé.';
} elseif (isset($_GET['reply_pending'])) {
    $flashMessage = 'Votre commentaire est en attente de modération.';
} elseif (isset($_GET['reported'])) {
    $flashMessage = 'Merci, le signalement a bien été pris en compte.';
} elseif (isset($_GET['reply_deleted'])) {
    $flashMessage = 'Réponse supprimée.';
} elseif (isset($_GET['liked'])) {
    $flashMessage = 'Merci pour votre like !';
} elseif (isset($_GET['unliked'])) {
    $flashMessage = 'Like retiré.';
}

try {
    $searchQuery = trim((string) ($_GET['search'] ?? ''));
    $filterCategory = trim((string) ($_GET['category'] ?? ''));
    if ($searchQuery !== '') {
        $posts = $postC->searchPosts($searchQuery);
    } elseif ($filterCategory !== '') {
        $posts = $postC->filterPostsByCategory($filterCategory);
    } else {
        $posts = $postC->listPost()->fetchAll(PDO::FETCH_ASSOC);
    }
    $categories = $postC->getCategories();
} catch (Exception $e) {
    http_response_code(500);
    $error = $e->getMessage();
    $posts = [];
    $categories = [];
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
      .nutrition-box {
        margin-top: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(220, 252, 231, 0.75);
        border: 1px solid rgba(34, 197, 94, 0.25);
        color: #14532d;
        font-size: 13.5px;
        line-height: 1.55;
      }

      .flash { margin: 0 0 14px; border-radius: 14px; padding: 12px 14px; border: 1px solid transparent; }
      .error { background: rgba(254, 226, 226, 0.9); border-color: rgba(239, 68, 68, 0.25); color: #991b1b; }
      .reply-form .field-error {
        margin: 0 0 12px;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(254, 226, 226, 0.9);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #991b1b;
        font-size: 0.95rem;
      }
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
      .translate-btn { background: none; border: none; color: #2563eb; cursor: pointer; font-size: 12px; text-decoration: underline; padding: 0; margin-left: 8px; }
      .translate-btn:hover { color: #1d4ed8; }
      .translated-content { background: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 8px; margin-top: 8px; color: #064e3b; font-style: italic; }
      .reply-form {
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: rgba(248, 250, 252, 0.8);
        border-radius: 14px;
        padding: 12px;
        margin-top: 10px;
      }
      .reply-form textarea{
        width: 100%;
        min-height: 90px;
        resize: vertical;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        padding: 10px 12px;
        font: inherit;
        line-height: 1.5;
        background: rgba(255, 255, 255, 0.92);
        color: #0f172a;
      }
      .reply-form textarea:focus { outline: none; border-color: rgba(37, 99, 235, 0.55); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12); }
      .reply-form-actions { display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap; margin-top: 10px; }
      .reply {
        border: 1px solid rgba(148, 163, 184, 0.25);
        background: rgba(248, 250, 252, 0.8);
        border-radius: 14px;
        padding: 12px 12px 10px;
        margin-top: 10px;
      }
      .reply-meta { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; color: #64748b; font-size: 12.5px; }
      .like-count { color: #2563eb; font-weight: 600; }
      .reply-content { margin-top: 8px; color: #334155; line-height: 1.55; font-size: 14px; }
      .reply-actions { margin-top: 10px; display: flex; gap: 10px; justify-content: flex-start; flex-wrap: wrap; }
      .btn-sm { padding: 7px 10px; font-size: 12.5px; border-radius: 10px; }
      .btn-liked { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #dc2626; }
      .reply-nested { margin-left: 24px; border-left: 3px solid rgba(99, 102, 241, 0.25); background: rgba(249, 250, 251, 0.9); }
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
          <a href="nutrition_analyzer_test.php">🍎 Nutritionnel</a>
          <a href="view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/index.html">Retour au site</a>
        </nav>
        <form method="get" action="" style="display:flex;gap:8px;" id="search-form">
          <input id="search-input" type="text" name="search" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher..." style="padding:9px 12px;border:1px solid rgba(148,163,184,0.35);border-radius:12px;background:rgba(255,255,255,0.9);font-size:14px;">
          <select id="category-select" name="category" style="padding:9px 12px;border:1px solid rgba(148,163,184,0.35);border-radius:12px;background:rgba(255,255,255,0.9);font-size:14px;">
            <option value="">Toutes les catégories</option>
            <?php foreach ($categories as $cat) { ?>
              <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
          <button type="submit" class="btn" style="padding:9px 12px;font-size:14px;">Filtrer</button>
          <?php if ($searchQuery !== '' || $filterCategory !== '') { ?>
            <a href="blog.php" class="btn" style="padding:9px 12px;font-size:14px;text-decoration:none;background:#e2e8f0;color:#0f172a;">Effacer</a>
          <?php } ?>
        </form>
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

      <div class="grid" id="posts-grid">
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
            $nutritionRaw = $row['nutrition'] ?? '';
            $nutritionData = null;
            if (is_string($nutritionRaw) && $nutritionRaw !== '') {
                $decodedNutrition = json_decode($nutritionRaw, true);
                if (is_array($decodedNutrition) && isset($decodedNutrition['success']) && $decodedNutrition['success']) {
                    $nutritionData = $decodedNutrition;
                }
            }
            $replies = $id > 0 ? $replyC->listRepliesByPostId($id) : [];
        ?>
          <article class="card"<?= $id > 0 ? ' id="post-' . $id . '"' : '' ?>>
            <?php if ($image !== '' && $image !== null) { ?>
              <a href="article.php?id=<?= $id ?>">
                <img class="cover" src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">
              </a>
            <?php } ?>
            <div class="card-inner">
              <h2 class="title"><a href="article.php?id=<?= $id ?>" style="color:inherit;text-decoration:none;"><?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?></a></h2>
              <div class="meta">
                <?php if ($categorie !== '') { ?><span class="badge"><?= htmlspecialchars($categorie, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                <?php if ($date !== '') { ?><span><?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
              </div>
              <?php if ($contenu !== '') { ?>
                <div class="content">
                  <?= nl2br(htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8')) ?>
                </div>
              <?php } ?>
              <?php if ($nutritionData !== null) { ?>
                <div class="nutrition-box">
                  <strong>🍎 Analyse nutritionnelle :</strong><br>
                  Aliment : <b><?= htmlspecialchars((string) ($nutritionData['food_name'] ?? 'Inconnu'), ENT_QUOTES, 'UTF-8') ?></b><br>
                  Calories (100g) : <b><?= htmlspecialchars((string) ($nutritionData['nutrition']['calories'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></b> kcal<br>
                  Protéines : <b><?= htmlspecialchars((string) ($nutritionData['nutrition']['protein'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></b>g,
                  Lipides : <b><?= htmlspecialchars((string) ($nutritionData['nutrition']['fat'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></b>g,
                  Glucides : <b><?= htmlspecialchars((string) ($nutritionData['nutrition']['carbs'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></b>g
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

                  <form class="reply-form" method="post" action="blog.php#post-<?= $id ?>" enctype="multipart/form-data">
                    <input type="hidden" name="add_reply" value="1">
                    <input type="hidden" name="post_id" value="<?= $id ?>">
                    <?php if ($pendingReplyPostId === $id && $replyError !== '') { ?>
                      <div class="field-error"><?= htmlspecialchars($replyError, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php } ?>
                    <label for="pseudo-<?= $id ?>" style="display:block;margin-top:0;font-size:12.5px;font-weight:600;">Nom *</label>
                    <input id="pseudo-<?= $id ?>" type="text" name="pseudo" value="<?= htmlspecialchars($pendingReplyPostId === $id ? $pendingReplyPseudo : '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Votre nom" style="width:100%;padding:10px 12px;border:1px solid rgba(148, 163, 184, 0.35);border-radius:12px;background:rgba(255,255,255,0.92);font:inherit;">
                    <textarea name="contenu" placeholder="Écrivez un commentaire…" style="margin-top:10px;"><?= htmlspecialchars($pendingReplyPostId === $id ? $pendingReplyContent : '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <label style="display:block;margin-top:8px;font-size:12.5px;font-weight:600;">Image (optionnel)</label>
                    <label for="image-<?= $id ?>" class="btn btn-sm" style="cursor: pointer; display: inline-block; margin-top:4px;">Choisir une image</label>
                    <input type="file" id="image-<?= $id ?>" name="image" accept="image/*" style="display: none;">
                    <div class="reply-form-actions">
                      <button type="submit" class="btn btn-sm">Publier</button>
                    </div>
                  </form>

                  <?php 
                    // Séparer les réponses principales et les réponses imbriquées
                    $topReplies = [];
                    $nestedReplies = [];
                    foreach ($replies as $reply) {
                        $parentId = (int) ($reply['parent_reply_id'] ?? 0);
                        if ($parentId === 0) {
                            $topReplies[] = $reply;
                        } else {
                            $nestedReplies[$parentId][] = $reply;
                        }
                    }
                  ?>
                  <?php if (count($topReplies) === 0) { ?>
                    <div class="reply">
                      <div class="reply-content">Aucune réponse pour le moment.</div>
                    </div>
                  <?php } else { ?>
                    <?php foreach ($topReplies as $reply) {
                        $rid = (int) ($reply['id'] ?? 0);
                        $rauthor = (string) ($reply['author'] ?? 'Visiteur');
                        $rstatut = (string) ($reply['statut'] ?? '');
                        $rcontenu = (string) ($reply['contenu'] ?? '');
                        $rimage = (string) ($reply['image'] ?? '');
                        $rdate = (string) ($reply['datePublication'] ?? '');
                        $rlikes = (int) ($reply['likes'] ?? 0);
                        $userLiked = $rid > 0 && $replyC->userHasLiked($rid);
                        $childReplies = $nestedReplies[$rid] ?? [];
                        ?>
                      <div class="reply"<?= $rid > 0 ? ' id="reply-' . $rid . '"' : '' ?>>
                        <div class="reply-meta">
                          <?php if ($rauthor !== '') { ?><span><?= htmlspecialchars($rauthor, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                          <?php if ($rdate !== '') { ?><span><?= htmlspecialchars($rdate, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                          <?php if ($rid > 0) { ?>
                            <span class="like-count"><?= $rlikes ?> j'aime</span>
                          <?php } ?>
                          <?php if ($rstatut === 'signale') { ?>
                            <span class="badge" style="background: rgba(251, 191, 36, 0.18); color: #92400e; border-color: rgba(251, 191, 36, 0.4);">Signalé</span>
                          <?php } ?>
                        </div>
                        <div class="reply-content" id="reply-content-<?= $rid ?>"><?= nl2br(htmlspecialchars($rcontenu, ENT_QUOTES, 'UTF-8')) ?></div>
                        <button class="translate-btn" onclick="traduireCommentaire(<?= $rid ?>)">🌐 Traduire</button>
                        <?php if ($rimage !== '') { ?>
                          <img src="<?= htmlspecialchars($rimage, ENT_QUOTES, 'UTF-8') ?>" alt="Image" style="max-width:100%;border-radius:8px;margin-top:8px;">
                        <?php } ?>
                        <?php if ($rid > 0) { ?>
                          <div class="reply-actions">
                            <button type="button" class="btn btn-sm" onclick="document.getElementById('reply-form-<?= $rid ?>').style.display='block'">💬 Répondre</button>
                            <?php if ($userLiked) { ?>
                              <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="unlike_reply_id" value="<?= $rid ?>">
                                <input type="hidden" name="like_post_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-sm btn-liked">❤️ J'aime</button>
                              </form>
                            <?php } else { ?>
                              <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="like_reply_id" value="<?= $rid ?>">
                                <input type="hidden" name="like_post_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-sm">🤍 J'aime</button>
                              </form>
                            <?php } ?>
                            <?php if ($rstatut !== 'signale') { ?>
                              <button type="button" class="btn btn-sm" onclick="document.getElementById('signal-form-<?= $rid ?>').style.display='block'">🚩 Signaler</button>
                            <?php } else { ?>
                              <span class="badge" style="background: rgba(248, 113, 113, 0.12); color: #b91c1c; border-color: rgba(248, 113, 113, 0.25);">Signalé</span>
                            <?php } ?>
                            <?php if ($isAdmin) { ?>
                              <a class="btn btn-sm" href="modifier_reply.php?id=<?= $rid ?>">Modifier</a>
                              <form method="post" action="" style="display:inline;" onsubmit="return confirm('Supprimer cette réponse ?');">
                                <input type="hidden" name="delete_reply_id" value="<?= $rid ?>">
                                <input type="hidden" name="reply_post_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                              </form>
                            <?php } ?>
                          </div>
                          <div id="reply-form-<?= $rid ?>" class="reply-form" style="display:none; margin-top:10px;">
                            <form method="post" action="blog.php#post-<?= $id ?>" enctype="multipart/form-data">
                              <input type="hidden" name="add_reply" value="1">
                              <input type="hidden" name="post_id" value="<?= $id ?>">
                              <input type="hidden" name="parent_reply_id" value="<?= $rid ?>">
                              <textarea name="contenu" placeholder="Répondre à ce commentaire…" required></textarea>
                              <label style="display:block;margin-top:8px;font-size:12.5px;font-weight:600;">Image (optionnel)</label>
                              <label for="image-<?= $rid ?>" class="btn btn-sm" style="cursor: pointer; display: inline-block; margin-top:4px;">Choisir une image</label>
                              <input type="file" id="image-<?= $rid ?>" name="image" accept="image/*" style="display: none;">
                              <div class="reply-form-actions">
                                <button type="button" class="btn btn-sm" onclick="document.getElementById('reply-form-<?= $rid ?>').style.display='none'">Annuler</button>
                                <button type="submit" class="btn btn-sm">Envoyer</button>
                              </div>
                            </form>
                          </div>
                          <div id="signal-form-<?= $rid ?>" class="reply-form" style="display:none; margin-top:10px;">
                            <form method="post" action="blog.php#reply-<?= $rid ?>">
                              <input type="hidden" name="signal_reply_id" value="<?= $rid ?>">
                              <label for="signal-reason-<?= $rid ?>" style="display:block;margin-top:0;font-size:12.5px;font-weight:600;">Raison du signalement</label>
                              <textarea id="signal-reason-<?= $rid ?>" name="signal_reason" placeholder="Expliquez pourquoi vous signalez ce commentaire…" style="width:100%;padding:10px 12px;border:1px solid rgba(148, 163, 184, 0.35);border-radius:12px;background:rgba(255,255,255,0.92);font:inherit;min-height:80px;"></textarea>
                              <div class="reply-form-actions">
                                <button type="button" class="btn btn-sm" onclick="document.getElementById('signal-form-<?= $rid ?>').style.display='none'">Annuler</button>
                                <button type="submit" class="btn btn-sm">Valider le signalement</button>
                              </div>
                            </form>
                          </div>
                        <?php } ?>
                      </div>
                      <?php foreach ($childReplies as $childReply) {
                          $childId = (int) ($childReply['id'] ?? 0);
                          $childAuthor = (string) ($childReply['author'] ?? 'Visiteur');
                          $childStatut = (string) ($childReply['statut'] ?? '');
                          $childContenu = (string) ($childReply['contenu'] ?? '');
                          $childImage = (string) ($childReply['image'] ?? '');
                          $childDate = (string) ($childReply['datePublication'] ?? '');
                          $childLikes = (int) ($childReply['likes'] ?? 0);
                          $childUserLiked = $childId > 0 && $replyC->userHasLiked($childId);
                          ?>
                        <div class="reply reply-nested"<?= $childId > 0 ? ' id="reply-' . $childId . '"' : '' ?>>
                          <div class="reply-meta">
                            <span>↳ Réponse</span>
                            <?php if ($childAuthor !== '') { ?><span><?= htmlspecialchars($childAuthor, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                            <?php if ($childDate !== '') { ?><span><?= htmlspecialchars($childDate, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                            <?php if ($childId > 0) { ?>
                              <span class="like-count"><?= $childLikes ?> j'aime</span>
                            <?php } ?>
                            <?php if ($childStatut === 'signale') { ?>
                              <span class="badge" style="background: rgba(251, 191, 36, 0.18); color: #92400e; border-color: rgba(251, 191, 36, 0.4);">Signalé</span>
                            <?php } ?>
                          </div>
                          <div class="reply-content" id="reply-content-<?= $childId ?>"><?= nl2br(htmlspecialchars($childContenu, ENT_QUOTES, 'UTF-8')) ?></div>
                          <button class="translate-btn" onclick="traduireCommentaire(<?= $childId ?>)">🌐 Traduire</button>
                          <?php if ($childImage !== '') { ?>
                            <img src="<?= htmlspecialchars($childImage, ENT_QUOTES, 'UTF-8') ?>" alt="Image" style="max-width:100%;border-radius:8px;margin-top:8px;">
                          <?php } ?>
                          <?php if ($childId > 0) { ?>
                            <div class="reply-actions">
                              <?php if ($childUserLiked) { ?>
                                <form method="post" action="" style="display:inline;">
                                  <input type="hidden" name="unlike_reply_id" value="<?= $childId ?>">
                                  <input type="hidden" name="like_post_id" value="<?= $id ?>">
                                  <button type="submit" class="btn btn-sm btn-liked">❤️ J'aime</button>
                                </form>
                              <?php } else { ?>
                                <form method="post" action="" style="display:inline;">
                                  <input type="hidden" name="like_reply_id" value="<?= $childId ?>">
                                  <input type="hidden" name="like_post_id" value="<?= $id ?>">
                                  <button type="submit" class="btn btn-sm">🤍 J'aime</button>
                                </form>
                              <?php } ?>
                              <?php if ($isAdmin) { ?>
                                <a class="btn btn-sm" href="modifier_reply.php?id=<?= $childId ?>">Modifier</a>
                                <form method="post" action="" style="display:inline;" onsubmit="return confirm('Supprimer cette réponse ?');">
                                  <input type="hidden" name="delete_reply_id" value="<?= $childId ?>">
                                  <input type="hidden" name="reply_post_id" value="<?= $id ?>">
                                  <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                              <?php } ?>
                              <?php if ($childStatut !== 'signale') { ?>
                                <button type="button" class="btn btn-sm" onclick="document.getElementById('signal-form-<?= $childId ?>').style.display='block'">🚩 Signaler</button>
                              <?php } else { ?>
                                <span class="badge" style="background: rgba(248, 113, 113, 0.12); color: #b91c1c; border-color: rgba(248, 113, 113, 0.25);">Signalé</span>
                              <?php } ?>
                            </div>
                          <?php } ?>
                          <div id="signal-form-<?= $childId ?>" class="reply-form" style="display:none; margin-top:10px;">
                            <form method="post" action="blog.php#reply-<?= $childId ?>">
                              <input type="hidden" name="signal_reply_id" value="<?= $childId ?>">
                              <label for="signal-reason-<?= $childId ?>" style="display:block;margin-top:0;font-size:12.5px;font-weight:600;">Raison du signalement</label>
                              <textarea id="signal-reason-<?= $childId ?>" name="signal_reason" placeholder="Expliquez pourquoi vous signalez ce commentaire…" style="width:100%;padding:10px 12px;border:1px solid rgba(148, 163, 184, 0.35);border-radius:12px;background:rgba(255,255,255,0.92);font:inherit;min-height:80px;"></textarea>
                              <div class="reply-form-actions">
                                <button type="button" class="btn btn-sm" onclick="document.getElementById('signal-form-<?= $childId ?>').style.display='none'">Annuler</button>
                                <button type="submit" class="btn btn-sm">Valider le signalement</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      <?php } ?>
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

    <script>
      async function traduireCommentaire(replyId) {
        const contentDiv = document.getElementById('reply-content-' + replyId);
        const originalText = contentDiv.innerText;
        const targetLang = prompt('Quelle langue? (en=Anglais, es=Espagnol, de=Allemand, it=Italien, pt=Portugais, ja=Japonais, zh=Chinois, ru=Russe, ar=Arabe):', 'en');
        
        if (!targetLang) return;

        const button = event.target;
        button.disabled = true;
        button.innerText = '⏳ Traduction...';

        try {
          const response = await fetch('api/translate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text: originalText, target_lang: targetLang }),
          });

          if (!response.ok) {
            alert('Erreur de traduction. Vérifiez la langue.');
            button.disabled = false;
            button.innerText = '🌐 Traduire';
            return;
          }

          const data = await response.json();
          if (data.success) {
            let translatedDiv = document.getElementById('translated-' + replyId);
            if (!translatedDiv) {
              translatedDiv = document.createElement('div');
              translatedDiv.id = 'translated-' + replyId;
              translatedDiv.className = 'translated-content';
              contentDiv.parentNode.insertBefore(translatedDiv, contentDiv.nextSibling);
            }
            translatedDiv.innerHTML = '<strong>Traduit en ' + data.target_lang.toUpperCase() + ':</strong> ' + escapeHtml(data.translated);
            button.innerText = '✅ Traduit';
          } else {
            alert('Erreur: ' + (data.error || 'Traduction échouée'));
            button.disabled = false;
            button.innerText = '🌐 Traduire';
          }
        } catch (err) {
          alert('Erreur réseau: ' + err.message);
          button.disabled = false;
          button.innerText = '🌐 Traduire';
        }
      }

      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      // Recherche dynamique (AJAX) côté front-office.
      (function initDynamicSearch() {
        const form = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const categorySelect = document.getElementById('category-select');
        const postsGrid = document.getElementById('posts-grid');
        if (!form || !searchInput || !categorySelect || !postsGrid) return;

        let debounceTimer = null;

        function setLoading() {
          postsGrid.innerHTML = '<div class="card"><div class="card-inner">Chargement...</div></div>';
        }

        async function fetchAndRender() {
          const search = (searchInput.value || '').trim();
          const category = (categorySelect.value || '').trim();

          const params = new URLSearchParams();
          if (search !== '') params.set('search', search);
          if (category !== '') params.set('category', category);

          setLoading();

          const url = 'api/search_posts.php' + (params.toString() ? ('?' + params.toString()) : '');

          try {
            const res = await fetch(url, {
              method: 'GET',
              headers: { 'Accept': 'application/json' },
              credentials: 'same-origin',
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
              postsGrid.innerHTML = '<div class="card"><div class="card-inner">Erreur pendant la recherche.</div></div>';
              return;
            }

            postsGrid.innerHTML = data.html || '';
          } catch (e) {
            postsGrid.innerHTML = '<div class="card"><div class="card-inner">Erreur réseau pendant la recherche.</div></div>';
          }
        }

        // Empêche le rechargement de page.
        form.addEventListener('submit', (e) => {
          e.preventDefault();
          fetchAndRender();
        });

        // Recherche au fil de la saisie (debounce).
        searchInput.addEventListener('input', () => {
          if (debounceTimer) clearTimeout(debounceTimer);
          debounceTimer = setTimeout(fetchAndRender, 350);
        });

        categorySelect.addEventListener('change', () => {
          if (debounceTimer) clearTimeout(debounceTimer);
          fetchAndRender();
        });
      })();
    </script>
  </body>
</html>
