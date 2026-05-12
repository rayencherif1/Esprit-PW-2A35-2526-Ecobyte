-- Script SQL pour ajouter manuellement la colonne summary
-- À exécuter directement dans phpMyAdmin ou MySQL Workbench

USE ecobyte;

-- Vérifier d'abord si la colonne existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'ecobyte'
    AND TABLE_NAME = 'post'
    AND COLUMN_NAME = 'summary'
);

-- Ajouter la colonne seulement si elle n'existe pas
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE post ADD COLUMN summary TEXT NULL AFTER nutrition',
    'SELECT "La colonne summary existe déjà" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Vérifier le résultat
DESCRIBE post;