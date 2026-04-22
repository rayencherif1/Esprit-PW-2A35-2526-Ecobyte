<?php
/**
 * Script de mise à jour de la base de données
 * Ajoute la colonne likes et la table reply_reaction
 */

require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    
    // Vérifier si la colonne likes existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM reply LIKE 'likes'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE `reply` ADD COLUMN `likes` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `post_id`");
        echo "✅ Colonne 'likes' ajoutée à la table 'reply'<br>";
    } else {
        echo "ℹ️ La colonne 'likes' existe déjà<br>";
    }
    
    // Créer la table reply_reaction si elle n'existe pas
    $stmt = $db->query("SHOW TABLES LIKE 'reply_reaction'");
    if ($stmt->rowCount() === 0) {
        $db->exec("
            CREATE TABLE `reply_reaction` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `reply_id` INT UNSIGNED NOT NULL,
              `ip_address` VARCHAR(45) NOT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_reply_ip` (`reply_id`, `ip_address`),
              KEY `idx_reply_id` (`reply_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ Table 'reply_reaction' créée<br>";
    } else {
        echo "ℹ️ La table 'reply_reaction' existe déjà<br>";
    }
    
    echo "<br>🎉 Mise à jour terminée avec succès !";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}