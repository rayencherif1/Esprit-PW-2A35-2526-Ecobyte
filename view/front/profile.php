<?php
/**
 * Page de profil utilisateur - Front Office (Client)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ?section=front&action=sign-in');
    exit;
}

$userId = $_SESSION['user_id'];
$userController = new UserController();
$user = $userController->getUser($userId);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($userController->updateProfile($userId, $_POST, $_FILES)) {
        $success = $userController->getSuccess();
        $user = $userController->getUser($userId); // Refresh data
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
    <title>Mon Profil - FoodMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="view/front/style.css">
</head>
<body class="bg-light">
    <header>
        <div class="container-fluid">
            <div class="row py-3 border-bottom bg-white">
                <div class="col-sm-4 col-lg-3 text-center text-sm-start">
                    <div class="main-logo">
                        <a href="?section=front">
                            <img src="/recette/public/image/logo.png" alt="logo" class="img-fluid" style="max-height: 45px;">
                        </a>
                    </div>
                </div>
                <div class="col-sm-8 col-lg-9 d-flex justify-content-end gap-3 align-items-center mt-4 mt-sm-0">
                    <a href="?section=front" class="btn btn-sm btn-outline-secondary">Retour au magasin</a>
                    <a href="?section=front&action=logout" class="btn btn-sm btn-danger">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow border-0">
                        <div class="card-body p-5">
                            <h2 class="card-title mb-4">Modifier mes informations</h2>

                            <?php if ($success): ?>
                                <div class="alert alert-success mt-3"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger mt-3">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data">
                                <div class="text-center mb-4">
                                    <div class="mb-3">
                                        <?php 
                                        $displayPhoto = !empty($user['photo']) ? $user['photo'] : 'view/front/images/user-icon.png';
                                        ?>
                                        <img src="<?php echo $displayPhoto; ?>" alt="Profile" 
                                             class="rounded-circle shadow-sm" 
                                             style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #fff;">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label btn btn-sm btn-outline-primary">
                                            Changer la photo
                                            <input type="file" name="photo" class="d-none" accept="image/*">
                                        </label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nom</label>
                                        <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Prénom</label>
                                        <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Numéro de téléphone</label>
                                    <input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Poids (kg)</label>
                                        <input type="number" step="0.1" name="poids" class="form-control" value="<?php echo htmlspecialchars($user['poids'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Taille (cm)</label>
                                        <input type="number" step="0.1" name="taille" class="form-control" value="<?php echo htmlspecialchars($user['taille'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                                    <input type="password" name="password" class="form-control">
                                </div>

                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-5 btn-lg">Enregistrer les modifications</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php exit; ?>
