<?php
// model/Categorie.php
require_once __DIR__ . '/../config/database.php';

class Categorie {
    private $id;
    private $nom;
    private $description;
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
}
?>