<?php
// controller/AuthController.php
session_start();

class AuthController {
    
    // Afficher le formulaire de login
    public function login() {
        require_once __DIR__ . '/../view/auth/login.php';
    }
    
    // Vérifier les identifiants
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Identifiants
            $valid_username = 'admin';
            $valid_password = 'ecobite2026';
            
            if ($username === $valid_username && $password === $valid_password) {
                $_SESSION['logged_in'] = true;
                // REDIRECTION VERS LE BACK OFFICE ARGON
                header('Location: /marketplace/view/back/pages/marketplace.php');
                exit();
            } else {
                $error = "Identifiants incorrects";
                require_once __DIR__ . '/../view/auth/login.php';
            }
        }
    }
    
    // Déconnexion
    public function logout() {
        session_destroy();
        // REDIRECTION VERS LE FRONT OFFICE FOODMART
        header('Location: /marketplace/view/front/index.php');
        exit();
    }
}
?>