-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2025 at 12:44 PM
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
-- Table structure for table `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `low_stock_alerts` tinyint(1) DEFAULT 1,
  `expiry_notifications` tinyint(1) DEFAULT 1,
  `low_stock_threshold` int(11) DEFAULT 10,
  `expiry_threshold_days` int(11) DEFAULT 30,
  `notification_methods` varchar(255) DEFAULT 'email,dashboard',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_settings`
--

INSERT INTO `notification_settings` (`id`, `user_id`, `low_stock_alerts`, `expiry_notifications`, `low_stock_threshold`, `expiry_threshold_days`, `notification_methods`, `created_at`, `updated_at`) VALUES
(1, 8, 1, 1, 30, 30, 'email', '2025-05-27 17:06:54', '2025-05-27 17:32:17');

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
(3, 'Paracetamol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 122, 1232, 12, 1234, '2025-04-15 10:33:33'),
(4, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1233, 12, 1234, '2025-04-20 12:19:08'),
(5, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:42:09'),
(6, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:42:42'),
(7, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-04-23 10:16:22'),
(8, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:56:26'),
(9, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-04-20 12:24:18'),
(10, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 10:26:20'),
(11, 'Paracetamol', 'Skincare', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1233, 12, 1234, '2025-04-20 12:18:57'),
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
(34, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6540, 30, 4, '2025-04-29 16:42:32'),
(35, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6543, 30, 4, '2025-04-29 16:44:46'),
(36, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6543, 30, 4, '2025-04-29 16:45:45'),
(37, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6543, 30, 4, '2025-04-29 16:46:53'),
(38, 'Codeine ', 'Another one', 'Normal', 'C09876', '2025-04-01', '2025-12-29', 'i;uhjuhbnhu', 30.00, 97.00, 345.00, 3456, 6542, 30, 4, '2025-04-29 16:49:03'),
(39, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 649, 345, 99, '2025-04-29 16:54:45'),
(40, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 650, 345, 99, '2025-04-29 16:55:19'),
(41, 'Amatem', 'Come on', 'Mosquito', 'A67859', '2025-04-08', '2025-05-08', 'asdfgch', 234.00, 543.00, 123.00, 876, 636, 345, 99, '2025-04-29 16:59:09'),
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
(20, 33, '200g', 765.00, 123.00, 23, 44, '2025-04-29 13:34:46'),
(21, 40, 'Children', 0.00, 0.00, 0, 0, '2025-04-29 16:55:19'),
(22, 40, 'Adult', 0.00, 0.00, 0, 0, '2025-04-29 16:55:19'),
(23, 52, 'Children', 205.00, 102.50, 900, 450, '2025-04-29 17:06:03'),
(24, 52, 'Adult', 220.00, 110.00, 243, 123, '2025-04-29 17:06:03'),
(25, 53, '250mg', 90.00, 45.00, 100, 298, '2025-04-29 17:14:44'),
(26, 53, '500mg', 30.00, 34.00, 45, 55, '2025-04-29 17:14:44');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `report_name` varchar(255) NOT NULL,
  `report_type` enum('sales_summary','inventory_valuation','low_stock','stock_movement','expiry_report','all','custom') NOT NULL,
  `report_period_start` date NOT NULL,
  `report_period_end` date NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(500) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `report_name`, `report_type`, `report_period_start`, `report_period_end`, `generated_by`, `generated_at`, `file_path`, `report_data`, `parameters`) VALUES
(1, 'Expiry Report Report - 2025-05-18 to 2025-05-25', 'expiry_report', '2025-05-18', '2025-05-25', 1, '2025-05-25 00:45:48', '../reports/generated/report_1748133948.html', NULL, '{\"time_period\":\"last_week\",\"start_date\":\"2025-05-18\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(2, 'Expiry Report Report - 2025-05-18 to 2025-05-25', 'expiry_report', '2025-05-18', '2025-05-25', 1, '2025-05-25 00:46:36', '../reports/generated/report_1748133996.csv', NULL, '{\"time_period\":\"last_week\",\"start_date\":\"2025-05-18\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"excel\"}'),
(3, 'Sales Summary Report - 2025-05-24 to 2025-05-24', 'sales_summary', '2025-05-24', '2025-05-24', 1, '2025-05-25 00:48:09', '../reports/generated/report_1748134089.html', NULL, '{\"time_period\":\"yesterday\",\"start_date\":\"2025-05-24\",\"end_date\":\"2025-05-24\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(4, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 00:54:57', '../reports/generated/report_1748134497.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(5, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 00:55:35', '../reports/generated/report_1748134535.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(6, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 13:25:48', '../reports/generated/report_1748179548.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(7, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 13:26:09', '../reports/generated/report_1748179569.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(8, 'Sales Summary Report - 2025-04-25 to 2025-05-25', 'sales_summary', '2025-04-25', '2025-05-25', 1, '2025-05-25 13:26:31', '../reports/generated/report_1748179591.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(9, 'Inventory Valuation Report - 2025-04-25 to 2025-05-25', 'inventory_valuation', '2025-04-25', '2025-05-25', 1, '2025-05-25 13:26:48', '../reports/generated/report_1748179608.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(10, 'Low Stock Report - 2025-04-25 to 2025-05-25', 'low_stock', '2025-04-25', '2025-05-25', 1, '2025-05-25 13:27:40', '../reports/generated/report_1748179660.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(11, 'Stock Movement Report - 2025-04-25 to 2025-05-25', 'stock_movement', '2025-04-25', '2025-05-25', 1, '2025-05-25 13:28:12', '../reports/generated/report_1748179692.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(12, 'Expiry Report Report - 2025-04-25 to 2025-05-25', 'expiry_report', '2025-04-25', '2025-05-25', 1, '2025-05-25 13:28:23', '../reports/generated/report_1748179703.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(13, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 13:28:43', '../reports/generated/report_1748179723.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":true,\"export_format\":\"pdf\"}'),
(14, 'Expiry Report Report - 2025-04-25 to 2025-05-25', 'expiry_report', '2025-04-25', '2025-05-25', 1, '2025-05-25 14:08:46', '../reports/generated/report_1748182126.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(15, 'Stock Movement Report - 2025-04-25 to 2025-05-25', 'stock_movement', '2025-04-25', '2025-05-25', 1, '2025-05-25 14:11:18', '../reports/generated/report_1748182278.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(16, 'Expiry Report Report - 2025-04-25 to 2025-05-25', 'expiry_report', '2025-04-25', '2025-05-25', 1, '2025-05-25 14:18:37', '../reports/generated/report_1748182717.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(17, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 14:19:10', '../reports/generated/report_1748182750.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(18, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 14:32:27', '../reports/generated/report_1748183547.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(19, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 14:33:35', '../reports/generated/report_1748183615.csv', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"excel\"}'),
(20, 'All Report - 2025-04-25 to 2025-05-25', 'all', '2025-04-25', '2025-05-25', 1, '2025-05-25 14:34:14', '../reports/generated/report_1748183654.csv', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"csv\"}'),
(21, 'Sales Summary Report - 2025-05-18 to 2025-05-25', 'sales_summary', '2025-05-18', '2025-05-25', 1, '2025-05-25 15:20:44', '../reports/generated/report_1748186444.html', NULL, '{\"time_period\":\"last_week\",\"start_date\":\"2025-05-18\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(22, 'Inventory Valuation Report - 2025-04-25 to 2025-05-25', 'inventory_valuation', '2025-04-25', '2025-05-25', 1, '2025-05-25 15:21:11', '../reports/generated/report_1748186471.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(23, 'Sales Summary Report - 2025-04-25 to 2025-05-25', 'sales_summary', '2025-04-25', '2025-05-25', 1, '2025-05-25 15:21:22', '../reports/generated/report_1748186482.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(24, 'Sales Summary Report - 2025-04-25 to 2025-05-25', 'sales_summary', '2025-04-25', '2025-05-25', 1, '2025-05-25 15:21:42', '../reports/generated/report_1748186502.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(25, 'Sales Summary Report - 2025-04-25 to 2025-05-25', 'sales_summary', '2025-04-25', '2025-05-25', 1, '2025-05-25 15:23:51', '../reports/generated/report_1748186631.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-25\",\"end_date\":\"2025-05-25\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(26, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 10:45:34', '../reports/generated/report_1748256334.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(27, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 10:54:24', '../reports/generated/report_1748256864.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(28, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 10:55:23', '../reports/generated/report_1748256923.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(29, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:02:17', '../reports/generated/report_1748257337.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(30, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:10:06', '../reports/generated/report_1748257806.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(31, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:13:53', '../reports/generated/report_1748258033.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(32, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:22:19', '../reports/generated/report_1748258539.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(33, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:28:08', '../reports/generated/report_1748258888.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(34, 'Inventory Valuation Report - 2025-04-26 to 2025-05-26', 'inventory_valuation', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:46:20', '../reports/generated/report_1748259980.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(35, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:47:20', '../reports/generated/report_1748260040.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(36, 'Stock Movement Report - 2025-04-26 to 2025-05-26', 'stock_movement', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:48:16', '../reports/generated/report_1748260096.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(37, 'Expiry Report Report - 2025-04-26 to 2025-05-26', 'expiry_report', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:49:17', '../reports/generated/report_1748260157.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(38, 'All Report - 2025-04-26 to 2025-05-26', 'all', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:49:49', '../reports/generated/report_1748260189.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(39, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:52:45', '../reports/generated/report_1748260365.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(40, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:54:46', '../reports/generated/report_1748260486.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(41, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:57:50', '../reports/generated/report_1748260670.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(42, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:58:11', '../reports/generated/report_1748260691.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(43, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 11:58:40', '../reports/generated/report_1748260720.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(44, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 12:06:19', '../reports/generated/report_1748261179.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(45, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 12:06:54', '../reports/generated/report_1748261214.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(46, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 12:12:04', '../reports/generated/report_1748261524.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(47, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 12:14:36', '../reports/generated/report_1748261676.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(48, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 12:15:23', '../reports/generated/report_1748261723.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(49, 'Low Stock Report - 2025-04-26 to 2025-05-26', 'low_stock', '2025-04-26', '2025-05-26', 1, '2025-05-26 12:21:49', '../reports/generated/report_1748262109.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(51, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 12:32:00', '../reports/generated/report_1748262720.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(52, 'Inventory Valuation Report - 2025-04-26 to 2025-05-26', 'inventory_valuation', '2025-04-26', '2025-05-26', 1, '2025-05-26 12:32:16', '../reports/generated/report_1748262736.html', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(62, 'Low Stock Report - 2025-05-19 to 2025-05-26', 'low_stock', '2025-05-19', '2025-05-26', 1, '2025-05-26 18:37:35', '../reports/generated/Low Stock Report - 2025-05-19 to 2025-05-26.html', NULL, '{\"time_period\":\"last_week\",\"start_date\":\"2025-05-19\",\"end_date\":\"2025-05-26\",\"include_charts\":true,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(63, 'Inventory Valuation Report - 2025-05-19 to 2025-05-26', 'inventory_valuation', '2025-05-19', '2025-05-26', 1, '2025-05-26 19:01:57', '../reports/generated/Inventory Valuation Report - 2025-05-19 to 2025-05-26.csv', NULL, '{\"time_period\":\"last_week\",\"start_date\":\"2025-05-19\",\"end_date\":\"2025-05-26\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"excel\"}'),
(64, 'Inventory Valuation Report - 2025-05-19 to 2025-05-26', 'inventory_valuation', '2025-05-19', '2025-05-26', 1, '2025-05-26 19:07:24', '../reports/generated/Inventory Valuation Report - 2025-05-19 to 2025-05-26.html', NULL, '{\"time_period\":\"last_week\",\"start_date\":\"2025-05-19\",\"end_date\":\"2025-05-26\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(65, 'Low Stock Report - 2025-05-19 to 2025-05-26', 'low_stock', '2025-05-19', '2025-05-26', 1, '2025-05-26 19:08:01', '../reports/generated/Low Stock Report - 2025-05-19 to 2025-05-26.html', NULL, '{\"time_period\":\"last_week\",\"start_date\":\"2025-05-19\",\"end_date\":\"2025-05-26\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"pdf\"}'),
(66, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 19:09:22', '../reports/generated/Sales Summary Report - 2025-04-26 to 2025-05-26.csv', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"csv\"}'),
(67, 'Sales Summary Report - 2025-04-26 to 2025-05-26', 'sales_summary', '2025-04-26', '2025-05-26', 1, '2025-05-26 19:09:40', '../reports/generated/Sales Summary Report - 2025-04-26 to 2025-05-26.csv', NULL, '{\"time_period\":\"last_month\",\"start_date\":\"2025-04-26\",\"end_date\":\"2025-05-26\",\"include_charts\":false,\"include_summary\":false,\"export_format\":\"excel\"}');

-- --------------------------------------------------------

--
-- Table structure for table `report_settings`
--

CREATE TABLE `report_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `default_date_range` varchar(50) DEFAULT 'last_7_days',
  `auto_generation` tinyint(1) DEFAULT 0,
  `auto_frequency` varchar(20) DEFAULT 'weekly',
  `default_format` varchar(10) DEFAULT 'PDF',
  `email_delivery` tinyint(1) DEFAULT 0,
  `delivery_emails` text DEFAULT NULL,
  `include_charts` tinyint(1) DEFAULT 1,
  `include_summary` tinyint(1) DEFAULT 1,
  `low_stock_threshold` int(11) DEFAULT 10,
  `expiry_alert_days` int(11) DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_settings`
--

INSERT INTO `report_settings` (`id`, `user_id`, `default_date_range`, `auto_generation`, `auto_frequency`, `default_format`, `email_delivery`, `delivery_emails`, `include_charts`, `include_summary`, `low_stock_threshold`, `expiry_alert_days`, `created_at`, `updated_at`) VALUES
(1, 8, 'last_30_days', 1, '0', 'CSV', 1, '0', 1, 1, 20, 30, '2025-05-29 14:28:33', '2025-05-29 14:29:45');

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
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sale_status` enum('Completed','Pending','Cancelled') NOT NULL DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `transaction_number`, `sale_date`, `subtotal`, `tax_amount`, `total_amount`, `payment_method`, `created_by`, `created_at`, `updated_by`, `updated_at`, `sale_status`) VALUES
(1, 'TXN-1747138818974333', '2025-05-13 14:20:19', 403.00, 40.30, 443.30, 'Cash', 1, '2025-05-13 12:20:19', NULL, '2025-05-13 12:20:19', 'Completed'),
(2, 'PENDING-174773500929', '2025-05-20 11:56:49', 813.00, 16.26, 829.26, 'Cash', 1, '2025-05-20 09:56:49', NULL, '2025-05-22 21:37:32', ''),
(3, 'PENDING-174782755401', '2025-05-21 13:39:14', 591.00, 11.82, 602.82, 'Cash', 1, '2025-05-21 11:39:14', NULL, '2025-05-22 21:36:16', 'Completed'),
(4, 'TXN-1747827641708427', '2025-05-21 13:40:41', 291.00, 5.82, 296.82, 'Cash', 1, '2025-05-21 11:40:41', NULL, '2025-05-21 11:40:41', 'Completed'),
(5, 'TXN-1747833047513584', '2025-05-21 15:10:47', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-21 13:10:47', NULL, '2025-05-21 13:10:47', 'Completed'),
(6, 'TXN-1747919169049', '2025-05-22 15:06:11', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-22 13:06:11', NULL, '2025-05-22 21:36:58', 'Completed'),
(7, 'PND-1747919711586', '2025-05-22 15:15:11', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-22 13:15:11', NULL, '2025-05-22 14:00:32', ''),
(8, 'PND-1747919826262', '2025-05-22 15:17:06', 1579.00, 31.58, 1610.58, 'Cash', 1, '2025-05-22 13:17:06', NULL, '2025-05-22 13:59:26', 'Completed'),
(9, 'TXN-1747919882849149', '2025-05-22 15:18:03', 1357.00, 27.14, 1384.14, 'Cash', 1, '2025-05-22 13:18:03', NULL, '2025-05-22 13:18:03', 'Completed'),
(10, 'TXN-1747949906377', '2025-05-22 23:38:26', 1702.00, 34.04, 1736.04, 'Cash', 1, '2025-05-22 21:38:26', NULL, '2025-05-22 21:39:13', 'Completed'),
(11, 'CNL-1747950149526', '2025-05-22 23:42:29', 468.00, 9.36, 477.36, 'Cash', 1, '2025-05-22 21:42:29', NULL, '2025-05-22 21:43:08', ''),
(12, 'CNL-1747952034309', '2025-05-23 00:13:54', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-22 22:13:54', NULL, '2025-05-22 22:14:04', 'Cancelled'),
(13, 'TXN-1747952167802', '2025-05-23 00:16:08', 1234.00, 24.68, 1258.68, 'Cash', 1, '2025-05-22 22:16:08', NULL, '2025-05-22 23:12:35', 'Completed'),
(14, 'TXN-1747955574192', '2025-05-23 01:12:54', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-22 23:12:54', NULL, '2025-05-22 23:13:41', 'Completed'),
(15, 'TXN-1747956633144', '2025-05-23 01:30:33', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-22 23:30:33', NULL, '2025-05-23 02:27:32', 'Completed'),
(16, 'TXN-1747958523399', '2025-05-23 02:02:03', 45.00, 0.90, 45.90, 'Cash', 1, '2025-05-23 00:02:03', NULL, '2025-05-23 02:23:57', 'Completed'),
(17, 'TXN-1747958584158477', '2025-05-23 02:03:06', 345.00, 6.90, 351.90, 'Cash', 1, '2025-05-23 00:03:06', NULL, '2025-05-23 00:03:06', 'Completed'),
(18, 'TXN-1747967067987492', '2025-05-23 04:24:28', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-23 02:24:28', NULL, '2025-05-23 02:24:28', 'Completed'),
(19, 'TXN-1747967897084', '2025-05-23 04:38:17', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-23 02:38:17', NULL, '2025-05-23 02:38:31', 'Completed'),
(20, 'TXN-1747967988085', '2025-05-23 04:39:48', 123.00, 2.46, 125.46, 'Cash', 1, '2025-05-23 02:39:48', NULL, '2025-05-23 02:40:00', 'Completed');

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

--
-- Dumping data for table `sale_details`
--

INSERT INTO `sale_details` (`id`, `sale_id`, `product_id`, `variant_id`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 1, 41, NULL, 2.00, 123.00, 246.00, '2025-05-13 12:20:19'),
(2, 1, 53, 26, 1.00, 34.00, 34.00, '2025-05-13 12:20:19'),
(3, 1, 33, 20, 1.00, 123.00, 123.00, '2025-05-13 12:20:19'),
(4, 2, 41, NULL, 1.00, 123.00, 123.00, '2025-05-20 09:56:49'),
(5, 2, 34, NULL, 2.00, 345.00, 690.00, '2025-05-20 09:56:49'),
(6, 3, 41, NULL, 2.00, 123.00, 246.00, '2025-05-21 11:39:14'),
(7, 3, 34, NULL, 1.00, 345.00, 345.00, '2025-05-21 11:39:14'),
(8, 4, 41, NULL, 2.00, 123.00, 246.00, '2025-05-21 11:40:41'),
(9, 4, 53, 25, 1.00, 45.00, 45.00, '2025-05-21 11:40:41'),
(10, 5, 41, NULL, 1.00, 123.00, 123.00, '2025-05-21 13:10:47'),
(11, 6, 41, NULL, 1.00, 123.00, 123.00, '2025-05-22 13:06:11'),
(12, 7, 41, NULL, 1.00, 123.00, 123.00, '2025-05-22 13:15:11'),
(13, 8, 34, NULL, 1.00, 345.00, 345.00, '2025-05-22 13:17:06'),
(14, 8, 4, NULL, 1.00, 1234.00, 1234.00, '2025-05-22 13:17:06'),
(15, 9, 41, NULL, 1.00, 123.00, 123.00, '2025-05-22 13:18:03'),
(16, 9, 11, NULL, 1.00, 1234.00, 1234.00, '2025-05-22 13:18:03'),
(17, 10, 41, NULL, 1.00, 123.00, 123.00, '2025-05-22 21:38:26'),
(18, 10, 38, NULL, 1.00, 345.00, 345.00, '2025-05-22 21:38:26'),
(19, 10, 3, NULL, 1.00, 1234.00, 1234.00, '2025-05-22 21:38:26'),
(20, 11, 39, NULL, 1.00, 123.00, 123.00, '2025-05-22 21:42:29'),
(21, 11, 34, NULL, 1.00, 345.00, 345.00, '2025-05-22 21:42:29'),
(22, 12, 42, NULL, 1.00, 123.00, 123.00, '2025-05-22 22:13:54'),
(23, 13, 3, NULL, 1.00, 1234.00, 1234.00, '2025-05-22 22:16:08'),
(24, 14, 39, NULL, 1.00, 123.00, 123.00, '2025-05-22 23:12:54'),
(25, 15, 41, NULL, 1.00, 123.00, 123.00, '2025-05-22 23:30:33'),
(26, 16, 53, 25, 1.00, 45.00, 45.00, '2025-05-23 00:02:03'),
(27, 17, 34, NULL, 1.00, 345.00, 345.00, '2025-05-23 00:03:06'),
(28, 18, 41, NULL, 1.00, 123.00, 123.00, '2025-05-23 02:24:28'),
(29, 19, 41, NULL, 1.00, 123.00, 123.00, '2025-05-23 02:38:17'),
(30, 20, 41, NULL, 1.00, 123.00, 123.00, '2025-05-23 02:39:48');

-- --------------------------------------------------------

--
-- Table structure for table `system_preferences`
--

CREATE TABLE `system_preferences` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_preferences`
--

INSERT INTO `system_preferences` (`id`, `setting_name`, `setting_value`, `updated_at`, `updated_by`) VALUES
(1, 'currency', 'NGN', '2025-05-30 10:34:58', 8),
(2, 'currency_symbol', '₦', '2025-05-30 10:34:58', 8),
(3, 'currency_position', 'before', '2025-05-30 10:34:58', 8),
(4, 'timezone', 'UTC', '2025-05-30 10:34:58', 8),
(5, 'date_format', 'Y-m-d', '2025-05-30 10:34:58', 8),
(6, 'decimal_places', '2', '2025-05-30 10:34:58', 8),
(7, 'thousand_separator', ',', '2025-05-30 10:34:58', 8),
(8, 'decimal_separator', '.', '2025-05-30 10:34:58', 8);

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
  `date_registered` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `email`, `home_address`, `phone`, `password`, `role`, `date_registered`, `updated_at`, `status`) VALUES
(1, 'Gorden', 'Dabie', 'dabiegorden49@gmail.com', '14, olorunda street off bioyin', '07051927036', 'Qqy49XX*', 'sales_rep', '2025-03-04 15:10:22.000000', '2025-05-27 14:37:54.000000', 'active'),
(2, 'Emmanuel', 'Ogu', 'emmanuelogu03@gmail.com', 'Catholic University', '0559380412', '$2y$10$uecGsh45B7I1G1oCHlECDuAd8kC8eSkze.F5m7cACy8APubBaxmdS', 'manager', '2025-03-05 13:48:32.000000', '2025-05-27 16:15:25.000000', 'active'),
(8, 'Abdulbasit', 'Alaka-Yusuf', 'olumide1807@gmail.com', '14, olorunda street off bioyin', '07051927036', '$2y$10$inNVW8Kn16qIIKpRn25Ae.1qNnJb4HnsW7.SB1ec1C8QfNJLFO9qW', 'manager', '2025-05-27 14:43:47.000000', '2025-05-27 16:36:38.000000', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission`, `granted`, `created_at`) VALUES
(13, 2, 'user_management', 1, '2025-05-27 14:36:07'),
(14, 2, 'inventory_management', 1, '2025-05-27 14:36:07'),
(15, 2, 'sales_management', 1, '2025-05-27 14:36:07'),
(16, 2, 'reports_access', 1, '2025-05-27 14:36:07'),
(17, 2, 'system_settings', 1, '2025-05-27 14:36:07'),
(18, 2, 'audit_logs', 1, '2025-05-27 14:36:07'),
(19, 1, 'inventory_view', 1, '2025-05-27 14:37:54'),
(20, 1, 'sales_management', 1, '2025-05-27 14:37:54'),
(23, 8, 'user_management', 1, '2025-05-27 15:45:12'),
(24, 8, 'inventory_management', 1, '2025-05-27 15:45:12'),
(25, 8, 'sales_management', 1, '2025-05-27 15:45:12'),
(26, 8, 'reports_access', 1, '2025-05-27 15:45:12'),
(27, 8, 'system_settings', 1, '2025-05-27 15:45:12'),
(28, 8, 'audit_logs', 1, '2025-05-27 15:45:12');

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
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_generated_by` (`generated_by`),
  ADD KEY `idx_period` (`report_period_start`,`report_period_end`);

--
-- Indexes for table `report_settings`
--
ALTER TABLE `report_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_transaction_number` (`transaction_number`),
  ADD KEY `idx_sale_date` (`sale_date`),
  ADD KEY `fk_sales_user` (`created_by`),
  ADD KEY `fk_sales_update` (`updated_by`);

--
-- Indexes for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_details_sale` (`sale_id`),
  ADD KEY `fk_details_product` (`product_id`),
  ADD KEY `fk_details_variant` (`variant_id`);

--
-- Indexes for table `system_preferences`
--
ALTER TABLE `system_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission`);

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
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `report_settings`
--
ALTER TABLE `report_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `sale_details`
--
ALTER TABLE `sale_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `system_preferences`
--
ALTER TABLE `system_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `report_settings`
--
ALTER TABLE `report_settings`
  ADD CONSTRAINT `report_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_update` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_sales_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD CONSTRAINT `fk_details_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_details_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_details_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `system_preferences`
--
ALTER TABLE `system_preferences`
  ADD CONSTRAINT `system_preferences_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
