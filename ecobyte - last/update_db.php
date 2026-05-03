<?php
/**
 * Script de mise à jour de la base de données
 * Ajoute la colonne likes et la table reply_reaction
 */

require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    
    // Vérifier si la table users existe déjà
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        $db->exec("CREATE TABLE `users` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `pseudo` VARCHAR(100) NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_users_pseudo` (`pseudo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Table 'users' créée<br>";
    } else {
        echo "ℹ️ La table 'users' existe déjà<br>";
    }

    // Vérifier si la colonne idUser existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM reply LIKE 'idUser'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE `reply` ADD COLUMN `idUser` INT UNSIGNED NULL DEFAULT NULL AFTER `post_id`");
        $db->exec("ALTER TABLE `reply` ADD CONSTRAINT `fk_reply_user` FOREIGN KEY (`idUser`) REFERENCES `users`(`id`) ON DELETE SET NULL");
        echo "✅ Colonne 'idUser' ajoutée à la table 'reply'<br>";
    } else {
        echo "ℹ️ La colonne 'idUser' existe déjà<br>";
    }

    // Vérifier si la colonne statut existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM reply LIKE 'statut'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE `reply` ADD COLUMN `statut` ENUM('en_attente','approuve','signale','rejete') NOT NULL DEFAULT 'en_attente' AFTER `idUser`");
        echo "✅ Colonne 'statut' ajoutée à la table 'reply'<br>";
    } else {
        echo "ℹ️ La colonne 'statut' existe déjà<br>";
    }

    // Vérifier si la colonne raisonSignalement existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM reply LIKE 'raisonSignalement'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE `reply` ADD COLUMN `raisonSignalement` VARCHAR(255) NULL DEFAULT NULL AFTER `statut`");
        echo "✅ Colonne 'raisonSignalement' ajoutée à la table 'reply'<br>";
    } else {
        echo "ℹ️ La colonne 'raisonSignalement' existe déjà<br>";
    }

    // Vérifier si la colonne likes existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM reply LIKE 'likes'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE `reply` ADD COLUMN `likes` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `raisonSignalement`");
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

    // Vérifier si la colonne nutrition existe déjà dans post
    $stmt = $db->query("SHOW COLUMNS FROM post LIKE 'nutrition'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE `post` ADD COLUMN `nutrition` TEXT NULL AFTER `image`");
        echo "✅ Colonne 'nutrition' ajoutée à la table 'post'<br>";
    } else {
        echo "ℹ️ La colonne 'nutrition' existe déjà<br>";
    }
    
    echo "<br>🎉 Mise à jour terminée avec succès !";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}