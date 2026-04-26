<?php
// model/Commande.php
require_once __DIR__ . '/../config/database.php';

class Commande {
    private $id;
    private $client_nom;
    private $client_prenom;
    private $client_email;
    private $client_telephone;
    private $date_naissance;
    private $adresse;
    private $adresse_complement;
    private $code_postal;
    private $ville;
    private $pays;
    private $instructions_livraison;
    private $adresse_facturation;
    private $code_postal_facturation;
    private $ville_facturation;
    private $mode_livraison;
    private $frais_livraison;
    private $mode_paiement;
    private $code_promo;
    private $notes;
    private $date_commande;
    private $total;
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getClientNom() { return $this->client_nom; }
    public function getClientPrenom() { return $this->client_prenom; }
    public function getClientEmail() { return $this->client_email; }
    public function getClientTelephone() { return $this->client_telephone; }
    public function getDateNaissance() { return $this->date_naissance; }
    public function getAdresse() { return $this->adresse; }
    public function getAdresseComplement() { return $this->adresse_complement; }
    public function getCodePostal() { return $this->code_postal; }
    public function getVille() { return $this->ville; }
    public function getPays() { return $this->pays; }
    public function getInstructionsLivraison() { return $this->instructions_livraison; }
    public function getAdresseFacturation() { return $this->adresse_facturation; }
    public function getCodePostalFacturation() { return $this->code_postal_facturation; }
    public function getVilleFacturation() { return $this->ville_facturation; }
    public function getModeLivraison() { return $this->mode_livraison; }
    public function getFraisLivraison() { return $this->frais_livraison; }
    public function getModePaiement() { return $this->mode_paiement; }
    public function getCodePromo() { return $this->code_promo; }
    public function getNotes() { return $this->notes; }
    public function getDateCommande() { return $this->date_commande; }
    public function getTotal() { return $this->total; }
    public function getDb() { return $this->db; }
    
    // Setters
    public function setId($id) { $this->id = $id; }
    public function setClientNom($client_nom) { $this->client_nom = $client_nom; }
    public function setClientPrenom($client_prenom) { $this->client_prenom = $client_prenom; }
    public function setClientEmail($client_email) { $this->client_email = $client_email; }
    public function setClientTelephone($client_telephone) { $this->client_telephone = $client_telephone; }
    public function setDateNaissance($date_naissance) { $this->date_naissance = $date_naissance; }
    public function setAdresse($adresse) { $this->adresse = $adresse; }
    public function setAdresseComplement($adresse_complement) { $this->adresse_complement = $adresse_complement; }
    public function setCodePostal($code_postal) { $this->code_postal = $code_postal; }
    public function setVille($ville) { $this->ville = $ville; }
    public function setPays($pays) { $this->pays = $pays; }
    public function setInstructionsLivraison($instructions_livraison) { $this->instructions_livraison = $instructions_livraison; }
    public function setAdresseFacturation($adresse_facturation) { $this->adresse_facturation = $adresse_facturation; }
    public function setCodePostalFacturation($code_postal_facturation) { $this->code_postal_facturation = $code_postal_facturation; }
    public function setVilleFacturation($ville_facturation) { $this->ville_facturation = $ville_facturation; }
    public function setModeLivraison($mode_livraison) { $this->mode_livraison = $mode_livraison; }
    public function setFraisLivraison($frais_livraison) { $this->frais_livraison = $frais_livraison; }
    public function setModePaiement($mode_paiement) { $this->mode_paiement = $mode_paiement; }
    public function setCodePromo($code_promo) { $this->code_promo = $code_promo; }
    public function setNotes($notes) { $this->notes = $notes; }
    public function setDateCommande($date_commande) { $this->date_commande = $date_commande; }
    public function setTotal($total) { $this->total = $total; }
}
?>