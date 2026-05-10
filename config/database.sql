-- Base de donnees pour le projet recette
-- Compatible MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS `recette`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `recette`;

-- Supprime d'abord la table enfant (dependance FK)
DROP TABLE IF EXISTS `instructions`;
DROP TABLE IF EXISTS `recettes`;

CREATE TABLE `recettes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(255) NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  `calories` INT NOT NULL DEFAULT 0,
  `temps_preparation` INT NOT NULL DEFAULT 0,
  `difficulte` VARCHAR(50) NOT NULL,
  `impact_carbone` VARCHAR(50) NOT NULL,
  `image` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `instructions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recette_id` INT UNSIGNED DEFAULT NULL,
  `nom` VARCHAR(255) NOT NULL,
  `image` VARCHAR(500) NOT NULL,
  `ingredients` TEXT NOT NULL,
  `preparation` TEXT NOT NULL,
  `nombre_etapes` INT NOT NULL DEFAULT 1,
  `temps` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_instructions_recette_id` (`recette_id`),
  KEY `idx_instructions_nom` (`nom`),
  CONSTRAINT `fk_instructions_recette`
    FOREIGN KEY (`recette_id`) REFERENCES `recettes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Exemple de donnees (optionnel)
-- INSERT INTO recettes (nom, type, calories, temps_preparation, difficulte, impact_carbone, image)
-- VALUES ('Salade composee', 'Dejeuner', 320, 15, 'Facile', 'Faible', '/recette/public/image/salade.jpg');
