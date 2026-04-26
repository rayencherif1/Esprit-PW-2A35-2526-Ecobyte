<?php
// controller/CommandeController.php
require_once __DIR__ . '/../model/Commande.php';
require_once __DIR__ . '/../model/Produit.php';

class CommandeController {
    private $db;
    private $produitModel;
    
    public function __construct() {
        $commande = new Commande();
        $this->db = $commande->getDb();
        $this->produitModel = new Produit();
    }
    
    // ========== MÉTHODES SQL ==========
    
    public function getAllCommandes() {
        $sql = "SELECT * FROM commandes ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getCommandeById($id) {
        $sql = "SELECT * FROM commandes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function getProduitsByCommandeId($commande_id) {
        $sql = "SELECT p.*, cp.quantite, cp.prix_unitaire
                FROM commande_produits cp 
                JOIN produits p ON cp.produit_id = p.id 
                WHERE cp.commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['commande_id' => $commande_id]);
        return $stmt->fetchAll();
    }
    
    // CREATE COMMANDE COMPLET AVEC TOUS LES CHAMPS
    public function createCommande($data) {
        $sql = "INSERT INTO commandes (
                    civilite, client_nom, client_prenom, client_email, client_telephone, date_naissance,
                    adresse, adresse_complement, code_postal, ville, pays, instructions_livraison,
                    adresse_facturation, code_postal_facturation, ville_facturation,
                    mode_livraison, frais_livraison, mode_paiement, code_promo, notes, total, date_commande
                ) VALUES (
                    :civilite, :client_nom, :client_prenom, :client_email, :client_telephone, :date_naissance,
                    :adresse, :adresse_complement, :code_postal, :ville, :pays, :instructions_livraison,
                    :adresse_facturation, :code_postal_facturation, :ville_facturation,
                    :mode_livraison, :frais_livraison, :mode_paiement, :code_promo, :notes, :total, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'civilite' => $data['civilite'] ?? 'M.',
            'client_nom' => $data['client_nom'] ?? '',
            'client_prenom' => $data['client_prenom'] ?? '',
            'client_email' => $data['client_email'] ?? '',
            'client_telephone' => $data['client_telephone'] ?? '',
            'date_naissance' => $data['date_naissance'] ?? null,
            'adresse' => $data['adresse'] ?? '',
            'adresse_complement' => $data['adresse_complement'] ?? '',
            'code_postal' => $data['code_postal'] ?? '',
            'ville' => $data['ville'] ?? '',
            'pays' => $data['pays'] ?? 'Tunisie',
            'instructions_livraison' => $data['instructions_livraison'] ?? '',
            'adresse_facturation' => $data['adresse_facturation'] ?? '',
            'code_postal_facturation' => $data['code_postal_facturation'] ?? '',
            'ville_facturation' => $data['ville_facturation'] ?? '',
            'mode_livraison' => $data['mode_livraison'] ?? 'standard',
            'frais_livraison' => $data['frais_livraison'] ?? 0,
            'mode_paiement' => $data['mode_paiement'] ?? 'carte',
            'code_promo' => $data['code_promo'] ?? '',
            'notes' => $data['notes'] ?? '',
            'total' => $data['total'] ?? 0
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function addProduitToCommande($commande_id, $produit_id, $quantite, $prix_unitaire) {
        $sql = "INSERT INTO commande_produits (commande_id, produit_id, quantite, prix_unitaire) 
                VALUES (:commande_id, :produit_id, :quantite, :prix_unitaire)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'commande_id' => $commande_id,
            'produit_id' => $produit_id,
            'quantite' => $quantite,
            'prix_unitaire' => $prix_unitaire
        ]);
    }
    
    public function deleteCommande($id) {
        // Supprimer d'abord les produits liés à la commande
        $sql = "DELETE FROM commande_produits WHERE commande_id = :commande_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['commande_id' => $id]);
        
        // Puis supprimer la commande
        $sql = "DELETE FROM commandes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function getProduitById($id) {
        $sql = "SELECT * FROM produits WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    // ========== MÉTHODES POUR LE ROUTEUR ==========
    
    public function index() {
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function detail() {
        $id = $_GET['id'] ?? 0;
        header('Location: /marketplace/view/back/pages/marketplace.php?action=detail_commande&id=' . $id);
        exit();
    }
    
    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        if ($id > 0) {
            $this->deleteCommande($id);
        }
        
        header('Location: /marketplace/view/back/pages/marketplace.php');
        exit();
    }
    
    public function panier() {
        session_start();
        $panier = $_SESSION['panier'] ?? [];
        $produits = [];
        $total = 0;
        
        foreach($panier as $produit_id => $item) {
            // Gérer le cas où $panier stocke soit un entier (quantité) soit un tableau
            if (is_array($item)) {
                $quantite = $item['quantite'];
                $nom = $item['nom'];
                $prix = $item['prix'];
                $produits[] = [
                    'id' => $produit_id,
                    'nom' => $nom,
                    'prix' => $prix,
                    'quantite' => $quantite,
                    'sous_total' => $prix * $quantite
                ];
                $total += $prix * $quantite;
            } else {
                // Ancien format (juste la quantité)
                $quantite = $item;
                $produit = $this->getProduitById($produit_id);
                if($produit) {
                    $produits[] = [
                        'id' => $produit['id'],
                        'nom' => $produit['nom'],
                        'prix' => $produit['prix'],
                        'quantite' => $quantite,
                        'sous_total' => $produit['prix'] * $quantite
                    ];
                    $total += $produit['prix'] * $quantite;
                }
            }
        }
        
        require_once __DIR__ . '/../view/front/panier.php';
    }
    
    public function addToPanier() {
        session_start();
        $produit_id = $_GET['id'] ?? 0;
        $quantite = intval($_GET['quantite'] ?? 1);
        
        // Récupérer les infos du produit
        $produit = $this->getProduitById($produit_id);
        
        if (!$produit) {
            header('Location: /marketplace/view/front/index2.php');
            exit();
        }
        
        if(!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
        
        // CORRECTION : Vérifier le type de la valeur existante
        if(isset($_SESSION['panier'][$produit_id])) {
            // Si c'est un tableau (nouveau format)
            if(is_array($_SESSION['panier'][$produit_id])) {
                $_SESSION['panier'][$produit_id]['quantite'] += $quantite;
            } 
            // Si c'est un nombre (ancien format à migrer)
            else {
                $ancienne_quantite = $_SESSION['panier'][$produit_id];
                $_SESSION['panier'][$produit_id] = [
                    'id' => $produit['id'],
                    'nom' => $produit['nom'],
                    'prix' => $produit['prix'],
                    'quantite' => $ancienne_quantite + $quantite
                ];
            }
        } else {
            $_SESSION['panier'][$produit_id] = [
                'id' => $produit['id'],
                'nom' => $produit['nom'],
                'prix' => $produit['prix'],
                'quantite' => $quantite
            ];
        }
        
        header('Location: /marketplace/index.php?controller=commande&action=panier');
        exit();
    }
    
    public function removeFromPanier() {
        session_start();
        $produit_id = $_GET['id'] ?? 0;
        
        if(isset($_SESSION['panier'][$produit_id])) {
            unset($_SESSION['panier'][$produit_id]);
        }
        
        header('Location: /marketplace/index.php?controller=commande&action=panier');
        exit();
    }
    
    public function clearPanier() {
        session_start();
        $_SESSION['panier'] = [];
        header('Location: /marketplace/index.php?controller=commande&action=panier');
        exit();
    }
    
    public function checkout() {
        session_start();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $panier = $_SESSION['panier'] ?? [];
            
            if(empty($panier)) {
                $_SESSION['error'] = "Votre panier est vide";
                header('Location: /marketplace/index.php?controller=commande&action=panier');
                exit();
            }
            
            // Calcul du sous-total
            $subtotal = 0;
            $panier_items = [];
            
            foreach($panier as $produit_id => $item) {
                if (is_array($item)) {
                    $quantite = $item['quantite'];
                    $prix = $item['prix'];
                    $subtotal += $prix * $quantite;
                    $panier_items[$produit_id] = ['quantite' => $quantite, 'prix' => $prix];
                } else {
                    $quantite = $item;
                    $produit = $this->getProduitById($produit_id);
                    $prix = $produit['prix'];
                    $subtotal += $prix * $quantite;
                    $panier_items[$produit_id] = ['quantite' => $quantite, 'prix' => $prix];
                }
            }
            
            // Application du code promo
            $code_promo = $_POST['code_promo'] ?? '';
            $promoDiscount = 0;
            if($code_promo === 'ECOBITE10') {
                $promoDiscount = $subtotal * 0.10;
            } elseif($code_promo === 'BIENVENUE20') {
                $promoDiscount = $subtotal * 0.20;
            }
            
            // Frais de livraison
            $frais_livraison = floatval($_POST['frais_livraison'] ?? 0);
            
            // Total final
            $total = $subtotal + $frais_livraison - $promoDiscount;
            
            // Préparer les données pour la commande
            $data = [
                'civilite' => $_POST['civilite'] ?? 'M.',
                'client_nom' => $_POST['client_nom'] ?? '',
                'client_prenom' => $_POST['client_prenom'] ?? '',
                'client_email' => $_POST['client_email'] ?? '',
                'client_telephone' => $_POST['client_telephone'] ?? '',
                'date_naissance' => $_POST['date_naissance'] ?? null,
                'adresse' => $_POST['adresse'] ?? '',
                'adresse_complement' => $_POST['adresse_complement'] ?? '',
                'code_postal' => $_POST['code_postal'] ?? '',
                'ville' => $_POST['ville'] ?? '',
                'pays' => $_POST['pays'] ?? 'Tunisie',
                'instructions_livraison' => $_POST['instructions_livraison'] ?? '',
                'adresse_facturation' => $_POST['adresse_facturation'] ?? '',
                'code_postal_facturation' => $_POST['code_postal_facturation'] ?? '',
                'ville_facturation' => $_POST['ville_facturation'] ?? '',
                'mode_livraison' => $_POST['mode_livraison'] ?? 'standard',
                'frais_livraison' => $frais_livraison,
                'mode_paiement' => $_POST['mode_paiement'] ?? 'carte',
                'code_promo' => $code_promo,
                'notes' => $_POST['notes'] ?? '',
                'total' => $total
            ];
            
            // Créer la commande
            $commande_id = $this->createCommande($data);
            
            // Ajouter les produits à la commande avec prix_unitaire
            foreach($panier_items as $produit_id => $item) {
                $this->addProduitToCommande($commande_id, $produit_id, $item['quantite'], $item['prix']);
                
                // Mettre à jour le stock
                $sql_stock = "UPDATE produits SET stock = stock - :quantite WHERE id = :id AND stock >= :quantite";
                $stmt_stock = $this->db->prepare($sql_stock);
                $stmt_stock->execute([
                    'quantite' => $item['quantite'],
                    'id' => $produit_id
                ]);
            }
            
            // Vider le panier
            $_SESSION['panier'] = [];
            
            // Rediriger vers la page de confirmation
            header('Location: /marketplace/index.php?controller=commande&action=confirmation&id=' . $commande_id);
            exit();
        }
        
        // Si GET, afficher le formulaire
        $this->panier();
    }
    
    public function confirmation() {
        $id = $_GET['id'] ?? 0;
        $commande = $this->getCommandeById($id);
        
        if (!$commande) {
            header('Location: /marketplace/view/front/index2.php');
            exit();
        }
        
        $produits = $this->getProduitsByCommandeId($id);
        require_once __DIR__ . '/../view/front/confirmation.php';
    }
    
    public function resetPanier() {
        session_start();
        $_SESSION['panier'] = [];
        header('Location: /marketplace/index.php?controller=commande&action=panier');
        exit();
    }
}
?>