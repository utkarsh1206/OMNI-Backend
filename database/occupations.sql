-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2024 at 07:24 AM
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
-- Database: `omnichannel`
--

-- --------------------------------------------------------

--
-- Table structure for table `occupations`
--

CREATE TABLE `occupations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `is_delete` enum('Yes','No') NOT NULL DEFAULT 'No',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `occupations`
--

INSERT INTO `occupations` (`id`, `user_id`, `title`, `status`, `is_delete`, `created_at`, `updated_at`) VALUES
(1, 0, 'Salaried', 'Active', 'No', '2020-01-21 11:39:44', '0000-00-00 00:00:00'),
(2, 0, 'Student', 'Active', 'No', '2020-01-21 11:40:02', '0000-00-00 00:00:00'),
(3, 0, 'Retired', 'Active', 'No', '2020-01-21 11:40:08', '0000-00-00 00:00:00'),
(4, 0, 'Business Owner', 'Active', 'No', '2020-01-21 11:40:26', '0000-00-00 00:00:00'),
(5, 0, 'Self-Employed Professional', 'Active', 'No', '2020-01-21 11:42:02', '0000-00-00 00:00:00'),
(6, 0, 'Independent Worker', 'Active', 'No', '2020-01-21 11:42:34', '0000-00-00 00:00:00'),
(7, 2, 'Farmer', 'Inactive', 'Yes', '2020-09-03 13:04:05', '2020-09-03 13:48:06'),
(8, 2, 'Shopkeeper New', 'Active', 'Yes', '2020-09-03 13:47:06', '2020-09-03 13:47:22'),
(9, 2, 'Farmer', 'Inactive', 'Yes', '2020-09-03 19:14:29', '2020-09-03 19:15:30');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
