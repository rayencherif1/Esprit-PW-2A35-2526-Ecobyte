<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../../controller/post.controller.php';
require_once __DIR__ . '/../../controller/ai_summary.php';
require_once __DIR__ . '/../../model/post.php';

$postC = new PostC();
$message = '';
$error = '';
$regeneratedPostId = null;

// Régénérer le résumé si demandé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = (int) $_POST['post_id'];
    if ($postId > 0) {
        try {
            $postData = $postC->getPostById($postId);
            if ($postData) {
                $newSummary = generateSummary($postData['contenu']);
                if (!empty($newSummary)) {
                    $post = new Post(
                        $postData['id'],
                        $postData['titre'],
                        $postData['contenu'],
                        $postData['datePublication'],
                        $postData['categorie'],
                        $postData['image'],
                        $postData['nutrition'] ?? null,
                        $newSummary
                    );
                    $postC->updatePost($post, $postId);
                    $message = "Résumé IA mis à jour pour l'article #$postId";
                    $regeneratedPostId = $postId;
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$posts = [];
try {
    $result = $postC->listPost();
    $posts = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IA Assistant — EcoByte Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="flex-none w-full max-w-full px-3 text-white">
                    <h1 class="mb-1 font-bold text-3xl">Assistant IA</h1>
                    <p class="opacity-80 text-sm">Générez des résumés automatiques pour vos articles de blog.</p>
                </div>
            </div>

            <div class="flex flex-wrap -mx-3">
                <div class="flex-none w-full max-w-full px-3">
                    <div class="relative flex flex-col min-w-0 mb-6 break-words bg-white border-0 shadow-xl rounded-2xl">
                        <div class="p-6 pb-0 mb-0 border-b-0 rounded-t-2xl">
                            <h6 class="font-bold text-slate-700 text-lg">Statut des Résumés</h6>
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
                                            <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Article</th>
                                            <th class="px-6 py-3 font-bold text-left uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Aperçu du Résumé</th>
                                            <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Statut</th>
                                            <th class="px-6 py-3 font-bold text-center uppercase align-middle bg-transparent border-b border-gray-200 shadow-none text-xxs border-b-solid tracking-none whitespace-nowrap text-slate-400 opacity-70">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td class="p-2 align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                    <div class="flex px-4 py-1">
                                                        <div class="flex flex-col justify-center">
                                                            <h6 class="mb-0 text-sm leading-normal font-bold"><?= htmlspecialchars(substr($post['titre'], 0, 40)) ?>...</h6>
                                                            <p class="mb-0 text-xs leading-tight text-slate-400">ID: #<?= $post['id'] ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-2 align-middle bg-transparent border-b shadow-transparent">
                                                    <div class="max-w-xs text-xs text-slate-400 truncate">
                                                        <?= !empty($post['summary']) ? htmlspecialchars(substr($post['summary'], 0, 80)) . '...' : '<i>Aucun résumé généré</i>' ?>
                                                    </div>
                                                </td>
                                                <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                    <?php if (!empty($post['summary'])): ?>
                                                        <span class="bg-gradient-to-tl from-green-600 to-lime-400 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Prêt</span>
                                                    <?php else: ?>
                                                        <span class="bg-gradient-to-tl from-slate-600 to-slate-300 px-2.5 text-xs rounded-1.8 py-1.4 inline-block whitespace-nowrap text-center align-baseline font-bold uppercase leading-none text-white">Manquant</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="p-2 text-center align-middle bg-transparent border-b whitespace-nowrap shadow-transparent">
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                        <button type="submit" class="text-xs font-semibold leading-tight text-blue-500 hover:text-blue-700 bg-blue-50 px-3 py-1 rounded-lg">
                                                            <i class="fas fa-robot mr-1"></i> Générer
                                                        </button>
                                                    </form>
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
        </div>
    </main>
</body>
</html>
