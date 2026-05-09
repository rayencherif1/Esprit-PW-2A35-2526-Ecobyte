-- =====================================================================
-- Schéma MySQL — module Entraînement (exercices + programmes)
-- À importer dans phpMyAdmin (XAMPP) sur la machine cible.
-- Encodage recommandé : utf8mb4
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Base : créez-la avant l'import ou décommentez la ligne suivante :
-- CREATE DATABASE IF NOT EXISTS nutrition_sante CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE nutrition_sante;

DROP TABLE IF EXISTS programme_exercice;
DROP TABLE IF EXISTS programmes;
DROP TABLE IF EXISTS exercices;

-- ---------------------------------------------------------------------
-- Table exercices : une ligne = un mouvement / exercice catalogué
-- ---------------------------------------------------------------------
CREATE TABLE exercices (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    type_exercice ENUM('musculation', 'cardio', 'perte_de_poids') NOT NULL,
    etapes TEXT NOT NULL COMMENT 'Description étape par étape (texte libre)',
    benefices TEXT NOT NULL COMMENT 'Bienfaits / objectifs santé',
    url_image VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'URL absolue http(s) vers une image',
    url_video VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'URL absolue http(s) vers une vidéo',
    nb_repetitions_suggerees INT UNSIGNED NOT NULL DEFAULT 10 COMMENT 'Nombre de répétitions suggéré (entier)',
    muscle_wger_id TINYINT UNSIGNED NULL COMMENT 'ID muscle wger.de pour suggestions API (NULL = pas d’API)',
    date_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_exercices_type (type_exercice),
    KEY idx_exercices_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table programmes : un programme = objectif + durée + liste d’exercices
-- ---------------------------------------------------------------------
CREATE TABLE programmes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    duree_semaines INT UNSIGNED NOT NULL COMMENT 'Durée prévue du programme en semaines',
    type_programme ENUM('musculation', 'cardio', 'perte_de_poids') NOT NULL,
    utilisateur_token VARCHAR(64) NULL DEFAULT NULL COMMENT 'Jeton session front ; NULL = catalogue public',
    date_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_programmes_type (type_programme),
    KEY idx_programmes_nom (nom),
    KEY idx_programmes_user_token (utilisateur_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table de jointure N:N — un programme contient plusieurs exercices (ordonnés)
-- ---------------------------------------------------------------------
CREATE TABLE programme_exercice (
    programme_id INT UNSIGNED NOT NULL,
    exercice_id INT UNSIGNED NOT NULL,
    ordre INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Ordre d’affichage dans le programme',
    repetitions INT UNSIGNED NULL COMMENT 'Répétitions spécifiques au programme (NULL = défaut exercice)',
    PRIMARY KEY (programme_id, exercice_id),
    KEY idx_pe_ordre (programme_id, ordre),
    CONSTRAINT fk_pe_programme FOREIGN KEY (programme_id) REFERENCES programmes (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pe_exercice FOREIGN KEY (exercice_id) REFERENCES exercices (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Profils IA Ollama (admin CRUD) — modèle + consignes système additionnelles
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS ollama_ia_profils;
CREATE TABLE ollama_ia_profils (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    modele VARCHAR(120) NOT NULL DEFAULT '' COMMENT 'Tag modèle Ollama ; vide = OLLAMA_MODEL (.env)',
    instructions_supplementaires TEXT NOT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = utilisé sur la page suggestion (un seul à la fois)',
    date_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ollama_ia_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ollama_ia_profils (nom, modele, instructions_supplementaires, actif) VALUES
('Défaut', '', '', 1);

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Données de démonstration (facultatif — à supprimer en production)
-- ---------------------------------------------------------------------
INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id) VALUES
('Squat au poids du corps', 'musculation',
 'Pieds largeur hanches. Descendez en poussant les hanches en arrière. Genoux alignés avec les orteils. Remontez en poussant sur les talons.',
 'Renforce cuisses et fessiers, améliore la mobilité de hanche.',
 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600', '', 12, 10),
('Course légère sur place', 'cardio',
 'Lever les genoux avec un rythme régulère, bras actifs, respiration contrôlée.',
 'Améliore l’endurance cardiovasculaire et la dépense énergétique.',
 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=600', '', 60, 10),
('Burpee modifié', 'perte_de_poids',
 'Squat, mains au sol, reculer les pieds en planche, revenir en squat, se relever sans saut au besoin.',
 'Mouvement complet pour monter le métabolisme.',
 'https://images.unsplash.com/photo-1599058945525-550d2b8d4b5e?w=600', '', 8, 6);

INSERT INTO programmes (nom, duree_semaines, type_programme) VALUES
('Full body débutant', 4, 'musculation'),
('Brûlage express', 3, 'perte_de_poids');

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions) VALUES
(1, 1, 1, 10),
(1, 2, 2, 45),
(2, 2, 1, 60),
(2, 3, 2, 10);

INSERT INTO programmes (nom, duree_semaines, type_programme) VALUES
('Cardio fondamental', 2, 'cardio');

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions) VALUES
(3, 2, 1, 90);
