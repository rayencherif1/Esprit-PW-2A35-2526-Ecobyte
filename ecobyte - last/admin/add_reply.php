<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../model/reply.php';
require_once __DIR__ . '/../controller/reply.controller.php';
require_once __DIR__ . '/../controller/post.controller.php';
require_once __DIR__ . '/includes/layout.php';

$error = '';

$postC = new PostC();
$liste = $postC->listPost();
$posts = $liste->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $contenu = trim((string) ($_POST['contenu'] ?? ''));
    
    // Gestion de l'upload d'image
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../view/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'view/uploads/' . $fileName;
        }
    }

    if ($postId <= 0) {
        $error = 'Veuillez choisir un article.';
    } elseif ($contenu === '') {
        $error = 'Le contenu est obligatoire.';
    } else {
        $reply = new Reply(null, $contenu, $imagePath, null, $postId, null, 'approuve', null, 0, null);
        try {
            $replyC = new ReplyC();
            $replyC->addReply($reply);
            header('Location: replies.php?created=1');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$postId = (int) ($_POST['post_id'] ?? 0);
$contenu = (string) ($_POST['contenu'] ?? '');

admin_layout_start('Nouvelle réponse', 'replies');
?>
        <h1>Nouvelle réponse</h1>
        <p class="muted" style="margin-top:-8px;margin-bottom:16px;">Ajoutez une réponse et associez-la à un article.</p>

        <div class="card">
            <?php if ($error !== '') { ?>
                <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <form method="post" action="" enctype="multipart/form-data">
                <label for="post_id">Article *</label>
                <select id="post_id" name="post_id" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:1rem;">
                    <option value="0">— Choisir un article —</option>
                    <?php foreach ($posts as $p) {
                        $pid = (int) ($p['id'] ?? 0);
                        $pt = (string) ($p['titre'] ?? '');
                        ?>
                        <option value="<?= $pid ?>" <?= $pid === $postId ? 'selected' : '' ?>>
                            #<?= $pid ?> — <?= htmlspecialchars($pt, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="contenu">Commentaire / Réponse *</label>
                <textarea id="contenu" name="contenu" placeholder="Écrivez la réponse ici…" style="min-height:220px;"><?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8') ?></textarea>

                <label for="image">Image</label>
                <label for="image" class="btn" style="cursor: pointer; display: inline-block;">Choisir une image</label>
                <input type="file" id="image" name="image" accept="image/*" style="display: none;">

                <button type="submit" class="btn">Publier</button>
                <a href="replies.php" class="btn btn-ghost" style="margin-left:8px;text-decoration:none;display:inline-block;">Annuler</a>
            </form>
        </div>
<?php
admin_layout_end();

