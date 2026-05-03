<?php
/**
 * Page de profil utilisateur - Front Office (Client)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ?section=front&action=sign-in');
    exit;
}

$userId = $_SESSION['user_id'];
$userController = new UserController();
$user = $userController->getUser($userId);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($userController->updateProfile($userId, $_POST, $_FILES)) {
        $success = $userController->getSuccess();
        $user = $userController->getUser($userId); // Refresh data
    } else {
        $errors = $userController->getErrors();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - FoodMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="view/front/style.css">
</head>
<body class="bg-light">
    <header>
        <div class="container-fluid">
            <div class="row py-3 border-bottom bg-white">
                <div class="col-sm-4 col-lg-3 text-center text-sm-start">
                    <div class="main-logo">
                        <a href="?section=front">
                            <img src="view/front/images/logo.png" alt="logo" class="img-fluid" style="max-height: 45px;">
                        </a>
                    </div>
                </div>
                <div class="col-sm-8 col-lg-9 d-flex justify-content-end gap-3 align-items-center mt-4 mt-sm-0">
                    <a href="?section=front" class="btn btn-sm btn-outline-secondary">Retour au magasin</a>
                    <a href="?section=front&action=logout" class="btn btn-sm btn-danger">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow border-0">
                        <div class="card-body p-5">
                            <h2 class="card-title mb-4">Modifier mes informations</h2>

                            <?php if ($success): ?>
                                <div class="alert alert-success mt-3"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger mt-3">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data" novalidate>
                                <div class="text-center mb-4">
                                    <div class="mb-3">
                                        <?php 
                                        $displayPhoto = !empty($user['photo']) ? $user['photo'] : 'view/front/images/user-icon.png';
                                        ?>
                                        <img src="<?php echo $displayPhoto; ?>" alt="Profile" 
                                             class="rounded-circle shadow-sm" 
                                             style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #fff;">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label btn btn-sm btn-outline-primary">
                                            Changer la photo
                                            <input type="file" name="photo" class="d-none" accept="image/*">
                                        </label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nom</label>
                                        <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Prénom</label>
                                        <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Numéro de téléphone</label>
                                    <input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Poids (kg)</label>
                                        <input type="number" step="0.1" name="poids" class="form-control" value="<?php echo htmlspecialchars($user['poids'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Taille (cm)</label>
                                        <input type="number" step="0.1" name="taille" class="form-control" value="<?php echo htmlspecialchars($user['taille'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                                    <input type="password" name="password" class="form-control">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label d-block">Sécurité Avancée</label>
                                    <button type="button" id="btn-face-id" class="btn btn-outline-dark mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video me-2" viewBox="0 0 16 16">
                                          <path fill-rule="evenodd" d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556v4.35zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H2z"/>
                                        </svg>
                                        Configurer la Caméra pour Face ID
                                    </button>
                                    
                                    <div id="camera-container" class="d-none text-center mt-3">
                                        <div class="position-relative d-inline-block">
                                            <video id="videoElement" width="320" height="240" autoplay muted class="rounded border shadow-sm"></video>
                                            <div id="scan-overlay" class="position-absolute top-50 start-50 translate-middle text-white fw-bold d-none" style="background: rgba(0,0,0,0.5); padding: 5px 10px; border-radius: 5px; z-index: 10;">Scan en cours...</div>
                                        </div>
                                        <p id="camera-status" class="form-text text-info mt-2">Chargement de l'intelligence artificielle...</p>
                                    </div>

                                    <div id="face-id-status" class="form-text text-success d-none mt-1">Visage configuré avec succès !</div>
                                </div>

                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-5 btn-lg">Enregistrer les modifications</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        const btnFaceId = document.getElementById('btn-face-id');
        const cameraContainer = document.getElementById('camera-container');
        const video = document.getElementById('videoElement');
        const cameraStatus = document.getElementById('camera-status');
        const scanOverlay = document.getElementById('scan-overlay');
        let modelsLoaded = false;

        btnFaceId.addEventListener('click', async () => {
            btnFaceId.classList.add('d-none');
            cameraContainer.classList.remove('d-none');
            
            if (!modelsLoaded) {
                cameraStatus.innerText = "Chargement de l'IA (veuillez patienter)...";
                const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';
                await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                modelsLoaded = true;
            }

            cameraStatus.innerText = "Allumage de la caméra...";
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(err => {
                    cameraStatus.innerText = "Erreur caméra : " + err;
                });
        });

        video.addEventListener('play', () => {
            cameraStatus.innerText = "Regardez la caméra bien en face.";
            
            // On scanne régulièrement jusqu'à trouver un visage
            const scanInterval = setInterval(async () => {
                scanOverlay.classList.remove('d-none');
                
                const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
                
                if (detection) {
                    clearInterval(scanInterval);
                    scanOverlay.innerText = "Visage détecté ! Enregistrement...";
                    
                    // Convertir en tableau classique
                    const descriptorArray = Array.from(detection.descriptor);
                    
                    // Envoyer au serveur
                    const response = await fetch('?section=front&action=webauthn-register', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ descriptor: descriptorArray })
                    });

                    const result = await response.json();
                    
                    // Éteindre la caméra
                    const stream = video.srcObject;
                    const tracks = stream.getTracks();
                    tracks.forEach(track => track.stop());
                    
                    cameraContainer.classList.add('d-none');
                    btnFaceId.classList.remove('d-none');
                    
                    if (result.success) {
                        document.getElementById('face-id-status').classList.remove('d-none');
                        alert("Votre visage a été enregistré avec succès ! Vous pouvez maintenant vous connecter avec la caméra.");
                    } else {
                        alert("Erreur lors de l'enregistrement : " + result.message);
                    }
                } else {
                    scanOverlay.classList.add('d-none');
                }
            }, 1000);
        });
    </script>
</body>
</html>
<?php exit; ?>
