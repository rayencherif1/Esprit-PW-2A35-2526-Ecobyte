<?php
/**
 * Script de vérification de la colonne summary
 */

require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();

    // Vérifier si la colonne summary existe
    $stmt = $db->query("SHOW COLUMNS FROM post LIKE 'summary'");
    if ($stmt->rowCount() > 0) {
        echo "✅ La colonne 'summary' existe dans la table 'post'<br>";

        // Tester un SELECT avec la colonne summary
        $stmt = $db->query("SELECT id, titre, summary FROM post LIMIT 5");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<br>📝 Aperçu des posts avec leurs résumés :<br>";
        foreach ($posts as $post) {
            echo "ID: {$post['id']} - Titre: {$post['titre']}<br>";
            echo "Résumé: " . (empty($post['summary']) ? "(vide)" : substr($post['summary'], 0, 100) . "...") . "<br><br>";
        }

    } else {
        echo "❌ La colonne 'summary' n'existe pas dans la table 'post'<br>";
    }

} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>