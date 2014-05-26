-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2014 at 08:16 AM
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
CREATE DATABASE IF NOT EXISTS `bfh6o` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `bfh6o`;

-- --------------------------------------------------------

--
-- Table structure for table `indexe`
--

DROP TABLE IF EXISTS `indexe`;
CREATE TABLE IF NOT EXISTS `indexe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `kuerzel` varchar(9) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5103 ;

-- --------------------------------------------------------

--
-- Table structure for table `indexe_values`
--

DROP TABLE IF EXISTS `indexe_values`;
CREATE TABLE IF NOT EXISTS `indexe_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tradeDate` date NOT NULL,
  `adjClose` double NOT NULL,
  `fk_indexe_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=48681 ;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nachricht` varchar(250) NOT NULL,
  `typ` tinyint(3) NOT NULL,
  `httpCode` smallint(3) NOT NULL,
  `zeitstempel` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `log_typ`
--

DROP TABLE IF EXISTS `log_typ`;
CREATE TABLE IF NOT EXISTS `log_typ` (
  `id` int(11) NOT NULL,
  `typ` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
