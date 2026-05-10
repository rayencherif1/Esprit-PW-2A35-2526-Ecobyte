<?php
/**
 * Page d'activation du compte
 */
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();
$success = '';
$error = '';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];
    
    if ($userController->activateAccount($email, $token)) {
        $success = $userController->getSuccess();
    } else {
        $errors = $userController->getErrors();
        $error = $errors[0] ?? "Erreur lors de l'activation.";
    }
} else {
    $error = "Lien d'activation invalide.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation du compte - Ecobyte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="view/front/style.css">
</head>
<body class="bg-light">
    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0 text-center p-5">
                    <div class="mb-4">
                        <img src="view/front/images/logo.png" alt="logo" class="img-fluid" style="max-height: 80px;">
                    </div>
                    <h3 class="mb-4">Activation de votre compte</h3>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <div class="mt-4">
                            <a href="?section=front&action=sign-in" class="btn btn-primary w-100 py-2">Aller à la page de connexion</a>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <div class="mt-4">
                            <a href="?section=front&action=sign-in" class="btn btn-outline-primary w-100 py-2">Retourner à la connexion</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
