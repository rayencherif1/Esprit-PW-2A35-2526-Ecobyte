<?php
/**
 * AdminProfileController
 * Contrôleur pour gérer les profils administrateurs CRUD
 * Gère désormais toutes les requêtes DB liées aux profils admin
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Profil.php';

class AdminProfileController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureAdminTableExists();
    }

    // ========== MÉTHODES DB (DÉPLACÉES DU MODÈLE) ==========

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

    private function db_getAllProfiles() {
        $query = "SELECT id, full_name, email, role, phone, address, city, zip_code, bio, created_at FROM profils_admin ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function db_getProfileById($id) {
        $query = "SELECT id, full_name, email, role, phone, address, city, zip_code, bio, created_at FROM profils_admin WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function db_createProfile($data) {
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
    }

    private function db_updateProfile($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        if (isset($data['full_name'])) { $fields[] = 'full_name = :full_name'; $params[':full_name'] = $data['full_name']; }
        if (isset($data['email'])) { $fields[] = 'email = :email'; $params[':email'] = $data['email']; }
        if (isset($data['role'])) { $fields[] = 'role = :role'; $params[':role'] = $data['role']; }
        if (array_key_exists('phone', $data)) { $fields[] = 'phone = :phone'; $params[':phone'] = $data['phone']; }
        if (array_key_exists('address', $data)) { $fields[] = 'address = :address'; $params[':address'] = $data['address']; }
        if (array_key_exists('city', $data)) { $fields[] = 'city = :city'; $params[':city'] = $data['city']; }
        if (array_key_exists('zip_code', $data)) { $fields[] = 'zip_code = :zip_code'; $params[':zip_code'] = $data['zip_code']; }
        if (array_key_exists('bio', $data)) { $fields[] = 'bio = :bio'; $params[':bio'] = $data['bio']; }
        if (isset($data['updated_at'])) { $fields[] = 'updated_at = :updated_at'; $params[':updated_at'] = $data['updated_at']; }
        if (empty($fields)) return false;
        $query = "UPDATE profils_admin SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    private function db_deleteProfile($id) {
        $query = "DELETE FROM profils_admin WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    // ========== MÉTHODES DU CONTRÔLEUR ==========

    public function listProfiles() {
        header('Content-Type: application/json');
        try {
            echo json_encode($this->db_getAllProfiles());
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function createProfile() {
        header('Content-Type: application/json');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['full_name']) || !isset($data['email']) || !isset($data['role'])) {
                http_response_code(400); echo json_encode(['error' => 'Données invalides']); return;
            }
            $result = $this->db_createProfile($data);
            if ($result) {
                http_response_code(201); echo json_encode(['success' => 'Profil créé avec succès', 'id' => $result]);
            } else {
                http_response_code(500); echo json_encode(['error' => 'Erreur lors de la création']);
            }
        } catch (Exception $e) {
            http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getProfile($id) {
        header('Content-Type: application/json');
        try {
            if (!is_numeric($id)) { http_response_code(400); echo json_encode(['error' => 'ID invalide']); return; }
            $profile = $this->db_getProfileById($id);
            if ($profile) echo json_encode($profile);
            else { http_response_code(404); echo json_encode(['error' => 'Profil non trouvé']); }
        } catch (Exception $e) {
            http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updateProfile($id) {
        header('Content-Type: application/json');
        try {
            if (!is_numeric($id)) { http_response_code(400); echo json_encode(['error' => 'ID invalide']); return; }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) { http_response_code(400); echo json_encode(['error' => 'Données invalides']); return; }
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->db_updateProfile($id, $data);
            if ($result) echo json_encode(['success' => 'Profil modifié avec succès']);
            else { http_response_code(500); echo json_encode(['error' => 'Erreur lors de la modification']); }
        } catch (Exception $e) {
            http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deleteProfile($id) {
        header('Content-Type: application/json');
        try {
            if (!is_numeric($id)) { http_response_code(400); echo json_encode(['error' => 'ID invalide']); return; }
            $result = $this->db_deleteProfile($id);
            if ($result) echo json_encode(['success' => 'Profil supprimé avec succès']);
            else { http_response_code(500); echo json_encode(['error' => 'Erreur lors de la suppression']); }
        } catch (Exception $e) {
            http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function handleRequest($method = null, $id = null) {
        $method = $method ?? $_GET['method'] ?? 'list';
        switch ($method) {
            case 'list': $this->listProfiles(); break;
            case 'create': $this->createProfile(); break;
            case 'get': $this->getProfile($id ?? $_GET['id'] ?? null); break;
            case 'update': $this->updateProfile($id ?? $_GET['id'] ?? null); break;
            case 'delete': $this->deleteProfile($id ?? $_GET['id'] ?? null); break;
            default:
                header('Content-Type: application/json'); http_response_code(400);
                echo json_encode(['error' => 'Méthode invalide']);
        }
    }
}
?>
