<?php
/**
 * Point d'entrée principal - Routeur de l'application
 * Gère le routage avec $_GET['action'] et $_GET['section']
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEBUG: Log the request and session state
$logData = date('Y-m-d H:i:s') . " - URI: " . $_SERVER['REQUEST_URI'] . " - ROLE: " . ($_SESSION['user_role'] ?? 'NONE') . " - LOGGED_IN: " . (isset($_SESSION['logged_in']) ? 'YES' : 'NO') . "\n";
file_put_contents(__DIR__ . '/session_debug.log', $logData, FILE_APPEND);


require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ProfilController.php';
require_once __DIR__ . '/controllers/AdminProfileController.php';

// Mettre à jour la date de dernière activité si l'utilisateur est connecté (Front ou Back)
$activityController = new UserController();
if (isset($_SESSION['user_id'])) {
    $activityController->updateLastActivity($_SESSION['user_id']);
}
if (isset($_SESSION['admin_id'])) {
    $activityController->updateLastActivity($_SESSION['admin_id']);
}

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
            case 'users':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $search = $_GET['search'] ?? null;
                $sort = $_GET['sort'] ?? 'date_creation';
                $order = $_GET['order'] ?? 'DESC';
                
                $users = $userController->listUsers($search, $sort, $order);
                
                if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
                    require __DIR__ . '/view/back/users_list_partial.php';
                    exit;
                }
                
                require __DIR__ . '/view/back/users.php';
                break;

            case 'exportPDF':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $search = $_GET['search'] ?? null;
                $sort = $_GET['sort'] ?? 'date_creation';
                $order = $_GET['order'] ?? 'DESC';
                
                $users = $userController->listUsers($search, $sort, $order);
                require __DIR__ . '/view/back/export-users-pdf.php';
                break;

            case 'addUser':
            case 'editUser':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
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
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $userController->deleteUser((int)$_GET['id']);
                header('Location: ?section=back&action=users');
                exit;

            case 'banUser':
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
                    exit;
                }
                $userController = new UserController();
                $userController->banUser((int)$_GET['id'], $_GET['duration'] ?? '1d');
                header('Location: ?section=back&action=users');
                exit;

            case 'logout':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userController = new UserController();
                    $userController->logout(true); // admin
                }
                header('Location: ?section=front&action=sign-in');
                exit;

            case 'sign-in':
            case 'home':
            default:
                if (!isset($_SESSION['admin_logged_in'])) {
                    header('Location: ?section=front&action=sign-in');
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

            case 'activate':
                // Page d'activation
                require __DIR__ . '/view/front/activate.php';
                break;

            case 'forgot-password':
                // Page mot de passe oublié
                require __DIR__ . '/view/front/forgot-password.php';
                break;

            case 'verify-code':
                // Page de vérification du code
                require __DIR__ . '/view/front/verify-code.php';
                break;

            case 'change-password':
                // Page de changement de mot de passe
                require __DIR__ . '/view/front/change-password.php';
                break;

            case 'get-face-descriptors':
                header('Content-Type: application/json');
                $userController = new UserController();
                $descriptors = $userController->getAllFaceDescriptors();
                $result = [];
                foreach ($descriptors as $d) {
                    $arr = json_decode($d['webauthn_public_key'], true);
                    if (is_array($arr)) {
                        $result[] = [
                            'userId' => $d['id'],
                            'nom' => $d['prenom'] . ' ' . $d['nom'],
                            'descriptor' => $arr
                        ];
                    }
                }
                echo json_encode($result);
                exit;

            case 'webauthn-login':
                header('Content-Type: application/json');
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    $userId = $input['userId'] ?? null;
                    
                    if ($userId) {
                        $userController = new UserController();
                        $user = $userController->loginWithFaceId($userId);
                        if ($user) {
                            // Redirection basée sur le rôle de l'utilisateur connecté (jamais admin via Face ID)
                            $redirect = '?section=front&action=home';
                            echo json_encode(['success' => true, 'redirect' => $redirect]);
                            exit;
                        } else {
                            echo json_encode(['success' => false, 'message' => $userController->getErrors()[0] ?? 'Erreur']);
                            exit;
                        }
                    }
                }
                echo json_encode(['success' => false, 'message' => 'Requête invalide']);
                exit;

            case 'webauthn-register':
                header('Content-Type: application/json');
                if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
                    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
                    exit;
                }
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    $descriptor = $input['descriptor'] ?? null;
                    
                    if ($descriptor) {
                        $userController = new UserController();
                        if ($userController->registerFaceDescriptor($_SESSION['user_id'], json_encode($descriptor))) {
                            echo json_encode(['success' => true]);
                            exit;
                        }
                    }
                }
                echo json_encode(['success' => false, 'message' => 'Erreur d\'enregistrement']);
                exit;

            case 'logout':
                // Déconnexion Front Office (uniquement via POST pour éviter le prefetch)
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userController = new UserController();
                    $userController->logout(false); // false = user
                }
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
