-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2014 at 08:18 AM
-- Server version: 5.6.16
-- PHP Version: 5.5.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES latin1 */;

--
-- Database: `bfh6o`
--

--
-- Truncate table before insert `indexe`
--

TRUNCATE TABLE `indexe`;
--
-- Dumping data for table `indexe`
--

INSERT INTO `indexe` (`id`, `name`, `kuerzel`) VALUES
(1000, '---- INDEX ----', ''),
(1101, 'SMI', '^SSMI'),
(1102, 'SPI', '^SSHI'),
(1103, 'DAX', '^GDAXI'),
(1105, 'NASDAQ-100', '^NDX'),
(2000, '---- AKTIEN ----', ''),
(2101, 'ABB N', 'ABBN.VX'),
(2102, 'Adecco N', 'ADEN.VX'),
(2103, 'Allianz SE', 'ALV.DE'),
(2104, 'Credit Suisse Groupe AG', 'CS'),
(2105, 'Roche Holding AG', 'ROG.VX'),
(2106, 'Nestle S.A.', 'NESN'),
(2107, 'Swatch Groupe', 'SWGNF'),
(2108, 'Swisscom AG', 'SCMWY'),
(2109, 'UBS AG', 'UBS'),
(2110, 'Volkswagen AG', 'VLKAY'),
(4000, '---- ROHSTOFFE ----', ''),
(4101, 'Goldkurs 1oz USD', ''),
(4102, 'Rohoel Brent USD', ''),
(5000, '---- DEVISEN ----', ''),
(5101, 'EUR-USD', ''),
(5102, 'EUR-CHF', '');

--
-- Truncate table before insert `indexe_values`
--

TRUNCATE TABLE `indexe_values`;
--
-- Dumping data for table `indexe_values`
--

INSERT INTO `indexe_values` (`id`, `tradeDate`, `adjClose`, `fk_indexe_id`) VALUES
(1, '1971-01-05', 0, 1101),
(2, '1971-01-05', 0, 1102),
(3, '1971-01-05', 0, 1103),
(4, '1971-01-05', 0, 1104),
(5, '1971-01-05', 0, 1105),
(6, '1971-01-05', 0, 2101),
(7, '1971-01-05', 0, 2102),
(8, '1971-01-05', 0, 2103),
(9, '1971-01-05', 0, 2104),
(10, '1971-01-05', 0, 2105),
(11, '1971-01-05', 0, 2106),
(12, '1971-01-05', 0, 2107),
(13, '1971-01-05', 0, 2108),
(14, '1971-01-05', 0, 2109),
(15, '1971-01-05', 0, 2110);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
