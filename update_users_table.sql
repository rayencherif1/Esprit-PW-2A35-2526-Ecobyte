-- Migration to add profile fields to users table
ALTER TABLE users 
ADD COLUMN password VARCHAR(255) AFTER email,
ADD COLUMN photo VARCHAR(255) DEFAULT NULL,
ADD COLUMN poids FLOAT DEFAULT NULL,
ADD COLUMN taille FLOAT DEFAULT NULL;
