-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 10:50 AM
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
-- Database: `barchathon`
--

-- --------------------------------------------------------

--
-- Table structure for table `parcours`
--

CREATE TABLE `parcours` (
  `id_parcours` int(11) NOT NULL,
  `nom_parcours` varchar(100) NOT NULL,
  `point_depart` varchar(100) NOT NULL,
  `point_arrivee` varchar(100) NOT NULL,
  `distance` decimal(5,2) NOT NULL,
  `difficulte` enum('facile','moyen','difficile') DEFAULT 'moyen',
  `id_marathon` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `parcours`
--
ALTER TABLE `parcours`
  ADD PRIMARY KEY (`id_parcours`),
  ADD KEY `fk_marathon` (`id_marathon`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `parcours`
--
ALTER TABLE `parcours`
  MODIFY `id_parcours` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `parcours`
--
ALTER TABLE `parcours`
  ADD CONSTRAINT `fk_marathon` FOREIGN KEY (`id_marathon`) REFERENCES `marathon` (`id_marathon`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
