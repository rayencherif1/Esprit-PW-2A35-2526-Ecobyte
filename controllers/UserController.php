<?php
/**
 * UserController
 * Contrôleur pour gérer les utilisateurs
 */

require_once __DIR__ . '/../model/User.php';

class UserController {
    private $userModel;
    private $errors = [];
    private $success = '';

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Récupère la liste de tous les utilisateurs
     * @return array
     */
    public function listUsers() {
        try {
            return $this->userModel->getAllUsers();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère un utilisateur par ID
     * @param int $id
     * @return array|false
     */
    public function getUser($id) {
        try {
            if (!$this->validateId($id)) {
                $this->errors[] = "ID invalide";
                return false;
            }
            return $this->userModel->getUserById($id);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Valide les données du formulaire d'ajout/modification
     * @param array $data
     * @param int|null $userId ID de l'utilisateur (pour modification)
     * @param bool $isProfile Update from profile page
     * @return bool
     */
    private function validateUserData($data, $userId = null, $isProfile = false) {
        $this->errors = [];

        // Validation du nom
        if (empty(trim($data['nom'] ?? ''))) {
            $this->errors[] = "Le nom est obligatoire";
        }

        // Validation du prénom
        if (empty(trim($data['prenom'] ?? ''))) {
            $this->errors[] = "Le prénom est obligatoire";
        }

        // Validation de l'email
        if (empty(trim($data['email'] ?? ''))) {
            $this->errors[] = "L'email est obligatoire";
        } else if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "L'email n'est pas valide";
        } else if ($this->userModel->emailExists(trim($data['email']), $userId)) {
            $this->errors[] = "Cet email est déjà utilisé";
        }

        // Validation du mot de passe (si création ou si fourni en modification)
        if (!$userId && empty($data['password'])) {
            $this->errors[] = "Le mot de passe est obligatoire";
        } else if (!empty($data['password']) && strlen($data['password']) < 6) {
            $this->errors[] = "Le mot de passe doit faire au moins 6 caractères";
        }

        return count($this->errors) === 0;
    }

    /**
     * Authentifie un utilisateur
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function login($email, $password) {
        try {
            $user = $this->userModel->getUserByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                // Créer la session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_photo'] = $user['photo'];
                $_SESSION['logged_in'] = true;
                
                return $user;
            }
            $this->errors[] = "Email ou mot de passe incorrect";
            return false;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }

    /**
     * Crée un utilisateur depuis le back-office
     * @param array $data
     * @return int|false
     */
    public function createUser($data) {
        if (!$this->validateUserData($data)) {
            return false;
        }
        try {
            $userId = $this->userModel->createUser($data);
            $this->success = "Utilisateur créé avec succès";
            return $userId;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Met à jour un utilisateur depuis le back-office
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser($id, $data) {
        if (!$this->validateUserData($data, $id)) {
            return false;
        }
        try {
            $result = $this->userModel->updateUser($id, $data);
            $this->success = "Utilisateur mis à jour avec succès";
            return $result;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Crée un nouvel utilisateur (Inscription)
     * @param array $data
     * @return int|false
     */
    public function register($data) {
        if (!$this->validateUserData($data)) {
            return false;
        }

        try {
            $userId = $this->userModel->createUser($data);
            $this->success = "Votre compte a été créé avec succès !";
            
            // Connecter l'utilisateur automatiquement après inscription
            $this->login($data['email'], $data['password']);
            
            return $userId;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Met à jour le profil utilisateur
     * @param int $id
     * @param array $data
     * @param array|null $files
     * @return bool
     */
    public function updateProfile($id, $data, $files = null) {
        if (!$this->validateUserData($data, $id, true)) {
            return false;
        }

        try {
            // Gérer l'upload de photo
            if ($files && isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../view/front/images/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $extension = pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $id . '_' . time() . '.' . $extension;
                $targetFile = $uploadDir . $filename;
                
                if (move_uploaded_file($files['photo']['tmp_name'], $targetFile)) {
                    $data['photo'] = 'view/front/images/uploads/' . $filename;
                    
                    // Mettre à jour la session
                    if (session_status() !== PHP_SESSION_NONE) {
                        $_SESSION['user_photo'] = $data['photo'];
                    }
                }
            }

            $result = $this->userModel->updateUser($id, $data);
            if ($result) {
                // Mettre à jour la session avec les nouvelles infos
                if (session_status() !== PHP_SESSION_NONE) {
                    $_SESSION['user_nom'] = $data['nom'];
                    $_SESSION['user_prenom'] = $data['prenom'];
                    $_SESSION['user_email'] = $data['email'];
                }
                $this->success = "Profil mis à jour avec succès";
            }
            return $result;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Supprime un utilisateur
     * @param int $id
     * @return bool
     */
    public function deleteUser($id) {
        if (!$this->validateId($id)) {
            $this->errors[] = "ID utilisateur invalide";
            return false;
        }

        try {
            $this->userModel->deleteUser($id);
            $this->success = "Utilisateur supprimé avec succès";
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
