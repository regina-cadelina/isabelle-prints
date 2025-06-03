-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2025 at 03:36 AM
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
-- Database: `isabelle_prints`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_options`)),
  `customization_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `is_active`, `created_at`) VALUES
(1, 'Tshirts', 'tshirts', 'Stylish and comfortable t-shirts designed to express your personality, perfect for any occasion.\r\n', 1, '2025-06-01 03:38:55'),
(2, 'Towels', 'towels', 'Soft, absorbent hand towels that combine everyday functionality with elegant style.\r\n', 1, '2025-06-01 03:38:55'),
(3, 'Mugs', 'mugs', 'Durable and stylish mugs perfect for your daily coffee, tea, or custom gift needs.', 1, '2025-06-01 03:38:55'),
(4, 'Tumblers', 'tumblers', 'Sleek, insulated tumblers designed to keep your drinks at the perfect temperature on the go.', 1, '2025-06-01 03:38:55');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `status` enum('pending','processing','delivered','cancelled') DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `downpayment_amount` decimal(10,2) DEFAULT 0.00,
  `payment_proof_file` varchar(255) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `bank_owner_name` varchar(255) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `billing_address` varchar(255) NOT NULL,
  `shipping_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `subtotal`, `shipping_cost`, `total_amount`, `created_at`, `downpayment_amount`, `payment_proof_file`, `reference_number`, `bank_owner_name`, `bank_name`, `billing_address`, `shipping_address`) VALUES
(79, 6, '', 'pending', 0.00, 0.00, 92.37, '2025-06-02 07:42:57', 46.18, 'payment_1748850177_683d5601b28d3.png', 'sdfsdf', 'fsdfsdf', 'sdfsdfsdf', 'fsdfsdf', 'fddfsf'),
(86, 6, 'ORD-20250602-2EC64FC3', 'pending', 79.98, 5.99, 92.37, '2025-06-02 07:50:13', 46.18, 'payment_1748850613_683d57b5b4324.png', 'sdfsdf', 'fsdfsdf', 'sdfsdfsdf', 'fsdfsdf', 'fddfsf'),
(87, 6, 'ORD-20250602-4C18BD35', 'pending', 0.00, 5.99, 5.99, '2025-06-02 07:50:27', 3.00, 'payment_1748850627_683d57c3cd8dc.png', 'sdfsdf', 'fsdfsdf', 'sdfsdfsdf', 'fsdfsdf', 'fddfsf'),
(88, 6, 'ORD-20250602-1D2DD50D', 'pending', 0.00, 5.99, 5.99, '2025-06-02 07:50:31', 3.00, 'payment_1748850631_683d57c76abd0.png', 'sdfsdf', 'fsdfsdf', 'sdfsdfsdf', 'fsdfsdf', 'fddfsf'),
(89, 6, 'ORD-20250602-CAED8DCC', 'pending', 49.99, 5.99, 59.98, '2025-06-02 07:51:10', 29.99, 'payment_1748850670_683d57ee8a37c.png', 'asdsadadassa', 'asdasdasdasd', 'adasddsad', 'asdassad', 'asdasdasd'),
(90, 2, 'ORD-20250602-1C822F2D', 'pending', 59.99, 5.99, 70.78, '2025-06-02 08:20:07', 35.39, 'payment_1748852407_683d5eb76931e.png', '123789', 'Benjo Estrella', 'BDO', '1234567', 'altura'),
(91, 2, 'ORD-20250602-FB49961C', 'pending', 29.99, 5.99, 38.38, '2025-06-02 08:30:40', 19.19, 'payment_1748853040_683d613044c3b.png', '123789', 'Benjo Estrella', 'BDO', '1234567', 'altura');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_options`)),
  `customization_notes` text DEFAULT NULL,
  `file_upload` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `total_price`, `selected_options`, `customization_notes`, `file_upload`) VALUES
