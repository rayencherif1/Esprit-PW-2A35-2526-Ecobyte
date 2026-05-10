<?php
// controller/CategorieController.php
require_once __DIR__ . '/../model/Categorie.php';

class CategorieController {
    private $db;
    
    public function __construct() {
        $categorie = new Categorie();
        $this->db = $categorie->getDb();
    }
    
    // Récupérer toutes les catégories
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer une catégorie par son ID
    public function getCategorieById($id) {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    // Ajouter une catégorie
    public function createCategorie($nom, $description) {
        $sql = "INSERT INTO categories (nom, description) VALUES (:nom, :description)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nom' => $nom,
            'description' => $description
        ]);
    }
    
    // Modifier une catégorie
    public function updateCategorie($id, $nom, $description) {
        $sql = "UPDATE categories SET nom = :nom, description = :description WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nom' => $nom,
            'description' => $description
        ]);
    }
    
    // Supprimer une catégorie
    public function deleteCategorie($id) {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    // ========== MÉTHODES POUR LE ROUTEUR ==========
    
    public function index() {
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function create() {
        header('Location: /marketplace/view/back/pages/marketplace.php?action=add_category');
        exit();
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (!empty($nom)) {
                try {
                    $this->createCategorie($nom, $description);
                    $_SESSION['success_message'] = 'Catégorie ajoutée avec succès!';
                } catch (Exception $e) {
                    $_SESSION['error_message'] = 'Erreur lors de l\'ajout de la catégorie: ' . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = 'Le nom de la catégorie est obligatoire';
            }
        }
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function edit() {
        $id = $_GET['id'] ?? 0;
        header('Location: /marketplace/view/back/pages/marketplace.php?action=edit_category&id=' . $id);
        exit();
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (!empty($nom) && $id > 0) {
                $this->updateCategorie($id, $nom, $description);
            }
        }
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        if ($id > 0) {
            $this->deleteCategorie($id);
        }
        
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
}
?>