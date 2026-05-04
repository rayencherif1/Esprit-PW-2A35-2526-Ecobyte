<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/model/post.php';
require_once __DIR__ . '/controller/post.controller.php';

$message = '';
$error = '';
$nutritionResult = null;

/**
 * Construit l'URL HTTP absolue d'une API locale.
 */
function buildLocalApiUrl(string $relativeApiPath): ?string
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        return null;
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $isHttps ? 'https' : 'http';
    $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    if ($baseDir === '' || $baseDir === '.') {
        $baseDir = '';
    }
    $relativeApiPath = ltrim($relativeApiPath, '/');

    return $scheme . '://' . $host . $baseDir . '/' . $relativeApiPath;
}

/**
 * Envoie l'image à une API locale et retourne le JSON décodé.
 */
function callLocalImageApi(string $relativeApiPath, string $imagePath, int $timeout = 20): ?array
{
    if (!is_file($imagePath)) {
        return null;
    }
    $apiUrl = buildLocalApiUrl($relativeApiPath);
    if ($apiUrl === null) {
        return null;
    }

    $ch = curl_init();
    $postFields = ['image' => new CURLFile($imagePath)];
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $apiResponse = curl_exec($ch);
    curl_close($ch);

    if (!is_string($apiResponse) || $apiResponse === '') {
        return null;
    }

    $decoded = json_decode($apiResponse, true);
    if (!is_array($decoded) || !($decoded['success'] ?? false)) {
        return null;
    }

    return $decoded;
}

/**
 * Génère un brouillon d'article depuis une image uploadée.
 */
function generateArticleFromImage(string $imagePath): ?array
{
    return callLocalImageApi('api/generate_article.php', $imagePath, 20);
}

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
    $generatedArticle = null;
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
            $generatedArticle = generateArticleFromImage($targetFile);
            // Appel API nutrition (même pour un article simple)
            $nutritionApiResult = callLocalImageApi('api/nutrition_analyzer.php', $targetFile, 10);
            if (is_array($nutritionApiResult)) {
                $nutritionResult = $nutritionApiResult;
                $_SESSION['nutritionResult'] = $nutritionResult;
            }
        }
    }

    // Auto-remplissage avancé: applique la génération uniquement si le bouton a été utilisé
    // et que les champs sont renvoyés remplis côté formulaire.
    if (is_array($generatedArticle)) {
        if ($titre === '' && !empty($generatedArticle['title'])) {
            $titre = trim((string) $generatedArticle['title']);
        }
        if ($contenu === '' && !empty($generatedArticle['content'])) {
            $contenu = trim((string) $generatedArticle['content']);
        }
        if ($categorie === '' && !empty($generatedArticle['category'])) {
            $categorie = trim((string) $generatedArticle['category']);
        } elseif ($categorie === '') {
            $categorie = 'Article auto-généré';
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
                <button type="button" id="btn-generate-article" class="btn" style="margin-top:10px;" disabled>Générer l'article depuis l'image</button>

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
                const titreInput = document.getElementById('titre');
                const categorieInput = document.getElementById('categorie');
                const contenuInput = document.getElementById('contenu');
                const resultDiv = document.getElementById('nutrition-result-js');
                const nutritionJsonInput = document.getElementById('nutrition-json');
                const generateBtn = document.getElementById('btn-generate-article');

                const generateArticleFromImage = function() {
                    if (!imageInput || !imageInput.files || !imageInput.files[0]) {
                        resultDiv.innerHTML = "<div class='err' style='margin-top:18px;'>Veuillez d'abord choisir une image.</div>";
                        return;
                    }

                    resultDiv.innerHTML = "<div class='ok' style='margin-top:18px;'>Génération de l'article en cours...</div>";
                    nutritionJsonInput.value = '';
                    if (generateBtn) {
                        generateBtn.disabled = true;
                    }

                    const formData = new FormData();
                    formData.append('image', imageInput.files[0]);
                    const articleFormData = new FormData();
                    articleFormData.append('image', imageInput.files[0]);
                    const nutritionFormData = new FormData();
                    nutritionFormData.append('image', imageInput.files[0]);

                    Promise.all([
                        fetch('api/generate_article.php', {
                            method: 'POST',
                            body: articleFormData
                        }).then(async r => {
                            if (!r.ok) {
                                const text = await r.text();
                                throw new Error('HTTP ' + r.status + ' - ' + text);
                            }
                            return r.json();
                        }),
                        fetch('api/nutrition_analyzer.php', {
                            method: 'POST',
                            body: nutritionFormData
                        }).then(async r => {
                            if (!r.ok) {
                                const text = await r.text();
                                throw new Error('Nutrition HTTP ' + r.status + ' - ' + text);
                            }
                            return r.json();
                        })
                    ])
                    .then(([articleData, nutritionData]) => {
                        if (!articleData.success) {
                            resultDiv.innerHTML = `<div class='err' style='margin-top:18px;'>❌ ${articleData.error || 'Impossible de générer l\'article.'}</div>`;
                            return;
                        }

                        titreInput.value = articleData.title || titreInput.value;
                        categorieInput.value = articleData.category || categorieInput.value;
                        contenuInput.value = articleData.content || contenuInput.value;

                        let nutritionHtml = "<span>Analyse nutritionnelle non trouvée.</span>";
                        nutritionJsonInput.value = '';
                        if (nutritionData && nutritionData.success) {
                            nutritionJsonInput.value = JSON.stringify(nutritionData);
                            nutritionHtml = `
                                <span>Aliment détecté : <b>${nutritionData.food_name}</b></span><br>
                                <span>Calories (100g) : <b>${nutritionData.nutrition.calories}</b> kcal</span><br>
                                <span>Protéines : <b>${nutritionData.nutrition.protein}</b>g, Lipides : <b>${nutritionData.nutrition.fat}</b>g, Glucides : <b>${nutritionData.nutrition.carbs}</b>g</span>
                            `;
                        }

                        resultDiv.innerHTML = `
                            <div class='ok' style='margin-top:18px;'>
                                <strong>📝 Article généré automatiquement :</strong><br>
                                <b>Titre :</b> ${articleData.title}<br>
                                <b>Catégorie :</b> ${articleData.category || 'Article auto-généré'}<br>
                                <b>Contenu :</b> ${articleData.content.split('\n')[0]}<br>
                                <small>${articleData.article_hint}</small>
                            </div>
                            <div class='ok' style='margin-top:8px;'>
                                <strong>🍎 Analyse nutritionnelle :</strong><br>
                                ${nutritionHtml}
                            </div>
                        `;
                    })
                    .catch(err => {
                        console.error('generate_article error:', err);
                        resultDiv.innerHTML = `<div class='err' style='margin-top:18px;'>Erreur lors de la génération d'article : ${err.message}</div>`;
                        nutritionJsonInput.value = '';
                    })
                    .finally(() => {
                        if (generateBtn) {
                            generateBtn.disabled = false;
                        }
                    });
                };

                if (imageInput) {
                    imageInput.addEventListener('change', function() {
                        resultDiv.innerHTML = '';
                        nutritionJsonInput.value = '';
                        if (generateBtn) {
                            generateBtn.disabled = !(imageInput.files && imageInput.files[0]);
                        }
                    });
                }
                if (generateBtn) {
                    generateBtn.addEventListener('click', function(event) {
                        event.preventDefault();
                        generateArticleFromImage();
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
