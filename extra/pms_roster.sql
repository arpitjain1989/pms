-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 15, 2013 at 12:08 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pms_test_server`
--

-- --------------------------------------------------------

--
-- Table structure for table `pms_roster`
--

CREATE TABLE IF NOT EXISTS `pms_roster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `weekoffdate` datetime DEFAULT NULL,
  `weekoffday` varchar(255) DEFAULT NULL,
  `addedon` datetime DEFAULT NULL,
  `addedby` int(11) DEFAULT NULL,
  `autoadded` tinyint(1) DEFAULT NULL,
  `reportinghead` int(11) DEFAULT NULL,
  `secondreportinghead` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `pms_roster`
--

INSERT INTO `pms_roster` (`id`, `userid`, `start_date`, `end_date`, `weekoffdate`, `weekoffday`, `addedon`, `addedby`, `autoadded`, `reportinghead`, `secondreportinghead`) VALUES
(1, 30, '2013-02-25 00:00:00', '2013-03-03 00:00:00', '2013-02-25 00:00:00', 'Monday', '2013-03-09 17:50:15', 8, 0, 8, 3),
(2, 31, '2013-02-25 00:00:00', '2013-03-03 00:00:00', '2013-02-25 00:00:00', 'Monday', '2013-03-09 17:50:15', 8, 0, 8, 3),
(3, 32, '2013-02-25 00:00:00', '2013-03-03 00:00:00', '2013-03-03 00:00:00', 'Sunday', '2013-03-09 17:50:15', 8, 0, 8, 3),
(4, 33, '2013-02-25 00:00:00', '2013-03-03 00:00:00', '2013-03-03 00:00:00', 'Sunday', '2013-03-09 17:50:15', 8, 0, 8, 3),
(5, 34, '2013-02-25 00:00:00', '2013-03-03 00:00:00', '2013-03-01 00:00:00', 'Friday', '2013-03-09 17:50:15', 8, 0, 8, 3),
(6, 35, '2013-02-25 00:00:00', '2013-03-03 00:00:00', '2013-03-03 00:00:00', 'Sunday', '2013-03-09 17:50:15', 8, 0, 8, 3),
(7, 36, '2013-02-25 00:00:00', '2013-03-03 00:00:00', '2013-02-25 00:00:00', 'Monday', '2013-03-09 17:50:15', 8, 0, 8, 3),
(8, 37, '2013-02-25 00:00:00', '2013-03-03 00:00:00', '2013-03-03 00:00:00', 'Sunday', '2013-03-09 17:50:15', 8, 0, 8, 3),
(9, 30, '2013-03-04 00:00:00', '2013-03-10 00:00:00', '2013-03-06 00:00:00', 'Wednesday', '2013-03-09 18:02:35', 8, 0, 8, 3),
(10, 31, '2013-03-04 00:00:00', '2013-03-10 00:00:00', '2013-03-05 00:00:00', 'Tuesday', '2013-03-09 18:02:35', 8, 0, 8, 3),
(11, 32, '2013-03-04 00:00:00', '2013-03-10 00:00:00', '2013-03-10 00:00:00', 'Sunday', '2013-03-09 18:02:35', 8, 0, 8, 3),
(12, 33, '2013-03-04 00:00:00', '2013-03-10 00:00:00', '2013-03-04 00:00:00', 'Monday', '2013-03-09 18:02:35', 8, 0, 8, 3),
(13, 34, '2013-03-04 00:00:00', '2013-03-10 00:00:00', '2013-03-07 00:00:00', 'Thursday', '2013-03-09 18:02:35', 8, 0, 8, 3),
(14, 35, '2013-03-04 00:00:00', '2013-03-10 00:00:00', '2013-03-10 00:00:00', 'Sunday', '2013-03-09 18:02:35', 8, 0, 8, 3),
(15, 36, '2013-03-04 00:00:00', '2013-03-10 00:00:00', '2013-03-07 00:00:00', 'Thursday', '2013-03-09 18:02:35', 8, 0, 8, 3),
(16, 37, '2013-03-04 00:00:00', '2013-03-10 00:00:00', '2013-03-10 00:00:00', 'Sunday', '2013-03-09 18:02:35', 8, 0, 8, 3);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
