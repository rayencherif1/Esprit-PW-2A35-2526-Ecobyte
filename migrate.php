<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=marketplace;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Check if columns exist and add them if not
    $columns = $db->query("SHOW COLUMNS FROM produits")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('date_ajout', $columns)) {
        $db->exec("ALTER TABLE produits ADD COLUMN date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "Colonne date_ajout ajoutée.\n";
    }
    
    if (!in_array('is_promo', $columns)) {
        $db->exec("ALTER TABLE produits ADD COLUMN is_promo TINYINT(1) DEFAULT 0");
        echo "Colonne is_promo ajoutée.\n";
    }
    
    if (!in_array('ventes', $columns)) {
        $db->exec("ALTER TABLE produits ADD COLUMN ventes INT DEFAULT 0");
        echo "Colonne ventes ajoutée.\n";
    }
    
    if (!in_array('prix_promo', $columns)) {
        $db->exec("ALTER TABLE produits ADD COLUMN prix_promo DECIMAL(10,2) NULL");
        echo "Colonne prix_promo ajoutée.\n";
    }

    // 2. Populate columns with random dummy data to test filters
    $produits = $db->query("SELECT id FROM produits")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($produits as $id) {
        $is_promo = (rand(1, 10) <= 3) ? 1 : 0; // 30% chance to be on sale
        $ventes = rand(0, 500); // Random sales between 0 and 500
        
        // Random date within the last 6 months
        $timestamp = time() - rand(0, 180 * 24 * 60 * 60);
        $date_ajout = date('Y-m-d H:i:s', $timestamp);
        
        $stmt = $db->prepare("UPDATE produits SET is_promo = :is_promo, ventes = :ventes, date_ajout = :date_ajout WHERE id = :id");
        $stmt->execute([
            'is_promo' => $is_promo,
            'ventes' => $ventes,
            'date_ajout' => $date_ajout,
            'id' => $id
        ]);
    }
    echo "Produits mis à jour avec des données de test (promo, ventes, dates).\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
