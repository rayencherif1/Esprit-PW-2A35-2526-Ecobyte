<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/model/post.php';
require_once __DIR__ . '/controller/post.controller.php';

$message = '';
$error = '';
$nutritionResult = null;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim((string) ($_POST['titre'] ?? ''));
    $contenu = trim((string) ($_POST['contenu'] ?? ''));
    $datePublication = trim((string) ($_POST['datePublication'] ?? ''));
    $categorie = trim((string) ($_POST['categorie'] ?? ''));
    $submittedNutritionJson = trim((string) ($_POST['nutrition_json'] ?? ''));
    if ($submittedNutritionJson !== '') {
        $submittedNutrition = json_decode($submittedNutritionJson, true);
        if (is_array($submittedNutrition)) {
            $nutritionResult = $submittedNutrition;
            $_SESSION['nutritionResult'] = $nutritionResult;
        }
    }
    // Gestion de l'upload d'image
    $imagePath = null;
    if (!isset($nutritionResult)) {
        $nutritionResult = null;
    }
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/view/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'view/uploads/' . $fileName;
            // Appel API nutrition
            $apiUrl = __DIR__ . '/api/nutrition_analyzer.php';
            $cfile = new CURLFile($targetFile);
            $postFields = ['image' => $cfile];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $apiResponse = curl_exec($ch);
            curl_close($ch);
            if ($apiResponse) {
                $nutritionResult = json_decode($apiResponse, true);
                $_SESSION['nutritionResult'] = $nutritionResult;
            }
        }
    }

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
        $nutritionJson = null;
        if (is_array($nutritionResult)) {
            $nutritionJson = json_encode($nutritionResult, JSON_UNESCAPED_UNICODE);
        }

        $post = new Post(null, $titre, $contenu, $datePublication, $categorie, $imagePath, $nutritionJson);
        try {
            $postC = new PostC();
            $postC->addPost($post);
            $message = 'Votre article a bien été envoyé. Il apparaît sur le blog.';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$titre = (string) ($_POST['titre'] ?? '');
$categorie = (string) ($_POST['categorie'] ?? '');
$datePublication = (string) ($_POST['datePublication'] ?? date('Y-m-d'));
$contenu = (string) ($_POST['contenu'] ?? '');

if (isset($_POST['nutrition_json']) && $_POST['nutrition_json']) {
    $nutritionResult = json_decode($_POST['nutrition_json'], true);
    if (is_array($nutritionResult)) {
        $_SESSION['nutritionResult'] = $nutritionResult;
    }
} elseif (isset($_SESSION['nutritionResult']) && is_array($_SESSION['nutritionResult'])) {
    $nutritionResult = $_SESSION['nutritionResult'];
}
unset($_SESSION['nutritionResult']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proposer un article — Ecobyte</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 640px; margin: 0 auto; }
        h1 { font-size: 1.35rem; margin: 0 0 8px; }
        .lead { color: #64748b; font-size: 0.95rem; margin: 0 0 20px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin: 14px 0 6px; }
        input[type="text"], input[type="date"], textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;
        }
        textarea { min-height: 280px; resize: vertical; font-family: inherit; line-height: 1.5; }
        .btn {
            margin-top: 20px; padding: 10px 20px; border: none; border-radius: 8px;
            background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; font-size: 1rem;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-ghost { background: #e2e8f0; color: #0f172a; margin-left: 8px; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; }
        .btn-ghost:hover { background: #cbd5e1; }
        .ok { background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .err { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
        .top { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .top a { color: #2563eb; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <a href="blog.php">← Voir le blog</a>
            <a href="view/Front office/FoodMart-1.0.0/FoodMart-1.0.0/index.html">Accueil du site</a>
        </div>
        <h1>Proposer un article</h1>
        <p class="lead">Remplissez le formulaire ci-dessous. Vous pourrez ensuite modifier ou supprimer l’article depuis le blog.</p>

        <div class="card">
            <?php if ($message !== '') { ?>
                <div class="ok"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <?php if ($error !== '') { ?>
                <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
            <form method="post" action="" enctype="multipart/form-data">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">

                <label for="categorie">Catégorie</label>
                <input type="text" id="categorie" name="categorie" value="<?= htmlspecialchars($categorie, ENT_QUOTES, 'UTF-8') ?>">

                <label for="datePublication">Date</label>
                <input type="date" id="datePublication" name="datePublication" value="<?= htmlspecialchars($datePublication, ENT_QUOTES, 'UTF-8') ?>">

                <label for="image">Image</label>
                <label for="image" class="btn" style="cursor: pointer; display: inline-block;">Choisir une image</label>
                <input type="file" id="image" name="image" accept="image/*" style="display: none;">

                <label for="contenu">Contenu</label>
                <textarea id="contenu" name="contenu" placeholder="Écrivez votre article…"><?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8') ?></textarea>
                <input type="hidden" id="nutrition-json" name="nutrition_json" value="">

                <button type="submit" class="btn">Publier</button>
                <a href="blog.php" class="btn-ghost">Annuler</a>
            </form>
            <div id="nutrition-result-js"></div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const imageInput = document.getElementById('image');
                const resultDiv = document.getElementById('nutrition-result-js');
                const nutritionJsonInput = document.getElementById('nutrition-json');
                if (imageInput) {
                    imageInput.addEventListener('change', function(e) {
                        resultDiv.innerHTML = '';
                        nutritionJsonInput.value = '';
                        if (imageInput.files && imageInput.files[0]) {
                            const formData = new FormData();
                            formData.append('image', imageInput.files[0]);
                            fetch('api/nutrition_analyzer.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    resultDiv.innerHTML = `
                                        <div class='ok' style='margin-top:18px;'>
                                            <strong>🍎 Analyse nutritionnelle de l'image :</strong><br>
                                            Aliment détecté : <b>${data.food_name}</b><br>
                                            Calories (100g) : <b>${data.nutrition.calories}</b> kcal<br>
                                            Protéines : <b>${data.nutrition.protein}</b>g, Lipides : <b>${data.nutrition.fat}</b>g, Glucides : <b>${data.nutrition.carbs}</b>g
                                        </div>
                                    `;
                                    nutritionJsonInput.value = JSON.stringify(data);
                                } else if (data.error) {
                                    resultDiv.innerHTML = `<div class='err' style='margin-top:18px;'>❌ ${data.error}</div>`;
                                    nutritionJsonInput.value = '';
                                }
                            })
                            .catch(() => {
                                resultDiv.innerHTML = `<div class='err' style='margin-top:18px;'>Erreur lors de l'analyse nutritionnelle.</div>`;
                                nutritionJsonInput.value = '';
                            });
                        }
                    });
                }
            });
            </script>
            <?php if ($nutritionResult && isset($nutritionResult['success']) && $nutritionResult['success']) { ?>
                <div class="ok" style="margin-top:18px;">
                    <strong>🍎 Analyse nutritionnelle de l'image :</strong><br>
                    <span>Aliment détecté : <b><?= htmlspecialchars($nutritionResult['food_name'], ENT_QUOTES, 'UTF-8') ?></b></span><br>
                    <span>Calories (100g) : <b><?= $nutritionResult['nutrition']['calories'] ?></b> kcal</span><br>
                    <span>Protéines : <b><?= $nutritionResult['nutrition']['protein'] ?></b>g, Lipides : <b><?= $nutritionResult['nutrition']['fat'] ?></b>g, Glucides : <b><?= $nutritionResult['nutrition']['carbs'] ?></b>g</span>
                </div>
            <?php } elseif ($nutritionResult && isset($nutritionResult['error'])) { ?>
                <div class="err" style="margin-top:18px;">❌ <?= htmlspecialchars($nutritionResult['error'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
