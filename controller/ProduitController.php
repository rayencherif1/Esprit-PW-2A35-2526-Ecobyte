<?php
// controller/ProduitController.php
require_once __DIR__ . '/../model/Produit.php';

class ProduitController {
    private $produitModel;
    
    public function __construct() {
        $this->produitModel = new Produit();
    }
    
    // Afficher la liste des produits (redirection vers Argon)
    public function index() {
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    // Afficher le formulaire d'ajout
    public function create() {
        header('Location: /marketplace/view/back/pages/marketplace.php?action=add_product');
        exit();
    }
    
    // Enregistrer un nouveau produit
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prix = $_POST['prix'] ?? 0;
            $stock = $_POST['stock'] ?? 0;
            $description = $_POST['description'] ?? '';
            $categorie_id = $_POST['categorie_id'] ?? null;
            
            $errors = [];
            if (empty($nom)) $errors[] = "Le nom est obligatoire";
            if ($prix <= 0) $errors[] = "Le prix doit être > 0";
            if ($stock < 0) $errors[] = "Le stock ne peut pas être négatif";
            
            if (empty($errors)) {
                $this->produitModel->create($nom, $prix, $stock, $description, $categorie_id);
            }
            header('Location: /marketplace/view/back/pages/marketplace.php');
            exit();
        }
    }
    
    // Afficher le formulaire de modification
    public function edit() {
        $id = $_GET['id'] ?? 0;
        header('Location: /marketplace/view/back/pages/marketplace.php?action=edit_product&id=' . $id);
        exit();
    }
    
    // Mettre à jour un produit
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $nom = $_POST['nom'] ?? '';
            $prix = $_POST['prix'] ?? 0;
            $stock = $_POST['stock'] ?? 0;
            $description = $_POST['description'] ?? '';
            $categorie_id = $_POST['categorie_id'] ?? null;
            
            $this->produitModel->update($id, $nom, $prix, $stock, $description, $categorie_id);
            header('Location: /marketplace/view/back/pages/marketplace.php');
            exit();
        }
    }
    
    // Supprimer un produit
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $this->produitModel->delete($id);
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    // Récupérer tous les produits
    public function getAllProduits() {
        return $this->produitModel->getAll();
    }
    
    // Afficher le catalogue (Front Office)
    public function front() {
        $produits = $this->produitModel->getAll();
        require_once __DIR__ . '/../view/front/index.php';
    }
}
?>