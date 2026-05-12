<?php
/**
 * Script pour ajouter manuellement la colonne summary
 */

require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();

    echo "<h2>🔧 Ajout manuel de la colonne 'summary'</h2>";

    // Vérifier d'abord si la colonne existe
    $stmt = $db->query("SHOW COLUMNS FROM post LIKE 'summary'");
    $exists = $stmt->rowCount() > 0;

    if ($exists) {
        echo "<p style='color: green;'>✅ La colonne 'summary' existe déjà !</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ La colonne 'summary' n'existe pas. Tentative d'ajout...</p>";

        // Ajouter la colonne
        $db->exec("ALTER TABLE post ADD COLUMN summary TEXT NULL AFTER nutrition");

        // Vérifier que ça a marché
        $stmt = $db->query("SHOW COLUMNS FROM post LIKE 'summary'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Colonne 'summary' ajoutée avec succès !</p>";
        } else {
            echo "<p style='color: red;'>❌ Échec de l'ajout de la colonne</p>";
        }
    }

    // Afficher la structure finale
    echo "<h3>Structure finale de la table 'post' :</h3>";
    $stmt = $db->query("DESCRIBE post");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Tester une requête avec summary
    echo "<h3>Test de requête avec summary :</h3>";
    try {
        $stmt = $db->query("SELECT id, titre, summary FROM post LIMIT 2");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Requête réussie !</p>";
        foreach ($posts as $post) {
            echo "<p><strong>{$post['titre']}</strong><br>Résumé: " . ($post['summary'] ?: '(vide)') . "</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}
?>