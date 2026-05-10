<?php
/**
 * Script de migration forcée pour ajouter la colonne summary
 */

require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();

    echo "<h2>🔧 Migration forcée - Ajout colonne 'summary'</h2>";

    // Vérifier si la colonne existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM post LIKE 'summary'");
    $exists = $stmt->rowCount() > 0;

    if ($exists) {
        echo "<p style='color: green;'>✅ La colonne 'summary' existe déjà !</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Ajout de la colonne 'summary'...</p>";

        try {
            $db->exec("ALTER TABLE post ADD COLUMN summary TEXT NULL AFTER nutrition");
            echo "<p style='color: green;'>✅ Colonne 'summary' ajoutée avec succès !</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erreur lors de l'ajout: " . $e->getMessage() . "</p>";
            exit;
        }
    }

    // Vérifier que la colonne est bien là
    $stmt = $db->query("DESCRIBE post");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Structure de la table 'post' :</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        $highlight = ($col['Field'] === 'summary') ? " style='background-color: #e8f5e8;'" : "";
        echo "<tr{$highlight}>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Tester une requête UPDATE
    echo "<h3>Test UPDATE :</h3>";
    try {
        $stmt = $db->prepare("UPDATE post SET summary = ? WHERE id = 1");
        $stmt->execute(['Test de résumé automatique - ' . date('Y-m-d H:i:s')]);
        echo "<p style='color: green;'>✅ UPDATE réussi !</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Erreur UPDATE: " . $e->getMessage() . "</p>";
    }

    // Tester une requête SELECT
    echo "<h3>Test SELECT :</h3>";
    try {
        $stmt = $db->query("SELECT id, titre, LEFT(summary, 50) as summary_preview FROM post LIMIT 3");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ SELECT réussi !</p>";
        echo "<ul>";
        foreach ($posts as $post) {
            echo "<li><strong>{$post['titre']}</strong><br>Résumé: " . ($post['summary_preview'] ?: '(vide)') . "</li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Erreur SELECT: " . $e->getMessage() . "</p>";
    }

    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>🎉 Migration terminée ! Le système de résumé IA devrait maintenant fonctionner.</p>";
    echo "<p><a href='blog.php'>← Tester le blog</a> | <a href='admin/summaries.php'>← Interface admin</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion: " . $e->getMessage() . "</p>";
}
?>