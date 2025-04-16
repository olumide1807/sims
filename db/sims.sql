-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 03:28 PM
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
(1, 'Ibruprofen', 'Skincare', 'Emzor', 'B123456', '2025-03-09', '2025-03-27', 'hgfudsyjhfnsbjkhgajwqajhaebhwv', 100.00, 50.00, 20.00, 10, 20, 10, 10, '2025-04-16 12:50:29'),
(2, 'Paracetamol', 'Skincare', 'Emzor', 'B123456', '2025-03-09', '2025-03-27', 'hgfudsyjhfnsbjkhgajwqajhaebhwv', 100.00, 50.00, 20.00, 10, 20, 10, 10, '2025-04-15 20:17:16'),
(3, 'Paracetamol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 122, 1234, 12, 1234, '2025-04-15 10:33:33'),
(4, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:41:54'),
(5, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:42:09'),
(6, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:42:42'),
(7, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:42:53'),
(8, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 08:56:26'),
(9, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-04-15 19:31:50'),
(10, 'Paracetamol', 'easy', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-03-08 10:26:20'),
(11, 'Paracetamol', 'Another one', 'Emzor', 'B123456', '2025-03-08', '2025-03-28', 'jkm hjrkm,anewjk ', 12345.00, 123.00, 1234.00, 123, 1234, 12, 1234, '2025-04-15 19:51:22'),
(12, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:30:57'),
(13, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:31:17'),
(14, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:33:01'),
(15, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:33:26'),
(16, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:34:00'),
(17, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-04-15 19:56:30'),
(18, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-04-15 12:30:17'),
(19, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:40:50'),
(20, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:43:06'),
(21, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:45:53'),
(22, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:48:45'),
(23, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:53:32'),
(24, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:57:38'),
(25, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:58:35'),
(26, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 10:58:58'),
(27, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 11:13:05'),
(28, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 11:13:46'),
(29, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 11:15:20'),
(30, 'Panadol', 'Come on', 'Emzor', 'B123456', '2025-03-08', '2025-03-31', 'fesgrdtvjhtdcbtesfdygftvgbhjkltgium', 100.00, 50.00, 20.00, 10, 5, 40, 20, '2025-03-08 11:15:45'),
(31, 'Panadol', 'Skincare', 'Emzor', 'B123456', '2025-03-15', '2025-03-31', 'gfedhfdgfrgbjghjgbtfhgvdrst', 500.00, 550.00, 25.00, 10, 50, 12345, 1233, '2025-03-08 11:22:02'),
(32, 'Panadol', 'Skincare', 'Emzor', 'B123456', '2025-03-15', '2025-03-31', 'gfedhfdgfrgbjghjgbtfhgvdrst', 500.00, 550.00, 25.00, 10, 50, 12345, 1233, '2025-03-08 11:24:29'),
(33, 'Ibruprofen', 'Another one', 'Emzor', 'B654321', '2025-03-13', '2025-03-31', 'This is a pain releive ', 100.00, 200.00, 50.00, 12, 5, 10, 19, '2025-03-13 12:25:11');

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
(1, 17, '1000g', 0.00, 0.00, 0, 100, '2025-04-16 12:35:01'),
(2, 18, '22', 0.00, 0.00, 0, 0, '2025-04-16 11:20:45'),
(3, 19, '800ml', 0.00, 0.00, 0, 0, '2025-04-15 20:54:14'),
(4, 20, '', 0.00, 0.00, 0, 0, '2025-03-08 10:43:06'),
(5, 21, '', 0.00, 0.00, 0, 0, '2025-03-08 10:45:53'),
(6, 22, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 10:48:45'),
(7, 23, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 10:53:32'),
(8, 24, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 10:57:38'),
(10, 26, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 10:58:58'),
(11, 27, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 11:13:05'),
(12, 28, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 11:13:46'),
(13, 29, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 11:15:20'),
(14, 30, '500ml', 0.00, 0.00, 0, 0, '2025-03-08 11:15:45'),
(15, 1, '200g', 5.00, 2.50, 100, 200, '2025-04-16 13:24:01'),
(16, 1, '400g', 123.00, 2345.00, 20, 30, '2025-04-16 13:24:35');

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
(1, 'Gorden', 'Dabie', 'dabiegorden49@gmail.com', '14, olorunda street off bioyin', '07051927036', 'Qqy49XX*', '', '2025-03-04 15:10:22.000000'),
(2, 'Emmanuel', 'Ogu', 'emmanuelogu03@gmail.com', 'Catholic University', '0559380412', 'yy1S=3L0', '', '2025-03-05 13:48:32.000000');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
