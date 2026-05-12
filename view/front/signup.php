<?php
/**
 * Page d'inscription - Front Office (Client)
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController = new UserController();
    
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation basique côté serveur
    if ($password !== $confirm_password) {
        $errors = ["Les mots de passe ne correspondent pas."];
    } else {
        $success = $userController->register($nom, $prenom, $email, $identifiant, $password);
        if ($success) {
            header('Location: ?section=front&action=sign-in&registered=1');
            exit;
        } else {
            $errors = $userController->getErrors();
        }
    }
}

$errors = $errors ?? [];

$pageTitle = "Inscription - EcoByte";
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
        background: linear-gradient(135deg, #4caf50, #2e7d32);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 14px;
        font-weight: 700;
        transition: all 0.3s;
    }
    .btn-premium-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(76,175,80,0.2);
        color: white;
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
            <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                <div class="auth-card">
                    <h2 class="text-center mb-4 fw-bold" style="color: #1e293b;">Créer un compte</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
                            <?php foreach ($errors as $error): ?>
                                <div class="small"><i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="signupForm">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Nom</label>
                                <input type="text" class="form-control" name="nom" placeholder="Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Prénom</label>
                                <input type="text" class="form-control" name="prenom" placeholder="John" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="john@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Identifiant</label>
                            <input type="text" class="form-control" name="identifiant" placeholder="johndoe123" required>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Mot de passe</label>
                                <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Confirmer</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-premium-auth w-100 py-3">
                            S'inscrire maintenant
                        </button>
                    </form>

                    <div class="text-center mt-5">
                        <p class="text-muted small">Vous avez déjà un compte ?</p>
                        <a href="?section=front&action=sign-in" class="text-decoration-none fw-bold" style="color: #4caf50;">Se connecter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
require __DIR__ . '/layout_footer.php'; 
?>
