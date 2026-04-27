<?php
// controller/ProduitController.php
require_once __DIR__ . '/../model/Produit.php';

class ProduitController {
    private $db;
    
    public function __construct() {
        $produit = new Produit();
        $this->db = $produit->getDb();
    }
    
    // Vérifier si l'utilisateur est connecté
    private function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /marketplace/index.php?controller=auth&action=login');
            exit();
        }
    }
    
    // Récupérer tous les produits
    public function getAllProduits() {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.categorie_id = c.id 
                ORDER BY p.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer les produits par catégorie
    public function getProduitsByCategorie($categorie_id) {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.categorie_id = c.id 
                WHERE p.categorie_id = :categorie_id
                ORDER BY p.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['categorie_id' => $categorie_id]);
        return $stmt->fetchAll();
    }
    
    // Récupérer les produits en promotion
    public function getProduitsEnPromo() {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.categorie_id = c.id 
                WHERE p.is_promo = 1
                ORDER BY p.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer les produits tendances (les plus vendus)
    public function getProduitsTendances() {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.categorie_id = c.id 
                ORDER BY p.ventes DESC LIMIT 15";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer les nouveautés
    public function getNouveauxProduits() {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.categorie_id = c.id 
                ORDER BY p.date_ajout DESC LIMIT 15";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Rechercher des produits
    public function searchProduits($query) {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.categorie_id = c.id 
                WHERE p.nom LIKE :query OR p.description LIKE :query OR c.nom LIKE :query
                ORDER BY p.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['query' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }
    
    // Récupérer un produit par son ID
    public function getProduitById($id) {
        $sql = "SELECT * FROM produits WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    // Ajouter un produit (avec catégorie obligatoire)
    public function createProduit($nom, $prix, $stock, $description, $categorie_id) {
        if (empty($categorie_id)) {
            return false;
        }
        
        $sql = "INSERT INTO produits (nom, prix, stock, description, categorie_id) 
                VALUES (:nom, :prix, :stock, :description, :categorie_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nom' => $nom,
            'prix' => $prix,
            'stock' => $stock,
            'description' => $description,
            'categorie_id' => $categorie_id
        ]);
    }
    
    // Modifier un produit
    public function updateProduit($id, $nom, $prix, $stock, $description, $categorie_id) {
        $sql = "UPDATE produits SET 
                nom = :nom, 
                prix = :prix, 
                stock = :stock, 
                description = :description, 
                categorie_id = :categorie_id 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nom' => $nom,
            'prix' => $prix,
            'stock' => $stock,
            'description' => $description,
            'categorie_id' => $categorie_id
        ]);
    }
    
    // Supprimer un produit
    public function deleteProduit($id) {
        $sql = "DELETE FROM produits WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // ========== MÉTHODES POUR LE ROUTEUR ==========
    
    public function index() {
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function create() {
        header('Location: /marketplace/view/back/pages/marketplace.php?action=add_product');
        exit();
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prix = $_POST['prix'] ?? 0;
            $stock = $_POST['stock'] ?? 0;
            $description = $_POST['description'] ?? '';
            $categorie_id = $_POST['categorie_id'] ?? null;
            
            if (!empty($nom) && $prix > 0 && !empty($categorie_id)) {
                try {
                    $this->createProduit($nom, $prix, $stock, $description, $categorie_id);
                    $_SESSION['success_message'] = 'Produit ajouté avec succès!';
                } catch (Exception $e) {
                    $_SESSION['error_message'] = 'Erreur lors de l\'ajout du produit: ' . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = 'Veuillez remplir tous les champs obligatoires (nom, prix, catégorie)';
            }
        }
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function edit() {
        $id = $_GET['id'] ?? 0;
        header('Location: /marketplace/view/back/pages/marketplace.php?action=edit_product&id=' . $id);
        exit();
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nom = $_POST['nom'] ?? '';
            $prix = $_POST['prix'] ?? 0;
            $stock = $_POST['stock'] ?? 0;
            $description = $_POST['description'] ?? '';
            $categorie_id = $_POST['categorie_id'] ?? null;
            
            if ($id > 0 && !empty($nom)) {
                $this->updateProduit($id, $nom, $prix, $stock, $description, $categorie_id);
            }
        }
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        if ($id > 0) {
            $this->deleteProduit($id);
        }
        
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function front() {
        $produits = $this->getAllProduits();
        require_once __DIR__ . '/../view/front/index2.php';
    }
}
?>