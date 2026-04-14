-- ========================================
-- Création de la base de données et tables
-- ========================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS rayench_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE rayench_db;

-- ========================================
-- Table des utilisateurs
-- ========================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table des profils
-- ========================================
CREATE TABLE IF NOT EXISTS profils (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    adresse VARCHAR(200),
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Données de test (optionnel)
-- ========================================
INSERT INTO users (nom, prenom, email, telephone) VALUES 
('Dupont', 'Jean', 'jean.dupont@example.com', '+33612345678'),
('Martin', 'Marie', 'marie.martin@example.com', '+33687654321'),
('Durand', 'Pierre', 'pierre.durand@example.com', '+33623456789');

INSERT INTO profils (user_id, bio, adresse, ville, code_postal) VALUES 
(1, 'Développeur passionné', '123 Rue de Paris', 'Paris', '75001'),
(2, 'Designer créatif', '456 Avenue de Lyon', 'Lyon', '69000'),
(3, 'Chef de projet', '789 Boulevard de Marseille', 'Marseille', '13000');
