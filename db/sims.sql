-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 05:09 PM
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
-- Database: `sims`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` int(10) NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `category`, `description`) VALUES
(1, 'easy', 'easy one'),
(2, 'Skincare', 'jfbn dhbhjewjknmwe'),
(3, 'Another one', 'jhnmbd'),
(4, 'Come on', 'asdfghjklnbvcxzerthb'),
(5, 'okay', 'sdfghgfdter');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id` int(10) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id`, `username`, `password`, `role`) VALUES
(1, 'username', 'password', 'Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `prod_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `desc` varchar(500) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `price_per_packet` decimal(10,2) NOT NULL,
  `price_per_sachet` decimal(10,2) NOT NULL,
  `quantity_packet` int(11) NOT NULL,
  `quantity_per_pack` int(11) NOT NULL,
  `low_stock_alert` int(11) NOT NULL,
  `expiry_alert_days` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `brand`, `batch_number`, `prod_date`, `exp_date`, `desc`, `cost_price`, `price_per_packet`, `price_per_sachet`, `quantity_packet`, `quantity_per_pack`, `low_stock_alert`, `expiry_alert_days`, `created_at`) VALUES
(3, 'Paracetamol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 122, 1234, 12, 1234, '2025-04-15 10:33:33'),
(4, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-04-20 12:19:08'),
(5, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:42:09'),
(6, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:42:42'),
(7, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-04-23 10:16:22'),
(8, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:56:26'),
(9, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-04-20 12:24:18'),
(10, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 10:26:20'),
(11, 'Paracetamol', 'Skincare', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-04-20 12:18:57'),
(12, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:30:57'),
(13, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-04-23 21:30:03'),
(14, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:33:01'),
(15, 'Panadol', 'Skincare', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-04-23 21:22:30'),
(16, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:34:00'),
(17, 'Panadol', 'easy', 'Normal', 'B123407', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 50, 20, '2025-04-23 21:31:30'),
(19, 'Panadol', 'Come on', 'Emzor', 'K09876', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-04-23 21:43:19'),
(20, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:43:06'),
(21, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:45:53'),
(22, 'Panadol', 'Come on', 'Emzor', 'B123457', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-04-23 21:26:51'),
(23, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:53:32'),
(24, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-04-20 12:30:57'),
(25, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:58:35'),
(26, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:58:58'),
(27, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 11:13:05'),
(28, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 11:13:46'),
(29, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 11:15:20'),
(30, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 11:15:45'),
(31, 'Panadol', 'Skincare', 'Emzor', 'B123456', '2025-03-15', '2025-03-31', 'gfedhfdgfrgbjghjgbtfhgvdrst', 500.00, 550.00, 25.00, 10, 50, 12345, 1233, '2025-03-08 11:22:02'),
(32, 'Panadol', 'Skincare', 'Emzor', 'B123456', '2025-03-15', '2025-03-31', 'gfedhfdgfrgbjghjgbtfhgvdrst', 500.00, 550.00, 25.00, 10, 50, 12345, 1233, '2025-03-08 11:24:29'),
(33, 'Ibruprofen', 'Another one', 'Emzor', 'B654321', '2025-03-13', '2025-03-31', 'This is a pain releive ', 100.00, 200.00, 50.00, 12, 5, 10, 19, '2025-03-13 12:25:11'),
(34, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6543, 30, 4, '2025-04-29 16:42:32'),
(35, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6543, 30, 4, '2025-04-29 16:44:46'),
(36, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6543, 30, 4, '2025-04-29 16:45:45'),
(37, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6543, 30, 4, '2025-04-29 16:46:53'),
(38, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6543, 30, 4, '2025-04-29 16:49:03'),
(39, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 16:54:45'),
(40, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 16:55:19'),
(41, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 16:59:09'),
(42, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 16:59:41'),
(43, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 16:59:56'),
(44, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:00:50'),
(45, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:01:41'),
(46, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:02:02'),
(47, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:02:36'),
(48, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:03:04'),
(49, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:03:26'),
(50, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:03:35'),
(51, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:05:22'),
(52, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 17:06:03'),
(53, 'Damatol', 'Skincare', 'Emzor', 'D54321', '2025-05-11', '2025-06-03', 'qwaesfrdftgyh', 200.00, 205.00, 203.00, 900, 450, 12, 50, '2025-04-29 17:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_name` varchar(255) NOT NULL,
  `price_per_packet` decimal(10,2) DEFAULT NULL,
  `price_per_sachet` decimal(10,2) DEFAULT NULL,
  `qty_packet` int(11) DEFAULT NULL,
  `qty_sachet` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_name`, `price_per_packet`, `price_per_sachet`, `qty_packet`, `qty_sachet`, `created_at`) VALUES
(1, 17, '100ml', 0.00, 0.00, 45, 100, '2025-04-23 21:08:29'),
(3, 19, '800ml', 0.00, 0.00, 0, 0, '2025-04-15 20:54:14'),
(4, 20, '', 0.00, 0.00, 0, 0, '2025-03-08 10:43:06'),
(5, 21, '', 0.00, 0.00, 0, 0, '2025-03-08 10:45:53'),
(6, 22, '500ml', 0.00, 0.00, 5, 20, '2025-04-23 11:31:07'),
(7, 23, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 10:53:32'),
(8, 24, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 10:57:38'),
(10, 26, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 10:58:58'),
(11, 27, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 11:13:05'),
(12, 28, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 11:13:46'),
(13, 29, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 11:15:20'),
(14, 30, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 11:15:45'),
(17, 22, '400g', 13.00, 15.00, 32, 89, '2025-04-23 11:51:42'),
(18, 22, '1000g', 10.00, 15.00, 20, 23, '2025-04-23 11:53:01'),
(19, 22, '100g', 20.00, 50.00, 100, 500, '2025-04-23 11:54:28'),
(20, 33, '200g', 765.00, 123.00, 23, 45, '2025-04-29 13:34:46'),
(21, 40, 'Children', 0.00, 0.00, 0, 0, '2025-04-29 16:55:19'),
(22, 40, 'Adult', 0.00, 0.00, 0, 0, '2025-04-29 16:55:19'),
(23, 52, 'Children', 205.00, 102.50, 900, 450, '2025-04-29 17:06:03'),
(24, 52, 'Adult', 220.00, 110.00, 243, 123, '2025-04-29 17:06:03'),
(25, 53, '250mg', 90.00, 45.00, 100, 300, '2025-04-29 17:14:44'),
(26, 53, '500mg', 30.00, 34.00, 45, 56, '2025-04-29 17:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `transaction_number` varchar(20) NOT NULL,
  `sale_date` datetime NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT 'Cash',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_details`
--

CREATE TABLE `sale_details` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `home_address` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `date_registered` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `email`, `home_address`, `phone`, `password`, `role`, `date_registered`) VALUES
(1, 'Gorden', 'Dabie', 'dabiegorden49@gmail.com', '14, olorunda street off bioyin', '07051927036', 'Qqy49XX*', 'manager', '2025-03-04 15:10:22.000000'),
(2, 'Emmanuel', 'Ogu', 'emmanuelogu03@gmail.com', 'Catholic University', '0559380412', 'yy1S=3L0', 'sales rep', '2025-03-05 13:48:32.000000');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `category` (`category`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_transaction_number` (`transaction_number`),
  ADD KEY `idx_sale_date` (`sale_date`),
  ADD KEY `fk_sales_user` (`created_by`);

--
-- Indexes for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_details_sale` (`sale_id`),
  ADD KEY `fk_details_product` (`product_id`),
  ADD KEY `fk_details_variant` (`variant_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cat_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_details`
--
ALTER TABLE `sale_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `category` FOREIGN KEY (`category`) REFERENCES `category` (`category`);

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD CONSTRAINT `fk_details_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_details_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_details_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
