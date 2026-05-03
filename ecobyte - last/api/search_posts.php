<?php
declare(strict_types=1);

require_once __DIR__ . '/../controller/post.controller.php';
require_once __DIR__ . '/../controller/reply.controller.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

try {
    $postC = new PostC();
    $replyC = new ReplyC();

    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

    $searchQuery = trim((string) ($_GET['search'] ?? ''));
    $filterCategory = trim((string) ($_GET['category'] ?? ''));

    if ($searchQuery !== '') {
        $posts = $postC->searchPosts($searchQuery);
    } elseif ($filterCategory !== '') {
        $posts = $postC->filterPostsByCategory($filterCategory);
    } else {
        $posts = $postC->listPost()->fetchAll(PDO::FETCH_ASSOC);
    }

    $html = '';
    $hasAny = false;

    foreach ($posts as $row) {
        $hasAny = true;

        $id = (int) ($row['id'] ?? 0);
        $titre = (string) ($row['titre'] ?? '');
        $contenu = (string) ($row['contenu'] ?? '');
        $date = (string) ($row['datePublication'] ?? '');
        $categorie = (string) ($row['categorie'] ?? '');
        $image = (string) ($row['image'] ?? '');
        $nutritionRaw = $row['nutrition'] ?? '';

        $nutritionData = null;
        if (is_string($nutritionRaw) && $nutritionRaw !== '') {
            $decodedNutrition = json_decode($nutritionRaw, true);
            if (is_array($decodedNutrition) && isset($decodedNutrition['success']) && $decodedNutrition['success']) {
                $nutritionData = $decodedNutrition;
            }
        }

        $replies = $id > 0 ? $replyC->listRepliesByPostId($id) : [];

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

        ob_start();
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
                            <textarea name="contenu" placeholder="Écrivez un commentaire…"></textarea>
                            <label style="display:block;margin-top:8px;font-size:12.5px;font-weight:600;">Image (optionnel)</label>
                            <label for="image-<?= $id ?>" class="btn btn-sm" style="cursor: pointer; display: inline-block; margin-top:4px;">Choisir une image</label>
                            <input type="file" id="image-<?= $id ?>" name="image" accept="image/*" style="display: none;">
                            <div class="reply-form-actions">
                                <button type="submit" class="btn btn-sm">Publier</button>
                            </div>
                        </form>

                        <?php if (count($topReplies) === 0) { ?>
                            <div class="reply">
                                <div class="reply-content">Aucune réponse pour le moment.</div>
                            </div>
                        <?php } else { ?>
                            <?php foreach ($topReplies as $reply) { ?>
                                <?php
                                $rid = (int) ($reply['id'] ?? 0);
                                $rcontenu = (string) ($reply['contenu'] ?? '');
                                $rimage = (string) ($reply['image'] ?? '');
                                $rdate = (string) ($reply['datePublication'] ?? '');
                                $rlikes = (int) ($reply['likes'] ?? 0);
                                $userLiked = $rid > 0 && $replyC->userHasLiked($rid);
                                $childReplies = $nestedReplies[$rid] ?? [];
                                ?>
                                <div class="reply"<?= $rid > 0 ? ' id="reply-' . $rid . '"' : '' ?>>
                                    <div class="reply-meta">
                                        <?php if ($rdate !== '') { ?><span><?= htmlspecialchars($rdate, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                                        <?php if ($rid > 0) { ?>
                                            <span class="like-count"><?= $rlikes ?> j'aime</span>
                                        <?php } ?>
                                    </div>
                                    <div class="reply-content" id="reply-content-<?= $rid ?>">
                                        <?= nl2br(htmlspecialchars($rcontenu, ENT_QUOTES, 'UTF-8')) ?>
                                    </div>
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
                                    <?php } ?>
                                </div>

                                <?php foreach ($childReplies as $childReply) { ?>
                                    <?php
                                    $childId = (int) ($childReply['id'] ?? 0);
                                    $childContenu = (string) ($childReply['contenu'] ?? '');
                                    $childImage = (string) ($childReply['image'] ?? '');
                                    $childDate = (string) ($childReply['datePublication'] ?? '');
                                    $childLikes = (int) ($childReply['likes'] ?? 0);
                                    $childUserLiked = $childId > 0 && $replyC->userHasLiked($childId);
                                    ?>
                                    <div class="reply reply-nested"<?= $childId > 0 ? ' id="reply-' . $childId . '"' : '' ?>>
                                        <div class="reply-meta">
                                            <span>↳ Réponse</span>
                                            <?php if ($childDate !== '') { ?><span><?= htmlspecialchars($childDate, ENT_QUOTES, 'UTF-8') ?></span><?php } ?>
                                            <?php if ($childId > 0) { ?>
                                                <span class="like-count"><?= $childLikes ?> j'aime</span>
                                            <?php } ?>
                                        </div>
                                        <div class="reply-content" id="reply-content-<?= $childId ?>">
                                            <?= nl2br(htmlspecialchars($childContenu, ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
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
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </article>
        <?php
        $html .= ob_get_clean();
    }

    if (!$hasAny) {
        $html = '<div class="card"><div class="card-inner">Aucun post pour le moment.</div></div>';
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}

