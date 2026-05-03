<?php
// controller/FavorisController.php
require_once __DIR__ . '/../model/Produit.php';

class FavorisController {
    private $db;
    
    public function __construct() {
        $produit = new Produit();
        $this->db = $produit->getDb();
    }
    
    // Vérifier et démarrer la session si nécessaire
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function isAjaxRequest(): bool {
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return strtolower($requestedWith) === 'xmlhttprequest';
    }

    private function json(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit();
    }

    private function produitExists(int $produit_id): bool {
        $sql = "SELECT id FROM produits WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $produit_id]);
        return $stmt->fetch() !== false;
    }
    
    // Ajouter/Retirer des favoris (toggle)
    public function add() {
        $this->checkSession();
        $produit_id = intval($_GET['id'] ?? 0);
        $user_id = intval($_SESSION['user_id'] ?? 1);

        if ($produit_id <= 0 || !$this->produitExists($produit_id)) {
            if ($this->isAjaxRequest()) {
                $this->json(['ok' => false, 'message' => 'Produit introuvable'], 404);
            }
            $_SESSION['error'] = "Produit introuvable";
            $redirect = $_SERVER['HTTP_REFERER'] ?? '/marketplace/view/front/index2.php';
            header('Location: ' . $redirect);
            exit();
        }

        try {
            // Vérifier si déjà dans les favoris
            $sql = "SELECT 1 FROM favoris WHERE user_id = :user_id AND produit_id = :produit_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
            $alreadyFavori = $stmt->fetch() !== false;

            if (!$alreadyFavori) {
                $sql = "INSERT INTO favoris (user_id, produit_id) VALUES (:user_id, :produit_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
                $_SESSION['success'] = "Produit ajouté aux favoris";
                if ($this->isAjaxRequest()) {
                    $this->json(['ok' => true, 'action' => 'added', 'produit_id' => $produit_id]);
                }
            } else {
                $sql = "DELETE FROM favoris WHERE user_id = :user_id AND produit_id = :produit_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
                $_SESSION['success'] = "Produit retiré des favoris";
                if ($this->isAjaxRequest()) {
                    $this->json(['ok' => true, 'action' => 'removed', 'produit_id' => $produit_id]);
                }
            }
        } catch (PDOException $e) {
            if ($this->isAjaxRequest()) {
                $this->json(['ok' => false, 'message' => 'Erreur base favoris'], 500);
            }
            $_SESSION['error'] = "Erreur favoris";
        }

        $redirect = $_SERVER['HTTP_REFERER'] ?? '/marketplace/view/front/index2.php';
        header('Location: ' . $redirect);
        exit();
    }
    
    // Retirer des favoris (méthode séparée)
    public function remove() {
        $this->checkSession();
        $produit_id = intval($_GET['id'] ?? 0);
        $user_id = intval($_SESSION['user_id'] ?? 1);
        
        if ($produit_id > 0) {
            $sql = "DELETE FROM favoris WHERE user_id = :user_id AND produit_id = :produit_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
            $_SESSION['success'] = "Produit retiré des favoris";
            if ($this->isAjaxRequest()) {
                $this->json(['ok' => true, 'action' => 'removed', 'produit_id' => $produit_id]);
            }
        }
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/marketplace/view/front/index2.php'));
        exit();
    }
    
    // Récupérer les favoris d'un utilisateur
    public function getFavoris() {
        $this->checkSession();
        $user_id = $_SESSION['user_id'] ?? 1;
        
        $sql = "SELECT p.* FROM produits p 
                JOIN favoris f ON p.id = f.produit_id 
                WHERE f.user_id = :user_id 
                ORDER BY f.date_ajout DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    }
    
    // Vérifier si un produit est favori
    public function isFavori($produit_id) {
        $this->checkSession();
        $user_id = $_SESSION['user_id'] ?? 1;
        
        $sql = "SELECT * FROM favoris WHERE user_id = :user_id AND produit_id = :produit_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'produit_id' => $produit_id]);
        return $stmt->fetch() !== false;
    }
    
    // Afficher la page des favoris
    public function index() {
        $this->checkSession();
        $favoris = $this->getFavoris();
        require_once __DIR__ . '/../view/front/favoris.php';
    }
}
?>