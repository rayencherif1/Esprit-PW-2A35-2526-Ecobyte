<?php
// controller/ExportController.php
require_once __DIR__ . '/../model/Produit.php';
require_once __DIR__ . '/../model/Categorie.php';
require_once __DIR__ . '/../model/Commande.php';
require_once __DIR__ . '/../lib/fpdf186/fpdf.php';

class ExportController {
    private $db;
    
    // Couleurs personnalisees
    private $primaryColor = array(46, 125, 50);      // Vert ecologie
    private $secondaryColor = array(0, 100, 60);     // Vert fonce
    private $accentColor = array(255, 193, 7);       // Or
    private $lightBg = array(240, 248, 245);         // Fond clair vert
    private $white = array(255, 255, 255);
    private $darkText = array(51, 51, 51);
    private $grayText = array(128, 128, 128);
    private $errorColor = array(220, 53, 69);        // Rouge pour rupture
    private $warningColor = array(255, 193, 7);      // Orange pour stock faible
    private $successColor = array(40, 167, 69);      // Vert pour disponible
    
    public function __construct() {
        $produit = new Produit();
        $this->db = $produit->getDb();
    }
    
    /**
     * Convertir UTF-8 vers ISO-8859-1 pour FPDF
     */
    private function toLatin1($text) {
        if (is_null($text)) return '';
        // Convertir UTF-8 en entities HTML, puis en ISO-8859-1
        $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        $text = html_entity_decode($text, ENT_QUOTES, 'ISO-8859-1');
        return $text;
    }
    
    /**
     * En-tete de page stylise
     */
    private function addHeader($pdf, $title) {
        // Bandeau colore en haut
        $pdf->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $pdf->Rect(0, 0, 210, 28, 'F');
        
        // Titre
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 28, $this->toLatin1($title), 0, 1, 'C');
        
        // Ligne decorative
        $pdf->SetFillColor($this->accentColor[0], $this->accentColor[1], $this->accentColor[2]);
        $pdf->Rect(0, 28, 210, 4, 'F');
        
