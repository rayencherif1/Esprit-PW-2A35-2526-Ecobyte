<?php
/**
 * Page de connexion - Front Office (Client)
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Traiter la connexion
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $userController = new UserController();
    $user = $userController->login($email, $password);
    
    if ($user) {
        // Succès
        header('Location: ?section=front&action=home');
        exit;
    } else {
        $errors = $userController->getErrors();
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
                            <img src="/recette/public/image/logo.png" alt="logo" class="img-fluid" style="max-height: 45px;">
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
                            <h2 class="card-title text-center mb-4">Connexion Client</h2>

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

                            <form method="POST" id="loginForm">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Entrez votre email">
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Entrez votre mot de passe">
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    Se connecter
                                </button>
                            </form>

                            <hr class="my-4">

                            <div class="text-center">
                                <p class="mb-2">Pas encore de compte?</p>
                                <a href="?section=front&action=signup" class="btn btn-outline-secondary w-100 py-2">
                                    Créer un compte
                                </a>
                            </div>

                            <div class="text-center mt-3">
                                <p class="text-muted text-sm">
                                    <a href="?section=back&action=sign-in" class="text-decoration-none">Connexion Admin</a>
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
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let email = document.getElementById('email').value.trim();
            let password = document.getElementById('password').value;
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === "" || password === "") {
                alert("Veuillez remplir tous les champs.");
                e.preventDefault();
                return;
            }

            if (!emailPattern.test(email)) {
                alert("Veuillez entrer une adresse email valide.");
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>
