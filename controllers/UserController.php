<?php
/**
 * UserController
 * Contrôleur pour gérer les utilisateurs
 * Contient désormais toute la logique métier et les requêtes DB
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/User.php';

class UserController {
    private $db;
    private $errors = [];
    private $success = '';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ========== MÉTHODES DE LOGIQUE DB (DÉPLACÉES DEPUIS LE MODÈLE) ==========

    // ========== MÉTHODES DU CONTRÔLEUR (LOGIQUE DB INTÉGRÉE) ==========

    public function listUsers($search = null, $sort = 'date_creation', $order = 'DESC') {
        try {
            $params = [];
            $sql = "SELECT u.*, p.adresse 
                    FROM users u 
                    LEFT JOIN profils p ON u.id = p.user_id 
                    WHERE 1=1";

            if ($search) {
                $searchTerm = "%$search%";
                $sql .= " AND (u.nom LIKE :s1 
                           OR u.prenom LIKE :s2 
                           OR CONCAT(u.nom, ' ', u.prenom) LIKE :s3 
                           OR CONCAT(u.prenom, ' ', u.nom) LIKE :s4 
                           OR u.email LIKE :s5 
                           OR u.telephone LIKE :s6 
                           OR p.adresse LIKE :s7)";
                $params[':s1'] = $searchTerm;
                $params[':s2'] = $searchTerm;
                $params[':s3'] = $searchTerm;
                $params[':s4'] = $searchTerm;
                $params[':s5'] = $searchTerm;
                $params[':s6'] = $searchTerm;
                $params[':s7'] = $searchTerm;
            }

            // Liste blanche pour le tri
            $allowedSort = ['date_creation', 'poids', 'taille', 'nom', 'email'];
            $sort = in_array($sort, $allowedSort) ? $sort : 'date_creation';
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

            $sql .= " ORDER BY u.$sort $order";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return [];
        }
    }

    private function db_getUserById($id) {
        $query = "SELECT id, nom, prenom, email, telephone, photo, poids, taille, date_creation, ban_until FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    private function db_getUserByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    private function db_createUser($data) {
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
    }

    private function db_updateUser($id, $data) {
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
    }

    private function db_deleteUser($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    private function db_emailExists($email, $excludeId = null) {
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
    }

    private function db_banUser($id, $until) {
        try {
            $query = "UPDATE users SET ban_until = :until WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':id' => $id, ':until' => $until]);
        } catch (PDOException $e) {
            if ($e->getCode() == '42S22') {
                $this->db->exec("ALTER TABLE users ADD COLUMN ban_until DATETIME DEFAULT NULL");
                $query = "UPDATE users SET ban_until = :until WHERE id = :id";
                $stmt = $this->db->prepare($query);
                return $stmt->execute([':id' => $id, ':until' => $until]);
            }
            throw $e;
        }
    }

    private function db_setResetToken($email, $token, $expiry) {
        $query = "UPDATE users SET reset_token = :token, reset_expires = :expiry WHERE email = :email";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':token' => $token, ':expiry' => $expiry, ':email' => $email]);
    }

    private function db_verifyResetToken($email, $token) {
        $query = "SELECT id FROM users WHERE email = :email AND reset_token = :token AND reset_expires > NOW()";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email, ':token' => $token]);
        return $stmt->fetch();
    }

    private function db_updatePassword($email, $password) {
        $query = "UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE email = :email";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':email' => $email
        ]);
    }

    // ========== MÉTHODES DU CONTRÔLEUR ==========


    public function getUser($id) {
        try {
            if (!$this->validateId($id)) {
                $this->errors[] = "ID invalide";
                return false;
            }
            return $this->db_getUserById($id);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    private function validateUserData($data, $userId = null, $isProfile = false) {
        $this->errors = [];
        if (empty(trim($data['nom'] ?? ''))) $this->errors[] = "Le nom est obligatoire";
        if (empty(trim($data['prenom'] ?? ''))) $this->errors[] = "Le prénom est obligatoire";
        if (empty(trim($data['email'] ?? ''))) {
            $this->errors[] = "L'email est obligatoire";
        } else if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "L'email n'est pas valide";
        } else if ($this->db_emailExists(trim($data['email']), $userId)) {
            $this->errors[] = "Cet email est déjà utilisé";
        }
        if (!$userId && empty($data['password'])) {
            $this->errors[] = "Le mot de passe est obligatoire";
        } else if (!empty($data['password']) && strlen($data['password']) < 6) {
            $this->errors[] = "Le mot de passe doit faire au moins 6 caractères";
        }
        return count($this->errors) === 0;
    }

    public function login($email, $password) {
        try {
            $user = $this->db_getUserByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                if ($user['ban_until']) {
                    $banTime = strtotime($user['ban_until']);
                    if ($banTime > time()) {
                        $expiry = ($user['ban_until'] === '9999-12-31 23:59:59') ? 'définitivement' : 'jusqu\'au ' . date('d/m/Y H:i', $banTime);
                        $this->errors[] = "Ce compte a été banni " . $expiry . ".";
                        return false;
                    }
                }
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_photo'] = $user['photo'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                $_SESSION['logged_in'] = true;
                
                // Vérifier la connexion et envoyer l'alerte (Géolocalisation IP-API)
                $this->checkAndNotifyLogin($user);
                
                return $user;
            }
            $this->errors[] = "Email ou mot de passe incorrect";
            return false;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function googleLogin($token) {
        try {
            // 1. Décoder le token JWT Google (sans validation cryptographique stricte pour simplifier)
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                $this->errors[] = "Token Google invalide";
                return false;
            }
            
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
            
            if (!$payload || !isset($payload['email'])) {
                $this->errors[] = "Impossible de lire les informations Google";
                return false;
            }

            $email = $payload['email'];
            $google_id = $payload['sub'];
            $nom = $payload['family_name'] ?? 'Inconnu';
            $prenom = $payload['given_name'] ?? 'Inconnu';
            $photo = $payload['picture'] ?? null;

            // 2. Chercher l'utilisateur
            $user = $this->db_getUserByEmail($email);
            
            if ($user) {
                // Mettre à jour google_id si vide
                if (empty($user['google_id'])) {
                    $query = "UPDATE users SET google_id = :google_id WHERE id = :id";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([':google_id' => $google_id, ':id' => $user['id']]);
                }
                
                // Vérifier s'il est banni
                if ($user['ban_until'] && strtotime($user['ban_until']) > time()) {
                    $this->errors[] = "Ce compte a été banni.";
                    return false;
                }
                
                // Créer la session
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_photo'] = $user['photo'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                $_SESSION['logged_in'] = true;
                
                $this->checkAndNotifyLogin($user);
                return $user;
            } else {
                // 3. Créer un nouveau compte Google
                $query = "INSERT INTO users (nom, prenom, email, google_id, photo, date_creation) 
                          VALUES (:nom, :prenom, :email, :google_id, :photo, NOW())";
                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':email' => $email,
                    ':google_id' => $google_id,
                    ':photo' => $photo
                ]);
                
                $newUserId = $this->db->lastInsertId();
                $newUser = $this->db_getUserById($newUserId);
                
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $newUser['id'];
                $_SESSION['user_nom'] = $newUser['nom'];
                $_SESSION['user_prenom'] = $newUser['prenom'];
                $_SESSION['user_email'] = $newUser['email'];
                $_SESSION['user_photo'] = $newUser['photo'];
                $_SESSION['user_role'] = 'user';
                $_SESSION['logged_in'] = true;
                
                $this->checkAndNotifyLogin($newUser);
                return $newUser;
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    private function checkAndNotifyLogin($user) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Inconnue';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Appareil inconnu';
        
        // Par défaut pour localhost (WAMP/XAMPP)
        $location = "Localisation non détectable (Localhost)";
        
        // Appel à l'API ip-api.com si ce n'est pas une IP locale
        if ($ip !== '::1' && $ip !== '127.0.0.1') {
            $api_url = "http://ip-api.com/json/{$ip}";
            $response = @file_get_contents($api_url);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    $location = $data['city'] . ", " . $data['country'];
                }
            }
        }

        $subject = "Nouvelle connexion detectee - Ecobyte";
        $message = "Bonjour " . $user['prenom'] . ",\n\n";
        $message .= "Une nouvelle connexion a votre compte Ecobyte a ete detectee.\n\n";
        $message .= "Details de la connexion :\n";
        $message .= "- Adresse IP : " . $ip . "\n";
        $message .= "- Localisation : " . $location . "\n";
        $message .= "- Appareil / Navigateur : " . $userAgent . "\n";
        $message .= "- Date : " . date('d/m/Y H:i:s') . "\n\n";
        $message .= "Si ce n'est pas vous, veuillez reinitialiser votre mot de passe immediatement.";

        // On envoie l'email
        $this->sendMail($user['email'], $subject, $message);
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
    }

    public function createUser($data) {
        if (!$this->validateUserData($data)) return false;
        try {
            $userId = $this->db_createUser($data);
            $this->success = "Utilisateur créé avec succès";
            return $userId;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function updateUser($id, $data) {
        if (!$this->validateUserData($data, $id)) return false;
        try {
            $result = $this->db_updateUser($id, $data);
            $this->success = "Utilisateur mis à jour avec succès";
            return $result;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function register($data) {
        if (!$this->validateUserData($data)) return false;
        try {
            $userId = $this->db_createUser($data);
            $this->success = "Votre compte a été créé avec succès !";
            return $userId;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function updateProfile($id, $data, $files = null) {
        if (!$this->validateUserData($data, $id, true)) return false;
        try {
            if ($files && isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../view/front/images/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $extension = pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $id . '_' . time() . '.' . $extension;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($files['photo']['tmp_name'], $targetFile)) {
                    $data['photo'] = 'view/front/images/uploads/' . $filename;
                    if (session_status() !== PHP_SESSION_NONE) $_SESSION['user_photo'] = $data['photo'];
                }
            }
            $result = $this->db_updateUser($id, $data);
            if ($result) {
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

    public function deleteUser($id) {
        if (!$this->validateId($id)) {
            $this->errors[] = "ID utilisateur invalide";
            return false;
        }
        try {
            $this->db_deleteUser($id);
            $this->success = "Utilisateur supprimé avec succès";
            return true;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function banUser($id, $duration) {
        try {
            $until = null;
            switch ($duration) {
                case '1d': $until = date('Y-m-d H:i:s', strtotime('+1 day')); break;
                case '2d': $until = date('Y-m-d H:i:s', strtotime('+2 days')); break;
                case '3d': $until = date('Y-m-d H:i:s', strtotime('+3 days')); break;
                case '5d': $until = date('Y-m-d H:i:s', strtotime('+5 days')); break;
                case 'perm': $until = '9999-12-31 23:59:59'; break;
                case 'unban': $until = null; break;
                default: $this->errors[] = "Durée invalide"; return false;
            }
            $result = $this->db_banUser($id, $until);
            if ($result) $this->success = $duration === 'unban' ? "Utilisateur débanni" : "Utilisateur banni avec succès";
            return $result;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function forgotPassword($email) {
        if (empty($email)) {
            $this->errors[] = "L'email est obligatoire";
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "L'email n'est pas valide";
            return false;
        }

        $user = $this->db_getUserByEmail($email);
        if (!$user) {
            // Pour la sécurité, on ne dit pas si l'email existe ou non, 
            // mais ici pour l'UX on va le dire car c'est un projet de cours
            $this->errors[] = "Aucun utilisateur trouvé avec cet email";
            return false;
        }

        // Générer un code à 6 chiffres
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        if ($this->db_setResetToken($email, $code, $expiry)) {
            // Envoyer l'email
            $subject = "Réinitialisation de votre mot de passe";
            $message = "Votre code de réinitialisation est : " . $code . "\n\nCe code expirera dans 1 heure.";
            
            // Simulation de l'envoi ou utilisation de mail()
            if ($this->sendMail($email, $subject, $message)) {
                $this->success = "Un code de réinitialisation a été envoyé à votre adresse email.";
                return true;
            } else {
                $this->errors[] = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
                return false;
            }
        }
        return false;
    }

    public function verifyCode($email, $code) {
        if (empty($code)) {
            $this->errors[] = "Le code est obligatoire";
            return false;
        }

        $user = $this->db_verifyResetToken($email, $code);
        if ($user) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['reset_email_verified'] = $email;
            return true;
        }
        $this->errors[] = "Code invalide ou expiré";
        return false;
    }

    public function resetPassword($email, $newPassword, $confirmPassword) {
        if (empty($newPassword)) $this->errors[] = "Le nouveau mot de passe est obligatoire";
        if ($newPassword !== $confirmPassword) $this->errors[] = "Les mots de passe ne correspondent pas";
        if (strlen($newPassword) < 6) $this->errors[] = "Le mot de passe doit faire au moins 6 caractères";

        if ($this->hasErrors()) return false;

        if ($this->db_updatePassword($email, $newPassword)) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            unset($_SESSION['reset_email_verified']); // Nettoyer après succès
            $this->success = "Votre mot de passe a été réinitialisé avec succès !";
            return true;
        }
        return false;
    }

    private function sendMail($to, $subject, $message) {
        require_once __DIR__ . '/../PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // REMPLACEZ PAR VOTRE EMAIL ET MOT DE PASSE D'APPLICATION GMAIL
            $mail->Username   = 'rayancherif1808@gmail.com'; // VOTRE EMAIL GMAIL
            $mail->Password   = 'jowlfycholadtxba'; // MOT DE PASSE D'APPLICATION
            
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Expéditeur et destinataire
            $mail->setFrom('rayancherif1808@gmail.com', 'Ecobyte Support');
            $mail->addAddress($to);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br($message);
            $mail->AltBody = $message;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function validateId($id) {
        return is_numeric($id) && (int)$id > 0;
    }

    // ==========================================
    // MÉTHODES FACE ID (CAMÉRA AVEC FACE-API.JS)
    // ==========================================

    public function registerFaceDescriptor($userId, $descriptor) {
        try {
            // Le descriptor est un tableau JSON de 128 valeurs (Float)
            $query = "UPDATE users SET webauthn_public_key = :descriptor WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':descriptor' => $descriptor,
                ':id' => $userId
            ]);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function getAllFaceDescriptors() {
        try {
            $query = "SELECT id, nom, prenom, webauthn_public_key FROM users WHERE webauthn_public_key IS NOT NULL AND webauthn_public_key != ''";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function loginWithFaceId($userId) {
        try {
            $user = $this->db_getUserById($userId);
            if ($user) {
                if ($user['ban_until'] && strtotime($user['ban_until']) > time()) {
                    $this->errors[] = "Ce compte a été banni.";
                    return false;
                }

                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_photo'] = $user['photo'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                $_SESSION['logged_in'] = true;
                
                $this->checkAndNotifyLogin($user);
                return $user;
            }
            $this->errors[] = "Utilisateur introuvable.";
            return false;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function getErrors() { return $this->errors; }
    public function getSuccess() { return $this->success; }
    public function hasErrors() { return count($this->errors) > 0; }
}
?>
