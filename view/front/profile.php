<?php
/**
 * Page de Profil Utilisateur - Front Office
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ?section=front&action=sign-in');
    exit;
}

$userController = new UserController();
$user = $userController->getUser($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'email' => $_POST['email'],
        'telephone' => $_POST['telephone'],
        'poids' => $_POST['poids'],
        'taille' => $_POST['taille']
    ];
    
    if ($userController->updateProfile($_SESSION['user_id'], $data, $_FILES)) {
        $success = "Profil mis à jour avec succès !";
        $user = $userController->getUser($_SESSION['user_id']); // Re-fetch
    } else {
        $errors = $userController->getErrors();
    }
}

$pageTitle = "Mon Profil - EcoByte";
require __DIR__ . '/layout_header.php';
?>

<style>
    .profile-card {
        background: white;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .profile-avatar-large {
        width: 120px;
        height: 120px;
        background: #4db6ac;
        color: white;
        font-size: 3rem;
        font-weight: 800;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 10px 20px rgba(77, 182, 172, 0.2);
    }
    .form-control {
        border-radius: 12px;
        padding: 12px 20px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
    }
    .btn-save {
        background: #1e293b;
        color: white;
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 600;
        border: none;
        transition: all 0.3s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .face-id-box {
        background: #f0f9ff;
        border-radius: 20px;
        padding: 25px;
        border: 1px dashed #0ea5e9;
    }
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="profile-card">
                <div class="text-center mb-5">
                    <div class="profile-avatar-large">
                        <?php echo strtoupper(substr($user['prenom'], 0, 1)); ?>
                    </div>
                    <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h2>
                    <p class="text-muted">Membre depuis le <?php echo date('d/m/Y', strtotime($user['date_creation'])); ?></p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Prénom</label>
                            <input type="text" class="form-control" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Nom</label>
                            <input type="text" class="form-control" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Téléphone</label>
                            <input type="text" class="form-control" name="telephone" value="<?php echo htmlspecialchars($user['telephone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Poids (kg)</label>
                            <input type="number" step="0.1" class="form-control" name="poids" value="<?php echo htmlspecialchars($user['poids']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Taille (cm)</label>
                            <input type="number" class="form-control" name="taille" value="<?php echo htmlspecialchars($user['taille']); ?>">
                        </div>
                    </div>

                    <div class="face-id-box mb-4 text-center">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-start">
                                <h5 class="fw-bold mb-1"><i class="fas fa-camera text-info me-2"></i> Reconnaissance Faciale</h5>
                                <p class="small text-muted mb-0">Sécurisez votre compte avec le Face ID.</p>
                            </div>
                            <button type="button" class="btn btn-info btn-sm rounded-pill text-white px-3" data-bs-toggle="modal" data-bs-target="#faceRegistrationModal">
                                <?php echo !empty($user['webauthn_public_key']) ? 'Mettre à jour' : 'Enregistrer'; ?>
                            </button>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" name="update_profile" class="btn btn-save">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Face ID (Récupéré du Hub) -->
<div class="modal fade" id="faceRegistrationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 30px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Scan Facial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="registration-camera-container" class="mb-3">
                    <video id="regVideoElement" width="100%" height="auto" autoplay muted class="rounded-4 border shadow-sm"></video>
                    <p id="reg-status" class="small text-info mt-2">Prêt pour le scan...</p>
                </div>
                <button type="button" id="btn-start-reg" class="btn btn-save w-100">
                    Lancer le Scan
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const btnStartReg = document.getElementById('btn-start-reg');
    const regVideo = document.getElementById('regVideoElement');
    const regStatus = document.getElementById('reg-status');
    let regModelsLoaded = false;
    let isRegDetecting = false;

    btnStartReg.addEventListener('click', async () => {
        btnStartReg.disabled = true;
        regStatus.innerText = "Chargement de l'IA...";
        try {
            if (!regModelsLoaded) {
                const URL = "https://justadudewhohacks.github.io/face-api.js/models";
                await faceapi.nets.tinyFaceDetector.loadFromUri(URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(URL);
                regModelsLoaded = true;
            }
            regVideo.srcObject = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
            regStatus.innerText = "Regardez la caméra...";
        } catch (e) {
            regStatus.innerText = "Erreur: " + e.message;
            btnStartReg.disabled = false;
        }
    });

    regVideo.addEventListener('play', () => {
        isRegDetecting = true;
        const runRegDetection = async () => {
            if (!isRegDetecting) return;
            const det = await faceapi.detectSingleFace(regVideo, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (det) {
                isRegDetecting = false;
                regStatus.innerText = "Visage détecté !";
                const res = await fetch("?section=front&action=webauthn-register", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ descriptor: Array.from(det.descriptor) })
                });
                const r = await res.json();
                if (r.success) {
                    regStatus.innerText = "Succès !";
                    setTimeout(() => { window.location.reload(); }, 1000);
                } else {
                    regStatus.innerText = "Erreur: " + (r.message || "Inconnue");
                    btnStartReg.disabled = false;
                }
                if (regVideo.srcObject) regVideo.srcObject.getTracks().forEach(t => t.stop());
            } else {
                if (isRegDetecting) setTimeout(runRegDetection, 500);
            }
        };
        runRegDetection();
    });
</script>

<?php require __DIR__ . '/layout_footer.php'; ?>
