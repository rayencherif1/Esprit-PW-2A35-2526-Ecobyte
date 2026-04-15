<?php
// controller/CategorieController.php
require_once __DIR__ . '/../model/Categorie.php';

class CategorieController {
    private $categorieModel;
    
    public function __construct() {
        $this->categorieModel = new Categorie();
    }
    
    // Afficher la liste des catégories
    public function index() {
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    // Afficher le formulaire d'ajout
    public function create() {
        header('Location: /marketplace/view/back/pages/marketplace.php?action=add_category');
        exit();
    }
    
    // Enregistrer une nouvelle catégorie
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (!empty($nom)) {
                $this->categorieModel->create($nom, $description);
            }
            header('Location: /marketplace/view/back/pages/marketplace.php');
            exit();
        }
    }
    
    // Afficher le formulaire de modification
    public function edit() {
        $id = $_GET['id'] ?? 0;
        header('Location: /marketplace/view/back/pages/marketplace.php?action=edit_category&id=' . $id);
        exit();
    }
    
    // Mettre à jour une catégorie
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';
            $this->categorieModel->update($id, $nom, $description);
            header('Location: /marketplace/view/back/pages/marketplace.php');
            exit();
        }
    }
    
    // Supprimer une catégorie
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->categorieModel->delete($id);
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    // Récupérer toutes les catégories
    public function getAllCategories() {
        return $this->categorieModel->getAll();
    }
}
?>