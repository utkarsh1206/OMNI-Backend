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
-- Table structure for table `industry_types`
--

CREATE TABLE `industry_types` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Show Field',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `is_delete` enum('No','Yes') NOT NULL DEFAULT 'No',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='master';

--
-- Dumping data for table `industry_types`
--

INSERT INTO `industry_types` (`id`, `title`, `status`, `is_delete`, `created_at`, `updated_at`) VALUES
(1, 'Accounting', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(2, 'Advertising', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(3, 'Aerospace', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(4, 'Agriculture', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(5, 'Aircraft', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(6, 'Airline', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(7, 'Apparel &amp; Accessories', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(8, 'Automotive', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(9, 'Banking', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(10, 'Biotechnology', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(11, 'Broadcasting', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(12, 'Brokerage', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(13, 'Call Centers', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(14, 'Cargo Handling', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(15, 'Chemicals', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(16, 'Computer', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(17, 'Construction', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(18, 'Consulting', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(19, 'Consumer Products', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(20, 'Cosmetics', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(21, 'Defense', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(22, 'Department Stores', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(23, 'Ecommerce', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(24, 'Education', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(25, 'Electronics', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(26, 'Energy', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(27, 'Engineering', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(28, 'Entertainment &amp; Leisure', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(29, 'Executive Search', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(30, 'Financial Services', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(31, 'Food, Beverage &amp; Tobacco', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(32, 'Gaming', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(33, 'Government', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(34, 'Grocery', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(35, 'Health Care', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(36, 'Hospitality', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(37, 'Insurance', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(38, 'Internet Publishing', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(39, 'Investment Banking', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(40, 'Legal', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(41, 'Machinery', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(42, 'Manufacturing', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(43, 'Media', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(44, 'Motion Picture &amp; Video', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(45, 'Music', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(46, 'Newspaper Publishers', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(47, 'Not for Profit', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(48, 'Online Auctions', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(49, 'Other', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(50, 'Pension Funds', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(51, 'Pharmaceuticals', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(52, 'Private Equity', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(53, 'Publishing', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(54, 'Real Estate', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(55, 'Retail &amp; Wholesale', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(56, 'Securities &amp; Commodity Exchanges', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(57, 'Service', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(58, 'Shipping', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(59, 'Soap &amp; Detergent', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(60, 'Software', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(61, 'Sports', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(62, 'Technology', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(63, 'Telecommunications', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(64, 'Television', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(65, 'Transportation', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(66, 'Trucking', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(67, 'Utilities', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(68, 'Venture Capital', 'Active', 'No', '2020-03-23 16:15:40', '0000-00-00 00:00:00'),
(69, 'Accounting / CPA', 'Active', 'No', '2020-08-18 14:42:19', '0000-00-00 00:00:00'),
(70, 'New Check', 'Active', 'Yes', '2020-09-03 10:48:12', '2020-09-03 10:49:43'),
(71, 'New', 'Inactive', 'Yes', '2020-09-03 10:49:33', '2020-09-03 18:56:54'),
(72, 'Chk', 'Inactive', 'Yes', '2020-09-03 18:54:17', '2020-09-03 18:54:52'),
(73, 'Chk', 'Inactive', 'Yes', '2020-09-03 18:56:08', '2020-09-03 18:56:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `industry_types`
--
ALTER TABLE `industry_types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `industry_types`
--
ALTER TABLE `industry_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
