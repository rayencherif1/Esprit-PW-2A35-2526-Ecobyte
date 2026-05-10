-- Création table profils Ollama (nouvelle install ou sans ancienne table Gemini).
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS ollama_ia_profils (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(150) NOT NULL,
    modele VARCHAR(120) NOT NULL DEFAULT '',
    instructions_supplementaires TEXT NOT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 0,
    date_creation TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ollama_ia_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ollama_ia_profils (nom, modele, instructions_supplementaires, actif)
SELECT 'Défaut', '', '', 1
FROM (SELECT 1 AS _) AS dummy
WHERE NOT EXISTS (SELECT 1 FROM ollama_ia_profils LIMIT 1);
