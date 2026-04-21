<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../model/post.php';
require_once __DIR__ . '/../controller/post.controller.php';
require_once __DIR__ . '/includes/layout.php';

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
        $post = new Post(null, $titre, $contenu, $datePublication, $categorie, $image === '' ? null : $image);
        try {
            $postC = new PostC();
            $postC->addPost($post);
            header('Location: posts.php?created=1');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$titre = (string) ($_POST['titre'] ?? '');
$categorie = (string) ($_POST['categorie'] ?? '');
$datePublication = (string) ($_POST['datePublication'] ?? date('Y-m-d'));
$image = (string) ($_POST['image'] ?? '');
$contenu = (string) ($_POST['contenu'] ?? '');

admin_layout_start('Nouvel article', 'add');
?>
        <h1>Nouvel article</h1>
        <p class="muted" style="margin-top:-8px;margin-bottom:16px;">Rédigez le titre et le contenu. Après publication, l’article apparaît sur le blog public.</p>

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

                <button type="submit" class="btn">Publier</button>
                <a href="posts.php" class="btn btn-ghost" style="margin-left:8px;text-decoration:none;display:inline-block;">Annuler</a>
            </form>
        </div>
<?php
admin_layout_end();
