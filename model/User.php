<?php
/**
 * Model User
 * Classe SIMPLE pour représenter un utilisateur (Attributs, Getters, Setters)
 */

class User {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $password;
    private $telephone;
    private $photo;
    private $poids;
    private $taille;
    private $date_creation;
    private $ban_until;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nom = $data['nom'] ?? null;
            $this->prenom = $data['prenom'] ?? null;
            $this->email = $data['email'] ?? null;
            $this->password = $data['password'] ?? null;
            $this->telephone = $data['telephone'] ?? null;
            $this->photo = $data['photo'] ?? null;
            $this->poids = $data['poids'] ?? null;
            $this->taille = $data['taille'] ?? null;
            $this->date_creation = $data['date_creation'] ?? null;
            $this->ban_until = $data['ban_until'] ?? null;
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getTelephone() { return $this->telephone; }
    public function getPhoto() { return $this->photo; }
    public function getPoids() { return $this->poids; }
    public function getTaille() { return $this->taille; }
    public function getDateCreation() { return $this->date_creation; }
    public function getBanUntil() { return $this->ban_until; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setTelephone($telephone) { $this->telephone = $telephone; }
    public function setPhoto($photo) { $this->photo = $photo; }
    public function setPoids($poids) { $this->poids = $poids; }
    public function setTaille($taille) { $this->taille = $taille; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }
    public function setBanUntil($ban_until) { $this->ban_until = $ban_until; }
}
?>
