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

    private function quickChartUrl(string $chartConfigJson, int $width = 900, int $height = 500): string {
        $encoded = urlencode($chartConfigJson);
        return "https://quickchart.io/chart?c={$encoded}&w={$width}&h={$height}&format=png&backgroundColor=white";
    }

    private function downloadToTempPng(string $url): ?string {
        $tmp = tempnam(sys_get_temp_dir(), 'ecobite_chart_');
        if ($tmp === false) return null;
        $path = $tmp . '.png';
        @unlink($tmp);
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'header' => "User-Agent: EcoBite/1.0\r\n"
            ]
        ]);
        $data = @file_get_contents($url, false, $ctx);
        if ($data === false) return null;
        file_put_contents($path, $data);
        return $path;
    }
    
    public function __construct() {
        $produit = new Produit();
        $this->db = $produit->getDb();
    }
    
    /**
     * Convertir UTF-8 vers ISO-8859-1 pour FPDF
     */
    private function toLatin1($text) {
        if (is_null($text)) return '';
        $text = (string)$text;
        if ($text === '') return '';

        // 1) Convertir en ISO-8859-1 avec translit (évite des "vides" sur caractères non supportés)
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
            if ($converted !== false && $converted !== '') {
                return $converted;
            }
        }

        // 2) Fallback entities -> latin1
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
            $txt = $text;
            if (is_null($txt) || (is_string($txt) && trim($txt) === '')) $txt = '-';
            $txt = $this->toLatin1((string)$txt);
            // Fit text to cell width to avoid "blank-looking" overflows
            $max = max(5, $width - 4);
            while ($pdf->GetStringWidth($txt) > $max && mb_strlen($txt) > 4) {
                $txt = mb_substr($txt, 0, -2);
                $txt = rtrim($txt) . '…';
            }
            $pdf->Cell($width, 8, $txt, 1, 0, 'L', $fill);
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

    /**
     * Exporter les statistiques + charts en PDF (QuickChart)
     */
    public function exportStatsPDF() {
        $produitController = new ProduitController();
        $categorieController = new CategorieController();
        $commandeController = new CommandeController();

        $produits = $produitController->getAllProduits();
        $categories = $categorieController->getAllCategories();
        $commandes = $commandeController->getAllCommandes();

        // Revenu réel
        $revenuTotal = 0;
        $panierMoyen = 0;
        $totalItemsVendus = 0;
        try {
            $row = $this->db->query("SELECT 
                    COALESCE(SUM(cp.quantite * cp.prix_unitaire), 0) AS ca_total,
                    COUNT(DISTINCT cp.commande_id) AS nb_commandes,
                    COALESCE(SUM(cp.quantite), 0) AS items_total
                FROM commande_produits cp")->fetch();
            $revenuTotal = floatval($row['ca_total'] ?? 0);
            $nbCmd = intval($row['nb_commandes'] ?? 0);
            $totalItemsVendus = intval($row['items_total'] ?? 0);
            $panierMoyen = $nbCmd > 0 ? ($revenuTotal / $nbCmd) : 0;
        } catch (Exception $e) {}

        $promoCount = 0;
        $stockStats = ['Rupture' => 0, 'Faible (<5)' => 0, 'OK' => 0];
        $ventesByCat = [];

        foreach ($produits as $p) {
            if (!empty($p['is_promo'])) $promoCount++;
            $s = intval($p['stock'] ?? 0);
            if ($s <= 0) $stockStats['Rupture']++;
            elseif ($s < 5) $stockStats['Faible (<5)']++;
            else $stockStats['OK']++;
        }

        // Ventes par catégorie (réel)
        try {
            $stmt = $this->db->query("
                SELECT COALESCE(cat.nom,'Sans catégorie') AS categorie,
                       COALESCE(SUM(cp.quantite),0) AS qte
                FROM commande_produits cp
                JOIN produits p ON p.id = cp.produit_id
                LEFT JOIN categories cat ON cat.id = p.categorie_id
                GROUP BY categorie
                ORDER BY qte DESC
                LIMIT 6
            ");
            $rows = $stmt->fetchAll() ?: [];
            foreach ($rows as $r) $ventesByCat[$r['categorie']] = intval($r['qte']);
        } catch (Exception $e) {}

        // CA par mois (6 derniers mois)
        $caParMois = [];
        try {
            $stmt = $this->db->query("
                SELECT DATE_FORMAT(c.date_commande, '%Y-%m') AS ym,
                       COALESCE(SUM(cp.quantite * cp.prix_unitaire), 0) AS ca
                FROM commandes c
                JOIN commande_produits cp ON cp.commande_id = c.id
                WHERE c.date_commande >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY ym
                ORDER BY ym ASC
            ");
            $caParMois = $stmt->fetchAll() ?: [];
        } catch (Exception $e) {}

        // Top produits (quantité)
        $topProduits = [];
        try {
            $stmt = $this->db->query("
                SELECT p.nom, COALESCE(SUM(cp.quantite),0) AS qte
                FROM commande_produits cp
                JOIN produits p ON p.id = cp.produit_id
                GROUP BY p.id, p.nom
                ORDER BY qte DESC
                LIMIT 5
            ");
            $topProduits = $stmt->fetchAll() ?: [];
        } catch (Exception $e) {}

        $pdf = new FPDF();
        $pdf->AddPage();
        $this->addHeader($pdf, 'Statistiques EcoBite');

        // Résumé
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, $this->toLatin1('Résumé'), 0, 1);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 7, $this->toLatin1('Produits: ' . count($produits) . ' | Catégories: ' . count($categories) . ' | Commandes: ' . count($commandes)), 0, 1);
        $pdf->Cell(0, 7, $this->toLatin1('Revenus totaux: ' . number_format($revenuTotal, 2, ',', ' ') . ' DT | Panier moyen: ' . number_format($panierMoyen, 2, ',', ' ') . ' DT'), 0, 1);
        $pdf->Cell(0, 7, $this->toLatin1('Articles vendus: ' . $totalItemsVendus . ' | Produits en promo: ' . $promoCount), 0, 1);
        $pdf->Ln(4);

        // Chart 1: ventes par catégorie
        $chart1 = json_encode([
            'type' => 'bar',
            'data' => [
                'labels' => array_keys($ventesByCat),
                'datasets' => [[
                    'label' => 'Ventes',
                    'data' => array_values($ventesByCat),
                    'backgroundColor' => 'rgba(46,125,50,0.25)',
                    'borderColor' => 'rgba(46,125,50,1)',
                    'borderWidth' => 2,
                    'borderRadius' => 10
                ]]
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true]]
            ]
        ], JSON_UNESCAPED_UNICODE);
        $url1 = $this->quickChartUrl($chart1, 900, 420);
        $png1 = $this->downloadToTempPng($url1);
        if ($png1) {
            $pdf->Image($png1, 15, $pdf->GetY(), 180);
            $pdf->Ln(90);
            @unlink($png1);
        }

        // Chart CA/mois
        if (!empty($caParMois)) {
            $chartCa = json_encode([
                'type' => 'line',
                'data' => [
                    'labels' => array_map(fn($r) => $r['ym'], $caParMois),
                    'datasets' => [[
                        'label' => 'CA',
                        'data' => array_map(fn($r) => (float)$r['ca'], $caParMois),
                        'borderColor' => 'rgba(33,150,243,1)',
                        'backgroundColor' => 'rgba(33,150,243,0.15)',
                        'fill' => true,
                        'tension' => 0.35
                    ]]
                ],
                'options' => ['plugins' => ['legend' => ['display' => false]]]
            ], JSON_UNESCAPED_UNICODE);
            $urlCa = $this->quickChartUrl($chartCa, 900, 420);
            $pngCa = $this->downloadToTempPng($urlCa);
            if ($pngCa) {
                $pdf->Image($pngCa, 15, $pdf->GetY(), 180);
                $pdf->Ln(90);
                @unlink($pngCa);
            }
        }

        // Chart top produits
        if (!empty($topProduits)) {
            $chartTop = json_encode([
                'type' => 'bar',
                'data' => [
                    'labels' => array_map(fn($r) => $r['nom'], $topProduits),
                    'datasets' => [[
                        'label' => 'Quantités',
                        'data' => array_map(fn($r) => (int)$r['qte'], $topProduits),
                        'backgroundColor' => 'rgba(76,175,80,0.25)',
                        'borderColor' => 'rgba(76,175,80,1)',
                        'borderWidth' => 2,
                        'borderRadius' => 10
                    ]]
                ],
                'options' => ['plugins' => ['legend' => ['display' => false]]]
            ], JSON_UNESCAPED_UNICODE);
            $urlTop = $this->quickChartUrl($chartTop, 900, 420);
            $pngTop = $this->downloadToTempPng($urlTop);
            if ($pngTop) {
                $pdf->AddPage();
                $this->addHeader($pdf, 'Top Produits');
                $pdf->Image($pngTop, 15, $pdf->GetY(), 180);
                $pdf->Ln(90);
                @unlink($pngTop);
            }
        }

        // Chart 2: stock + promo
        $chart2 = json_encode([
            'type' => 'doughnut',
            'data' => [
                'labels' => array_keys($stockStats),
                'datasets' => [[
                    'data' => array_values($stockStats),
                    'backgroundColor' => ['#ef5350', '#ffb300', '#66bb6a'],
                    'borderWidth' => 0
                ]]
            ],
            'options' => [
                'plugins' => ['legend' => ['position' => 'bottom']],
                'cutout' => '60%'
            ]
        ], JSON_UNESCAPED_UNICODE);
        $url2 = $this->quickChartUrl($chart2, 900, 420);
        $png2 = $this->downloadToTempPng($url2);

        $promoStats = ['En promo' => $promoCount, 'Hors promo' => max(0, count($produits) - $promoCount)];
        $chart3 = json_encode([
            'type' => 'doughnut',
            'data' => [
                'labels' => array_keys($promoStats),
                'datasets' => [[
                    'data' => array_values($promoStats),
                    'backgroundColor' => ['#fb8c00', '#90a4ae'],
                    'borderWidth' => 0
                ]]
            ],
            'options' => [
                'plugins' => ['legend' => ['position' => 'bottom']],
                'cutout' => '60%'
            ]
        ], JSON_UNESCAPED_UNICODE);
        $url3 = $this->quickChartUrl($chart3, 900, 420);
        $png3 = $this->downloadToTempPng($url3);

        $pdf->AddPage();
        $this->addHeader($pdf, 'Stocks & Promotions');

        if ($png2) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $this->toLatin1('Stock'), 0, 1);
            $pdf->Image($png2, 15, $pdf->GetY(), 180);
            $pdf->Ln(90);
            @unlink($png2);
        }
        if ($png3) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $this->toLatin1('Promotions'), 0, 1);
            $pdf->Image($png3, 15, $pdf->GetY(), 180);
            $pdf->Ln(90);
            @unlink($png3);
        }

        $this->addFooter($pdf);
        $pdf->Output('D', 'stats_ecobite_' . date('Ymd') . '.pdf');
        exit;
    }
}
?>