(1, 86, 4, 1, 59.99, 59.99, NULL, NULL, NULL),
(2, 86, 2, 1, 19.99, 19.99, NULL, NULL, NULL),
(3, 89, 5, 1, 49.99, 49.99, NULL, NULL, NULL),
(4, 90, 4, 1, 59.99, 59.99, NULL, NULL, NULL),
(5, 91, 8, 1, 29.99, 29.99, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_bestseller` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `is_sale` tinyint(1) DEFAULT 0,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `product_name`, `name`, `slug`, `description`, `short_description`, `base_price`, `sku`, `image_url`, `is_bestseller`, `is_new`, `is_sale`, `sale_price`, `status`, `created_at`, `updated_at`, `stock_quantity`, `is_active`) VALUES
(1, 1, 'dasdas', 'tshirt (1)', 'tshirt (1)', 'etc etc', 'sadas', 24.99, 'dsadas', NULL, 1, 0, 0, NULL, 'active', '2025-06-01 03:38:55', '2025-06-03 01:35:05', 0, 1),
(2, 1, '', 'Tshirt', 'Tshirt', 'etc etc', NULL, 19.99, NULL, NULL, 0, 0, 0, NULL, 'active', '2025-06-01 03:38:55', '2025-06-01 07:08:31', 0, 1),
(3, 3, '', 'mug', 'mug', 'etcc', NULL, 19.99, NULL, NULL, 0, 1, 0, NULL, 'active', '2025-06-01 03:38:55', '2025-06-01 07:09:39', 0, 1),
(4, 2, '', 'hand towel', 'hand towel', 'hhunsdf', NULL, 59.99, NULL, NULL, 0, 0, 0, NULL, 'active', '2025-06-01 03:38:55', '2025-06-01 07:10:01', 0, 1),
(5, 2, '', 'hand towel (1)', 'hand towel (1)', 'fdsfds', NULL, 49.99, NULL, NULL, 0, 0, 0, NULL, 'active', '2025-06-01 03:38:55', '2025-06-01 07:10:24', 0, 1),
(6, 3, '', 'mug (1)', 'mug (1)', 'fdfs', NULL, 29.99, NULL, NULL, 0, 0, 0, NULL, 'active', '2025-06-01 03:38:55', '2025-06-01 07:10:43', 0, 1),
(7, 4, '', 'tumbler', 'tumber', 'asdsa', NULL, 14.99, NULL, NULL, 0, 0, 0, NULL, 'active', '2025-06-01 03:38:55', '2025-06-01 07:11:00', 0, 1),
(8, 4, '', 'asdad', 'asdsad', 'asdasd', NULL, 29.99, NULL, NULL, 0, 0, 1, 29.99, 'active', '2025-06-01 03:38:55', '2025-06-01 07:11:18', 0, 1),
(9, 3, 'hello', '', 'hello', 'oishfkshfkhsadkfhjsfkjlshbcxmvbhfksjfkjhsf', 'shdakjsd', 20000.00, '2345', NULL, 0, 1, 0, NULL, 'active', '2025-06-03 01:28:34', '2025-06-03 01:28:34', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_options`
--

CREATE TABLE `product_options` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `option_type` enum('size','color','finish') NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `option_value` varchar(100) NOT NULL,
  `price_modifier` decimal(10,2) DEFAULT 0.00,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_options`
--

INSERT INTO `product_options` (`id`, `product_id`, `option_type`, `option_name`, `option_value`, `price_modifier`, `is_default`) VALUES
(1, 1, 'size', 'Standard', '3.5\" x 2\"', 0.00, 1),
(2, 1, 'size', 'Square', '2.5\" x 2.5\"', 5.00, 0),
(3, 1, 'size', 'Folded', '3.5\" x 4\"', 10.00, 0),
(4, 1, 'color', 'White', '#FFFFFF', 0.00, 1),
(5, 1, 'color', 'Cream', '#F5F5DC', 2.00, 0),
(6, 1, 'color', 'Gray', '#808080', 2.00, 0),
(7, 1, 'finish', 'Matte', 'matte', 0.00, 1),
(8, 1, 'finish', 'Gloss', 'gloss', 3.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_type` enum('customer','admin','staff') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `user_address` varchar(99) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `google_id`, `password`, `first_name`, `last_name`, `phone`, `address`, `created_at`, `updated_at`, `user_type`, `is_active`, `user_address`) VALUES
(2, 'admin@isabelleprints.com', NULL, '$2y$10$EocjYbN70Z3.7dW0oNPv7ud5xgE.vmVluldbpbeltLmYRELD0HAjq', 'Admin', 'User', NULL, NULL, '2025-06-02 03:50:40', '2025-06-02 06:29:09', 'admin', 1, ''),
(3, 'customer@test.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'Customer', NULL, NULL, '2025-06-02 03:50:40', '2025-06-02 03:50:40', 'customer', 1, ''),
(4, 'reginaangeli.21@gmail.com', NULL, '$2y$10$Iiczn/VbClJPyh4xqDeQzebQWJde6NUxUW5sGhwjOXW5Ie4AfOfQO', 'regina', 'angeli', NULL, NULL, '2025-06-02 04:39:27', '2025-06-02 04:39:27', 'customer', 1, ''),
(5, 'asa@gmail.com', NULL, '$2y$10$EocjYbN70Z3.7dW0oNPv7ud5xgE.vmVluldbpbeltLmYRELD0HAjq', 'juan benjo', 'estrella', NULL, NULL, '2025-06-02 05:31:43', '2025-06-02 05:31:43', 'customer', 1, ''),
(6, 'amybalion533@gmail.com', NULL, '$2y$10$7SMkZJmL/ho1cXuK.QgY3ubgsRdzwXMIa1AwATI0KgreW1.kR68oK', 'fsdfsdf', 'fsdfsaf', NULL, NULL, '2025-06-02 07:19:14', '2025-06-02 07:19:14', 'customer', 1, NULL),
(7, 'elen.estrella2020@gmail.com', '117390083436592258764', '$2y$10$GZ5wreooVF5ticzbdee0xei20A259t2nqAd/VgIuD8HrnrGgP2bXG', 'Elenita', 'Estrella', NULL, NULL, '2025-06-02 08:38:52', '2025-06-02 08:43:59', 'customer', 1, NULL),
(8, 'juan_benjo_estrella@dlsl.edu.ph', '100458441179828845559', '$2y$10$GZ5wreooVF5ticzbdee0xei20A259t2nqAd/VgIuD8HrnrGgP2bXG', 'JUAN BENJO', 'ESTRELLA', NULL, NULL, '2025-06-02 08:39:46', '2025-06-02 08:42:20', 'customer', 1, NULL),
(9, 'benjoestrella13@gmail.com', '113310552141018603236', '$2y$10$GZ5wreooVF5ticzbdee0xei20A259t2nqAd/VgIuD8HrnrGgP2bXG', 'Benjo', 'Estrella', NULL, NULL, '2025-06-02 08:42:09', '2025-06-02 08:42:09', 'customer', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_options`
--
ALTER TABLE `product_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_options`
--
ALTER TABLE `product_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_options`
--
ALTER TABLE `product_options`
  ADD CONSTRAINT `product_options_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
