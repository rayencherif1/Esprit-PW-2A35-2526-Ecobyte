<?php
// index.php - Routeur principal
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'controller/ProduitController.php';
require_once 'controller/CategorieController.php';
require_once 'controller/CommandeController.php';
require_once 'controller/AuthController.php';  // ← NOUVEAU

$controller = $_GET['controller'] ?? 'produit';
$action = $_GET['action'] ?? 'index';

switch($controller) {
    case 'produit':
        $controllerInstance = new ProduitController();
        break;
    case 'categorie':
        $controllerInstance = new CategorieController();
        break;
    case 'commande':
        $controllerInstance = new CommandeController();
        break;
    case 'auth':                              // ← NOUVEAU
        $controllerInstance = new AuthController();
        break;
    default:
        $controllerInstance = new ProduitController();
}

if(method_exists($controllerInstance, $action)) {
    $controllerInstance->$action();
} else {
    echo "Erreur 404 : Action '$action' non trouvée dans le contrôleur '$controller'";
}
?>