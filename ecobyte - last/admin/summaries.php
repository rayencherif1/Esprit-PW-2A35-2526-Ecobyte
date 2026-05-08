<?php

session_start();
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$isAdmin) {
    header('Location: /admin/login.php');
    exit;
}

require_once __DIR__ . '/../controller/post.controller.php';
require_once __DIR__ . '/../controller/ai_summary.php';

$postC = new PostC();
$posts = [];
$regeneratedPostId = null;

// Récupérer tous les posts
try {
    $result = $postC->listPost();
    $posts = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

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
                    $regeneratedPostId = $postId;
                    // Rafraîchir la liste
                    $result = $postC->listPost();
                    $posts = $result->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des résumés IA - Ecobyte Admin</title>
    <link rel="stylesheet" href="../view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/css/ai-summary.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        h1 {
            color: #333;
            margin-top: 0;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 15px;
        }
        
        .header a {
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }
        
        .header a:hover {
            background: #1d4ed8;
        }
        
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            color: #495057;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tr:hover {
            background: #f9f9f9;
        }
        
        .post-title {
            font-weight: 600;
            color: #2563eb;
        }
        
        .summary-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-generated {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #997404;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .summary-preview {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 13px;
            line-height: 1.5;
            max-height: 80px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            
            table th, table td {
                padding: 10px 5px;
            }
            
            .actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 Gestion des résumés IA</h1>
            <a href="index.php">← Retour au dashboard</a>
        </div>
        
        <?php if ($regeneratedPostId) { ?>
            <div class="success-message">
                ✓ Résumé régénéré avec succès pour le post #<?= $regeneratedPostId ?>
            </div>
        <?php } ?>
        
        <?php if (isset($error)) { ?>
            <div class="error-message">
                ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>
        
        <?php if (empty($posts)) { ?>
            <div class="empty-state">
                <p>Aucun post trouvé.</p>
            </div>
        <?php } else { ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%;">Titre du post</th>
                        <th style="width: 20%;">Catégorie</th>
                        <th style="width: 25%;">Résumé</th>
                        <th style="width: 15%;">Statut</th>
                        <th style="width: 20%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post) { ?>
                        <tr>
                            <td>
                                <div class="post-title"><?= htmlspecialchars(substr($post['titre'], 0, 50), ENT_QUOTES, 'UTF-8') ?></div>
                                <small style="color: #999;">Post #<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><?= htmlspecialchars($post['categorie'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if (!empty($post['summary'])) { ?>
                                    <div class="summary-preview"><?= htmlspecialchars(substr($post['summary'], 0, 100), ENT_QUOTES, 'UTF-8') ?>...</div>
                                <?php } else { ?>
                                    <small style="color: #999;">Pas de résumé</small>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="summary-status <?= !empty($post['summary']) ? 'status-generated' : 'status-pending' ?>">
                                    <?= !empty($post['summary']) ? '✓ Généré' : '⏳ En attente' ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <?= !empty($post['summary']) ? '🔄 Régénérer' : '⚙️ Générer' ?>
                                        </button>
                                    </form>
                                    <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-secondary btn-sm">Éditer</a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</body>
</html>
