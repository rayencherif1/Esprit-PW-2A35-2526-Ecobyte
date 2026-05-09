-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 02:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestion_allergie`
--

-- --------------------------------------------------------

--
-- Table structure for table `allergie`
--

CREATE TABLE `allergie` (
  `id_allergie` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `gravite` varchar(20) NOT NULL,
  `symptomes` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allergie`
--

INSERT INTO `allergie` (`id_allergie`, `nom`, `description`, `gravite`, `symptomes`) VALUES
(1, 'Arachides', 'Allergie aux arachides et dérivés (cacahuètes, huile d\'arachide, farine)', 'grave', 'Urticaire, gonflement des lèvres et de la gorge, difficultés respiratoires, vomissements, choc anaphylactique'),
(2, 'Lactose', 'Intolérance au lactose présent dans les produits laitiers', 'moyenne', 'Ballonnements, diarrhée, douleurs abdominales, nausées, gaz intestinaux, crampes'),
(3, 'Gluten', 'Maladie cœliaque - intolérance au gluten présent dans le blé, orge et seigle', 'moyenne', 'Troubles digestifs, fatigue chronique, maux de tête, anémie, perte de poids, douleurs articulaires'),
(4, 'Pollen de bouleau', 'Allergie saisonnière au pollen de bouleau (printemps)', 'faible', 'Éternuements en salve, nez qui coule abondamment, yeux qui piquent et larmoient, gorge irritée, asthme');

-- --------------------------------------------------------

--
-- Table structure for table `traitement`
--

CREATE TABLE `traitement` (
  `id_traitement` int(11) NOT NULL,
  `nom_traitement` varchar(255) NOT NULL,
  `conseils` text DEFAULT NULL,
  `interdiction` text DEFAULT NULL,
  `id_allergie` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `traitement`
--

INSERT INTO `traitement` (`id_traitement`, `nom_traitement`, `conseils`, `interdiction`, `id_allergie`) VALUES
(13, 'Adrénaline auto-injectable (EpiPen)', 'Toujours avoir deux stylos sur soi, injecter dans la cuisse dès les premiers signes de choc anaphylactique, appeler les secours immédiatement après injection', 'Ne pas hésiter à l\'utiliser en cas de doute, ne pas attendre que les symptômes s\'aggravent, ne pas injecter dans la fesse', 1),
(14, 'Antihistaminique (Cétirizine)', 'Prendre 10mg dès l\'apparition des premiers symptômes légers (démangeaisons, urticaire), renouvelable toutes les 24h si nécessaire', 'Ne pas dépasser la dose prescrite, ne pas prendre en cas de symptômes graves, éviter l\'alcool', 1),
(15, 'Régime sans lactose', 'Privilégier les laits végétaux (amande, soja, avoine, riz, coco), choisir des fromages affinés (comté, parmesan, emmental) pauvres en lactose', 'Éviter le lait, crème, yaourt, fromages frais (ricotta, cottage), beurre, glaces, chocolat au lait', 2),
(16, 'Comprimés de lactase (Lactaid)', 'Prendre 1 à 2 comprimés juste avant chaque repas contenant du lactose, adapter la dose selon la quantité ingérée (plus de lactose = plus de comprimés)', 'Ne pas utiliser comme substitut au régime sans lactose en cas d\'intolérance sévère, ne pas croquer les comprimés', 2),
(17, 'Régime sans gluten strict', 'Consommer du riz, maïs, quinoa, sarrasin, millet, amarante, patates, légumineuses, vérifier les labels \"sans gluten\" certifiés', 'Éviter le blé, orge, seigle, avoine non certifiée, pains, pâtes, gâteaux, viennoiseries, bière, sauces épaissies', 3),
(18, 'Consultation diététique', 'Suivi régulier par un nutritionniste spécialisé tous les 6 mois, bilan sanguin annuel (fer, calcium, vitamines B9, B12, D)', 'Ne pas arrêter le gluten avant le diagnostic cœliaque (risque de faux négatifs), ne pas s\'auto-médiquer', 3),
(19, 'Antihistaminiques (Loratadine)', 'Prendre 10mg par jour pendant la saison pollinique (mars à mai), commencer 2 semaines avant le début de la saison si possible', 'Éviter les antihistaminiques sédatifs (diphenhydramine) avant la conduite, ne pas dépasser 10mg par jour', 4),
(20, 'Corticostéroïdes nasaux', 'Utiliser en spray nasal matin et soir (2 pulvérisations par narine), commencer 2-4 semaines avant la saison pollinique', 'Ne pas utiliser sans avis médical chez l\'enfant de moins de 6 ans, ne pas dépasser 3 mois d\'affilée', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allergie`
--
ALTER TABLE `allergie`
  ADD PRIMARY KEY (`id_allergie`);

--
-- Indexes for table `traitement`
--
ALTER TABLE `traitement`
  ADD PRIMARY KEY (`id_traitement`),
  ADD KEY `fk_allergie` (`id_allergie`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allergie`
--
ALTER TABLE `allergie`
  MODIFY `id_allergie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `traitement`
--
ALTER TABLE `traitement`
  MODIFY `id_traitement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `traitement`
--
ALTER TABLE `traitement`
  ADD CONSTRAINT `fk_allergie` FOREIGN KEY (`id_allergie`) REFERENCES `allergie` (`id_allergie`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
