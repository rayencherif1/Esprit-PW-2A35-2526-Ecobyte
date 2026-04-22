<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../controller/reply.controller.php';
require_once __DIR__ . '/includes/layout.php';

$message = '';
$error = '';
if (isset($_GET['deleted'])) {
    $message = 'Réponse supprimée.';
} elseif (isset($_GET['created'])) {
    $message = 'Réponse publiée.';
} elseif (isset($_GET['updated'])) {
    $message = 'Réponse mise à jour.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    if ($id > 0) {
        try {
            $replyC = new ReplyC();
            $replyC->deleteReply($id);
            header('Location: replies.php?deleted=1');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$replyC = new ReplyC();
$postIdFilter = (int) ($_GET['post_id'] ?? 0);
$rows = $postIdFilter > 0
    ? $replyC->listRepliesWithPostByPostId($postIdFilter)
    : $replyC->listRepliesWithPost();

admin_layout_start('Gestion des réponses', 'replies');
?>
        <h1>Réponses</h1>
        <p class="muted" style="margin-top:-8px;margin-bottom:16px;">Gérez les commentaires/réponses des visiteurs. Une réponse est liée à un article.</p>
        <p style="margin-top:-8px;margin-bottom:16px;">
            <a class="btn" href="add_reply.php" style="margin-top:0;">Ajouter une réponse</a>
            <?php if ($postIdFilter > 0) { ?>
                <a class="btn btn-ghost" href="replies.php" style="margin-top:0;margin-left:8px;text-decoration:none;display:inline-block;">Tout afficher</a>
            <?php } ?>
        </p>

        <?php if ($message !== '') { ?>
            <div class="ok"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>
        <?php if ($error !== '') { ?>
            <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>

        <div class="card">
            <?php if (count($rows) === 0) { ?>
                <p>Aucune réponse pour le moment.</p>
            <?php } else { ?>
                <table>
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Réponse</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) {
                            $id = (int) ($row['id'] ?? 0);
                            $postId = (int) ($row['post_id'] ?? 0);
                            $postTitre = (string) ($row['post_titre'] ?? '');
                            $contenu = (string) ($row['contenu'] ?? '');
                            $date = (string) ($row['datePublication'] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <a href="replies.php?post_id=<?= $postId ?>" style="color:#2563eb;text-decoration:none;cursor:pointer;">
                                        <strong><?= htmlspecialchars($postTitre, ENT_QUOTES, 'UTF-8') ?></strong>
                                    </a>
                                </td>
                                <td><?= nl2br(htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8')) ?></td>
                                <td><?= $date !== '' ? htmlspecialchars($date, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="edit_reply.php?id=<?= $id ?>" class="btn" style="margin-top:0;padding:6px 12px;font-size:0.85rem;">Modifier</a>
                                        <form method="post" action="" style="display:inline;" onsubmit="return confirm('Supprimer cette réponse ?');">
                                            <input type="hidden" name="delete_id" value="<?= $id ?>">
                                            <button type="submit" class="btn btn-danger" style="margin-top:0;padding:6px 12px;font-size:0.85rem;">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
<?php
admin_layout_end();

