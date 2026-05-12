<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userInitial = 'U';
if (isset($_SESSION['user_prenom'])) {
    $userInitial = strtoupper(substr($_SESSION['user_prenom'], 0, 1));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EcoByte'; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="view/front/css/premium.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="bg-light">
    <!-- Navbar (Matching Hub) -->
    <nav class="navbar navbar-hub sticky-top mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php?section=front&action=home" class="logo text-decoration-none">
                <img src="view/front/images/logo.png" alt="EcoByte" style="height: 45px; object-fit: contain;">
            </a>
            <div class="d-flex align-items-center gap-3">
                <?php if (!isset($hideUserButton) || !$hideUserButton): ?>
                    <div class="dropdown">
                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                            <a href="#" class="user-circle shadow-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo strtoupper(substr($_SESSION['user_prenom'], 0, 1)); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Bonjour, <?php echo htmlspecialchars($_SESSION['user_prenom']); ?></h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?section=front&action=profile"><i class="fas fa-user-circle me-2"></i>Mon Profil</a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                                <form id="logout-form" action="index.php?section=front&action=logout" method="POST" style="display: none;"></form>
                            </ul>
                        <?php else: ?>
                            <a href="#" class="user-circle shadow-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user" style="font-size: 0.9rem;"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="index.php?section=front&action=sign-in"><i class="fas fa-sign-in-alt me-2"></i>Connexion</a></li>
                                <li><a class="dropdown-item" href="index.php?section=front&action=signup"><i class="fas fa-user-plus me-2"></i>Créer un compte</a></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="animate-fade-in">
