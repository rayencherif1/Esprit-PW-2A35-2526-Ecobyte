<?php
require_once __DIR__ . '/../../controller/ProduitController.php';

if (isset($_GET['query'])) {
    $produitController = new ProduitController();
    $query = trim($_GET['query']);
    $catId = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
    
    $results = $produitController->searchProduits($query);
    
    if ($catId > 0) {
        $results = array_filter($results, function($p) use ($catId) {
            return $p['categorie_id'] == $catId;
        });
    }
    
    header('Content-Type: application/json');
    $output = [];
    foreach(array_slice($results, 0, 8) as $p) {
        $output[] = [
            'id' => $p['id'],
            'nom' => $p['nom'],
            'prix' => number_format($p['prix'], 2),
            'categorie' => $p['categorie_nom'] ?? 'Produit',
            'url' => 'index2.php?search=' . urlencode($p['nom']) . '#produits-section'
        ];
    }
    echo json_encode(array_values($output));
    exit;
}
?>
