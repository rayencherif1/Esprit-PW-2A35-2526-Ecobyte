<?php
// model/Categorie.php
require_once __DIR__ . '/../config/database.php';

class Categorie {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    // Récupérer toutes les catégories
    public function getAll() {
        $sql = "SELECT * FROM categories ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer une catégorie par son ID
    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    // Ajouter une catégorie
    public function create($nom, $description) {
        $sql = "INSERT INTO categories (nom, description) VALUES (:nom, :description)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nom' => $nom,
            'description' => $description
        ]);
    }
    
    // Modifier une catégorie
    public function update($id, $nom, $description) {
        $sql = "UPDATE categories SET nom = :nom, description = :description WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nom' => $nom,
            'description' => $description
        ]);
    }
    
    // Supprimer une catégorie
    public function delete($id) {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
?>