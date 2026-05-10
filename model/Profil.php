<?php
/**
 * Model Profil
 * Classe SIMPLE pour représenter un profil (Attributs, Getters, Setters)
 */

class Profil {
    private $id;
    private $user_id;
    private $bio;
    private $adresse;
    private $ville;
    private $code_postal;
    private $date_creation;

    // Pour profils_admin
    private $full_name;
    private $email;
    private $role;
    private $phone;
    private $address;
    private $city;
    private $zip_code;
    private $created_at;
    private $updated_at;




    



    public function __construct($data = []) {
        // Le développement est désormais dans le contrôleur
    }

    // Getters & Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }

    public function getBio() { return $this->bio; }
    public function setBio($bio) { $this->bio = $bio; }

    public function getAdresse() { return $this->adresse; }
    public function setAdresse($adresse) { $this->adresse = $adresse; }

    public function getVille() { return $this->ville; }
    public function setVille($ville) { $this->ville = $ville; }

    public function getCodePostal() { return $this->code_postal; }
    public function setCodePostal($code_postal) { $this->code_postal = $code_postal; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }

    // Admin Getters & Setters
    public function getFullName() { return $this->full_name; }
    public function setFullName($full_name) { $this->full_name = $full_name; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getRole() { return $this->role; }
    public function setRole($role) { $this->role = $role; }

    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }

    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }

    public function getCity() { return $this->city; }
    public function setCity($city) { $this->city = $city; }

    public function getZipCode() { return $this->zip_code; }
    public function setZipCode($zip_code) { $this->zip_code = $zip_code; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }

    public function getUpdatedAt() { return $this->updated_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
}
?>
