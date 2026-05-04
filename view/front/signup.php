<?php
/**
 * Page d'inscription - Front Office
 */

require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['google_credential'])) {
        $user = $userController->googleLogin($_POST['google_credential'], true);
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
            true
        );
        if ($user) {
            header('Location: ?section=front&action=home');
            exit;
        } else {
            $errors = $userController->getErrors();
        }
    } else {
        $result = $userController->register($_POST);
        if ($result) {
            $success = $userController->getSuccess();
            // Redirection vers la page de connexion après 3 secondes
            header('Refresh: 3; URL=?section=front&action=sign-in');
        } else {
            $errors = $userController->getErrors();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire - FoodMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="view/front/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <a href="?section=front">
                                <img src="view/front/images/logo.png" alt="logo" class="img-fluid" style="max-height: 50px;">
                            </a>
                            <h2 class="mt-3">Créer un compte</h2>
                        </div>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                                <p class="mb-0 small">Redirection vers la page de connexion dans 3 secondes...</p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Boutons Google et Facebook -->
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
                                 data-text="signup_with" 
                                 data-shape="rectangular" 
                                 data-logo_alignment="left">
                            </div>
                        </div>

                        <div class="d-flex justify-content-center mb-4">
                            <button type="button" class="btn btn-outline-primary w-100 py-2 d-flex justify-content-center align-items-center" onclick="checkLoginState();">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-facebook me-2" viewBox="0 0 16 16">
                                  <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
                                </svg>
                                S'inscrire avec Facebook
                            </button>
                        </div>

                        <div class="d-flex align-items-center mb-4">
                            <hr class="flex-grow-1">
                            <span class="mx-2 text-muted">ou avec votre email</span>
                            <hr class="flex-grow-1">
                        </div>

                        <form method="POST" action="" id="signupForm" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom</label>
                                    <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prénom</label>
                                    <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="password" class="form-control" placeholder="Min 6 caractères">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Téléphone (Optionnel)</label>
                                <input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Poids (kg)</label>
                                    <input type="number" step="0.1" name="poids" class="form-control" value="<?php echo htmlspecialchars($_POST['poids'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Taille (cm)</label>
                                    <input type="number" step="0.1" name="taille" class="form-control" value="<?php echo htmlspecialchars($_POST['taille'] ?? ''); ?>">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mt-3">S'inscrire</button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted">Déjà un compte ? <a href="?section=front&action=sign-in" class="text-decoration-none">Connectez-vous</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/fr_FR/sdk.js"></script>
    <script>
        // Google Login
        function handleCredentialResponse(response) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '?section=front&action=signup';
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
                        form.action = '?section=front&action=signup';

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


        document.getElementById('signupForm').addEventListener('submit', function(e) {
            let nom = document.getElementsByName('nom')[0].value.trim();
            let prenom = document.getElementsByName('prenom')[0].value.trim();
            let email = document.getElementsByName('email')[0].value.trim();
            let password = document.getElementsByName('password')[0].value;
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (nom === "" || prenom === "" || email === "" || password === "") {
                alert("Veuillez remplir tous les champs obligatoires (Nom, Prénom, Email, Mot de passe).");
                e.preventDefault();
                return;
            }

            if (!emailPattern.test(email)) {
                alert("Veuillez entrer une adresse email valide.");
                e.preventDefault();
                return;
            }

            if (password.length < 6) {
                alert("Le mot de passe doit contenir au moins 6 caractères.");
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>
