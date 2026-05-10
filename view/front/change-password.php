<?php
/**
 * Étape 3 : Nouveau mot de passe
 */

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['reset_email_verified'])) {
    header('Location: ?section=front&action=forgot-password');
    exit;
}

$userController = new UserController();
$errors = [];
$success = '';
$email = $_SESSION['reset_email_verified'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($userController->resetPassword($email, $password, $confirm_password)) {
        $success = $userController->getSuccess();
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
    <title>Nouveau mot de passe - FoodMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="view/front/style.css">
</head>
<body class="bg-light">
    <section class="py-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">
                    <div class="card shadow border-0">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">Nouveau mot de passe</h2>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <?php echo htmlspecialchars($success); ?>
                                    <div class="mt-3">
                                        <a href="?section=front&action=sign-in" class="btn btn-success w-100">Se connecter</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo htmlspecialchars($error); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nouveau mot de passe</label>
                                        <input type="password" name="password" class="form-control" placeholder="Min 6 caractères" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirmez" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 py-2">Mettre à jour</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
