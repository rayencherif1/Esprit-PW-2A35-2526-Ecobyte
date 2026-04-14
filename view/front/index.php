<?php
/**
 * Front Office - Page d'accueil (FoodMart)
 * Charge le template HTML et remplace les chemins
 */

// Démarrer la session si elle n'est pas déjà lancée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Déterminer le chemin vers la racine du projet
$rootPath = dirname(dirname(__DIR__));

// Charger les dépendances (nécessaire si accédé directement)
if (!class_exists('UserController')) {
    require_once $rootPath . '/config.php';
    require_once $rootPath . '/controllers/UserController.php';
}

// Récupérer le contenu HTML
$htmlContent = file_get_contents(__DIR__ . '/index.html');

// Déterminer si on est accédé par le routeur racine ou directement
$isDirectAccess = (basename(dirname($_SERVER['PHP_SELF'])) === 'front');
$baseUrl = $isDirectAccess ? '../../' : '';
$resourcePath = $isDirectAccess ? '' : 'view/front/';

// Remplacer les chemins des ressources
$htmlContent = str_replace(
    ['src="images/', 'href="images/', 'href="css/', 'src="js/', 'href="index.html'],
    ['src="' . $resourcePath . 'images/', 'href="' . $resourcePath . 'images/', 'href="' . $resourcePath . 'css/', 'src="' . $resourcePath . 'js/', 'href="' . $baseUrl . 'index.php?section=front'],
    $htmlContent
);

// Remplacer l'icône de profil par un menu déroulant dynamique
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $userPhoto = !empty($_SESSION['user_photo']) ? $_SESSION['user_photo'] : 'view/front/images/user-icon.png';
    // Ajuster le chemin de la photo si accès direct
    $displayPhoto = $isDirectAccess ? '../../' . $userPhoto : $userPhoto;
    
    $profileHtml = '
    <div class="dropdown mx-1">
        <a href="#" class="rounded-circle bg-light p-0 d-flex align-items-center justify-content-center overflow-hidden" 
           style="width: 40px; height: 40px; border: 2px solid #f1f1f1;" 
           data-bs-toggle="dropdown" aria-expanded="false">
            <img src="' . $displayPhoto . '" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            <li><h6 class="dropdown-header">Bonjour, ' . htmlspecialchars($_SESSION['user_prenom']) . '</h6></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="' . $baseUrl . 'index.php?section=front&action=profile">Mon Profil</a></li>
            <li><a class="dropdown-item text-danger" href="' . $baseUrl . 'index.php?section=front&action=logout">Déconnexion</a></li>
        </ul>
    </div>';
} else {
    // Icône par défaut pour l'état non-connecté
    $defaultIcon = 'view/front/images/user-icon.png';
    $displayPhoto = $isDirectAccess ? '../../' . $defaultIcon : $defaultIcon;
    
    $profileHtml = '
    <div class="dropdown mx-1">
        <a href="#" class="rounded-circle bg-light p-0 d-flex align-items-center justify-content-center overflow-hidden" 
           style="width: 40px; height: 40px; border: 2px solid #f1f1f1;" 
           data-bs-toggle="dropdown" aria-expanded="false">
            <img src="' . $displayPhoto . '" alt="User" style="width: 70%; height: 70%; opacity: 0.6;">
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            <li><a class="dropdown-item" href="' . $baseUrl . 'index.php?section=front&action=sign-in">Connexion</a></li>
            <li><a class="dropdown-item" href="' . $baseUrl . 'index.php?section=front&action=signup">Créer un compte</a></li>
        </ul>
    </div>';
}

$pattern = '/<li>\s*<a href="#" class="rounded-circle bg-light p-2 mx-1">\s*<svg width="24" height="24" viewBox="0 0 24 24">\s*<use xlink:href="#user"><\/use>\s*<\/svg>\s*<\/a>\s*<\/li>/i';
$htmlContent = preg_replace($pattern, '<li>' . $profileHtml . '</li>', $htmlContent);

// Remplacer les liens "signin" s'il y en a
$htmlContent = str_replace(
    'href="?section=front" class="btn btn-secondary">Sign in',
    'href="?section=front&action=sign-in" class="btn btn-secondary">Sign in',
    $htmlContent
);

// Afficher le contenu
echo $htmlContent;
?>
