-- ================================================================
-- EcoByte Unified DB — Module Boutique Bio (branche: ilyess)
-- Tables: categories, produits, commandes, items_commande, favoris
-- ================================================================

USE ecobyte_unified;

-- ── CATEGORIES ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `categories` (`id`, `nom`, `description`) VALUES
(1, 'Fruits & Légumes', 'Produits frais de saison'),
(2, 'Boulangerie & Viennoiserie', 'Pains, brioches, gâteaux'),
(3, 'Boissons & Jus', 'Jus naturels, smoothies, tisanes'),
(4, 'Épicerie Fine', 'Huiles, épices, condiments bio'),
(5, 'Produits Frais', 'Viandes, fromages, produits laitiers'),
(6, 'Compléments & Nutrition Sport', 'Whey, créatine, vitamines');

-- ── PRODUITS ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `categorie_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `nutriscore` varchar(1) DEFAULT NULL,
  `is_promo` tinyint(1) DEFAULT 0,
  `prix_promo` decimal(10,2) DEFAULT NULL,
  `ventes` int(11) DEFAULT 0,
  `date_ajout` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `categorie_id` (`categorie_id`),
  CONSTRAINT `fk_produit_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `produits` (`id`, `nom`, `description`, `prix`, `stock`, `categorie_id`, `nutriscore`, `is_promo`, `prix_promo`, `ventes`) VALUES
(1, 'Whey Protéine Vanille', 'Protéine de lactosérum 100% naturelle', 49.99, 80, 6, 'A', 0, NULL, 320),
(2, 'Créatine Monohydrate', 'Améliore les performances sportives', 34.99, 50, 6, 'B', 1, 28.99, 210),
(3, 'Bananes Bio (1kg)', 'Bananes mûres biologiques', 3.50, 200, 1, 'A', 0, NULL, 450),
(4, 'Jus d\'Orange Frais (1L)', 'Pressé à froid, sans sucre ajouté', 5.90, 60, 3, 'A', 1, 4.50, 180),
(5, 'Pain Complet Bio', 'Pain bio à base de farine complète', 2.80, 40, 2, 'B', 0, NULL, 95),
(6, 'Huile d\'Olive Extra Vierge', 'AOC Tunisie, 1ère pression à froid', 12.50, 75, 4, 'A', 0, NULL, 130),
(7, 'Tomates Cerises Bio (500g)', 'Tomates fraîches du jardin', 4.20, 120, 1, 'A', 0, NULL, 280),
(8, 'Vitamines C + Zinc', 'Complément immunité 60 comprimés', 14.99, 90, 6, 'C', 1, 11.99, 156),
(9, 'Lait d\'Amande Bio (1L)', 'Alternative végétale sans lactose', 3.99, 55, 5, 'B', 0, NULL, 200),
(10, 'Miel de Thym Naturel (500g)', 'Miel artisanal 100% pur', 8.90, 35, 4, 'A', 0, NULL, 88);

-- ── COMMANDES ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `commandes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `nom_client` varchar(100) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','confirmee','livree','annulee') DEFAULT 'en_attente',
  `date_commande` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── ITEMS COMMANDE ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `items_commande` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commande_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `commande_id` (`commande_id`),
  KEY `produit_id` (`produit_id`),
  CONSTRAINT `fk_item_commande` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── FAVORIS ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `favoris` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favori` (`user_id`, `produit_id`),
  KEY `produit_id` (`produit_id`),
  CONSTRAINT `fk_favori_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
