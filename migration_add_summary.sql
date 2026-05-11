-- Migration: Ajouter la colonne summary à la table post
-- Cette colonne stockera les résumés générés par l'IA

ALTER TABLE `post` ADD COLUMN `summary` TEXT NULL DEFAULT NULL AFTER `image`;
