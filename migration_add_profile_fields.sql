-- Migration: Ajouter les champs optionnels à la table profils_admin
-- Exécutez ce script si la table profils_admin existe déjà

ALTER TABLE profils_admin 
ADD COLUMN phone VARCHAR(20) AFTER role,
ADD COLUMN address VARCHAR(255) AFTER phone,
ADD COLUMN city VARCHAR(100) AFTER address,
ADD COLUMN zip_code VARCHAR(20) AFTER city,
ADD COLUMN bio TEXT AFTER zip_code;
