<?php
/**
 * ProfilController
 * Contrôleur pour gérer les profils utilisateur
 */

require_once __DIR__ . '/../model/Profil.php';
require_once __DIR__ . '/../model/User.php';

class ProfilController {
    private $profilModel;
    private $userModel;
    private $errors = [];
    private $success = '';

    public function __construct() {
        $this->profilModel = new Profil();
        $this->userModel = new User();
    }

    /**
     * Récupère le profil d'un utilisateur
     * @param int $userId
     * @return array|false
     */
    public function getProfil($userId) {
        try {
            if (!$this->validateId($userId)) {
                $this->errors[] = "ID utilisateur invalide";
                return false;
            }
            return $this->profilModel->getProfilByUserId($userId);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Alias pour récupérer le profil par user_id
     * @param int $userId
     * @return array|false
     */
    public function getProfilByUserId($userId) {
        return $this->getProfil($userId);
    }

    /**
     * Valide les données du profil
     * @param array $data
     * @return bool
     */
    private function validateProfilData($data) {
        $this->errors = [];

        // Validation de la bio (optionnel)
        if (!empty($data['bio'] ?? '')) {
            if (strlen(trim($data['bio'])) > 500) {
                $this->errors[] = "La bio ne peut pas dépasser 500 caractères";
            }
        }

        // Validation de l'adresse (optionnel)
        if (!empty($data['adresse'] ?? '')) {
            if (strlen(trim($data['adresse'])) > 200) {
                $this->errors[] = "L'adresse ne peut pas dépasser 200 caractères";
            }
        }

        // Validation de la ville (optionnel)
        if (!empty($data['ville'] ?? '')) {
            if (strlen(trim($data['ville'])) > 100) {
                $this->errors[] = "La ville ne peut pas dépasser 100 caractères";
            }
        }

        // Validation du code postal (optionnel)
        if (!empty($data['code_postal'] ?? '')) {
            if (!preg_match('/^[0-9a-zA-Z\s\-]{2,10}$/', trim($data['code_postal']))) {
                $this->errors[] = "Le code postal n'est pas valide";
            }
        }

        return count($this->errors) === 0;
    }

    /**
     * Crée un profil pour un utilisateur
     * @param array $data
     * @return int|false ID du profil créé ou false
     */
    public function createProfil($data) {
        if (!isset($data['user_id']) || !$this->validateId($data['user_id'])) {
            $this->errors[] = "ID utilisateur invalide";
            return false;
        }

        // Vérifier que l'utilisateur existe
        if (!$this->userModel->getUserById($data['user_id'])) {
            $this->errors[] = "L'utilisateur n'existe pas";
            return false;
        }

        if (!$this->validateProfilData($data)) {
            return false;
        }

        try {
            $cleanData = [
                'user_id' => (int)$data['user_id'],
                'bio' => !empty(trim($data['bio'] ?? '')) ? trim($data['bio']) : null,
                'adresse' => !empty(trim($data['adresse'] ?? '')) ? trim($data['adresse']) : null,
                'ville' => !empty(trim($data['ville'] ?? '')) ? trim($data['ville']) : null,
                'code_postal' => !empty(trim($data['code_postal'] ?? '')) ? trim($data['code_postal']) : null
            ];

            $profilId = $this->profilModel->createProfil($cleanData);
            $this->success = "Profil créé avec succès";
            return $profilId;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Met à jour le profil d'un utilisateur
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateProfil($userId, $data) {
        if (!$this->validateId($userId)) {
            $this->errors[] = "ID utilisateur invalide";
            return false;
        }

        if (!$this->validateProfilData($data)) {
            return false;
        }

        try {
            $cleanData = [
                'bio' => !empty(trim($data['bio'] ?? '')) ? trim($data['bio']) : null,
                'adresse' => !empty(trim($data['adresse'] ?? '')) ? trim($data['adresse']) : null,
                'ville' => !empty(trim($data['ville'] ?? '')) ? trim($data['ville']) : null,
                'code_postal' => !empty(trim($data['code_postal'] ?? '')) ? trim($data['code_postal']) : null
            ];

            $this->profilModel->updateProfil($userId, $cleanData);
            $this->success = "Profil mis à jour avec succès";
            return true;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Valide un ID
     * @param mixed $id
     * @return bool
     */
    private function validateId($id) {
        return is_numeric($id) && (int)$id > 0;
    }

    /**
     * Retourne les erreurs
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Retourne le message de succès
     * @return string
     */
    public function getSuccess() {
        return $this->success;
    }

    /**
     * Vérifie s'il y a des erreurs
     * @return bool
     */
    public function hasErrors() {
        return count($this->errors) > 0;
    }
}
?>
