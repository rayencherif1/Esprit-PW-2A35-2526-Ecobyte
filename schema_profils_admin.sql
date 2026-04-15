-- Création de la table profils_admin pour stocker les profils administrateurs
CREATE TABLE IF NOT EXISTS profils_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(100),
    zip_code VARCHAR(20),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insérer quelques profils d'exemple
INSERT INTO profils_admin (full_name, email, role, phone, address, city, zip_code, bio) VALUES 
('Admin User', 'admin2026', 'Administrator', '+33 1 23 45 67 89', '123 Rue de l\'Admin', 'Paris', '75001', 'Administrateur principal du système'),
('John Doe', 'john@example.com', 'Manager', '+33 2 34 56 78 90', '456 Avenue Manager', 'Lyon', '69001', 'Manager des projets'),
('Jane Smith', 'jane@example.com', 'Supervisor', '+33 3 45 67 89 01', '789 Boulevard Supervisor', 'Marseille', '13001', 'Superviseur des opérations');

