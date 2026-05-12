<?php
// ecobiteweb/View/Front-Office/send_allergie_email.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/MailController.php';
require_once __DIR__ . '/../../Controller/allergie.Controller.php';
require_once __DIR__ . '/../../Controller/traitement.Controller.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $destinataire = filter_var($_POST['email_destinataire'] ?? '', FILTER_SANITIZE_EMAIL);
    $nom_proche = htmlspecialchars($_POST['nom_proche'] ?? 'Quelqu\'un');
    $id_allergie = (int)($_POST['id_allergie'] ?? 0);
    $message_perso = htmlspecialchars($_POST['message_perso'] ?? '');
    
    $errors = [];
    
    if (empty($destinataire) || !filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    if (empty($nom_proche)) {
        $errors[] = "Veuillez entrer votre nom.";
    }
    
    if ($id_allergie <= 0) {
        $errors[] = "Allergie non valide.";
    }
    
    if (empty($errors)) {
        $allergieController = new AllergieC();
        $allergie = $allergieController->getAllergieById($id_allergie);
        
        if ($allergie) {
            $traitementController = new TraitementC();
            $traitements = $traitementController->listTraitementByAllergie($id_allergie);
            
            if (!is_array($traitements)) {
                $traitements = [];
            }
            
            $mailController = new MailController();
            $sent = $mailController->sendAllergieEmail(
                $destinataire, $nom_proche, $allergie, $traitements, $message_perso
            );
            
            if ($sent) {
                $_SESSION['email_success'] = "✅ Fiche envoyée avec succès à {$destinataire} !";
            } else {
                $_SESSION['email_error'] = "❌ Erreur lors de l'envoi.";
            }
        } else {
            $_SESSION['email_error'] = "Allergie non trouvée.";
        }
    } else {
        $_SESSION['email_error'] = implode(" ", $errors);
    }
    
    // Nettoyer et rediriger
    if (ob_get_level()) ob_clean();
    $redirect_url = $_SERVER['HTTP_REFERER'] ?? 'front_allergies.php';
    header("Location: $redirect_url");
    exit();
}
?>