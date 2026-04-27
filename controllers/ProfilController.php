<?php
/**
 * ProfilController
 * Contrôleur pour gérer les profils utilisateur
 * Gère désormais toutes les requêtes DB liées aux profils
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Profil.php';

class ProfilController {
    private $db;
    private $errors = [];
    private $success = '';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ========== MÉTHODES DB (DÉPLACÉES DU MODÈLE) ==========

    private function db_getAllProfils() {
        $query = "SELECT id, user_id, bio, adresse, ville, code_postal, date_creation FROM profils ORDER BY date_creation DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function db_getProfilByUserId($userId) {
        $query = "SELECT id, user_id, bio, adresse, ville, code_postal, date_creation FROM profils WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch();
    }

    private function db_createProfil($data) {
        $query = "INSERT INTO profils (user_id, bio, adresse, ville, code_postal, date_creation) 
                 VALUES (:user_id, :bio, :adresse, :ville, :code_postal, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':bio' => $data['bio'] ?? null,
            ':adresse' => $data['adresse'] ?? null,
            ':ville' => $data['ville'] ?? null,
            ':code_postal' => $data['code_postal'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    private function db_updateProfil($userId, $data) {
        $query = "UPDATE profils SET bio = :bio, adresse = :adresse, ville = :ville, 
                 code_postal = :code_postal WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':user_id' => $userId,
            ':bio' => $data['bio'] ?? null,
            ':adresse' => $data['adresse'] ?? null,
            ':ville' => $data['ville'] ?? null,
            ':code_postal' => $data['code_postal'] ?? null
        ]);
    }

    private function db_userExists($userId) {
        $query = "SELECT COUNT(*) FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }

    // ========== MÉTHODES DU CONTRÔLEUR ==========

    public function getProfil($userId) {
        try {
            if (!$this->validateId($userId)) {
                $this->errors[] = "ID utilisateur invalide";
                return false;
            }
            return $this->db_getProfilByUserId($userId);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function getProfilByUserId($userId) {
        return $this->getProfil($userId);
    }

    private function validateProfilData($data) {
        $this->errors = [];
        if (!empty($data['bio'] ?? '')) {
            if (strlen(trim($data['bio'])) > 500) $this->errors[] = "La bio ne peut pas dépasser 500 caractères";
        }
        if (!empty($data['adresse'] ?? '')) {
            if (strlen(trim($data['adresse'])) > 200) $this->errors[] = "L'adresse ne peut pas dépasser 200 caractères";
        }
        if (!empty($data['ville'] ?? '')) {
            if (strlen(trim($data['ville'])) > 100) $this->errors[] = "La ville ne peut pas dépasser 100 caractères";
        }
        if (!empty($data['code_postal'] ?? '')) {
            if (!preg_match('/^[0-9a-zA-Z\s\-]{2,10}$/', trim($data['code_postal']))) $this->errors[] = "Le code postal n'est pas valide";
        }
        return count($this->errors) === 0;
    }

    public function createProfil($data) {
        if (!isset($data['user_id']) || !$this->validateId($data['user_id'])) {
            $this->errors[] = "ID utilisateur invalide";
            return false;
        }
        if (!$this->db_userExists($data['user_id'])) {
            $this->errors[] = "L'utilisateur n'existe pas";
            return false;
        }
        if (!$this->validateProfilData($data)) return false;
        try {
            $cleanData = [
                'user_id' => (int)$data['user_id'],
                'bio' => !empty(trim($data['bio'] ?? '')) ? trim($data['bio']) : null,
                'adresse' => !empty(trim($data['adresse'] ?? '')) ? trim($data['adresse']) : null,
                'ville' => !empty(trim($data['ville'] ?? '')) ? trim($data['ville']) : null,
                'code_postal' => !empty(trim($data['code_postal'] ?? '')) ? trim($data['code_postal']) : null
            ];
            $profilId = $this->db_createProfil($cleanData);
            $this->success = "Profil créé avec succès";
            return $profilId;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function updateProfil($userId, $data) {
        if (!$this->validateId($userId)) {
            $this->errors[] = "ID utilisateur invalide";
            return false;
        }
        if (!$this->validateProfilData($data)) return false;
        try {
            $cleanData = [
                'bio' => !empty(trim($data['bio'] ?? '')) ? trim($data['bio']) : null,
                'adresse' => !empty(trim($data['adresse'] ?? '')) ? trim($data['adresse']) : null,
                'ville' => !empty(trim($data['ville'] ?? '')) ? trim($data['ville']) : null,
                'code_postal' => !empty(trim($data['code_postal'] ?? '')) ? trim($data['code_postal']) : null
            ];
            $this->db_updateProfil($userId, $cleanData);
            $this->success = "Profil mis à jour avec succès";
            return true;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    private function validateId($id) {
        return is_numeric($id) && (int)$id > 0;
    }

    public function getErrors() { return $this->errors; }
    public function getSuccess() { return $this->success; }
    public function hasErrors() { return count($this->errors) > 0; }
}
?>
