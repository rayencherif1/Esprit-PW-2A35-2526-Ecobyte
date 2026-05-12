<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/post.php';
require_once __DIR__ . '/../../controller/post.controller.php';

$message = '';
$error = '';
$nutritionResult = null;

function buildLocalApiUrl(string $relativeApiPath): ?string {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') return null;
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $isHttps ? 'https' : 'http';
    $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . $baseDir . '/' . ltrim($relativeApiPath, '/');
}

function callLocalImageApi(string $relativeApiPath, string $imagePath, int $timeout = 20): ?array {
    if (!is_file($imagePath)) return null;
    $apiUrl = buildLocalApiUrl($relativeApiPath);
    if ($apiUrl === null) return null;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => new CURLFile($imagePath)]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $apiResponse = curl_exec($ch);
    curl_close($ch);
    return is_string($apiResponse) ? json_decode($apiResponse, true) : null;
}

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim((string) ($_POST['titre'] ?? ''));
    $contenu = trim((string) ($_POST['contenu'] ?? ''));
    $datePublication = trim((string) ($_POST['datePublication'] ?? date('Y-m-d')));
    $categorie = trim((string) ($_POST['categorie'] ?? ''));
    
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            $imagePath = 'view/uploads/' . $fileName;
        }
    }

    if ($titre === '' || $categorie === '' || $contenu === '') {
        $error = 'Veuillez remplir tous les champs obligatoires (*).';
    } else {
        $post = new Post(null, $titre, $contenu, $datePublication, $categorie, $imagePath, null);
        try {
            $postC = new PostC();
            $postC->addPost($post);
            $message = 'Votre article a été publié avec succès !';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$titre = (string) ($_POST['titre'] ?? '');
$categorie = (string) ($_POST['categorie'] ?? '');
$datePublication = (string) ($_POST['datePublication'] ?? date('Y-m-d'));
$contenu = (string) ($_POST['contenu'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proposer un article — EcoByte</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #f4f7f6; }
        .ecobyte-topbar { background: #1a1a2e; padding: 12px 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .card { border: none; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); overflow: hidden; }
        .form-label { font-weight: 700; color: #475569; font-size: 0.9rem; margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 12px; padding: 12px 16px; border: 1px solid #e2e8f0; }
        .form-control:focus { border-color: #4caf50; box-shadow: 0 0 0 4px rgba(76,175,80,0.1); }
        .btn-primary { background: linear-gradient(135deg, #4caf50, #45a049); border: none; border-radius: 50px; padding: 12px 32px; font-weight: 700; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(76,175,80,0.3); }
        .btn-ai { background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; border: none; border-radius: 12px; }
        .btn-ai:disabled { opacity: 0.6; cursor: not-allowed; }
    </style>
</head>
<body>

    <nav class="ecobyte-topbar sticky-top">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <a href="/2int/index.php" class="text-decoration-none d-flex align-items-center gap-2">
                <span class="fs-4">🌿</span>
                <span class="fw-bolder text-white tracking-tight"><span style="color: #4caf50;">ECO</span><span style="color: #ff6b35;">BYTE</span></span>
            </a>
            <a href="blog.php" class="text-white-50 text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i> Retour au blog
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="mb-4">
                    <h1 class="fw-bolder text-dark" style="font-family: 'Nunito', sans-serif;">📝 Proposer un article</h1>
                    <p class="text-muted">Partagez votre savoir et vos astuces avec la communauté EcoByte.</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fs-4"></i>
                        <div><?= htmlspecialchars($message) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <div class="card bg-white p-4 p-md-5">
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="row g-4">
                            <!-- Titre -->
                            <div class="col-12">
                                <label for="titre" class="form-label">Titre de l'article *</label>
                                <input type="text" id="titre" name="titre" class="form-control" placeholder="Ex: 5 astuces pour manger bio" value="<?= htmlspecialchars($titre) ?>" required>
                            </div>

                            <!-- Catégorie & Date -->
                            <div class="col-md-6">
                                <label for="categorie" class="form-label">Catégorie *</label>
                                <input type="text" id="categorie" name="categorie" class="form-control" placeholder="Ex: Nutrition, Santé..." value="<?= htmlspecialchars($categorie) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="datePublication" class="form-label">Date de publication</label>
                                <input type="date" id="datePublication" name="datePublication" class="form-control" value="<?= htmlspecialchars($datePublication) ?>">
                            </div>

                            <!-- Image & AI -->
                            <div class="col-12">
                                <label class="form-label">Image de couverture</label>
                                <div class="d-flex flex-column flex-md-row gap-3">
                                    <div class="flex-grow-1">
                                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                    </div>
                                    <button type="button" id="btn-generate-article" class="btn btn-ai px-4 py-2" disabled>
                                        <i class="fas fa-magic me-2"></i> Générer via l'IA
                                    </button>
                                </div>
                                <small class="text-muted mt-2 d-block">L'IA peut générer un brouillon d'article à partir de votre image.</small>
                            </div>

                            <!-- Contenu -->
                            <div class="col-12">
                                <label for="contenu" class="form-label">Contenu de l'article *</label>
                                <textarea id="contenu" name="contenu" class="form-control" placeholder="Rédigez votre article ici..." style="min-height: 250px;" required><?= htmlspecialchars($contenu) ?></textarea>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                                <a href="blog.php" class="text-muted text-decoration-none fw-bold small">Annuler</a>
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="fas fa-paper-plane me-2"></i> Publier l'article
                                </button>
                            </div>
                        </div>
                    </form>

                    <div id="ai-status" class="mt-4"></div>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image');
        const generateBtn = document.getElementById('btn-generate-article');
        const aiStatus = document.getElementById('ai-status');

        imageInput.addEventListener('change', () => {
            generateBtn.disabled = !imageInput.files[0];
        });

        generateBtn.addEventListener('click', async () => {
            aiStatus.innerHTML = '<div class="alert alert-info border-0 rounded-4 small"><i class="fas fa-spinner fa-spin me-2"></i> L\'IA analyse votre image et génère l\'article...</div>';
            generateBtn.disabled = true;

            const formData = new FormData();
            formData.append('image', imageInput.files[0]);

            try {
                const [articleRes, nutritionRes] = await Promise.all([
                    fetch('api/generate_article.php', { method: 'POST', body: formData }).then(r => r.json()),
                    fetch('api/nutrition_analyzer.php', { method: 'POST', body: formData }).then(r => r.json())
                ]);

                if (articleRes.success) {
                    document.getElementById('titre').value = articleRes.title || '';
                    document.getElementById('categorie').value = articleRes.category || 'Nutrition';
                    document.getElementById('contenu').value = articleRes.content || '';
                    aiStatus.innerHTML = '<div class="alert alert-success border-0 rounded-4 small"><i class="fas fa-check-circle me-2"></i> Article généré avec succès !</div>';
                } else {
                    aiStatus.innerHTML = '<div class="alert alert-warning border-0 rounded-4 small">L\'IA n\'a pas pu générer l\'article, mais vous pouvez le rédiger manuellement.</div>';
                }
            } catch (err) {
                aiStatus.innerHTML = '<div class="alert alert-danger border-0 rounded-4 small">Erreur de connexion avec l\'IA.</div>';
            } finally {
                generateBtn.disabled = false;
            }
        });
    });
    </script>
</body>
</html>
