<?php
// controller/FavorisController.php
require_once __DIR__ . '/../model/Produit.php';

class FavorisController {
    private $db;
    
    public function __construct() {
        $produit = new Produit();
        $this->db = $produit->getDb();
    }
    
    // Vérifier et démarrer la session si nécessaire
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Ajouter/Retirer des favoris (toggle)
    public function add() {
        $this->checkSession();
        $produit_id = $_GET['id'] ?? 0;
        $user_id = $_SESSION['user_id'] ?? 1;
        
        if ($produit_id > 0) {
            // Vérifier si déjà dans les favoris
            $sql = "SELECT * FROM favoris WHERE user_id = :user_id AND produit_id = :produit_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
            
            if (!$stmt->fetch()) {
                // Ajouter aux favoris
                $sql = "INSERT INTO favoris (user_id, produit_id) VALUES (:user_id, :produit_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
                $_SESSION['success'] = "Produit ajouté aux favoris";
            } else {
                // Retirer des favoris
                $sql = "DELETE FROM favoris WHERE user_id = :user_id AND produit_id = :produit_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
                $_SESSION['success'] = "Produit retiré des favoris";
            }
        }
        
        // Redirection vers la page précédente
        $redirect = $_SERVER['HTTP_REFERER'] ?? '/marketplace/view/front/index2.php';
        header('Location: ' . $redirect);
        exit();
    }
    
    // Retirer des favoris (méthode séparée)
    public function remove() {
        $this->checkSession();
        $produit_id = $_GET['id'] ?? 0;
        $user_id = $_SESSION['user_id'] ?? 1;
        
        if ($produit_id > 0) {
            $sql = "DELETE FROM favoris WHERE user_id = :user_id AND produit_id = :produit_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
            $_SESSION['success'] = "Produit retiré des favoris";
        }
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/marketplace/view/front/index2.php'));
        exit();
    }
    
    // Récupérer les favoris d'un utilisateur
    public function getFavoris() {
        $this->checkSession();
        $user_id = $_SESSION['user_id'] ?? 1;
        
        $sql = "SELECT p.* FROM produits p 
                JOIN favoris f ON p.id = f.produit_id 
                WHERE f.user_id = :user_id 
                ORDER BY f.date_ajout DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    }
    
    // Vérifier si un produit est favori
    public function isFavori($produit_id) {
        $this->checkSession();
        $user_id = $_SESSION['user_id'] ?? 1;
        
        $sql = "SELECT * FROM favoris WHERE user_id = :user_id AND produit_id = :produit_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
        return $stmt->fetch() !== false;
    }
    
    // Afficher la page des favoris
    public function index() {
        $this->checkSession();
        $favoris = $this->getFavoris();
        require_once __DIR__ . '/../view/front/favoris.php';
    }
}
?>