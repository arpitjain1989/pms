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
-- Table structure for table `pms_roster_detail`
--

CREATE TABLE IF NOT EXISTS `pms_roster_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rosterid` int(11) DEFAULT NULL,
  `rostereddate` datetime DEFAULT NULL,
  `attendance` varchar(10) DEFAULT NULL,
  `shiftid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=113 ;

--
-- Dumping data for table `pms_roster_detail`
--

INSERT INTO `pms_roster_detail` (`id`, `rosterid`, `rostereddate`, `attendance`, `shiftid`) VALUES
(1, 1, '2013-02-25 00:00:00', 'WO', 9),
(2, 1, '2013-02-26 00:00:00', 'P', 9),
(3, 1, '2013-02-27 00:00:00', 'P', 9),
(4, 1, '2013-02-28 00:00:00', 'P', 9),
(5, 1, '2013-03-01 00:00:00', 'P', 9),
(6, 1, '2013-03-02 00:00:00', 'P', 9),
(7, 1, '2013-03-03 00:00:00', 'P', 9),
(8, 2, '2013-02-25 00:00:00', 'WO', 8),
(9, 2, '2013-02-26 00:00:00', 'SC', 6),
(10, 2, '2013-02-27 00:00:00', 'P', 8),
(11, 2, '2013-02-28 00:00:00', 'P', 8),
(12, 2, '2013-03-01 00:00:00', 'SC', 10),
(13, 2, '2013-03-02 00:00:00', 'SC', 6),
(14, 2, '2013-03-03 00:00:00', 'SC', 15),
(15, 3, '2013-02-25 00:00:00', 'P', 6),
(16, 3, '2013-02-26 00:00:00', 'P', 6),
(17, 3, '2013-02-27 00:00:00', 'P', 6),
(18, 3, '2013-02-28 00:00:00', 'P', 6),
(19, 3, '2013-03-01 00:00:00', 'P', 6),
(20, 3, '2013-03-02 00:00:00', 'P', 6),
(21, 3, '2013-03-03 00:00:00', 'WO', 6),
(22, 4, '2013-02-25 00:00:00', 'P', 6),
(23, 4, '2013-02-26 00:00:00', 'P', 6),
(24, 4, '2013-02-27 00:00:00', 'P', 6),
(25, 4, '2013-02-28 00:00:00', 'P', 6),
(26, 4, '2013-03-01 00:00:00', 'SC', 15),
(27, 4, '2013-03-02 00:00:00', 'SC', 15),
(28, 4, '2013-03-03 00:00:00', 'WO', 6),
(29, 5, '2013-02-25 00:00:00', 'P', 6),
(30, 5, '2013-02-26 00:00:00', 'P', 6),
(31, 5, '2013-02-27 00:00:00', 'P', 6),
(32, 5, '2013-02-28 00:00:00', 'P', 6),
(33, 5, '2013-03-01 00:00:00', 'WO', 6),
(34, 5, '2013-03-02 00:00:00', 'SC', 10),
(35, 5, '2013-03-03 00:00:00', 'SC', 10),
(36, 6, '2013-02-25 00:00:00', 'P', 9),
(37, 6, '2013-02-26 00:00:00', 'P', 9),
(38, 6, '2013-02-27 00:00:00', 'P', 9),
(39, 6, '2013-02-28 00:00:00', 'P', 9),
(40, 6, '2013-03-01 00:00:00', 'P', 9),
(41, 6, '2013-03-02 00:00:00', 'P', 9),
(42, 6, '2013-03-03 00:00:00', 'WO', 9),
(43, 7, '2013-02-25 00:00:00', 'WO', 10),
(44, 7, '2013-02-26 00:00:00', 'P', 10),
(45, 7, '2013-02-27 00:00:00', 'P', 10),
(46, 7, '2013-02-28 00:00:00', 'P', 10),
(47, 7, '2013-03-01 00:00:00', 'SC', 9),
(48, 7, '2013-03-02 00:00:00', 'SC', 9),
(49, 7, '2013-03-03 00:00:00', 'SC', 9),
(50, 8, '2013-02-25 00:00:00', 'SC', 6),
(51, 8, '2013-02-26 00:00:00', 'SC', 6),
(52, 8, '2013-02-27 00:00:00', 'SC', 6),
(53, 8, '2013-02-28 00:00:00', 'SC', 6),
(54, 8, '2013-03-01 00:00:00', 'SC', 6),
(55, 8, '2013-03-02 00:00:00', 'SC', 6),
(56, 8, '2013-03-03 00:00:00', 'WO', 0),
(57, 9, '2013-03-04 00:00:00', 'P', 9),
(58, 9, '2013-03-05 00:00:00', 'P', 9),
(59, 9, '2013-03-06 00:00:00', 'WO', 9),
(60, 9, '2013-03-07 00:00:00', 'P', 9),
(61, 9, '2013-03-08 00:00:00', 'P', 9),
(62, 9, '2013-03-09 00:00:00', 'P', 9),
(63, 9, '2013-03-10 00:00:00', 'P', 9),
(64, 10, '2013-03-04 00:00:00', 'P', 8),
(65, 10, '2013-03-05 00:00:00', 'WO', 8),
(66, 10, '2013-03-06 00:00:00', 'P', 8),
(67, 10, '2013-03-07 00:00:00', 'SC', 10),
(68, 10, '2013-03-08 00:00:00', 'P', 8),
(69, 10, '2013-03-09 00:00:00', 'P', 8),
(70, 10, '2013-03-10 00:00:00', 'P', 8),
(71, 11, '2013-03-04 00:00:00', 'P', 6),
(72, 11, '2013-03-05 00:00:00', 'P', 6),
(73, 11, '2013-03-06 00:00:00', 'P', 6),
(74, 11, '2013-03-07 00:00:00', 'SC', 8),
(75, 11, '2013-03-08 00:00:00', 'P', 6),
(76, 11, '2013-03-09 00:00:00', 'P', 6),
(77, 11, '2013-03-10 00:00:00', 'WO', 6),
(78, 12, '2013-03-04 00:00:00', 'WO', 6),
(79, 12, '2013-03-05 00:00:00', 'SC', 8),
(80, 12, '2013-03-06 00:00:00', 'SC', 15),
(81, 12, '2013-03-07 00:00:00', 'SC', 15),
(82, 12, '2013-03-08 00:00:00', 'SC', 15),
(83, 12, '2013-03-09 00:00:00', 'SC', 15),
(84, 12, '2013-03-10 00:00:00', 'SC', 15),
(85, 13, '2013-03-04 00:00:00', 'SC', 15),
(86, 13, '2013-03-05 00:00:00', 'SC', 15),
(87, 13, '2013-03-06 00:00:00', 'SC', 9),
(88, 13, '2013-03-07 00:00:00', 'WO', 6),
(89, 13, '2013-03-08 00:00:00', 'SC', 10),
(90, 13, '2013-03-09 00:00:00', 'SC', 10),
(91, 13, '2013-03-10 00:00:00', 'SC', 10),
(92, 14, '2013-03-04 00:00:00', 'P', 9),
(93, 14, '2013-03-05 00:00:00', 'P', 9),
(94, 14, '2013-03-06 00:00:00', 'P', 9),
(95, 14, '2013-03-07 00:00:00', 'P', 9),
(96, 14, '2013-03-08 00:00:00', 'P', 9),
(97, 14, '2013-03-09 00:00:00', 'P', 9),
(98, 14, '2013-03-10 00:00:00', 'WO', 9),
(99, 15, '2013-03-04 00:00:00', 'P', 10),
(100, 15, '2013-03-05 00:00:00', 'P', 10),
(101, 15, '2013-03-06 00:00:00', 'P', 10),
(102, 15, '2013-03-07 00:00:00', 'WO', 10),
(103, 15, '2013-03-08 00:00:00', 'SC', 9),
(104, 15, '2013-03-09 00:00:00', 'SC', 9),
(105, 15, '2013-03-10 00:00:00', 'SC', 9),
(106, 16, '2013-03-04 00:00:00', 'SC', 6),
(107, 16, '2013-03-05 00:00:00', 'SC', 6),
(108, 16, '2013-03-06 00:00:00', 'SC', 6),
(109, 16, '2013-03-07 00:00:00', 'SC', 6),
(110, 16, '2013-03-08 00:00:00', 'SC', 6),
(111, 16, '2013-03-09 00:00:00', 'SC', 6),
(112, 16, '2013-03-10 00:00:00', 'WO', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
