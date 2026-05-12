-- =====================================================================
-- BASE DE DONNÉES UNIFIÉE ECOBYTE
-- Regroupe les 6 modules : Fitness, Boutique, Blog, Santé, Cuisine, Utilisateurs
-- =====================================================================
-- Branche 1 : selem  → Fitness & Sport (exercices, programmes)
-- Branche 2 : ilyess → Santé & Allergies
-- Branche 3 : blog   → Blog & Communauté
-- Branche 4 : rayen  → Utilisateurs & Auth
-- Branche 5 : user   → [à compléter]
-- Branche 6 : mohamed → Cuisine & Recettes
-- =====================================================================

CREATE DATABASE IF NOT EXISTS ecobyte_unified
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ecobyte_unified;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- MODULE 1 : SELEM — FITNESS & SPORT
-- Tables : exercices, programmes, programme_exercice, ollama_ia_profils
-- =====================================================================

DROP TABLE IF EXISTS programme_exercice;
DROP TABLE IF EXISTS user_programmes;
DROP TABLE IF EXISTS programmes;
DROP TABLE IF EXISTS exercices;
DROP TABLE IF EXISTS ollama_ia_profils;

-- Catalogue des exercices
CREATE TABLE exercices (
    id                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom                     VARCHAR(150) NOT NULL,
    type_exercice           ENUM('musculation','cardio','perte_de_poids') NOT NULL,
    etapes                  TEXT NOT NULL COMMENT 'Description étape par étape',
    benefices               TEXT NOT NULL COMMENT 'Bienfaits / objectifs santé',
    url_image               VARCHAR(500) NOT NULL DEFAULT '',
    url_video               VARCHAR(500) NOT NULL DEFAULT '',
    nb_repetitions_suggerees INT UNSIGNED NOT NULL DEFAULT 10,
    muscle_wger_id          TINYINT UNSIGNED NULL COMMENT 'ID muscle wger.de API',
    date_creation           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_exercices_type (type_exercice),
    KEY idx_exercices_nom  (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Programmes publics (admin) et personnels (front)
CREATE TABLE programmes (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom               VARCHAR(150) NOT NULL,
    duree_semaines    INT UNSIGNED NOT NULL,
    type_programme    ENUM('musculation','cardio','perte_de_poids') NOT NULL,
    utilisateur_token VARCHAR(64) NULL DEFAULT NULL COMMENT 'NULL = catalogue public',
    date_creation     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_programmes_type       (type_programme),
    KEY idx_programmes_nom        (nom),
    KEY idx_programmes_user_token (utilisateur_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jointure programme ↔ exercice
CREATE TABLE programme_exercice (
    programme_id INT UNSIGNED NOT NULL,
    exercice_id  INT UNSIGNED NOT NULL,
    ordre        INT UNSIGNED NOT NULL DEFAULT 1,
    repetitions  INT UNSIGNED NULL,
    PRIMARY KEY (programme_id, exercice_id),
    KEY idx_pe_ordre (programme_id, ordre),
    CONSTRAINT fk_pe_programme FOREIGN KEY (programme_id) REFERENCES programmes (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pe_exercice  FOREIGN KEY (exercice_id)  REFERENCES exercices  (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Profils IA Ollama (recommandations personnalisées)
CREATE TABLE ollama_ia_profils (
    id                        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom                       VARCHAR(150) NOT NULL,
    modele                    VARCHAR(120) NOT NULL DEFAULT '',
    instructions_supplementaires TEXT NOT NULL,
    actif                     TINYINT(1) NOT NULL DEFAULT 0,
    date_creation             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification         TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ollama_ia_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Données de démo (Fitness) ─────────────────────────────────────────────────
INSERT INTO ollama_ia_profils (nom, modele, instructions_supplementaires, actif)
VALUES ('Défaut', '', '', 1);

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id) VALUES
('Squat au poids du corps', 'musculation',
 'Pieds largeur hanches. Descendez en poussant les hanches en arrière. Genoux alignés avec les orteils. Remontez en poussant sur les talons.',
 'Renforce cuisses et fessiers, améliore la mobilité de hanche.',
 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=600', '', 12, 10),
('Course légère sur place', 'cardio',
 'Lever les genoux avec un rythme régulier, bras actifs, respiration contrôlée.',
 'Améliore l''endurance cardiovasculaire et la dépense énergétique.',
 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=600', '', 60, 10),
('Burpee modifié', 'perte_de_poids',
 'Squat, mains au sol, reculer les pieds en planche, revenir en squat, se relever sans saut.',
 'Mouvement complet pour monter le métabolisme.',
 'https://images.unsplash.com/photo-1599058945525-550d2b8d4b5e?w=600', '', 8, 6);

INSERT INTO programmes (nom, duree_semaines, type_programme) VALUES
('Full body débutant',  4, 'musculation'),
('Brûlage express',     3, 'perte_de_poids'),
('Cardio fondamental',  2, 'cardio');

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions) VALUES
(1, 1, 1, 10), (1, 2, 2, 45),
(2, 2, 1, 60), (2, 3, 2, 10),
(3, 2, 1, 90);

-- =====================================================================
-- FIN MODULE SELEM — Les modules suivants seront ajoutés ici
-- MODULE 2 : ilyess  → Santé & Allergies        [à venir]
-- MODULE 3 : blog    → Blog & Communauté         [à venir]
-- MODULE 4 : rayen   → Utilisateurs & Auth       [à venir]
-- MODULE 5 : user    → [à identifier]            [à venir]
-- MODULE 6 : mohamed → Cuisine & Recettes        [à venir]
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 1;
