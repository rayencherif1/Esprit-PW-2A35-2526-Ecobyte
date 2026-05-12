<?php
/**
 * Page de connexion - Front Office (Client)
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController = new UserController();
    
    // Traiter la connexion avec Google
    if (isset($_POST['google_credential'])) {
        $user = $userController->googleLogin($_POST['google_credential'], false);
        if ($user) {
            header('Location: ?section=front&action=home');
            exit;
        } else {
            $errors = $userController->getErrors();
        }
    } elseif (isset($_POST['facebook_id'])) {
        $user = $userController->facebookLogin(
            $_POST['facebook_id'], 
            $_POST['facebook_email'] ?? '', 
            $_POST['facebook_nom'] ?? '', 
            $_POST['facebook_prenom'] ?? '', 
            $_POST['facebook_photo'] ?? '',
            false
        );
        if ($user) {
            header('Location: ?section=front&action=home');
            exit;
        } else {
            $errors = $userController->getErrors();
        }
    } else {
        // Traiter la connexion classique
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        $user = $userController->login($email, $password);
        
        if ($user) {
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                header('Location: ?section=back&action=users');
            } else {
                header('Location: ?section=front&action=home');
            }
            exit;
        } else {
            $errors = $userController->getErrors();
        }
    }
}

$errors = $errors ?? [];

$pageTitle = "Connexion - EcoByte";
$hideUserButton = true;
require __DIR__ . '/layout_header.php'; 
?>

<style>
    .auth-card {
        background: #ffffff;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
    }
    .form-control {
        border-radius: 12px;
        padding: 12px 20px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        font-family: 'Poppins', sans-serif;
    }
    .form-control:focus {
        border-color: #4caf50;
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        background-color: #fff;
    }
    .btn-premium-auth {
        background: linear-gradient(135deg, #1e293b, #334155);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 14px;
        font-weight: 700;
        transition: all 0.3s;
    }
    .btn-premium-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        color: white;
    }
    .btn-social {
        border-radius: 12px;
        padding: 12px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
        background: white;
    }
    .btn-social:hover {
        background: #f1f5f9;
    }
</style>

<section class="py-5 min-vh-100 d-flex align-items-center" style="background: #f8fafc;">
    <div class="container">
        <div class="mb-4">
            <a href="index.php?section=front&action=home" class="text-decoration-none text-muted fw-500">
                <i class="fas fa-arrow-left me-2"></i> Retour au Hub
            </a>
        </div>
        <div class="row">
            <div class="col-md-6 offset-md-3 col-lg-5 offset-lg-3.5">
                <div class="auth-card">
                    <h2 class="text-center mb-4 fw-bold" style="color: #1e293b;">Connexion</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
                            <?php foreach ($errors as $error): ?>
                                <div class="small"><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="loginForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email ou Identifiant</label>
                            <input type="text" class="form-control" name="email" placeholder="votre@email.com">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label class="form-label small fw-bold text-muted">Mot de passe</label>
                                <a href="?section=front&action=forgot-password" class="small text-decoration-none text-info">Oublié ?</a>
                            </div>
                            <input type="password" class="form-control" name="password" placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn btn-premium-auth w-100 mt-2">
                            Se connecter
                        </button>
                    </form>

                    <div class="text-center my-4 position-relative">
                        <hr>
                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">ou continuer avec</span>
                    </div>

                    <!-- Social Buttons -->
                    <div class="d-grid gap-3">
                        <div class="d-flex justify-content-center">
                            <div id="g_id_onload"
                                 data-client_id="116948001518-6dm53guvdr7bc8bjst1s1ad6hqm2mce6.apps.googleusercontent.com"
                                 data-callback="handleCredentialResponse"
                                 data-auto_prompt="false">
                            </div>
                            <div class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="outline" data-text="signin_with" data-size="large" data-logo_alignment="left"></div>
                        </div>

                        <button type="button" class="btn btn-social d-flex align-items-center justify-content-center" onclick="checkLoginState();">
                            <i class="fab fa-facebook text-primary me-2"></i> Se connecter avec Facebook
                        </button>

                        <button type="button" id="btn-login-face-id" class="btn btn-social d-flex align-items-center justify-content-center">
                            <i class="fas fa-camera text-info me-2"></i> Se connecter avec la Caméra
                        </button>
                    </div>

                    <div id="login-camera-container" class="d-none mt-3 text-center">
                        <video id="loginVideoElement" width="100%" height="auto" autoplay muted class="rounded-4 border shadow-sm"></video>
                        <p id="login-camera-status" class="small text-info mt-2">Initialisation de l'IA...</p>
                    </div>

                    <div class="text-center mt-5">
                        <p class="text-muted small">Pas encore de compte ?</p>
                        <a href="?section=front&action=signup" class="text-decoration-none fw-bold" style="color: #4caf50;">Créer un compte</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$extraScripts = <<<JS
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/fr_FR/sdk.js"></script>
<script>
    // GOOGLE
    function handleCredentialResponse(response) {
        let form = document.createElement("form");
        form.method = "POST";
        form.action = "?section=front&action=sign-in";
        let input = document.createElement("input");
        input.type = "hidden"; input.name = "google_credential"; input.value = response.credential;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    // FACEBOOK
    window.fbAsyncInit = function() {
        FB.init({ appId: "967417232327743", cookie: true, xfbml: true, version: "v18.0" });
    };

    function checkLoginState() {
        FB.login(function(response) {
            if (response.status === "connected") {
                FB.api("/me", {fields: "id,name,first_name,last_name,picture"}, function(u) {
                    let form = document.createElement("form");
                    form.method = "POST"; form.action = "?section=front&action=sign-in";
                    let data = { facebook_id: u.id, facebook_email: u.email||"", facebook_nom: u.last_name||u.name||"", facebook_prenom: u.first_name||"", facebook_photo: u.picture?.data?.url||"" };
                    for (const [k, v] of Object.entries(data)) {
                        let input = document.createElement("input"); input.type = "hidden"; input.name = k; input.value = v; form.appendChild(input);
                    }
                    document.body.appendChild(form); form.submit();
                });
            }
        }, {scope: "public_profile"});
    }

    // FACE ID (VERSION OPTIMISÉE SANS BLOCAGE)
    const btnFace = document.getElementById("btn-login-face-id");
    const camCont = document.getElementById("login-camera-container");
    const video = document.getElementById("loginVideoElement");
    const status = document.getElementById("login-camera-status");
    let modelsLoaded = false;
    let faces = [];
    let isDetecting = false;

    btnFace.addEventListener("click", async () => {
        btnFace.disabled = true;
        status.innerText = "Initialisation de l'IA (Ceci peut prendre 5-10s)...";
        camCont.classList.remove("d-none");
        
        try {
            if (!modelsLoaded) {
                // Utilisation de TinyFaceDetector pour la performance (beaucoup plus léger)
                const MODEL_URL = "https://justadudewhohacks.github.io/face-api.js/models";
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                modelsLoaded = true;
            }
            
            status.innerText = "IA Chargée. Récupération des visages...";
            const res = await fetch("?section=front&action=get-face-descriptors");
            const data = await res.json();
            
            if (!data || data.length === 0) {
                alert("Aucun visage enregistré. Veuillez d'abord en enregistrer un dans votre profil.");
                btnFace.disabled = false;
                camCont.classList.add("d-none");
                return;
            }
            
            faces = data.map(d => new faceapi.LabeledFaceDescriptors(d.userId.toString(), [new Float32Array(d.descriptor)]));
            
            const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
            video.srcObject = stream;
            status.innerText = "Prêt. Regardez la caméra.";
            
        } catch (e) {
            console.error(e);
            status.innerText = "Erreur : " + e.message;
            btnFace.disabled = false;
        }
    });

    video.addEventListener("play", () => {
        const matcher = new faceapi.FaceMatcher(faces, 0.5); // Seuil de confiance moyen
        isDetecting = true;
        
        const runDetection = async () => {
            if (!isDetecting) return;
            
            try {
                // Utilisation de TinyFaceDetectorOptions pour éviter le blocage du CPU
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
                
                if (detection) {
                    const match = matcher.findBestMatch(detection.descriptor);
                    if (match.label !== "unknown") {
                        isDetecting = false;
                        status.innerText = "Visage reconnu !";
                        if (video.srcObject) video.srcObject.getTracks().forEach(t => t.stop());
                        
                        const res = await fetch("?section=front&action=webauthn-login", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ userId: match.label })
                        });
                        
                        const r = await res.json();
                        if (r.success) {
                            window.location.href = r.redirect || "?section=front&action=home";
                            return;
                        } else {
                            status.innerText = "Erreur de connexion.";
                            isDetecting = true;
                        }
                    } else {
                        status.innerText = "Visage non reconnu. Ajustez votre position.";
                    }
                } else {
                    status.innerText = "Aucun visage détecté...";
                }
            } catch (err) {
                console.error(err);
            }
            
            if (isDetecting) setTimeout(runDetection, 600); // 1.5 FPS pour une fluidité maximale du site
        };
        
        runDetection();
    });
</script>
JS;
require __DIR__ . '/layout_footer.php'; 
?>
