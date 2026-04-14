<?php
/**
 * Point d'entrée principal - Routeur de l'application
 * Gère le routage avec $_GET['action'] et $_GET['section']
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ProfilController.php';
require_once __DIR__ . '/controllers/AdminProfileController.php';

// Récupérer la section (front ou back)
$section = isset($_GET['section']) ? $_GET['section'] : 'back';  // Par défaut: back office (admin)

// Récupérer l'action demandée (par défaut: users)
$action = isset($_GET['action']) ? $_GET['action'] : 'users';

// Initialiser les variables pour les vues
$errors = [];
$success = '';
$users = [];
$user = null;

try {
    // Créer un contrôleur pour récupérer tous les profils
    $profilController = new ProfilController();
    $profils = [];
    
    if ($section === 'back') {
        // ========== BACK OFFICE (ADMIN) ==========
        
        switch ($action) {
            case 'sign-in':
                // Page de connexion admin
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';
                    
                    if ($email === 'admin2026' && $password === 'adminadmin') {
                        $_SESSION['admin_logged_in'] = true;
                        header('Location: ?section=back&action=users');
                        exit;
                    } else {
                        $errors[] = "Identifiants admin incorrects";
                    }
                }
                require __DIR__ . '/view/back/sign-in.php';
                break;

            case 'logout':
                unset($_SESSION['admin_logged_in']);
                header('Location: ?section=back&action=sign-in');
                exit;

            case 'users':
                // Vérifier si l'admin est connecté
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=back&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $users = $userController->listUsers();
                require __DIR__ . '/view/back/users.php';
                break;

            case 'addUser':
            case 'editUser':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=back&action=sign-in');
                    exit;
                }
                
                $userController = new UserController();
                $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
                $user = $userId ? $userController->getUser($userId) : null;

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if ($userId) {
                        $result = $userController->updateUser($userId, $_POST);
                    } else {
                        $result = $userController->createUser($_POST);
                    }

                    if ($result) {
                        header('Location: ?section=back&action=users');
                        exit;
                    } else {
                        $errors = $userController->getErrors();
                        require __DIR__ . '/view/back/add-user.php';
                    }
                } else {
                    require __DIR__ . '/view/back/add-user.php';
                }
                break;

            case 'deleteUser':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=back&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $userController->deleteUser((int)$_GET['id']);
                header('Location: ?section=back&action=users');
                exit;

            case 'home':
            default:
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=back&action=sign-in');
                    exit;
                }
                header('Location: ?section=back&action=users');
                exit;
        }
    } else {
        // ========== FRONT OFFICE (PUBLIC) ==========
        
        switch ($action) {
            case 'sign-in':
                // Page de connexion client
                require __DIR__ . '/view/front/sign-in.php';
                break;

            case 'signup':
                // Page d'inscription client
                require __DIR__ . '/view/front/signup.php';
                break;

            case 'logout':
                // Déconnexion
                $userController = new UserController();
                $userController->logout();
                header('Location: ?section=front&action=home');
                exit;

            case 'profile':
                // Page de profil client
                require __DIR__ . '/view/front/profile.php';
                break;

            case 'shop':
            case 'home':
            default:
                // Page shop/accueil front (template FoodMart)
                require __DIR__ . '/view/front/index.php';
                break;
        }
    }
} catch (Exception $e) {
    // Gestion des erreurs non prévues
    $errors[] = htmlspecialchars($e->getMessage());
    if ($section === 'back') {
        require __DIR__ . '/view/back/error.php';
    } else {
        require __DIR__ . '/view/front/error.php';
    }
}
?>
