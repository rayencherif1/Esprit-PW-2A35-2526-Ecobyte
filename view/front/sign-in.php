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
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>Connexion - FoodMart</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="view/front/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Open+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <header>
        <div class="container-fluid">
            <div class="row py-3 border-bottom">
                <div class="col-sm-4 col-lg-3 text-center text-sm-start">
                    <div class="main-logo">
                        <a href="?section=front">
                            <img src="view/front/images/logo.png" alt="logo" class="img-fluid" style="max-height: 45px;">
                        </a>
                    </div>
                </div>
                <div class="col-sm-8 col-lg-9 d-flex justify-content-end gap-3 align-items-center mt-4 mt-sm-0">
                    <a href="?section=front" class="btn btn-sm btn-outline-secondary">Retour au magasin</a>
                </div>
            </div>
        </div>
    </header>

    <section class="py-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h2 class="card-title text-center mb-4">Connexion</h2>

                            <!-- Messages d'erreur -->
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Erreur(s):</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" id="loginForm" novalidate>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Entrez votre email">
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <label for="password" class="form-label">Mot de passe</label>
                                        <a href="?section=front&action=forgot-password" class="text-sm text-decoration-none">Mot de passe oublié ?</a>
                                    </div>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Entrez votre mot de passe">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    Se connecter
                                </button>
                            </form>

                            <hr class="my-4">

                            <!-- Bouton Google Sign-In -->
                            <div class="d-flex justify-content-center mb-3">
                                <div id="g_id_onload"
                                     data-client_id="116948001518-6dm53guvdr7bc8bjst1s1ad6hqm2mce6.apps.googleusercontent.com"
                                     data-callback="handleCredentialResponse"
                                     data-auto_prompt="false">
                                </div>
                                <div class="g_id_signin" 
                                     data-type="standard" 
                                     data-size="large" 
                                     data-theme="outline" 
                                     data-text="sign_in_with" 
                                     data-shape="rectangular" 
                                     data-logo_alignment="left">
                                </div>
                            </div>

                            <!-- Bouton Facebook Sign-In -->
                            <div class="d-flex justify-content-center mb-4">
                                <button type="button" class="btn btn-outline-primary w-100 py-2 d-flex justify-content-center align-items-center" onclick="checkLoginState();">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-facebook me-2" viewBox="0 0 16 16">
                                      <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                                    </svg>
                                    Se connecter avec Facebook
                                </button>
                            </div>

                            <!-- Bouton Face ID (Caméra) -->
                            <div class="text-center mb-4">
                                <button type="button" id="btn-login-face-id" class="btn btn-outline-dark w-100 py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-camera-video me-2" viewBox="0 0 16 16">
                                      <path fill-rule="evenodd" d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5zm11.5 5.175 3.5 1.556V4.269l-3.5 1.556v4.35zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H2z"/>
                                    </svg>
                                    Se connecter avec la Caméra (Face ID)
                                </button>
                                
                                <div id="login-camera-container" class="d-none mt-3">
                                    <div class="position-relative d-inline-block">
                                        <video id="loginVideoElement" width="320" height="240" autoplay muted class="rounded border shadow-sm"></video>
                                        <div id="login-scan-overlay" class="position-absolute top-50 start-50 translate-middle text-white fw-bold d-none" style="background: rgba(0,0,0,0.5); padding: 5px 10px; border-radius: 5px; z-index: 10;">Analyse du visage...</div>
                                    </div>
                                    <p id="login-camera-status" class="form-text text-info mt-2">Initialisation de l'IA...</p>
                                </div>
                            </div>

                            <div class="text-center">
                                <p class="mb-2">Pas encore de compte?</p>
                                <a href="?section=front&action=signup" class="btn btn-outline-secondary w-100 py-2">
                                    Créer un compte
                                </a>
                            </div>

                            <div class="text-center mt-3">
                                <p class="text-muted text-sm">
                                    <!-- Lien admin supprimé car la connexion est unifiée -->
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Infos de démo -->
                    <div class="card mt-3 bg-light border-0">
                        <div class="card-body text-center text-muted small">
                            <p class="mb-0"><strong>Identifiants de test:</strong></p>
                            <p class="mb-0">Email: test@example.com</p>
                            <p class="mb-0">Mot de passe: demo123</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-5 border-top">
        <div class="container text-center text-muted">
            <p>© 2026 FoodMart. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <!-- SDK Facebook -->
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/fr_FR/sdk.js"></script>
    <script>
        // [...] Code Google conservé (handleCredentialResponse)
        function handleCredentialResponse(response) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '?section=front&action=sign-in';
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'google_credential';
            input.value = response.credential;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        // Facebook Login Init
        window.fbAsyncInit = function() {
            FB.init({
                appId      : '967417232327743',
                cookie     : true,
                xfbml      : true,
                version    : 'v18.0'
            });
        };

        function checkLoginState() {
            FB.login(function(response) {
                if (response.status === 'connected') {
                    FB.api('/me', {fields: 'id,name,email,first_name,last_name,picture'}, function(userInfo) {
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '?section=front&action=sign-in';

                        let inputs = {
                            facebook_id: userInfo.id,
                            facebook_email: userInfo.email || '',
                            facebook_nom: userInfo.last_name || userInfo.name || 'Inconnu',
                            facebook_prenom: userInfo.first_name || 'Inconnu',
                            facebook_photo: userInfo.picture?.data?.url || ''
                        };

                        for (const [key, value] of Object.entries(inputs)) {
                            let input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    });
                }
            }, {scope: 'public_profile'});
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let email = document.getElementById('email').value.trim();
            let password = document.getElementById('password').value;
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === "" || password === "") {
                alert("Veuillez remplir tous les champs.");
                e.preventDefault(); return;
            }
            if (!emailPattern.test(email) && email !== 'admin2026') {
                alert("Veuillez entrer une adresse email valide.");
                e.preventDefault(); return;
            }
        });

        // Connexion Caméra (Face-API.js)
        const btnLoginFaceId = document.getElementById('btn-login-face-id');
        const loginCameraContainer = document.getElementById('login-camera-container');
        const loginVideo = document.getElementById('loginVideoElement');
        const loginCameraStatus = document.getElementById('login-camera-status');
        const loginScanOverlay = document.getElementById('login-scan-overlay');
        
        let loginModelsLoaded = false;
        let registeredFaces = [];

        btnLoginFaceId.addEventListener('click', async () => {
            btnLoginFaceId.classList.add('d-none');
            loginCameraContainer.classList.remove('d-none');
            
            loginCameraStatus.innerText = "Téléchargement de l'IA et des profils...";
            
            try {
                if (!loginModelsLoaded) {
                    const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';
                    await Promise.all([
                        faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                    ]);
                    loginModelsLoaded = true;
                }

                // Récupérer tous les descripteurs de la base de données
                const response = await fetch('?section=front&action=get-face-descriptors');
                const data = await response.json();
                
                if (data.length === 0) {
                    alert("Aucun visage n'a été enregistré dans la base de données ! Veuillez d'abord vous connecter avec un mot de passe et configurer Face ID dans votre profil.");
                    btnLoginFaceId.classList.remove('d-none');
                    loginCameraContainer.classList.add('d-none');
                    return;
                }

                registeredFaces = data.map(d => {
                    return new faceapi.LabeledFaceDescriptors(
                        d.userId.toString(), 
                        [new Float32Array(d.descriptor)]
                    );
                });

                loginCameraStatus.innerText = "Allumage de la caméra...";
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                loginVideo.srcObject = stream;
                
            } catch (err) {
                console.error(err);
                loginCameraStatus.innerText = "Erreur : " + err.message;
            }
        });

        loginVideo.addEventListener('play', () => {
            loginCameraStatus.innerText = "Regardez la caméra pour vous connecter.";
            
            // On rend la reconnaissance beaucoup plus stricte (0.45 au lieu de 0.6)
            // Plus le chiffre est petit, plus c'est strict (acceptera moins de visages)
            const faceMatcher = new faceapi.FaceMatcher(registeredFaces, 0.45); 
            
            const scanInterval = setInterval(async () => {
                loginScanOverlay.classList.remove('d-none');
                
                const detection = await faceapi.detectSingleFace(loginVideo).withFaceLandmarks().withFaceDescriptor();
                
                if (detection) {
                    const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                    
                    if (bestMatch.label !== 'unknown') {
                        clearInterval(scanInterval);
                        loginScanOverlay.innerText = "Visage reconnu avec succès !";
                        loginScanOverlay.classList.replace('text-white', 'text-success');
                        
                        // Éteindre la caméra
                        loginVideo.srcObject.getTracks().forEach(t => t.stop());
                        
                        // Envoyer l'ID trouvé au serveur pour forcer la connexion
                        const response = await fetch('?section=front&action=webauthn-login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ userId: bestMatch.label })
                        });

                        const result = await response.json();
                        if (result.success) {
                            window.location.href = '?section=front&action=home';
                        } else {
                            alert("Erreur de connexion : " + result.message);
                        }
                    } else {
                        loginScanOverlay.innerText = "Visage inconnu (accès refusé)";
                        loginScanOverlay.classList.replace('text-white', 'text-danger');
                    }
                } else {
                    loginScanOverlay.classList.add('d-none');
                }
            }, 1000);
        });
    </script>
</body>
</html>
