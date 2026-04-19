-- ========================================
-- Recettes + Instructions (jointure 1–1)
-- Base MySQL : mes_recettes (identique à config.php → DB_NAME)
-- ========================================
CREATE DATABASE IF NOT EXISTS mes_recettes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mes_recettes;

CREATE TABLE IF NOT EXISTS recettes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(180) NOT NULL,
    type VARCHAR(80) NOT NULL,
    calories INT NOT NULL DEFAULT 0,
    temps_preparation INT UNSIGNED NOT NULL DEFAULT 0,
    difficulte VARCHAR(50) NOT NULL,
    impact_carbone VARCHAR(50) NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recettes_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Une fiche instruction liée à une recette (recette_id UNIQUE = relation 1–1)
CREATE TABLE IF NOT EXISTS instructions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recette_id INT UNSIGNED NULL,
    nom VARCHAR(180) NOT NULL,
    image VARCHAR(255) NOT NULL,
    ingredients TEXT NOT NULL,
    preparation TEXT NOT NULL,
    nombre_etapes INT UNSIGNED NOT NULL DEFAULT 1,
    temps INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_instructions_recette
        FOREIGN KEY (recette_id) REFERENCES recettes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    UNIQUE KEY uq_instructions_recette (recette_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données initiales (ids fixes pour rester alignés avec les anciennes données de session)
INSERT INTO recettes (id, nom, type, calories, temps_preparation, difficulte, impact_carbone, image) VALUES
(1, 'Citron', 'Petit déjeuner', 60, 5, '★★', '0.1 kg', '/recette/public/image/citron.jpg'),
(2, 'Curry', 'Déjeuner', 520, 40, '★★★★', '2.2 kg', '/recette/public/image/curry.jpg'),
(3, 'Pain', 'Petit déjeuner', 250, 20, '★★', '0.5 kg', '/recette/public/image/pain.jpg'),
(4, 'Salade', 'Déjeuner', 180, 15, '★', '0.2 kg', '/recette/public/image/salade.jpg'),
(5, 'Soupe', 'Dîner', 150, 25, '★★', '0.3 kg', '/recette/public/image/soupe.jpg')
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    type = VALUES(type),
    calories = VALUES(calories),
    temps_preparation = VALUES(temps_preparation),
    difficulte = VALUES(difficulte),
    impact_carbone = VALUES(impact_carbone),
    image = VALUES(image);

ALTER TABLE recettes AUTO_INCREMENT = 6;

INSERT INTO instructions (id, recette_id, nom, image, ingredients, preparation, nombre_etapes, temps) VALUES
(1, 4, 'Salade du marche', '/recette/public/image/salade.jpg',
 'Laitue, tomates cerises, concombre, huile d''olive, citron.',
 'Laver les legumes. Couper en morceaux. Assaisonner et servir frais.', 4, 15),
(2, 5, 'Soupe maison', '/recette/public/image/soupe.jpg',
 'Legumes de saison, bouillon, herbes, sel, poivre.',
 'Faire revenir les legumes. Mouiller au bouillon. Laisser mijoter puis mixer.', 5, 35)
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    image = VALUES(image),
    ingredients = VALUES(ingredients),
    preparation = VALUES(preparation),
    nombre_etapes = VALUES(nombre_etapes),
    temps = VALUES(temps),
    recette_id = VALUES(recette_id);

ALTER TABLE instructions AUTO_INCREMENT = 3;
