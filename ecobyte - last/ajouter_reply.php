<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/model/reply.php';
require_once __DIR__ . '/controller/reply.controller.php';
require_once __DIR__ . '/controller/post.controller.php';

$postId = (int) ($_GET['post_id'] ?? 0);
if ($postId <= 0) {
    header('Location: blog.php');
    exit;
}

$postC = new PostC();
$post = $postC->getPostById($postId);
if ($post === null) {
    header('Location: blog.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = trim((string) ($_POST['contenu'] ?? ''));
    
    // Gestion de l'upload d'image
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
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

    if ($contenu === '') {
        $error = 'Le contenu est obligatoire.';
    } else {
        $reply = new Reply(null, $contenu, $imagePath, null, $postId);
        try {
            $replyC = new ReplyC();
            $replyC->addReply($reply);
            header('Location: blog.php#post-' . $postId);
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$contenu = (string) ($_POST['contenu'] ?? '');
$postTitre = (string) ($post['titre'] ?? '');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Répondre — Ecobyte</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 720px; margin: 0 auto; }
        h1 { font-size: 1.35rem; margin: 0 0 8px; }
        .lead { color: #64748b; font-size: 0.95rem; margin: 0 0 20px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin: 14px 0 6px; }
        input[type="text"], input[type="datetime-local"], textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;
        }
        textarea { min-height: 220px; resize: vertical; font-family: inherit; line-height: 1.5; }
        .btn {
            margin-top: 20px; padding: 10px 20px; border: none; border-radius: 8px;
            background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-ghost { background: #e2e8f0; color: #0f172a; margin-left: 8px; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; }
        .btn-ghost:hover { background: #cbd5e1; }
        .err { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
        .top { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .top a { color: #2563eb; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <a href="blog.php#post-<?= $postId ?>">← Retour au post</a>
            <a href="view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/index.html">Accueil du site</a>
        </div>
        <h1>Répondre</h1>
        <p class="lead">Votre réponse sera affichée sous l’article: <strong><?= htmlspecialchars($postTitre, ENT_QUOTES, 'UTF-8') ?></strong></p>

        <div class="card">
            <?php if ($error !== '') { ?>
                <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <form method="post" action="" enctype="multipart/form-data">
                <label for="contenu">Commentaire / Réponse *</label>
                <textarea id="contenu" name="contenu" placeholder="Écrivez votre réponse…"><?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8') ?></textarea>

                <label for="image">Image</label>
                <label for="image" class="btn" style="cursor: pointer; display: inline-block;">Choisir une image</label>
                <input type="file" id="image" name="image" accept="image/*" style="display: none;">

                <button type="submit" class="btn">Publier</button>
                <a href="blog.php#post-<?= $postId ?>" class="btn-ghost">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>

