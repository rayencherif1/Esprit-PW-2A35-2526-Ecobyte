<?php
/**
 * EcoByte Hub - Point d'entrée principal (MVC)
 * Gère le routage centralisé de tous les modules.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controller/UserController.php';
require_once __DIR__ . '/controller/ProfilController.php';

$section = $_GET['section'] ?? 'front';
$action = $_GET['action'] ?? 'home';

try {
    if ($section === 'back') {
        // ========== BACK OFFICE (ADMINISTRATION) ==========
        switch ($action) {
            case 'users':
                if (!isset($_SESSION['admin_logged_in'])) { header('Location: ?section=front&action=sign-in'); exit; }
                $userController = new UserController();
                $users = $userController->listUsers($_GET['search'] ?? null, $_GET['sort'] ?? 'date_creation', $_GET['order'] ?? 'DESC');
                require __DIR__ . '/view/back/users.php';
                break;
            
            case 'sign-in':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userController = new UserController();
                    if ($userController->login($_POST['email'], $_POST['password']) && isset($_SESSION['admin_logged_in'])) {
                        header('Location: ?section=back&action=users'); exit;
                    }
                }
                require __DIR__ . '/view/back/sign-in.php';
                break;

            case 'logout':
                $userController = new UserController();
                $userController->logout(true);
                header('Location: ?section=front&action=sign-in');
                exit;

            default:
                header('Location: ?section=back&action=users');
                exit;
        }
    } else {
        // ========== FRONT OFFICE (CLIENT) ==========
        switch ($action) {
            case 'home':
                require __DIR__ . '/view/front/hub.php';
                break;

            case 'sign-in':
                require __DIR__ . '/view/front/sign-in.php';
                break;

            case 'signup':
                require __DIR__ . '/view/front/signup.php';
                break;

            case 'profile':
                if (!isset($_SESSION['logged_in'])) { header('Location: ?section=front&action=sign-in'); exit; }
                require __DIR__ . '/view/front/profile.php';
                break;

            case 'kitchen':
                require __DIR__ . '/view/front/front.php';
                break;

            case 'health':
                require __DIR__ . '/view/front/allergy_report.php';
                break;

            case 'blog':
                require __DIR__ . '/view/front/blog.php';
                break;

            case 'ai':
                require __DIR__ . '/view/front/chatbot.php';
                break;

            case 'fitness':
                header('Location: /2int/public/index.php?action=home');
                exit;

            case 'shop':
                header('Location: /2int/view/back_boutique/index.php');
                exit;

            case 'logout':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $userController = new UserController();
                    $userController->logout(false);
                }
                header('Location: ?section=front&action=home');
                exit;

            // API FACE ID
            case 'get-face-descriptors':
                header('Content-Type: application/json');
                $userController = new UserController();
                $descriptors = $userController->getAllFaceDescriptors();
                $result = [];
                foreach ($descriptors as $d) {
                    $arr = json_decode($d['webauthn_public_key'], true);
                    if (is_array($arr)) {
                        $result[] = ['userId' => $d['id'], 'descriptor' => $arr];
                    }
                }
                echo json_encode($result);
                exit;

            case 'webauthn-login':
                header('Content-Type: application/json');
                $input = json_decode(file_get_contents('php://input'), true);
                $userController = new UserController();
                if ($userController->loginWithFaceId($input['userId'] ?? null)) {
                    echo json_encode(['success' => true, 'redirect' => '?section=front&action=home']);
                } else {
                    echo json_encode(['success' => false]);
                }
                exit;

            case 'webauthn-register':
                header('Content-Type: application/json');
                $input = json_decode(file_get_contents('php://input'), true);
                $userController = new UserController();
                if ($userController->registerFaceDescriptor($_SESSION['user_id'], json_encode($input['descriptor']))) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false]);
                }
                exit;

            default:
                require __DIR__ . '/view/front/hub.php';
                break;
        }
    }
} catch (Exception $e) {
    die("Erreur système : " . $e->getMessage());
}
