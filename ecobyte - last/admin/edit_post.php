<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/post.php';
require_once __DIR__ . '/../controller/post.controller.php';
require_once __DIR__ . '/includes/layout.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: posts.php');
    exit;
}

$postC = new PostC();
$row = $postC->getPostById($id);
if ($row === null) {
    header('Location: posts.php');
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
            header('Location: posts.php?updated=1');
            exit;
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

admin_layout_start('Modifier l’article', 'edit');
?>
        <h1>Modifier l’article</h1>
        <p class="muted" style="margin-top:-8px;margin-bottom:16px;">Changez le titre, le texte, la catégorie, la date ou l’image. Enregistrez pour mettre à jour le blog.</p>

        <div class="card">
            <?php if ($error !== '') { ?>
                <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <form method="post" action="">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">

                <label for="categorie">Catégorie</label>
                <input type="text" id="categorie" name="categorie" value="<?= htmlspecialchars($categorie, ENT_QUOTES, 'UTF-8') ?>">

                <label for="datePublication">Date de publication</label>
                <input type="date" id="datePublication" name="datePublication" value="<?= htmlspecialchars($datePublication, ENT_QUOTES, 'UTF-8') ?>">

                <label for="image">Image (URL ou chemin)</label>
                <input type="text" id="image" name="image" placeholder="optionnel" value="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>">

                <label for="contenu">Contenu de l’article</label>
                <textarea id="contenu" name="contenu" placeholder="Écrivez le texte complet ici…"><?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8') ?></textarea>

                <button type="submit" class="btn">Enregistrer les modifications</button>
                <a href="posts.php" class="btn btn-ghost" style="margin-left:8px;text-decoration:none;display:inline-block;">Annuler</a>
            </form>
        </div>
<?php
admin_layout_end();
