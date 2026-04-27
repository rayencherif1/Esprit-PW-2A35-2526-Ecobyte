<?php
/**
 * Étape 2 : Vérification du code
 */

$userController = new UserController();
$errors = [];
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    
    if ($userController->verifyCode($email, $code)) {
        header('Location: ?section=front&action=change-password');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du code - FoodMart</title>
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
                            <h2 class="text-center mb-4">Vérification</h2>
                            <p class="text-muted text-center mb-4">Entrez le code à 6 chiffres envoyé à <strong><?php echo htmlspecialchars($email); ?></strong></p>

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
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                <div class="mb-4">
                                    <label class="form-label">Code de vérification</label>
                                    <input type="text" name="code" class="form-control text-center fw-bold fs-4" placeholder="000000" maxlength="6" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2">Vérifier</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
