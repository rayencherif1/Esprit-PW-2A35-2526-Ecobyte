<?php
// controller/CommandeController.php
require_once __DIR__ . '/../model/Commande.php';
require_once __DIR__ . '/../model/Produit.php';

class CommandeController {
    private $commandeModel;
    private $produitModel;
    
    public function __construct() {
        $this->commandeModel = new Commande();
        $this->produitModel = new Produit();
    }
    
    // Vérifier si l'utilisateur est connecté (pour Back Office)
    private function checkAuth() {
        session_start();
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /marketplace/index.php?controller=auth&action=login');
            exit();
        }
    }
    
    // ========== BACK OFFICE (protégé) ==========
    
    // Afficher la liste des commandes
    public function index() {
        $this->checkAuth();
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    // Afficher le détail d'une commande
    public function detail() {
        $this->checkAuth();
        $id = $_GET['id'] ?? 0;
        header('Location: /marketplace/view/back/pages/marketplace.php?action=detail_commande&id=' . $id);
        exit();
    }
    
    // Supprimer une commande
    public function delete() {
        $this->checkAuth();
        $id = $_GET['id'] ?? 0;
        $this->commandeModel->delete($id);
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    // ========== FRONT OFFICE (public) ==========
    
    // Afficher le panier
    public function panier() {
        session_start();
        $panier = $_SESSION['panier'] ?? [];
        $produits = [];
        $total = 0;
        
        foreach($panier as $produit_id => $quantite) {
            $produit = $this->produitModel->getById($produit_id);
            if($produit) {
                $produit['quantite'] = $quantite;
                $produit['sous_total'] = $produit['prix'] * $quantite;
                $total += $produit['sous_total'];
                $produits[] = $produit;
            }
        }
        
        require_once __DIR__ . '/../view/front/panier.php';
    }
    
    // Ajouter au panier
    public function addToPanier() {
        session_start();
        $produit_id = $_GET['id'] ?? 0;
        $quantite = $_GET['quantite'] ?? 1;
        
        if(!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
        
        if(isset($_SESSION['panier'][$produit_id])) {
            $_SESSION['panier'][$produit_id] += $quantite;
        } else {
            $_SESSION['panier'][$produit_id] = $quantite;
        }
        
        header('Location: /marketplace/index.php?controller=commande&action=panier');
        exit();
    }
    
    // Retirer du panier
    public function removeFromPanier() {
        session_start();
        $produit_id = $_GET['id'] ?? 0;
        
        if(isset($_SESSION['panier'][$produit_id])) {
            unset($_SESSION['panier'][$produit_id]);
        }
        
        header('Location: /marketplace/index.php?controller=commande&action=panier');
        exit();
    }
    
    // Valider la commande
    public function checkout() {
        session_start();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_nom = $_POST['client_nom'] ?? '';
            $client_email = $_POST['client_email'] ?? '';
            $panier = $_SESSION['panier'] ?? [];
            
            $errors = [];
            if(empty($client_nom)) {
                $errors[] = "Le nom est obligatoire";
            }
            if(empty($client_email) || !filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email valide obligatoire";
            }
            if(empty($panier)) {
                $errors[] = "Votre panier est vide";
            }
            
            if(empty($errors)) {
                $total = 0;
                foreach($panier as $produit_id => $quantite) {
                    $produit = $this->produitModel->getById($produit_id);
                    $total += $produit['prix'] * $quantite;
                }
                
                $commande_id = $this->commandeModel->create($client_nom, $client_email, $total);
                
                foreach($panier as $produit_id => $quantite) {
                    $this->commandeModel->addProduit($commande_id, $produit_id, $quantite);
                }
                
                $_SESSION['panier'] = [];
                header('Location: /marketplace/index.php?controller=commande&action=confirmation&id=' . $commande_id);
                exit();
            } else {
                $produits = [];
                $total = 0;
                foreach($panier as $produit_id => $quantite) {
                    $produit = $this->produitModel->getById($produit_id);
                    if($produit) {
                        $produit['quantite'] = $quantite;
                        $produit['sous_total'] = $produit['prix'] * $quantite;
                        $total += $produit['sous_total'];
                        $produits[] = $produit;
                    }
                }
                require_once __DIR__ . '/../view/front/panier.php';
            }
        }
    }
    
    // Récupérer toutes les commandes
    public function getAllCommandes() {
        return $this->commandeModel->getAll();
    }
    
    // ========== MÉTHODE MANQUANTE AJOUTÉE ==========
    // Récupérer les produits d'une commande spécifique
    public function getProduitsByCommandeId($commande_id) {
        return $this->commandeModel->getProduitsByCommandeId($commande_id);
    }
    
    // Page de confirmation
    public function confirmation() {
        $id = $_GET['id'] ?? 0;
        $commande = $this->commandeModel->getById($id);
        $produits = $this->commandeModel->getProduitsByCommandeId($id);
        require_once __DIR__ . '/../view/front/confirmation.php';
    }
}
?>