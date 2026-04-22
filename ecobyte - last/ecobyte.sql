-- Base Ecobyte — posts du blog
-- Import: phpMyAdmin > Importer ce fichier
-- Ou en ligne de commande: mysql -u root < database/ecobyte.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `ecobyte`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ecobyte`;

DROP TABLE IF EXISTS `post`;

CREATE TABLE `post` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(255) NOT NULL,
  `contenu` TEXT NULL,
  `datePublication` DATE NULL,
  `categorie` VARCHAR(100) NULL DEFAULT NULL,
  `image` VARCHAR(512) NULL DEFAULT NULL COMMENT 'Chemin ou URL de l image',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `reply`;

CREATE TABLE `reply` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contenu` TEXT NOT NULL,
  `datePublication` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `post_id` INT UNSIGNED NOT NULL,
  `likes` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_reply_post_id` (`post_id`),
  CONSTRAINT `fk_reply_post`
    FOREIGN KEY (`post_id`) REFERENCES `post` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des réactions (likes sur les commentaires)
DROP TABLE IF EXISTS `reply_reaction`;

CREATE TABLE `reply_reaction` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reply_id` INT UNSIGNED NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'Adresse IP pour éviter les doublons',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reply_ip` (`reply_id`, `ip_address`),
  KEY `idx_reply_id` (`reply_id`),
  CONSTRAINT `fk_reaction_reply`
    FOREIGN KEY (`reply_id`) REFERENCES `reply` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de démonstration (optionnel)
INSERT INTO `post` (`titre`, `contenu`, `datePublication`, `categorie`, `image`) VALUES
(
  'Bienvenue sur le blog Ecobyte',
  'Ceci est un premier article de test. Vous pouvez le modifier ou le supprimer depuis le back-office.',
  CURDATE(),
  'Actualités',
  NULL
);

-- Une réponse de démonstration (optionnel)
INSERT INTO `reply` (`contenu`, `post_id`, `likes`) VALUES
(
  'Premier commentaire : bienvenue !',
  1,
  0
);

SET FOREIGN_KEY_CHECKS = 1;
