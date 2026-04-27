<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../controller/post.controller.php';

require_once __DIR__ . '/includes/layout.php';

$message = '';
$error = '';
if (isset($_GET['created'])) {
    $message = 'Article publié.';
} elseif (isset($_GET['updated'])) {
    $message = 'Article mis à jour.';
} elseif (isset($_GET['deleted'])) {
    $message = 'Article supprimé.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    if ($id > 0) {
        try {
            $postC = new PostC();
            $postC->deletePost($id);
            header('Location: posts.php?deleted=1');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$postC = new PostC();

$searchQuery = trim((string) ($_GET['search'] ?? ''));
$filterCategory = trim((string) ($_GET['category'] ?? ''));

if ($searchQuery !== '') {
    $rows = $postC->searchPosts($searchQuery);
} elseif ($filterCategory !== '') {
    $rows = $postC->filterPostsByCategory($filterCategory);
} else {
    $liste = $postC->listPost();
    $rows = $liste->fetchAll(PDO::FETCH_ASSOC);
}

$categories = $postC->getCategories();

$mostCommented = $postC->getMostCommentedPosts(5);

admin_layout_start('Gestion des articles', 'posts');
?>
        <h1>Mes articles</h1>
        <p class="muted" style="margin-top:-8px;margin-bottom:16px;">Créez, modifiez ou supprimez le contenu de vos posts. Les visiteurs voient le résultat sur le blog public.</p>

        <form method="get" action="" style="margin-bottom:16px;">
            <input type="text" name="search" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher par titre, contenu ou catégorie..." style="width:300px;padding:8px;border:1px solid #cbd5e1;border-radius:8px;">
            <select name="category" onchange="this.form.submit()" style="margin-left:8px;padding:8px;border:1px solid #cbd5e1;border-radius:8px;">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat) { ?>
                    <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="btn" style="margin-left:8px;">Filtrer</button>
            <?php if ($searchQuery !== '' || $filterCategory !== '') { ?>
                <a href="posts.php" class="btn btn-ghost" style="margin-left:8px;text-decoration:none;">Effacer</a>
            <?php } ?>
        </form>

        <?php if ($message !== '') { ?>
            <div class="ok"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>
        <?php if ($error !== '') { ?>
            <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php } ?>

        <div class="card">
            <?php if (count($rows) === 0) { ?>
                <p><?php if ($searchQuery !== '') { ?>Aucun article trouvé pour "<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>".<?php } else { ?>Aucun article pour le moment.<?php } ?> <a href="add_post.php">Créer un article</a></p>
            <?php } else { ?>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) {
                            $id = (int) $row['id'];
                            $titre = (string) ($row['titre'] ?? '');
                            $cat = (string) ($row['categorie'] ?? '');
                            $date = (string) ($row['datePublication'] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <a href="replies.php?post_id=<?= $id ?>" style="color:#2563eb;text-decoration:none;cursor:pointer;">
                                        <strong><?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?></strong>
                                    </a>
                                </td>
                                <td><?= $cat !== '' ? htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                <td><?= $date !== '' ? htmlspecialchars($date, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="edit_post.php?id=<?= $id ?>" class="btn" style="margin-top:0;padding:6px 12px;font-size:0.85rem;">Modifier le contenu</a>
                                        <a href="replies.php?post_id=<?= $id ?>" class="btn btn-ghost" style="margin-top:0;padding:6px 12px;font-size:0.85rem;text-decoration:none;display:inline-block;">Voir réponses</a>
                                        <form method="post" action="" style="display:inline;" onsubmit="return confirm('Supprimer cet article ?');">
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

        <?php if (!empty($mostCommented)) { ?>
        <div class="card" style="margin-top:20px;">
            <h2 style="margin-top:0;">Articles les plus commentés</h2>
            <canvas id="commentChart" width="400" height="200"></canvas>
        </div>
        <script>
            const ctx = document.getElementById('commentChart').getContext('2d');
            const data = {
                labels: [<?php foreach ($mostCommented as $post) { echo '"' . addslashes($post['titre']) . '", '; } ?>],
                datasets: [{
                    label: 'Nombre de commentaires',
                    data: [<?php foreach ($mostCommented as $post) { echo (int)$post['reply_count'] . ', '; } ?>],
                    backgroundColor: 'rgba(37, 99, 235, 0.5)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1
                }]
            };
            const config = {
                type: 'bar',
                data: data,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            };
            new Chart(ctx, config);
        </script>
        <?php } ?>
<?php
admin_layout_end();
