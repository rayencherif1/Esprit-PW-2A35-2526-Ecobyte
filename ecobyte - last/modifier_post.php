<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/model/post.php';
require_once __DIR__ . '/controller/post.controller.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die('Accès refusé.');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: blog.php');
    exit;
}

$postC = new PostC();
$row = $postC->getPostById($id);
if ($row === null) {
    header('Location: blog.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim((string) ($_POST['titre'] ?? ''));
    $contenu = trim((string) ($_POST['contenu'] ?? ''));
    $datePublication = trim((string) ($_POST['datePublication'] ?? ''));
    $categorie = trim((string) ($_POST['categorie'] ?? ''));
    $image = trim((string) ($_POST['image'] ?? ''));

    if ($titre === '') {
        $error = 'Le titre est obligatoire.';
    } elseif ($categorie === '') {
        $error = 'La catégorie est obligatoire.';
    } elseif ($contenu === '') {
        $error = 'Le contenu est obligatoire.';
    } else {
        if ($datePublication === '') {
            $datePublication = date('Y-m-d');
        }
        $post = new Post($id, $titre, $contenu, $datePublication, $categorie, $image === '' ? null : $image);
        try {
            $postC->updatePost($post, $id);
            $message = 'Article mis à jour.';
            $row = $postC->getPostById($id) ?? $row;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$titre = trim((string) ($_POST['titre'] ?? $row['titre'] ?? ''));
$contenu = (string) ($_POST['contenu'] ?? $row['contenu'] ?? '');
$categorie = trim((string) ($_POST['categorie'] ?? $row['categorie'] ?? ''));
$datePublication = trim((string) ($_POST['datePublication'] ?? $row['datePublication'] ?? date('Y-m-d')));
$image = trim((string) ($_POST['image'] ?? $row['image'] ?? ''));

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier l’article — Ecobyte</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 720px; margin: 0 auto; }
        h1 { font-size: 1.35rem; margin: 0 0 8px; }
        .lead { color: #64748b; font-size: 0.95rem; margin: 0 0 20px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin: 14px 0 6px; }
        input[type="text"], input[type="date"], textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;
        }
        textarea { min-height: 280px; resize: vertical; font-family: inherit; line-height: 1.5; }
        .btn {
            margin-top: 20px; padding: 10px 20px; border: none; border-radius: 8px;
            background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-ghost { background: #e2e8f0; color: #0f172a; margin-left: 8px; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; }
        .btn-ghost:hover { background: #cbd5e1; }
        .ok { background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .err { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
        .top { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .top a { color: #2563eb; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <a href="blog.php">← Retour au blog</a>
            <a href="view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/index.html">Accueil du site</a>
        </div>
        <h1>Modifier l’article</h1>
        <p class="lead">Modifiez le titre, la catégorie, la date, l’image ou le contenu.</p>

        <div class="card">
            <?php if ($message !== '') { ?>
                <div class="ok"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <?php if ($error !== '') { ?>
                <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <form method="post" action="">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">

                <label for="categorie">Catégorie</label>
                <input type="text" id="categorie" name="categorie" value="<?= htmlspecialchars($categorie, ENT_QUOTES, 'UTF-8') ?>">

                <label for="datePublication">Date</label>
                <input type="date" id="datePublication" name="datePublication" value="<?= htmlspecialchars($datePublication, ENT_QUOTES, 'UTF-8') ?>">

                <label for="image">Image (URL ou chemin)</label>
                <input type="text" id="image" name="image" placeholder="optionnel" value="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>">

                <label for="contenu">Contenu</label>
                <textarea id="contenu" name="contenu" placeholder="Écrivez votre article…"><?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8') ?></textarea>

                <button type="submit" class="btn">Enregistrer</button>
                <a href="blog.php" class="btn-ghost">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>

