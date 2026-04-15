<?php
/**
 * Model Profil
 * Classe pour gérer les profils avec PDO
 */

require_once __DIR__ . '/../config.php';

class Profil {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureAdminTableExists();
    }

    /**
     * Crée la table profils_admin si elle n'existe pas
     */
    private function ensureAdminTableExists() {
        $query = "CREATE TABLE IF NOT EXISTS profils_admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            role VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            address VARCHAR(255),
            city VARCHAR(100),
            zip_code VARCHAR(20),
            bio TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($query);
    }

    /**
     * Récupère tous les profils
     * @return array
     */
    public function getAllProfils() {
        try {
            $query = "SELECT id, user_id, bio, adresse, ville, code_postal, date_creation FROM profils ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des profils: " . $e->getMessage());
        }
    }

    /**
     * Récupère le profil d'un utilisateur
     * @param int $userId
     * @return array|false
     */
    public function getProfilByUserId($userId) {
        try {
            $query = "SELECT id, user_id, bio, adresse, ville, code_postal, date_creation FROM profils WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération du profil: " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau profil
     * @param array $data
     * @return int ID du profil créé
     */
    public function createProfil($data) {
        try {
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
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création du profil: " . $e->getMessage());
        }
    }

    /**
     * Met à jour un profil
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateProfil($userId, $data) {
        try {
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
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour du profil: " . $e->getMessage());
        }
    }

    /**
     * Supprime un profil
     * @param int $id
     * @return bool
     */
    public function deleteProfil($userId) {
        try {
            $query = "DELETE FROM profils WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression du profil: " . $e->getMessage());
        }
    }

    /**
     * Récupère tous les profils administrateurs (table profils_admin)
     * @return array
     */
    public function getAllProfiles() {
        try {
            $query = "SELECT id, full_name, email, role, phone, address, city, zip_code, bio, created_at FROM profils_admin ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des profils: " . $e->getMessage());
        }
    }

    /**
     * Récupère un profil administrateur spécifique
     * @param int $id
     * @return array|false
     */
    public function getProfileById($id) {
        try {
            $query = "SELECT id, full_name, email, role, phone, address, city, zip_code, bio, created_at FROM profils_admin WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération du profil: " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau profil administrateur
     * @param array $data
     * @return int|false ID du profil créé
     */
    public function createProfile($data) {
        try {
            $query = "INSERT INTO profils_admin (full_name, email, role, phone, address, city, zip_code, bio, created_at) 
                     VALUES (:full_name, :email, :role, :phone, :address, :city, :zip_code, :bio, :created_at)";
            $stmt = $this->db->prepare($query);
            
            $stmt->execute([
                ':full_name' => $data['full_name'],
                ':email' => $data['email'],
                ':role' => $data['role'],
                ':phone' => $data['phone'] ?? null,
                ':address' => $data['address'] ?? null,
                ':city' => $data['city'] ?? null,
                ':zip_code' => $data['zip_code'] ?? null,
                ':bio' => $data['bio'] ?? null,
                ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création du profil: " . $e->getMessage());
        }
    }

    /**
     * Met à jour un profil administrateur
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProfile($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];

            if (isset($data['full_name'])) {
                $fields[] = 'full_name = :full_name';
                $params[':full_name'] = $data['full_name'];
            }
            if (isset($data['email'])) {
                $fields[] = 'email = :email';
                $params[':email'] = $data['email'];
            }
            if (isset($data['role'])) {
                $fields[] = 'role = :role';
                $params[':role'] = $data['role'];
            }
            if (array_key_exists('phone', $data)) {
                $fields[] = 'phone = :phone';
                $params[':phone'] = $data['phone'];
            }
            if (array_key_exists('address', $data)) {
                $fields[] = 'address = :address';
                $params[':address'] = $data['address'];
            }
            if (array_key_exists('city', $data)) {
                $fields[] = 'city = :city';
                $params[':city'] = $data['city'];
            }
            if (array_key_exists('zip_code', $data)) {
                $fields[] = 'zip_code = :zip_code';
                $params[':zip_code'] = $data['zip_code'];
            }
            if (array_key_exists('bio', $data)) {
                $fields[] = 'bio = :bio';
                $params[':bio'] = $data['bio'];
            }
            if (isset($data['updated_at'])) {
                $fields[] = 'updated_at = :updated_at';
                $params[':updated_at'] = $data['updated_at'];
            }

            if (empty($fields)) {
                return false;
            }

            $query = "UPDATE profils_admin SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour du profil: " . $e->getMessage());
        }
    }

    /**
     * Supprime un profil administrateur
     * @param int $id
     * @return bool
     */
    public function deleteProfile($id) {
        try {
            $query = "DELETE FROM profils_admin WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression du profil: " . $e->getMessage());
        }
    }
}
?>