        // Reset
        $pdf->SetTextColor($this->darkText[0], $this->darkText[1], $this->darkText[2]);
        $pdf->Ln(12);
    }
    
    /**
     * Pied de page stylise
     */
    private function addFooter($pdf) {
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor($this->grayText[0], $this->grayText[1], $this->grayText[2]);
        $pdf->Cell(0, 10, 'EcoBite - ' . date('d/m/Y') . ' - Page ' . $pdf->PageNo(), 0, 0, 'C');
        $pdf->SetTextColor($this->darkText[0], $this->darkText[1], $this->darkText[2]);
    }
    
    /**
     * En-tete de tableau colore
     */
    private function tableHeader($pdf, $headers) {
        $pdf->SetFillColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetDrawColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        
        foreach ($headers as $width => $text) {
            $pdf->Cell($width, 10, $this->toLatin1($text), 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        $pdf->SetTextColor($this->darkText[0], $this->darkText[1], $this->darkText[2]);
    }
    
    /**
     * Ligne de tableau avec couleur alternee
     */
    private function tableRow($pdf, $cells, $fill = false) {
        $pdf->SetFont('Arial', '', 10);
        
        if ($fill) {
            $pdf->SetFillColor($this->lightBg[0], $this->lightBg[1], $this->lightBg[2]);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        
        $pdf->SetDrawColor(200, 200, 200);
        
        foreach ($cells as $width => $text) {
            $pdf->Cell($width, 8, $this->toLatin1($text), 1, 0, 'L', $fill);
        }
        $pdf->Ln();
    }
    
    /**
     * Exporter les produits en PDF avec mise en forme elegante
     */
    public function exportProduitsPDF() {
        $produitController = new ProduitController();
        $produits = $produitController->getAllProduits();

        $pdf = new FPDF();
        $pdf->AddPage();
        $this->addHeader($pdf, 'Liste des Produits');
        
        // En-tete du tableau (sans ID)
        $headers = array(
            75 => 'Nom du Produit',
            40 => 'Prix',
            35 => 'Stock',
            50 => 'Statut'
        );
        $this->tableHeader($pdf, $headers);

        // Donnees avec lignes alternees
        $fill = false;
        foreach($produits as $p) {
            $statut = 'Disponible';
            if(isset($p['stock'])) {
                if($p['stock'] <= 0) {
                    $statut = 'Rupture de stock';
                } elseif($p['stock'] < 5) {
                    $statut = 'Stock faible';
                }
            }
            $nom = isset($p['nom']) && trim($p['nom']) !== '' ? $this->toLatin1($p['nom']) : '-';
            $prix = isset($p['prix']) && $p['prix'] !== '' ? number_format($p['prix'], 2, ',', ' ') . ' DT' : '-';
            $stock = isset($p['stock']) && $p['stock'] !== '' ? $p['stock'] . ' unites' : '-';
            $statutAff = $statut !== '' ? $this->toLatin1($statut) : '-';
            $cells = array(
                75 => $nom,
                40 => $prix,
                35 => $stock,
                50 => $statutAff
            );
            $this->tableRow($pdf, $cells, $fill);
            $fill = !$fill;
        }

        // Total
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor($this->lightBg[0], $this->lightBg[1], $this->lightBg[2]);
        $pdf->Cell(0, 10, 'Total: ' . count($produits) . ' produits', 0, 1, 'L', true);
        
        $this->addFooter($pdf);
        $pdf->Output('D', 'produits_ecobite_'.date('Ymd').'.pdf');
        exit;
    }
    
    /**
     * Exporter les commandes en PDF
     */
    public function exportCommandesPDF() {
        $commandeController = new CommandeController();
        $commandes = $commandeController->getAllCommandes();

        $pdf = new FPDF();
        $pdf->AddPage();
        $this->addHeader($pdf, 'Historique des Commandes');
        
        // En-tete du tableau (sans ID)
        $headers = array(
            55 => 'Client',
            65 => 'Email',
            35 => 'Date',
            30 => 'Total',
            25 => 'Statut'
        );
        $this->tableHeader($pdf, $headers);

        $fill = false;
        $totalRevenus = 0;
        
        foreach($commandes as $c) {
            $totalRevenus += isset($c['total']) ? $c['total'] : 0;
            $statut = isset($c['statut']) && $c['statut'] !== '' ? $c['statut'] : 'En attente';
            $client = isset($c['client_nom']) && trim($c['client_nom']) !== '' ? $this->toLatin1($c['client_nom']) : '-';
            $email = isset($c['client_email']) && trim($c['client_email']) !== '' ? $this->toLatin1($c['client_email']) : '-';
            $date = isset($c['date_commande']) && $c['date_commande'] !== '' ? date('d/m/Y', strtotime($c['date_commande'])) : '-';
            $total = isset($c['total']) && $c['total'] !== '' ? number_format($c['total'], 2, ',', ' ') . ' DT' : '-';
            $statutAff = $this->toLatin1($statut);
            $cells = array(
                55 => $client,
                65 => $email,
                35 => $date,
                30 => $total,
                25 => $statutAff
            );
            $this->tableRow($pdf, $cells, $fill);
            $fill = !$fill;
        }

        // Total revenus
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor($this->lightBg[0], $this->lightBg[1], $this->lightBg[2]);
        $pdf->Cell(0, 10, 'Total revenus: ' . number_format($totalRevenus, 2, ',', ' ') . ' DT', 0, 1, 'L', true);
        
        $this->addFooter($pdf);
        $pdf->Output('D', 'commandes_ecobite_'.date('Ymd').'.pdf');
        exit;
    }
    
    /**
     * Exporter les categories en PDF
     */
    public function exportCategoriesPDF() {
        $categorieController = new CategorieController();
        $categories = $categorieController->getAllCategories();

        $pdf = new FPDF();
        $pdf->AddPage();
        $this->addHeader($pdf, 'Categories de Produits');
        
        // En-tete du tableau (sans ID)
        $headers = array(
            70 => 'Nom',
            120 => 'Description'
        );
        $this->tableHeader($pdf, $headers);

        $fill = false;
        foreach($categories as $cat) {
            $nom = isset($cat['nom']) && trim($cat['nom']) !== '' ? $this->toLatin1($cat['nom']) : '-';
            $desc = isset($cat['description']) && trim($cat['description']) !== '' ? $this->toLatin1($cat['description']) : 'Aucune description disponible';
            $cells = array(
                70 => $nom,
                120 => $desc
            );
            $this->tableRow($pdf, $cells, $fill);
            $fill = !$fill;
        }

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor($this->lightBg[0], $this->lightBg[1], $this->lightBg[2]);
        $pdf->Cell(0, 10, 'Total: ' . count($categories) . ' categories', 0, 1, 'L', true);
        
        $this->addFooter($pdf);
        $pdf->Output('D', 'categories_ecobite_'.date('Ymd').'.pdf');
        exit;
    }
    
    /**
     * Exporter un rapport complet (produits + commandes + categories)
     */
    public function exportRapportCompletPDF() {
        $produitController = new ProduitController();
        $categorieController = new CategorieController();
        $commandeController = new CommandeController();

        $produits = $produitController->getAllProduits();
        $categories = $categorieController->getAllCategories();
        $commandes = $commandeController->getAllCommandes();
        $totalRevenus = array_sum(array_column($commandes, 'total'));

        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Page de garde
        $this->addHeader($pdf, 'Rapport Complet');
        
        // Stats globales dans un cadre elegant
        $pdf->SetFillColor($this->lightBg[0], $this->lightBg[1], $this->lightBg[2]);
        $pdf->SetDrawColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $pdf->SetLineWidth(1);
        $pdf->Rect(20, 50, 170, 80, 'D');
        
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
        $pdf->Cell(0, 18, 'Statistiques Globales', 0, 1, 'C');
        
        $pdf->SetFont('Arial', '', 13);
        $pdf->SetTextColor($this->darkText[0], $this->darkText[1], $this->darkText[2]);
        
        $pdf->Cell(0, 12, 'Nombre de produits: ' . count($produits), 0, 1, 'C');
        $pdf->Cell(0, 12, 'Nombre de categories: ' . count($categories), 0, 1, 'C');
        $pdf->Cell(0, 12, 'Nombre de commandes: ' . count($commandes), 0, 1, 'C');
        
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $pdf->Cell(0, 14, 'Revenus totaux: ' . number_format($totalRevenus, 2, ',', ' ') . ' DT', 0, 1, 'C');
        
        $this->addFooter($pdf);

        // PAGE PRODUITS
        $pdf->AddPage();
        $this->addHeader($pdf, 'Liste des Produits');
        
        $headers = array(
            75 => 'Nom du Produit',
            40 => 'Prix',
            35 => 'Stock',
            50 => 'Statut'
        );
        $this->tableHeader($pdf, $headers);

        $fill = false;
        foreach($produits as $p) {
            $statut = 'Disponible';
            if(isset($p['stock'])) {
                if($p['stock'] <= 0) $statut = 'Rupture de stock';
                elseif($p['stock'] < 5) $statut = 'Stock faible';
            }
            $nom = isset($p['nom']) && trim($p['nom']) !== '' ? $this->toLatin1($p['nom']) : '-';
            $prix = isset($p['prix']) && $p['prix'] !== '' ? number_format($p['prix'], 2, ',', ' ') . ' DT' : '-';
            $stock = isset($p['stock']) && $p['stock'] !== '' ? $p['stock'] : '-';
            $statutAff = $statut !== '' ? $this->toLatin1($statut) : '-';
            $cells = array(
                75 => $nom,
                40 => $prix,
                35 => $stock,
                50 => $statutAff
            );
            $this->tableRow($pdf, $cells, $fill);
            $fill = !$fill;
        }

        // PAGE COMMANDES
        $pdf->AddPage();
        $this->addHeader($pdf, 'Historique des Commandes');
        
        $headers = array(
            55 => 'Client',
            65 => 'Email',
            35 => 'Date',
            30 => 'Total',
            25 => 'Statut'
        );
        $this->tableHeader($pdf, $headers);

        $fill = false;
        foreach($commandes as $c) {
            $client = isset($c['client_nom']) && trim($c['client_nom']) !== '' ? $this->toLatin1($c['client_nom']) : '-';
            $email = isset($c['client_email']) && trim($c['client_email']) !== '' ? $this->toLatin1($c['client_email']) : '-';
            $date = isset($c['date_commande']) && $c['date_commande'] !== '' ? date('d/m/Y', strtotime($c['date_commande'])) : '-';
            $total = isset($c['total']) && $c['total'] !== '' ? number_format($c['total'], 2, ',', ' ') . ' DT' : '-';
            $statut = isset($c['statut']) && $c['statut'] !== '' ? $this->toLatin1($c['statut']) : 'En attente';
            $cells = array(
                55 => $client,
                65 => $email,
                35 => $date,
                30 => $total,
                25 => $statut
            );
            $this->tableRow($pdf, $cells, $fill);
            $fill = !$fill;
        }

        // PAGE CATEGORIES
        $pdf->AddPage();
        $this->addHeader($pdf, 'Categories de Produits');
        
        $headers = array(
            70 => 'Nom',
            120 => 'Description'
        );
        $this->tableHeader($pdf, $headers);

        $fill = false;
        foreach($categories as $cat) {
            $nom = isset($cat['nom']) && trim($cat['nom']) !== '' ? $this->toLatin1($cat['nom']) : '-';
            $desc = isset($cat['description']) && trim($cat['description']) !== '' ? $this->toLatin1($cat['description']) : 'Aucune description disponible';
            $cells = array(
                70 => $nom,
                120 => $desc
            );
            $this->tableRow($pdf, $cells, $fill);
            $fill = !$fill;
        }

        $pdf->Output('D', 'rapport_complet_ecobite_'.date('Ymd').'.pdf');
        exit;
    }
}
?>