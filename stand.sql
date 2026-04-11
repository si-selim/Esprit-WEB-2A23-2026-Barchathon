-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 11 avr. 2026 à 11:39
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `barchathon`
--

-- --------------------------------------------------------

--
-- Structure de la table `stand`
--

CREATE TABLE `stand` (
  `ID_stand` int(11) NOT NULL,
  `nom_stand` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ID_parcours` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stand`
--

INSERT INTO `stand` (`ID_stand`, `nom_stand`, `position`, `description`, `ID_parcours`) VALUES
(1, 'Stand Hydratation', 'Km 5', 'Stand de distribution eau et boissons', 1),
(2, 'rouriiiiiiiii', 'manouba', 'roua roua ya rabi', 4),
(3, 'rouriiiiiiiii', 'manouba', 'roua roua ya rabi', 4);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `stand`
--
ALTER TABLE `stand`
  ADD PRIMARY KEY (`ID_stand`),
  ADD KEY `ID_parcours` (`ID_parcours`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `stand`
--
ALTER TABLE `stand`
  MODIFY `ID_stand` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
