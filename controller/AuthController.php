<?php
// controller/AuthController.php

class AuthController {
    
    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        require_once __DIR__ . '/../view/auth/login.php';
    }
    
    public function authenticate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $valid_username = 'admin';
            $valid_password = 'ecobite2026';
            
            if ($username === $valid_username && $password === $valid_password) {
                $_SESSION['logged_in'] = true;
                header('Location: /marketplace/view/back/pages/marketplace.php');
                exit();
            } else {
                $error = "Identifiants incorrects";
                require_once __DIR__ . '/../view/auth/login.php';
            }
        }
    }
    
    public function logout() {
        session_start();
        session_destroy();
        header('Location: /marketplace/view/front/index2.php');
        exit();
    }
}