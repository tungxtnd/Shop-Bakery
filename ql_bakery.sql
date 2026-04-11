-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.45 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table ql_bakery.cart_items
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `service_id` int DEFAULT NULL,
  `card_message` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.cart_items: ~5 rows (approximately)
INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `service_id`, `card_message`) VALUES
	(1, 1, 1, 2, 1, NULL),
	(2, 2, 3, 1, NULL, NULL),
	(3, 1, 5, 1, 2, NULL),
	(7, 4, 1, 1, NULL, ''),
	(8, 4, 1, 1, NULL, '');

-- Dumping structure for table ql_bakery.collections
CREATE TABLE IF NOT EXISTS `collections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.collections: ~6 rows (approximately)
INSERT INTO `collections` (`id`, `name`, `description`) VALUES
	(1, 'Cookies & Snacks', 'Delightful bite-sized treats and crunchy cookies, perfect for everyday snacking or sharing'),
	(2, 'Chilled Cakes & Desserts', 'Cool, creamy, and refreshing desserts including mousses and tiramisu for a sweet escape.'),
	(3, 'Traditional Cake', 'Classic, time-honored cake recipes baked to perfection, bringing back the comforting taste of home.'),
	(4, 'Birthday Cake', 'Beautifully crafted and delicious signature cakes to make every birthday celebration unforgettable.'),
	(5, 'Pastries Cake', 'Flaky, buttery, and freshly baked French pastries that pair perfectly with your morning coffee.'),
	(6, 'Drinks', 'Refreshing beverages, signature coffees, and fine teas crafted to perfectly complement your sweet treats.');

-- Dumping structure for table ql_bakery.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `target_user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `type` enum('login','logout','order_status','review','admin_message','shipper_assignment','shipper_update') NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notif_user` (`user_id`),
  KEY `fk_notif_target` (`target_user_id`),
  KEY `fk_notif_product` (`product_id`),
  KEY `fk_notif_order` (`order_id`),
  CONSTRAINT `fk_notif_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notif_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notif_target` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ql_bakery.notifications: ~103 rows (approximately)
INSERT INTO `notifications` (`id`, `user_id`, `target_user_id`, `product_id`, `order_id`, `type`, `message`, `created_at`) VALUES
	(1, 4, 4, NULL, 3, 'order_status', 'Your order #3 has been placed and is pending confirmation.', '2026-03-15 03:29:24'),
	(2, 4, 4, NULL, 4, 'order_status', 'Your order #4 has been placed and is pending confirmation.', '2026-03-15 03:33:21'),
	(3, 4, 4, NULL, 5, 'order_status', 'Your order #5 has been placed and is pending confirmation.', '2026-03-15 03:36:11'),
	(4, 4, 4, NULL, 6, 'order_status', 'Your order #6 has been placed and is pending confirmation.', '2026-03-15 04:18:13'),
	(5, 4, 4, NULL, 7, 'order_status', 'Your order #7 has been placed and is pending confirmation.', '2026-03-15 04:22:34'),
	(6, 4, 4, NULL, 8, 'order_status', 'Your order #8 has been placed and is pending confirmation.', '2026-03-15 15:20:29'),
	(7, 4, 4, NULL, 9, 'order_status', 'Your order #9 has been placed and is pending confirmation.', '2026-03-16 03:19:53'),
	(8, 4, 4, NULL, NULL, 'logout', 'You have logged out.', '2026-03-16 03:24:00'),
	(9, 5, 5, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-16 04:35:06'),
	(10, 4, 4, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-20 14:45:42'),
	(11, 4, 4, NULL, NULL, 'logout', 'You have logged out.', '2026-03-20 14:48:09'),
	(12, 4, 4, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-20 15:01:36'),
	(13, 4, 4, NULL, NULL, 'logout', 'You have logged out.', '2026-03-20 15:03:06'),
	(14, 4, 4, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-20 15:04:25'),
	(16, 4, 4, NULL, 11, 'order_status', 'Your order #11 has been placed and is pending confirmation.', '2026-03-20 15:05:16'),
	(17, 4, 4, NULL, 12, 'order_status', 'Your order #12 has been placed and is pending confirmation.', '2026-03-20 15:07:04'),
	(18, 4, 4, NULL, 13, 'order_status', 'Your order #13 has been placed and is pending confirmation.', '2026-03-20 15:07:16'),
	(19, 4, 4, NULL, 14, 'order_status', 'Your order #14 has been placed and is pending confirmation.', '2026-03-20 15:07:32'),
	(20, 4, 4, NULL, NULL, 'logout', 'You have logged out.', '2026-03-20 15:07:42'),
	(21, 5, 5, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-20 15:07:51'),
	(22, 5, 5, NULL, 15, 'order_status', 'Your order #15 has been placed and is pending confirmation.', '2026-03-20 15:07:58'),
	(23, 5, 5, NULL, NULL, 'logout', 'You have logged out.', '2026-03-20 15:08:10'),
	(24, 4, 4, NULL, 16, 'order_status', 'Your order #16 has been placed and is pending confirmation.', '2026-03-20 15:08:33'),
	(25, 4, 4, NULL, NULL, 'logout', 'You have logged out.', '2026-03-20 15:09:03'),
	(30, 5, 5, NULL, 19, 'order_status', 'Your order #19 has been placed and is pending confirmation.', '2026-03-20 15:11:21'),
	(34, 7, 7, NULL, 22, 'order_status', 'Your order #22 has been placed and is pending confirmation.', '2026-03-22 09:35:52'),
	(35, 7, 7, NULL, 23, 'order_status', 'Your order #23 has been placed and is pending confirmation.', '2026-03-22 10:01:57'),
	(36, 7, 7, NULL, 24, 'order_status', 'Your order #24 has been placed and is pending confirmation.', '2026-03-22 10:03:13'),
	(37, 7, 7, NULL, 25, 'order_status', 'Your order #25 has been placed and is pending confirmation.', '2026-03-22 10:08:10'),
	(38, 7, 7, NULL, 26, 'order_status', 'Your order #26 has been placed and is pending confirmation.', '2026-03-22 10:09:48'),
	(39, 8, 8, NULL, 27, 'order_status', 'Your order #27 has been placed and is pending confirmation.', '2026-03-23 04:48:02'),
	(40, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-26 15:09:26'),
	(41, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-03-26 16:02:57'),
	(42, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-26 16:03:36'),
	(43, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-03-26 16:03:39'),
	(44, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-26 16:03:55'),
	(45, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-03-26 16:05:14'),
	(46, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-26 16:05:31'),
	(47, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-03-26 16:05:34'),
	(48, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-03-26 16:05:44'),
	(49, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-03-26 16:05:47'),
	(50, 9, 9, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 10:04:26'),
	(51, 9, 9, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 10:12:44'),
	(52, 9, 9, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 10:12:54'),
	(53, 9, 9, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 10:13:14'),
	(54, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 10:13:33'),
	(55, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 10:19:21'),
	(56, 9, 9, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 10:19:27'),
	(57, 9, 9, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 10:41:44'),
	(58, 9, 9, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 10:42:59'),
	(59, 9, 9, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 11:28:59'),
	(60, 9, 9, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 14:00:14'),
	(61, 9, 9, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 14:09:02'),
	(62, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 14:09:57'),
	(63, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 14:10:48'),
	(64, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 14:11:08'),
	(65, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 14:46:17'),
	(66, 9, 9, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 14:46:25'),
	(67, 9, 9, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 14:50:37'),
	(68, 4, 4, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 15:08:41'),
	(69, 4, 4, NULL, 28, 'order_status', 'Your order #28 has been placed and is pending confirmation.', '2026-04-06 15:08:56'),
	(70, 4, 4, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 15:09:31'),
	(71, 4, 4, NULL, 29, 'order_status', 'Your order #29 has been placed and is pending confirmation.', '2026-04-06 15:09:44'),
	(72, 4, 4, NULL, 30, 'order_status', 'Your order #30 has been placed and is pending confirmation.', '2026-04-06 15:11:04'),
	(73, 4, 4, NULL, 31, 'order_status', 'Your order #31 has been placed and is pending confirmation.', '2026-04-06 15:21:40'),
	(74, 10, 10, NULL, 32, 'order_status', 'Your order #32 has been placed and is pending confirmation.', '2026-04-06 15:23:32'),
	(75, 10, 10, NULL, NULL, 'logout', 'You have logged out.', '2026-04-06 15:26:46'),
	(76, 4, 4, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-06 15:45:39'),
	(77, 4, 4, NULL, 33, 'order_status', 'Your order #33 has been placed and is pending confirmation.', '2026-04-06 15:46:35'),
	(78, 4, 4, NULL, 34, 'order_status', 'Your order #34 has been placed and is pending confirmation.', '2026-04-06 15:48:22'),
	(79, 4, 4, NULL, 35, 'order_status', 'Your order #35 has been placed and is pending confirmation.', '2026-04-06 15:54:03'),
	(80, 4, 4, NULL, 36, 'order_status', 'Your order #36 has been placed and is pending confirmation.', '2026-04-06 16:00:38'),
	(81, 4, 4, NULL, 37, 'order_status', 'Your order #37 has been placed and is pending confirmation.', '2026-04-06 16:03:05'),
	(82, 4, 4, NULL, 38, 'order_status', 'Your order #38 has been placed and is pending confirmation.', '2026-04-06 16:07:03'),
	(83, 4, 4, NULL, 39, 'order_status', 'Your order #39 has been placed and is pending confirmation.', '2026-04-06 16:09:50'),
	(84, 4, 4, NULL, 40, 'order_status', 'Your order #40 has been placed and is pending confirmation.', '2026-04-06 16:11:17'),
	(85, 4, 4, NULL, 41, 'order_status', 'Your order #41 has been placed and is pending confirmation.', '2026-04-06 16:13:37'),
	(86, 4, 4, NULL, 42, 'order_status', 'Đơn hàng #42 của bạn đã được đặt thành công.', '2026-04-06 16:19:41'),
	(87, 4, 4, NULL, 43, 'order_status', 'Your order #43 has been placed and is pending confirmation.', '2026-04-06 16:21:10'),
	(88, 4, 4, NULL, 44, 'order_status', 'Your order #44 has been placed and is pending confirmation.', '2026-04-06 16:21:29'),
	(89, 4, 4, NULL, 45, 'order_status', 'Your order #45 has been placed and is pending confirmation.', '2026-04-06 16:34:51'),
	(90, 7, 7, NULL, 46, 'order_status', 'Your order #46 has been placed and is pending confirmation.', '2026-04-06 16:36:28'),
	(91, 7, 7, NULL, 47, 'order_status', 'Your order #47 has been placed and is pending confirmation.', '2026-04-06 16:40:43'),
	(92, 7, 7, NULL, 48, 'order_status', 'Your order #48 has been placed and is pending confirmation.', '2026-04-06 16:41:41'),
	(93, 4, 4, NULL, 49, 'order_status', 'Your order #49 has been placed and is pending confirmation.', '2026-04-06 16:43:12'),
	(94, 4, 4, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-08 14:41:15'),
	(95, 4, 4, NULL, 50, 'order_status', 'Your order #50 has been placed and is pending confirmation.', '2026-04-08 14:42:31'),
	(96, 4, 4, NULL, NULL, 'logout', 'You have logged out.', '2026-04-08 15:23:46'),
	(97, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-08 15:24:50'),
	(98, 4, 4, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-11 08:22:27'),
	(99, 4, 4, NULL, NULL, 'logout', 'You have logged out.', '2026-04-11 08:22:44'),
	(100, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-11 08:22:55'),
	(101, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-04-11 08:23:02'),
	(102, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-11 08:25:06'),
	(103, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-04-11 08:25:26'),
	(104, 9, 9, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-11 08:25:33'),
	(105, 9, 9, NULL, NULL, 'logout', 'You have logged out.', '2026-04-11 08:25:59'),
	(106, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-11 08:46:08'),
	(107, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-04-11 08:59:29'),
	(108, 3, 3, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-11 09:00:48'),
	(109, 3, 3, NULL, NULL, 'logout', 'You have logged out.', '2026-04-11 09:09:34'),
	(110, 9, 9, NULL, NULL, 'login', 'You have logged in successfully.', '2026-04-11 09:09:41'),
	(111, 9, 9, NULL, NULL, 'logout', 'You have logged out.', '2026-04-11 09:09:47');

-- Dumping structure for table ql_bakery.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Paid','Processing','Ready for Delivery','Delivering','Completed','Cancelled') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.orders: ~46 rows (approximately)
INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `order_date`, `status`) VALUES
	(1, 1, 920000.00, '2025-11-01 13:00:21', 'Pending'),
	(2, 2, 390000.00, '2025-11-01 13:00:21', 'Delivering'),
	(3, 4, 460000.00, '2026-03-15 03:29:24', 'Pending'),
	(4, 4, 460000.00, '2026-03-15 03:33:21', 'Pending'),
	(5, 4, 460000.00, '2026-03-15 03:36:11', 'Pending'),
	(6, 4, 460000.00, '2026-03-15 04:18:13', 'Pending'),
	(7, 4, 480000.00, '2026-03-15 04:22:34', 'Pending'),
	(8, 4, 480000.00, '2026-03-15 15:20:29', 'Pending'),
	(9, 4, 550000.00, '2026-03-16 03:19:53', 'Pending'),
	(10, 4, 480000.00, '2026-03-20 15:04:45', 'Pending'),
	(11, 4, 480000.00, '2026-03-20 15:05:16', 'Pending'),
	(12, 4, 550000.00, '2026-03-20 15:07:04', 'Pending'),
	(13, 4, 550000.00, '2026-03-20 15:07:16', 'Pending'),
	(14, 4, 420000.00, '2026-03-20 15:07:32', 'Pending'),
	(15, 5, 550000.00, '2026-03-20 15:07:58', 'Pending'),
	(16, 4, 420000.00, '2026-03-20 15:08:33', 'Pending'),
	(19, 5, 550000.00, '2026-03-20 15:11:21', 'Pending'),
	(22, 7, 550000.00, '2026-03-22 09:35:52', 'Pending'),
	(23, 7, 420000.00, '2026-03-22 10:01:57', 'Pending'),
	(24, 7, 420000.00, '2026-03-22 10:03:13', 'Pending'),
	(25, 7, 550000.00, '2026-03-22 10:08:10', 'Pending'),
	(26, 7, 630000.00, '2026-03-22 10:09:48', 'Pending'),
	(27, 8, 460000.00, '2026-03-23 04:48:02', 'Pending'),
	(28, 4, 480000.00, '2026-04-06 15:08:56', 'Pending'),
	(29, 4, 0.00, '2026-04-06 15:09:44', 'Pending'),
	(30, 4, 550000.00, '2026-04-06 15:11:04', 'Pending'),
	(31, 4, 90000.00, '2026-04-06 15:21:40', 'Pending'),
	(32, 10, 390000.00, '2026-04-06 15:23:32', 'Pending'),
	(33, 4, 420000.00, '2026-04-06 15:46:35', 'Pending'),
	(34, 4, 420000.00, '2026-04-06 15:48:22', 'Pending'),
	(35, 4, 420000.00, '2026-04-06 15:54:03', 'Pending'),
	(36, 4, 420000.00, '2026-04-06 16:00:38', 'Pending'),
	(37, 4, 420000.00, '2026-04-06 16:03:05', 'Pending'),
	(38, 4, 420000.00, '2026-04-06 16:07:03', 'Pending'),
	(39, 4, 420000.00, '2026-04-06 16:09:50', 'Pending'),
	(40, 4, 420000.00, '2026-04-06 16:11:17', 'Pending'),
	(41, 4, 420000.00, '2026-04-06 16:13:37', 'Pending'),
	(42, 4, 420000.00, '2026-04-06 16:19:41', 'Pending'),
	(43, 4, 420000.00, '2026-04-06 16:21:10', 'Pending'),
	(44, 4, 500000.00, '2026-04-06 16:21:29', 'Pending'),
	(45, 4, 500000.00, '2026-04-06 16:34:51', 'Pending'),
	(46, 7, 500000.00, '2026-04-06 16:36:28', 'Pending'),
	(47, 7, 500000.00, '2026-04-06 16:40:43', 'Pending'),
	(48, 7, 630000.00, '2026-04-06 16:41:41', 'Pending'),
	(49, 4, 650000.00, '2026-04-06 16:43:12', 'Pending'),
	(50, 4, 590000.00, '2026-04-08 14:42:31', 'Processing');

-- Dumping structure for table ql_bakery.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `service_id` int DEFAULT NULL,
  `card_message` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.order_items: ~46 rows (approximately)
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `service_id`, `card_message`) VALUES
	(1, 1, 1, 2, 450000.00, 1, NULL),
	(2, 1, 5, 1, 430000.00, 2, NULL),
	(3, 2, 3, 1, 390000.00, NULL, NULL),
	(4, 4, 5, 1, 430000.00, NULL, ''),
	(5, 5, 5, 1, 430000.00, NULL, ''),
	(6, 6, 5, 1, 430000.00, NULL, ''),
	(7, 7, 1, 1, 450000.00, NULL, ''),
	(8, 8, 1, 1, 450000.00, NULL, ''),
	(9, 9, 2, 1, 520000.00, NULL, ''),
	(10, 10, 1, 1, 450000.00, NULL, ''),
	(11, 11, 1, 1, 450000.00, NULL, ''),
	(12, 12, 2, 1, 520000.00, NULL, ''),
	(13, 13, 2, 1, 520000.00, NULL, ''),
	(14, 14, 3, 1, 390000.00, NULL, ''),
	(15, 15, 2, 1, 520000.00, NULL, ''),
	(16, 16, 3, 1, 390000.00, NULL, ''),
	(19, 19, 2, 1, 520000.00, NULL, ''),
	(22, 22, 2, 1, 520000.00, NULL, ''),
	(23, 23, 3, 1, 390000.00, NULL, ''),
	(24, 24, 3, 1, 390000.00, NULL, ''),
	(25, 25, 2, 1, 520000.00, NULL, ''),
	(26, 26, 6, 1, 600000.00, NULL, ''),
	(27, 27, 5, 1, 430000.00, NULL, ''),
	(28, 28, 1, 1, 450000.00, NULL, ''),
	(29, 29, 1, 1, 450000.00, NULL, ''),
	(30, 30, 2, 1, 520000.00, NULL, ''),
	(31, 31, 18, 1, 60000.00, NULL, ''),
	(32, 32, 8, 1, 360000.00, NULL, ''),
	(33, 33, 3, 1, 390000.00, NULL, ''),
	(34, 34, 3, 1, 390000.00, NULL, ''),
	(35, 35, 3, 1, 390000.00, NULL, ''),
	(36, 36, 3, 1, 390000.00, NULL, ''),
	(37, 37, 3, 1, 390000.00, NULL, ''),
	(38, 38, 3, 1, 390000.00, NULL, ''),
	(39, 39, 3, 1, 390000.00, NULL, ''),
	(40, 40, 3, 1, 390000.00, NULL, ''),
	(41, 41, 3, 1, 390000.00, NULL, ''),
	(42, 42, 3, 1, 390000.00, NULL, ''),
	(43, 43, 3, 1, 390000.00, NULL, ''),
	(44, 44, 4, 1, 470000.00, NULL, ''),
	(45, 45, 4, 1, 470000.00, NULL, ''),
	(46, 46, 4, 1, 470000.00, NULL, ''),
	(47, 47, 4, 1, 470000.00, NULL, ''),
	(48, 48, 6, 1, 600000.00, NULL, ''),
	(49, 49, 11, 1, 620000.00, NULL, ''),
	(50, 50, 12, 1, 560000.00, NULL, '');

-- Dumping structure for table ql_bakery.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `collection_id` int DEFAULT NULL,
  `stock` int DEFAULT '0',
  `status` enum('in_stock','out_of_stock') COLLATE utf8mb4_general_ci DEFAULT 'in_stock',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `edit_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `collection_id` (`collection_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.products: ~22 rows (approximately)
INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `collection_id`, `stock`, `status`, `created_at`, `edit_at`) VALUES
	(1, 'Cheesecake', 'No-bake cheesecake with cream cheese & soy, salted egg yolk sauce, lemon sauce, gluten-free Mirliton bread.', 450000.00, 'opla-no-bake-cheesecake-234811.webp', 2, 2, 'in_stock', '2025-11-01 13:00:21', NULL),
	(2, 'Raspberry Tiramisu', 'Ladyfinger biscuits infused with raspberries, organic Da Lat strawberries, light tiramisu cream, whipped cream, fresh fruit, rose and berry jelly.', 520000.00, 'RaspberryTiramisu15cm.webp', 4, 0, 'in_stock', '2025-11-01 13:00:21', NULL),
	(3, 'Truffle Cream Cheese Roll', 'The bread is soft and fluffy, filled with truffle cream cheese, black truffle salsa, and black olives.', 390000.00, 'TRUFFLE_BUN_11.webp', 1, -3, 'in_stock', '2025-11-01 13:00:21', NULL),
	(4, 'Fresh Buttercream Cake', 'Fresh avocado mousse, avocado sauce, soft roll cake, egg custard cream, caramelized cashews & roasted cocoa beans, chocolate cherries, passion fruit caramel.', 470000.00, 'BIRTHDAYREBIRTH9020_2.webp', 3, 2, 'in_stock', '2025-11-01 13:00:21', NULL),
	(5, 'Mandarin Cake & Earl Grey Tea', 'The soft, moist tangerine butter cake base is combined with slow-cooked tangerine jam made from peeled and hand-separated tangerine segments, light Earl Grey tea cream, and a refreshing tangerine jelly topping. When you take a bite, you\'ll experience a distinct shift in flavor: soft – moist – smooth; fresh – refreshing – slightly bitter, then mellow.', 430000.00, 'Mandarincake15cm_01_11.webp', 4, 0, 'in_stock', '2025-11-01 13:00:21', NULL),
	(6, 'Orchid Grace', 'Graceful orchids in a delicate arrangement.', 600000.00, 'cannele-203299.webp', 2, 5, 'in_stock', '2025-11-01 13:00:21', NULL),
	(7, 'Peony Bloom', 'Fragrant peonies in full bloom.', 580000.00, 'cheese-tart-511220.webp', 2, 9, 'in_stock', '2025-11-01 13:00:21', NULL),
	(8, 'Gerbera Fun Pack', 'Cheerful gerbera daisies in bold colors.', 360000.00, 'chocolate-cannele-994447.webp', 1, 9, 'in_stock', '2025-11-01 13:00:21', NULL),
	(9, 'Lavender Peace', 'Soothing lavender bunch for peace and calm.', 490000.00, 'chocolate-hazelnut-kiss-468145.webp', 5, 6, 'in_stock', '2025-11-01 13:00:21', NULL),
	(10, 'Daisy Freshness', 'Fresh and charming daisies.', 350000.00, 'CINNAMONCOFFEEBUN_11.webp', 1, 8, 'in_stock', '2025-11-01 13:00:21', NULL),
	(11, 'Hydrangea Bloom Box', 'Pastel hydrangeas in a decorative box.', 620000.00, 'Copy_of_Bakes04021324.webp', 3, 3, 'in_stock', '2025-11-01 13:00:21', NULL),
	(12, 'Romantic Mixed Roses', 'A mix of red, pink and white roses.', 560000.00, 'Copyof_AB_8696.webp', 2, 6, 'in_stock', '2025-11-01 13:00:21', NULL),
	(13, 'Yellow Tulip Joy', 'Yellow tulips representing cheerfulness.', 410000.00, 'salted-duck-egg-curd-croissant-402524.webp', 1, 5, 'in_stock', '2025-11-01 13:00:21', NULL),
	(14, 'Anthurium Exotic Charm', 'Exotic red anthuriums in a sleek arrangement.', 530000.00, 'tiramisu-lava-723383.webp', 5, 3, 'in_stock', '2025-11-01 13:00:21', NULL),
	(15, 'Blue Rose Rarity', 'Rare and stunning blue roses for a unique touch.', 750000.00, 'MATCHA_TIRAMISU_12cm.webp', 3, 2, 'in_stock', '2025-11-01 13:00:21', NULL),
	(16, 'Espresso Coffee', 'Two shots of iced Arabica coffee', 60000.00, 'ESPRESSO_COLD.webp', 6, 10, 'in_stock', '2026-03-14 16:12:38', NULL),
	(17, 'Americano Coffee', 'Two shots of Arabica coffee (Ethiopian x Brazilian beans), water', 60000.00, 'AMERICANO_COLD_561e98f3-0f2c-4651-b84c-408f9b210646.webp', 6, 2, 'in_stock', '2026-03-14 16:15:33', NULL),
	(18, 'Latte Coffee', 'Two shots of Arabica coffee (Ethiopian and Brazilian beans), fresh milk', 60000.00, 'LATTE_COLD.webp', 6, 4, 'in_stock', '2026-03-14 16:16:53', NULL),
	(19, 'Cappuccino Coffee', 'Two shots of Arabica coffee (Ethiopian and Brazilian beans), fresh milk, and a layer of milk foam.', 60000.00, 'CAPUCCINO_ICED.webp', 6, 4, 'in_stock', '2026-03-14 16:18:55', NULL),
	(20, 'Milk Coffee', 'Two shots of robusta, condensed milk, ice', 60000.00, 'CAFE_S_A_COLD.webp', 6, 7, 'in_stock', '2026-03-14 16:20:35', NULL),
	(21, 'Brewing Coffee - Ethiopia Guji Shikaso', 'The bright citrus and lemon flavor, slightly resembling light tea, with a lingering tart and refreshing aftertaste.', 75000.00, 'BakesBrewingCoffee.webp', 6, 5, 'in_stock', '2026-03-14 16:21:33', NULL),
	(22, 'Fresh Orange Americano', 'Espresso shot, fresh orange juice.', 83000.00, 'FRESH_ORANGE_AMERICANO.webp', 6, 4, 'in_stock', '2026-03-14 16:33:05', '2026-04-06 14:22:35');

-- Dumping structure for table ql_bakery.product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ql_bakery.product_images: ~33 rows (approximately)
INSERT INTO `product_images` (`id`, `product_id`, `image_name`) VALUES
	(1, 3, 'TRUFFLE_BUN_02_45.webp'),
	(3, 1, 'opla-no-bake-cheesecake-294235.webp'),
	(4, 1, 'opla-no-bake-cheesecake-762556.webp'),
	(5, 2, 'RASPBERRYTIRAMISU_4.webp'),
	(6, 2, 'RASPBERRYTIRAMISU_3.webp'),
	(7, 4, 'fresh-avocado-mousse-254674.webp'),
	(8, 4, 'fresh-avocado-mousse-885762.webp'),
	(9, 5, 'Mandarincake15cm_02_11.webp'),
	(10, 5, 'Mandarincake15cm_03_11.webp'),
	(11, 5, 'Mandarincakegroup_11.webp'),
	(12, 5, 'Mandarincake12cm_03_11.webp'),
	(13, 6, 'cannele-237879.webp'),
	(14, 7, 'cheese-tart-486498.webp'),
	(15, 8, 'chocolate-cannele-447682.webp'),
	(16, 8, 'chocolate-cannele-487976.webp'),
	(17, 9, 'chocolate-hazelnut-kiss-809667.webp'),
	(18, 10, 'CINNAMONCOFFEEBUN_02_45.webp'),
	(19, 11, 'Copy_of_Bakes04021323.webp'),
	(20, 11, 'Copy_of_Bakes04021326.webp'),
	(21, 12, 'Copyof_AB_8706.webp'),
	(22, 13, 'salted-duck-egg-curd-croissant-800621.webp'),
	(23, 14, 'tiramisu-lava-154243.webp'),
	(24, 15, 'MATCHA_TIRA_GROUP.webp'),
	(25, 15, 'MATCHA_TIRA_01.webp'),
	(26, 15, 'MATCHA_TIRA_02.webp'),
	(27, 15, 'MATCHA_TIRA_05.webp'),
	(28, 16, 'ESPRESSO_HOT.webp'),
	(29, 17, 'AMERICANO_HOT_85b1a0de-5b06-4744-96ae-5428398e2595.webp'),
	(30, 18, 'LATTE_HOT.webp'),
	(31, 20, 'CAFE_S_A_HOT.webp'),
	(32, 21, 'AB_1351_1.webp'),
	(33, 21, 'AB_1353_1.webp'),
	(34, 22, 'fresh-orange-americano-761583.webp');

-- Dumping structure for table ql_bakery.reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `rating` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.reviews: ~3 rows (approximately)
INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
	(1, 1, 1, 5, 'Absolutely beautiful roses!', '2025-11-01 13:00:21'),
	(2, 2, 3, 4, 'Sunflowers were nice but arrived a bit late.', '2025-11-01 13:00:21'),
	(3, 1, 5, 5, 'Lovely carnations, my mom loved them.', '2025-11-01 13:00:21');

-- Dumping structure for table ql_bakery.review_images
CREATE TABLE IF NOT EXISTS `review_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `review_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `review_id` (`review_id`),
  CONSTRAINT `review_images_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.review_images: ~4 rows (approximately)
INSERT INTO `review_images` (`id`, `review_id`, `image_path`, `created_at`) VALUES
	(1, 1, 'review1_img1.png', '2025-11-01 13:00:21'),
	(2, 1, 'review1_img2.png', '2025-11-01 13:00:21'),
	(3, 2, 'review2_img1.png', '2025-11-01 13:00:21'),
	(4, 3, 'review3_img1.png', '2025-11-01 13:00:21');

-- Dumping structure for table ql_bakery.services
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.services: ~3 rows (approximately)
INSERT INTO `services` (`id`, `name`, `price`, `image`, `description`) VALUES
	(1, 'Love Greeting Card', 20000.00, 'card1.png', 'A romantic greeting card for your loved one.'),
	(2, 'Birthday Card', 15000.00, 'card2.png', 'Colorful card for birthday wishes.'),
	(3, 'Sympathy Card', 18000.00, 'card3.png', 'A thoughtful message for condolences.');

-- Dumping structure for table ql_bakery.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `role` enum('customer','admin','staff') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'customer',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.users: ~10 rows (approximately)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `role`) VALUES
	(1, 'Alice Nguyen', 'alice@example.com', 'hashed_pw1', '0901234567', '123 Hanoi Street', 'customer'),
	(2, 'Bob Tran', 'bob@example.com', 'hashed_pw2', '0912345678', '456 HCMC Street', 'customer'),
	(3, 'Admin Bakery', 'admin@bakery.com', '$2y$10$6ibghMf.73sebY4RsR5e0O8J0ySRklEvXrGe1NQjsWzyGAnH.ePxy', '0987654321', 'Admin Office', 'admin'),
	(4, 'tung', 'tung@gmail.com', '$2y$10$5KxUBujm.zjd7.iUiIORMOOAlF/r0EhuUslg1bnTuLyJIxhsRsMQ6', '039887183', 'adfa', 'customer'),
	(5, 'linh', 'linh@gmail.com', '123', '0865786558', 'Ha Noi', 'customer'),
	(7, 'tung', 'tungxtnd2004@gmail.com', '12345', '0865786558', 'Hà Nội', 'customer'),
	(8, 'linh', 'linh16@gmail.com', '12345', '0865786558', 'Hà Nội', 'customer'),
	(9, 'staff', 'staff@gmail.com', '$2y$10$pAq76rcOOkZNdiK9zG431.3sHk3TuFWHo6XpRfsIiY50U4geXM75S', '0899876523', NULL, 'staff'),
	(10, 'linh nguyen', 'linh2@gmail.com', '12345', '0865786558', 'HaNoi', 'customer'),
	(11, 'linh', 'linh3@gmail.com', '$2y$10$9ZTdyfyGmqRX7TMOEmtMk.Re36GyDJrUa9W7hwziLsWv8t10jOScO', '0865786559', 'Hà Nội', 'customer');

-- Dumping structure for table ql_bakery.work_schedules
CREATE TABLE IF NOT EXISTS `work_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `work_date` date NOT NULL,
  `shift_type` enum('Morning (08:00-12:00)','Afternoon (12:00-17:00)','Evening (17:00-22:00)') NOT NULL,
  `status` enum('Registered','Approved') DEFAULT 'Registered',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `work_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ql_bakery.work_schedules: ~1 rows (approximately)
INSERT INTO `work_schedules` (`id`, `user_id`, `work_date`, `shift_type`, `status`, `created_at`) VALUES
	(1, 9, '2026-04-07', 'Morning (08:00-12:00)', 'Registered', '2026-04-06 17:43:23');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
