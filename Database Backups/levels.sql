-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 26, 2019 at 12:58 PM
-- Server version: 5.6.41-84.1
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dynamoca_report_cards`
--

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `id` int(11) NOT NULL,
  `level_groups_id` int(11) NOT NULL,
  `level_number` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`id`, `level_groups_id`, `level_number`, `active`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 1, 5, 1),
(6, 1, 6, 1),
(7, 2, 1, 1),
(8, 2, 2, 1),
(9, 2, 3, 1),
(10, 2, 4, 1),
(11, 2, 5, 1),
(12, 2, 6, 1),
(13, 2, 7, 1),
(14, 2, 8, 1),
(15, 2, 9, 1),
(16, 3, 1, 1),
(17, 3, 2, 1),
(18, 3, 3, 1),
(19, 3, 4, 1),
(20, 3, 5, 1),
(21, 3, 6, 1),
(22, 3, 7, 1),
(23, 3, 8, 1),
(24, 3, 9, 1),
(25, 3, 10, 1),
(26, 4, 1, 1),
(27, 4, 2, 1),
(28, 4, 3, 1),
(29, 4, 4, 1),
(30, 4, 5, 1),
(31, 4, 6, 1),
(32, 4, 7, 1),
(33, 4, 8, 1),
(34, 4, 9, 1),
(35, 4, 10, 1),
(36, 4, 11, 1),
(37, 4, 12, 1),
(38, 4, 13, 1),
(39, 4, 14, 1),
(40, 4, 15, 1),
(41, 4, 16, 1),
(42, 4, 17, 1),
(43, 4, 18, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
