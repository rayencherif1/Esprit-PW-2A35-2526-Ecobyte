<?php
/**
 * Model User
 * Classe pour gérer les utilisateurs avec PDO
 */

require_once __DIR__ . '/../config.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Récupère tous les utilisateurs
     * @return array
     */
    public function getAllUsers() {
        try {
            $query = "SELECT id, nom, prenom, email, telephone, photo, poids, taille, date_creation FROM users ORDER BY date_creation DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des utilisateurs: " . $e->getMessage());
        }
    }

    /**
     * Récupère un utilisateur par ID
     * @param int $id
     * @return array|false
     */
    public function getUserById($id) {
        try {
            $query = "SELECT id, nom, prenom, email, telephone, photo, poids, taille, date_creation FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
        }
    }

    /**
     * Récupère un utilisateur par Email (pour la connexion)
     * @param string $email
     * @return array|false
     */
    public function getUserByEmail($email) {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de l'utilisateur par email: " . $e->getMessage());
        }
    }

    /**
     * Crée un nouvel utilisateur
     * @param array $data Données de l'utilisateur
     * @return int ID de l'utilisateur créé
     */
    public function createUser($data) {
        try {
            $query = "INSERT INTO users (nom, prenom, email, password, telephone, photo, poids, taille, date_creation) 
                     VALUES (:nom, :prenom, :email, :password, :telephone, :photo, :poids, :taille, NOW())";
            $stmt = $this->db->prepare($query);
            
            $stmt->execute([
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':telephone' => $data['telephone'] ?? null,
                ':photo' => $data['photo'] ?? null,
                ':poids' => $data['poids'] ?? null,
                ':taille' => $data['taille'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
        }
    }

    /**
     * Met à jour un utilisateur
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser($id, $data) {
        try {
            $fields = "nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, poids = :poids, taille = :taille";
            $params = [
                ':id' => $id,
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email' => $data['email'],
                ':telephone' => $data['telephone'] ?? null,
                ':poids' => $data['poids'] ?? null,
                ':taille' => $data['taille'] ?? null
            ];

            if (isset($data['photo'])) {
                $fields .= ", photo = :photo";
                $params[':photo'] = $data['photo'];
            }

            if (!empty($data['password'])) {
                $fields .= ", password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $query = "UPDATE users SET $fields WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
        }
    }

    /**
     * Supprime un utilisateur
     * @param int $id
     * @return bool
     */
    public function deleteUser($id) {
        try {
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur: " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un email existe déjà
     * @param string $email
     * @param int|null $excludeId ID à exclure (pour la mise à jour)
     * @return bool
     */
    public function emailExists($email, $excludeId = null) {
        try {
            if ($excludeId) {
                $query = "SELECT COUNT(*) as count FROM users WHERE email = :email AND id != :id";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':email' => $email, ':id' => $excludeId]);
            } else {
                $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':email' => $email]);
            }
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la vérification de l'email: " . $e->getMessage());
        }
    }
}
?>
