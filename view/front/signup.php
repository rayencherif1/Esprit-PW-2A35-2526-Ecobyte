<?php
/**
 * Page d'inscription - Front Office
 */

require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $userController->register($_POST);
    if ($result) {
        $success = $userController->getSuccess();
        // Redirect or show success
        header('Refresh: 3; URL=?section=front&action=home');
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
                                <p class="mb-0 small">Redirection vers l'accueil dans 3 secondes...</p>
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

                        <form method="POST" action="" id="signupForm">
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
    <script>
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
