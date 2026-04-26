<?php
// model/Produit.php
require_once __DIR__ . '/../config/database.php';

class Produit {
    private $id;
    private $nom;
    private $prix;
    private $stock;
    private $description;
    private $categorie_id;
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrix() { return $this->prix; }
    public function getStock() { return $this->stock; }
    public function getDescription() { return $this->description; }
    public function getCategorieId() { return $this->categorie_id; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrix($prix) { $this->prix = $prix; }
    public function setStock($stock) { $this->stock = $stock; }
    public function setDescription($description) { $this->description = $description; }
    public function setCategorieId($categorie_id) { $this->categorie_id = $categorie_id; }
}
?>