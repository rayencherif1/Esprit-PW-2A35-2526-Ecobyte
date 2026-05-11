<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../../controller/post.controller.php';

$message = '';
$error = '';
if (isset($_GET['created'])) {
    $message = 'Article publié avec succès.';
} elseif (isset($_GET['updated'])) {
    $message = 'Article mis à jour avec succès.';
} elseif (isset($_GET['deleted'])) {
    $message = 'Article supprimé.';
}

$postC = new PostC();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];
    if ($id > 0) {
        try {
            $postC->deletePost($id);
            header('Location: posts.php?deleted=1');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Articles — EcoByte Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #f4f6f9; }
    </style>
</head>
<body class="m-0 font-sans text-base antialiased font-normal bg-gray-50 text-slate-500">
    
    <?php include 'sidebar.php'; ?>

    <!-- Header background -->
    <div style="position:fixed; top:0; left:256px; right:0; height:300px; background:#5e72e4; z-index:0;"></div>

    <main class="relative z-1 ml-64 min-h-screen">
        <div class="w-full px-6 py-6 mx-auto">
            
            <!-- Header Title -->
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                            <h1 class="mb-1 font-bold text-white text-3xl">Gestion du Blog</h1>
                            <p class="text-white opacity-80 text-sm">Créez et gérez vos articles et le contenu communautaire.</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <a href="add_post.php" class="inline-block px-6 py-3 font-bold text-center text-white uppercase align-middle transition-all bg-transparent border border-white rounded-lg shadow-none cursor-pointer leading-pro text-xs ease-soft-in hover:scale-102 active:opacity-85 hover:bg-white hover:text-blue-600">
                                <i class="fas fa-plus mr-2"></i> Nouvel Article
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 shadow-xl rounded-2xl">
                        <div class="p-6 pb-0 mb-0 border-b-0 rounded-t-2xl">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <h6 class="font-bold text-slate-700">Liste des Articles</h6>
                                
                                <!-- Search & Filter -->
                                <form method="get" class="flex flex-wrap gap-2">
                                    <div class="relative flex flex-wrap items-stretch w-full transition-all rounded-lg ease-soft">
                                        <span class="text-sm ease-soft leading-5.6 absolute z-50 -ml-px flex h-full items-center whitespace-nowrap rounded-lg rounded-tr-none rounded-br-none border border-r-0 border-transparent bg-transparent py-2 px-2.5 text-center font-normal text-slate-500 transition-all">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Rechercher..." class="pl-8.75 text-sm focus:shadow-soft-primary-outline ease-soft w-full leading-5.6 relative -ml-px block min-w-0 flex-auto rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 pr-3 text-gray-700 transition-all placeholder:text-gray-500 focus:border-fuchsia-300 focus:outline-none focus:transition-shadow">
                                    </div>
                                    <select name="category" onchange="this.form.submit()" class="text-sm focus:shadow-soft-primary-outline ease-soft leading-5.6 block w-full rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 text-gray-700 transition-all focus:border-fuchsia-300 focus:outline-none focus:transition-shadow">
                                        <option value="">Toutes catégories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>

                            <?php if ($message): ?>
                                <div class="mt-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg border border-green-200"><?= htmlspecialchars($message) ?></div>
                            <?php endif; ?>
                            <?php if ($error): ?>
                                <div class="mt-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg border border-red-200"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="flex-auto px-0 pt-0 pb-2">
                            <div class="p-0 overflow-x-auto">
                                <table class="items-center w-full mb-0 align-top border-gray-200 text-slate-500">
                                    <thead class="align-bottom">
                                        <tr>
                                            <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Titre</th>
                                            <th class="px-6 py-3 pl-2 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Catégorie</th>
                                            <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Date</th>
                                            <th class="px-6 py-3 font-semibold capitalize align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $row): ?>
                                            <tr>
                                                <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                    <div class="flex px-4 py-1">
                                                        <div class="flex flex-col justify-center">
                                                            <h6 class="mb-0 text-sm leading-normal font-bold"><?= htmlspecialchars((string)($row['titre'] ?? '')) ?></h6>
                                                            <p class="mb-0 text-xs leading-tight text-slate-400">ID: #<?= $row['id'] ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                    <span class="text-xs font-semibold leading-tight text-slate-400"><?= htmlspecialchars((string)($row['categorie'] ?? 'Divers')) ?></span>
                                                </td>
                                                <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                    <span class="text-xs font-semibold leading-tight text-slate-400"><?= htmlspecialchars((string)($row['datePublication'] ?? '—')) ?></span>
                                                </td>
                                                <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                    <div class="flex gap-2">
                                                        <a href="edit_post.php?id=<?= $row['id'] ?>" class="text-xs font-semibold leading-tight text-slate-400 hover:text-blue-600"> <i class="fas fa-edit mr-1"></i> Modifier </a>
                                                        <a href="replies.php?post_id=<?= $row['id'] ?>" class="text-xs font-semibold leading-tight text-slate-400 hover:text-blue-600"> <i class="fas fa-comments mr-1"></i> Réponses </a>
                                                        <form method="POST" class="inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                                            <button type="submit" class="text-xs font-semibold leading-tight text-red-500 hover:text-red-700"> <i class="fas fa-trash mr-1"></i> </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <?php if (!empty($mostCommented)): ?>
                <div class="flex flex-wrap -mx-3 mt-6">
                    <div class="w-full max-w-full px-3 lg:w-7/12">
                        <div class="relative flex flex-col min-w-0 break-words bg-white border-0 shadow-xl rounded-2xl">
                            <div class="p-6 pb-0 mb-0 border-b-0 rounded-t-2xl">
                                <h6 class="font-bold text-slate-700">Engagement Communautaire</h6>
                                <p class="text-sm leading-normal">Top 5 des articles les plus commentés</p>
                            </div>
                            <div class="flex-auto p-4">
                                <canvas id="commentChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    const ctx = document.getElementById('commentChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: [<?php foreach ($mostCommented as $post) { echo '"' . addslashes((string)$post['titre']) . '", '; } ?>],
                            datasets: [{
                                label: 'Commentaires',
                                data: [<?php foreach ($mostCommented as $post) { echo (int)$post['reply_count'] . ', '; } ?>],
                                backgroundColor: '#5e72e4',
                                borderRadius: 5,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, grid: { borderDash: [2] } } }
                        }
                    });
                </script>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>
