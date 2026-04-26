<?php
// controller/AuthController.php
class AuthController {
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vider toutes les variables de session
        $_SESSION = array();
        
        // Supprimer le cookie de session si présent
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Détruire la session
        session_destroy();
        
        // Rediriger vers l'accueil
        header('Location: /marketplace/view/front/index2.php');
        exit();
    }
    
    public function login() {
        // Afficher la page de connexion admin
        require_once __DIR__ . '/../view/auth/login.php';
        exit();
    }
    
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Identifiants admin (à modifier selon vos besoins)
            if ($username === 'admin' && $password === 'admin123') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['logged_in'] = true;
                $_SESSION['admin_user'] = $username;
                
                header('Location: /marketplace/view/back/pages/marketplace.php');
                exit();
            } else {
                $error = 'Identifiants incorrects';
                require_once __DIR__ . '/../view/auth/login.php';
                exit();
            }
        }
        header('Location: /marketplace/index.php?controller=auth&action=login');
        exit();
    }
}
?>