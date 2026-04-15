<?php
// model/Produit.php
require_once __DIR__ . '/../config/database.php';

class Produit {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    // Récupérer tous les produits
    public function getAll() {
        $sql = "SELECT p.*, c.nom as categorie_nom 
                FROM produits p 
                LEFT JOIN categories c ON p.categorie_id = c.id 
                ORDER BY p.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer un produit par son ID
    public function getById($id) {
        $sql = "SELECT * FROM produits WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    // Ajouter un produit
    public function create($nom, $prix, $stock, $description, $categorie_id) {
        $sql = "INSERT INTO produits (nom, prix, stock, description, categorie_id) 
                VALUES (:nom, :prix, :stock, :description, :categorie_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nom' => $nom,
            'prix' => $prix,
            'stock' => $stock,
            'description' => $description,
            'categorie_id' => $categorie_id ?: null
        ]);
    }
    
    // Modifier un produit
    public function update($id, $nom, $prix, $stock, $description, $categorie_id) {
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
            'categorie_id' => $categorie_id ?: null
        ]);
    }
    
    // Supprimer un produit
    public function delete($id) {
        $sql = "DELETE FROM produits WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
?>