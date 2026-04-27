<?php
/**
 * Page Mot de Passe Oublié
 */

$userController = new UserController();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if ($userController->forgotPassword($email)) {
        header('Location: ?section=front&action=verify-code&email=' . urlencode($email));
        exit;
    } else {
        $errors = $userController->getErrors();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - FoodMart</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="view/front/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="bg-light">
    <header>
        <div class="container-fluid">
            <div class="row py-3 border-bottom">
                <div class="col-12 text-center text-sm-start">
                    <div class="main-logo ms-3">
                        <a href="?section=front">
                            <img src="view/front/images/logo.png" alt="logo" class="img-fluid" style="max-height: 45px;">
                        </a>
                    </div>
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
                            <h2 class="card-title text-center mb-4">Mot de passe oublié</h2>
                            <p class="text-muted text-center mb-4">Entrez votre adresse email pour recevoir un code de réinitialisation.</p>

                            <!-- Messages d'erreur -->
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="votre@email.com" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    Envoyer le code
                                </button>
                            </form>

                            <div class="text-center mt-4">
                                <a href="?section=front&action=sign-in" class="text-decoration-none">Retour à la connexion</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 border-top mt-auto">
        <div class="container text-center text-muted">
            <p>© 2026 FoodMart. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
