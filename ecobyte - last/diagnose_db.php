<?php
/**
 * Script de diagnostic complet de la base de données
 */

require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();

    echo "<h2>🔍 Diagnostic de la base de données Ecobyte</h2>";

    // Vérifier la structure de la table post
    echo "<h3>Structure de la table 'post' :</h3>";
    $stmt = $db->query("DESCRIBE post");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $summaryExists = false;
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
        if ($col['Field'] === 'summary') {
            $summaryExists = true;
        }
    }
    echo "</table>";

    if ($summaryExists) {
        echo "<p style='color: green;'>✅ La colonne 'summary' existe !</p>";
    } else {
        echo "<p style='color: red;'>❌ La colonne 'summary' est manquante !</p>";
    }

    // Tester un SELECT simple
    echo "<h3>Test SELECT avec colonne summary :</h3>";
    try {
        $stmt = $db->query("SELECT id, titre, summary FROM post LIMIT 3");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Requête SELECT réussie</p>";
        echo "<ul>";
        foreach ($posts as $post) {
            $summary = $post['summary'] ?? '(null)';
            echo "<li>ID {$post['id']}: {$post['titre']} - Résumé: " . substr($summary, 0, 50) . "...</li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Erreur SELECT: " . $e->getMessage() . "</p>";
    }

    // Tester un UPDATE
    echo "<h3>Test UPDATE de la colonne summary :</h3>";
    try {
        $stmt = $db->prepare("UPDATE post SET summary = 'Test résumé' WHERE id = 1");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Requête UPDATE réussie</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Erreur UPDATE: " . $e->getMessage() . "</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion: " . $e->getMessage() . "</p>";
}
?>