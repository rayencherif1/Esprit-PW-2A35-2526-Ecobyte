<?php
/**
 * AdminProfileController
 * Contrôleur pour gérer les profils administrateurs CRUD
 */

require_once __DIR__ . '/../model/Profil.php';

class AdminProfileController {
    private $profilModel;

    public function __construct() {
        $this->profilModel = new Profil();
    }

    /**
     * Récupère tous les profils
     */
    public function listProfiles() {
        header('Content-Type: application/json');
        try {
            $profiles = $this->profilModel->getAllProfiles();
            echo json_encode($profiles);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Crée un nouveau profil
     */
    public function createProfile() {
        header('Content-Type: application/json');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['full_name']) || !isset($data['email']) || !isset($data['role'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Données invalides']);
                return;
            }

            $result = $this->profilModel->createProfile([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'zip_code' => $data['zip_code'] ?? null,
                'bio' => $data['bio'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                http_response_code(201);
                echo json_encode(['success' => 'Profil créé avec succès', 'id' => $result]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la création']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Récupère un profil spécifique
     */
    public function getProfile($id) {
        header('Content-Type: application/json');
        try {
            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                return;
            }

            $profile = $this->profilModel->getProfileById($id);
            
            if ($profile) {
                echo json_encode($profile);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Profil non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Modifie un profil
     */
    public function updateProfile($id) {
        header('Content-Type: application/json');
        try {
            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Données invalides']);
                return;
            }

            $updateData = [];
            if (isset($data['full_name'])) $updateData['full_name'] = $data['full_name'];
            if (isset($data['email'])) $updateData['email'] = $data['email'];
            if (isset($data['role'])) $updateData['role'] = $data['role'];
            if (array_key_exists('phone', $data)) $updateData['phone'] = $data['phone'];
            if (array_key_exists('address', $data)) $updateData['address'] = $data['address'];
            if (array_key_exists('city', $data)) $updateData['city'] = $data['city'];
            if (array_key_exists('zip_code', $data)) $updateData['zip_code'] = $data['zip_code'];
            if (array_key_exists('bio', $data)) $updateData['bio'] = $data['bio'];
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $result = $this->profilModel->updateProfile($id, $updateData);

            if ($result) {
                echo json_encode(['success' => 'Profil modifié avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la modification']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Supprime un profil
     */
    public function deleteProfile($id) {
        header('Content-Type: application/json');
        try {
            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID invalide']);
                return;
            }

            $result = $this->profilModel->deleteProfile($id);

            if ($result) {
                echo json_encode(['success' => 'Profil supprimé avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la suppression']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Gère les requêtes selon la méthode
     */
    public function handleRequest($method = null, $id = null) {
        $method = $method ?? $_GET['method'] ?? 'list';
        
        switch ($method) {
            case 'list':
                $this->listProfiles();
                break;
            case 'create':
                $this->createProfile();
                break;
            case 'get':
                $this->getProfile($id ?? $_GET['id'] ?? null);
                break;
            case 'update':
                $this->updateProfile($id ?? $_GET['id'] ?? null);
                break;
            case 'delete':
                $this->deleteProfile($id ?? $_GET['id'] ?? null);
                break;
            default:
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['error' => 'Méthode invalide']);
        }
    }
}
?>
