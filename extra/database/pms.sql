-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 12, 2013 at 09:26 AM
-- Server version: 5.5.24-log
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pms`
--

-- --------------------------------------------------------

--
-- Table structure for table `pms_admin`
--

CREATE TABLE IF NOT EXISTS `pms_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `pms_admin`
--

INSERT INTO `pms_admin` (`id`, `name`, `email`, `phone`, `username`, `password`, `created_date`) VALUES
(1, 'admin', 'test@transformsolution.net', 1234567890, 'admin', '2eef28ab3d5273ba75abc953cbadb80b', '2013-01-07 13:09:10');

-- --------------------------------------------------------

--
-- Table structure for table `pms_clients`
--

CREATE TABLE IF NOT EXISTS `pms_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `pms_clients`
--

INSERT INTO `pms_clients` (`id`, `name`, `display_name`, `email`, `contact`) VALUES
(1, 'Client1', 'clientNo1', 'client1@client.com', 1234567890),
(5, 'asfsdfsf', 'asdfsdfsdf', 'sfsafsdf@sfdasf.com', 234242424);

-- --------------------------------------------------------

--
-- Table structure for table `pms_departments`
--

CREATE TABLE IF NOT EXISTS `pms_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `pms_departments`
--

INSERT INTO `pms_departments` (`id`, `title`, `description`) VALUES
(6, 'department1', 'description1'),
(7, 'department21', 'description21');

-- --------------------------------------------------------

--
-- Table structure for table `pms_designation`
--

CREATE TABLE IF NOT EXISTS `pms_designation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `pms_designation`
--

INSERT INTO `pms_designation` (`id`, `title`, `description`) VALUES
(3, 'designation1', 'description1'),
(4, 'designation2', 'description2');

-- --------------------------------------------------------

--
-- Table structure for table `pms_employee`
--

CREATE TABLE IF NOT EXISTS `pms_employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `teamleader` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `pms_employee`
--

