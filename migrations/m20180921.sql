-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 25, 2018 at 01:29 PM
-- Server version: 5.7.14-7
-- PHP Version: 7.2.7-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `insqube`
--


-- --------------------------------------------------------

--
-- Table structure for table `dt_addresses`
--

CREATE TABLE IF NOT EXISTS `dt_addresses` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) UNSIGNED NOT NULL,
  `type_id` bigint(11) UNSIGNED NOT NULL,
  `country_id` int(3) UNSIGNED NOT NULL,
  `state_id` int(11) UNSIGNED DEFAULT NULL,
  `address1_id` int(11) UNSIGNED DEFAULT NULL,
  `alt_state_text` varchar(150) DEFAULT NULL,
  `alt_address1_text` varchar(150) DEFAULT NULL,
  `address2` varchar(150) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `zip_postal_code` varchar(20) DEFAULT NULL,
  `phones` varchar(50) DEFAULT NULL,
  `faxes` varchar(50) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(80) DEFAULT NULL,
  `web` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) UNSIGNED NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_type` (`type`,`type_id`) USING BTREE,
  KEY `__fkc__address__country` (`country_id`),
  KEY `__fkc__address__state` (`state_id`),
  KEY `__fkc__address__localbody` (`address1_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dt_addresses`
--
ALTER TABLE `dt_addresses`
  ADD CONSTRAINT `__fkc__address__country` FOREIGN KEY (`country_id`) REFERENCES `master_countries` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `__fkc__address__localbody` FOREIGN KEY (`address1_id`) REFERENCES `master_localbodies` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `__fkc__address__state` FOREIGN KEY (`state_id`) REFERENCES `master_states` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
