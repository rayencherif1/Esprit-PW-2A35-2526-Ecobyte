<?php
require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    
    // Ajouter la colonne parent_reply_id
    $stmt = $db->query("SHOW COLUMNS FROM reply LIKE 'parent_reply_id'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE `reply` ADD COLUMN `parent_reply_id` INT UNSIGNED NULL DEFAULT NULL AFTER `post_id`");
        echo "✅ Colonne 'parent_reply_id' ajoutée<br>";
    }
    
    // Ajouter clé étrangère
    $db->exec("ALTER TABLE `reply` ADD CONSTRAINT `fk_reply_parent` FOREIGN KEY (`parent_reply_id`) REFERENCES `reply`(`id`) ON DELETE CASCADE");
    echo "✅ Clé étrangère ajoutée<br>";
    
    echo "🎉 Mise à jour terminée !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}