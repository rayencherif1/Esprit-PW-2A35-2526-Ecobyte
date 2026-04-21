<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../model/reply.php';
require_once __DIR__ . '/../controller/reply.controller.php';
require_once __DIR__ . '/../controller/post.controller.php';
require_once __DIR__ . '/includes/layout.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: replies.php');
    exit;
}

$replyC = new ReplyC();
$row = $replyC->getReplyById($id);
if ($row === null) {
    header('Location: replies.php');
    exit;
}

$error = '';

$postC = new PostC();
$liste = $postC->listPost();
$posts = $liste->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $contenu = trim((string) ($_POST['contenu'] ?? ''));

    if ($postId <= 0) {
        $error = 'Veuillez choisir un article.';
    } elseif ($contenu === '') {
        $error = 'Le contenu est obligatoire.';
    } else {
        $reply = new Reply($id, $contenu, null, $postId);
        try {
            // updateReply = admin-only
            $replyC->updateReply($reply, $id);
            header('Location: replies.php?updated=1');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$postId = (int) ($_POST['post_id'] ?? $row['post_id'] ?? 0);
$contenu = (string) ($_POST['contenu'] ?? $row['contenu'] ?? '');

admin_layout_start('Modifier la réponse', 'replies');
?>
        <h1>Modifier la réponse</h1>
        <p class="muted" style="margin-top:-8px;margin-bottom:16px;">Modifiez la réponse et/ou changez l’article associé.</p>

        <div class="card">
            <?php if ($error !== '') { ?>
                <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <form method="post" action="">
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

                <button type="submit" class="btn">Enregistrer</button>
                <a href="replies.php" class="btn btn-ghost" style="margin-left:8px;text-decoration:none;display:inline-block;">Annuler</a>
            </form>
        </div>
<?php
admin_layout_end();