INSERT INTO `pms_employee` (`id`, `employee_code`, `name`, `email`, `contact`, `address`, `department`, `designation`, `teamleader`, `password`, `status`, `role`) VALUES
(9, 'tf123', 'Name1', 'admin@transformsolution.net', '1234567890', 'test1', '7', '4', 'test1', 'test1', '1', 'agent'),
(10, 'tf1234', 'Name2', 'name2@name.com', '242342342342', 'test2', '6', '3', 'test1', '', '0', 'agent'),
(11, 'tf567', 'Name3', 'name3@name.com', '2343567890', 'sdafasf sfasfsafas fasdf asdfasdf', '6', '3', 'test2', '', '0', ''),
(12, 'es278', 'Name4', 'name4@name.com', '2342123234', 'sfsfsf sf sadf sfd sdfsadfsdf sdfsafsad sdfasf', '6', '3', 'test3', 'test1', '0', 'agent'),
(15, 'aaaaaaaaaa', 'bbbbbbbbb', 'aaaaaaaaa@ssadf.com', '2424242434', 'sfsfdasdf', '6', '3', 'asfdasdf', '', '0', 'asfdasdfaf'),
(16, '1', 'chandni', 'Chandni.patel@transformsolution.net', '23445345345', 'Sdfas Sdfsa Fsadasf Sadf', 'Department2', 'Designation 2', 'Team Leader 2', '342423', 'Employee', 'Deactive'),
(17, '2', 'gagan', 'Gagan.mahatma@transformsolution.net', '', 'Sdfas Fsaf Sdfsa Fsadasf Sadf', 'Department3', 'Designation 3', 'Team Leader 3', '3424223', 'Employee', 'Deactive'),
(18, '3', 'vikas sharma', 'Vikas.sharma@transformsolution.net', '', 'Sdfas Sdfsa Fsadasf Sadf', 'Department4', 'Designation 4', 'Team Leader 4', '232343', 'Employee', 'Deactive'),
(19, '4', 'test1', 'Test1@test.com', '2452424242', 'Safdsadf Sfdsafdasd', 'Department1', 'Designation1', 'Team Leader1', '123456', 'Employee', 'Active'),
(20, '5', 'test2', 'Test2@test.com', '2452424242', 'Safsafwrewerdfsdf Dsfewrwr', 'Department2', 'Designation2', 'Team Leader2', '123457', 'Employee', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `pms_form_field`
--

CREATE TABLE IF NOT EXISTS `pms_form_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `iscompulsory` tinyint(1) NOT NULL,
  `validation_rule` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pms_form_field`
--

INSERT INTO `pms_form_field` (`id`, `title`, `type`, `iscompulsory`, `validation_rule`) VALUES
(1, 'textbox1', '1', 0, '1'),
(2, 'Textarea', '2', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `pms_job_types`
--

CREATE TABLE IF NOT EXISTS `pms_job_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `has_target` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `billable_hour` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pms_job_types`
--

INSERT INTO `pms_job_types` (`id`, `title`, `description`, `has_target`, `target`, `billable_hour`) VALUES
(1, 'job type1', 'description1', '1', '', 12),
(3, 'job type2', 'description2', '0', '200', 100);

-- --------------------------------------------------------

--
-- Table structure for table `pms_module`
--

CREATE TABLE IF NOT EXISTS `pms_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `pms_module`
--

INSERT INTO `pms_module` (`id`, `title`, `description`) VALUES
(1, 'Roles', ''),
(2, 'Departments', ''),
(3, 'Designation', ''),
(4, 'Employee', ''),
(5, 'Project', ''),
(6, 'JobType', ''),
(7, 'Task', ''),
(8, 'Clients', '');

-- --------------------------------------------------------

--
-- Table structure for table `pms_project`
--

CREATE TABLE IF NOT EXISTS `pms_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `client` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `form` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pms_project`
--

INSERT INTO `pms_project` (`id`, `title`, `owner`, `description`, `client`, `status`, `form`) VALUES
(2, 'project1', 'owner1', 'description1', 'client', '1', 'form'),
(3, 'project2', 'owner2', 'description2', 'client2', '0', 'form2');

-- --------------------------------------------------------

--
-- Table structure for table `pms_roles`
--

CREATE TABLE IF NOT EXISTS `pms_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `pms_roles`
--

INSERT INTO `pms_roles` (`id`, `title`, `description`) VALUES
(2, 'roles2', 'description2'),
(8, 'Roles4', 'rolesdesc4'),
(9, 'roles5', 'rolesdesc5');

-- --------------------------------------------------------

--
-- Table structure for table `pms_role_details`
--

CREATE TABLE IF NOT EXISTS `pms_role_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roll_id` int(11) NOT NULL,
  `modules` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=63 ;

--
-- Dumping data for table `pms_role_details`
--

INSERT INTO `pms_role_details` (`id`, `roll_id`, `modules`) VALUES
(53, 2, 1),
(54, 2, 2),
(55, 2, 3),
(56, 2, 4),
(57, 8, 1),
(58, 8, 2),
(59, 8, 3),
(60, 9, 2),
(61, 9, 3),
(62, 9, 4);

-- --------------------------------------------------------

--
-- Table structure for table `pms_settings`
--

CREATE TABLE IF NOT EXISTS `pms_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` int(12) NOT NULL,
  `email` varchar(255) NOT NULL,
  `website_title` varchar(255) NOT NULL,
  `website_description` varchar(255) NOT NULL,
  `website_keywords` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `pms_settings`
--

INSERT INTO `pms_settings` (`id`, `phone`, `email`, `website_title`, `website_description`, `website_keywords`) VALUES
(1, 1234567890, 'test1@transformsolution.net', 'Transform Solution', 'Transform solution project management System', 'transform,admin,transformsolution');

-- --------------------------------------------------------

--
-- Table structure for table `pms_task`
--

CREATE TABLE IF NOT EXISTS `pms_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `project` varchar(255) NOT NULL,
  `job_type` varchar(255) NOT NULL,
  `team_leader` varchar(255) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `overtime` varchar(255) NOT NULL,
  `rework` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `pms_task`
--

INSERT INTO `pms_task` (`id`, `title`, `description`, `project`, `job_type`, `team_leader`, `start_date`, `end_date`, `overtime`, `rework`) VALUES
(3, 'task1', 'description1', '3', '3', 'team leader 1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', '1'),
(5, 'task111111', 'description1', '2', '1', 'team leader 11', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0', '0'),
(6, 'task21', 'description21', '2', '1', 'team leader 2', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0', '0'),
(7, 'tasknew', 'tastdescnew', '2', '3', 'team leader new', '2013-01-10 07:46:57', '0000-00-00 00:00:00', '0', '0'),
(8, 'tasknew1', 'tastdescnew2', '2', '1', 'team leader new', '2013-01-10 07:55:03', '0000-00-00 00:00:00', '0', '0'),
(9, 'tasknew2', 'tastdescnew2', '3', '3', 'team leader new2', '2013-01-10 08:56:27', '0000-00-00 00:00:00', '0', '1'),
(10, 'tasknew3', 'tastdescnew3', '2', '1', 'team leader new3', '2013-01-10 09:00:13', '0000-00-00 00:00:00', '0', '0'),
(11, 'tasknew4', 'tastdescnew4', '2', '1', 'team leader new4', '2012-12-31 19:45:00', '2013-01-31 10:45:00', '0', '1');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
