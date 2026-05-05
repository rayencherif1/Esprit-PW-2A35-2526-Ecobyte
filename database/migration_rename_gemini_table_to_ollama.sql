-- À exécuter UNE FOIS si votre base contient encore la table « gemini_ia_profils »
-- (sinon ignorez ce fichier et utilisez migration_ollama_ia_profils.sql).

SET NAMES utf8mb4;
RENAME TABLE gemini_ia_profils TO ollama_ia_profils;
