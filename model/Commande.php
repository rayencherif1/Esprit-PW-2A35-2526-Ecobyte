<?php
// model/Commande.php
require_once __DIR__ . '/../config/database.php';

class Commande {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    // Récupérer toutes les commandes
    public function getAll() {
        $sql = "SELECT * FROM commandes ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer une commande par son ID
    public function getById($id) {
        $sql = "SELECT * FROM commandes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    // Récupérer les produits d'une commande
    public function getProduitsByCommandeId($commande_id) {
        $sql = "SELECT p.*, cp.quantite 
                FROM commande_produits cp 
                JOIN produits p ON cp.produit_id = p.id 
                WHERE cp.commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['commande_id' => $commande_id]);
        return $stmt->fetchAll();
    }
    
    // Ajouter une commande
    public function create($client_nom, $client_email, $total) {
        $sql = "INSERT INTO commandes (client_nom, client_email, total) 
                VALUES (:client_nom, :client_email, :total)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'client_nom' => $client_nom,
            'client_email' => $client_email,
            'total' => $total
        ]);
        return $this->db->lastInsertId();
    }
    
    // Ajouter un produit à une commande
    public function addProduit($commande_id, $produit_id, $quantite) {
        $sql = "INSERT INTO commande_produits (commande_id, produit_id, quantite) 
                VALUES (:commande_id, :produit_id, :quantite)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'commande_id' => $commande_id,
            'produit_id' => $produit_id,
            'quantite' => $quantite
        ]);
    }
    
    // Supprimer une commande
    public function delete($id) {
        $sql = "DELETE FROM commandes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
?>