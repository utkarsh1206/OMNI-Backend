-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 22, 2024 at 12:22 PM
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
-- Table structure for table `business_types`
--

CREATE TABLE `business_types` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Show Field',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `is_delete` enum('No','Yes') NOT NULL DEFAULT 'No',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='master';

--
-- Dumping data for table `business_types`
--

INSERT INTO `business_types` (`id`, `title`, `status`, `is_delete`, `created_at`, `updated_at`) VALUES
(1, 'Analyst', 'Active', 'No', '2020-03-23 16:18:22', '2020-09-03 17:25:12'),
(2, 'Competitor', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(3, 'Customer', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(4, 'Integrator', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(5, 'Investor', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(6, 'Partner', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(7, 'Press', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(8, 'Prospect', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(9, 'Reseller', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(10, 'Other', 'Active', 'No', '2020-03-23 16:18:22', '0000-00-00 00:00:00'),
(11, 'Farmer', 'Inactive', 'Yes', '2020-09-02 14:21:55', '2020-09-03 17:23:24'),
(12, 'Check New', 'Active', 'Yes', '2020-09-02 14:28:06', '2020-09-02 14:32:25'),
(13, 'Check New', 'Active', 'Yes', '2020-09-02 14:28:35', '2020-09-02 14:32:19'),
(14, 'Check New q', 'Inactive', 'Yes', '2020-09-02 14:31:55', '2020-09-02 14:32:13'),
(15, 'Asdsad', 'Inactive', 'Yes', '2020-09-02 15:10:07', '2020-09-02 15:18:12'),
(16, 'Farmer', 'Inactive', 'Yes', '2020-09-03 17:43:22', '2020-09-03 17:43:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `business_types`
--
ALTER TABLE `business_types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `business_types`
--
ALTER TABLE `business_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
