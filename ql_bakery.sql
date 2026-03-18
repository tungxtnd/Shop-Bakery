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


-- Dumping database structure for ql_bakery
CREATE DATABASE IF NOT EXISTS `ql_bakery` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `ql_bakery`;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.cart_items: ~3 rows (approximately)
INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `service_id`, `card_message`) VALUES
	(1, 1, 1, 2, 1, NULL),
	(2, 2, 3, 1, NULL, NULL),
	(3, 1, 5, 1, 2, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table ql_bakery.notifications: ~0 rows (approximately)

-- Dumping structure for table ql_bakery.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','shipped','delivered') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.orders: ~2 rows (approximately)
INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `order_date`, `status`) VALUES
	(1, 1, 920000.00, '2025-11-01 13:00:21', 'pending'),
	(2, 2, 390000.00, '2025-11-01 13:00:21', 'shipped');

-- Dumping structure for table ql_bakery.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `service_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.order_items: ~3 rows (approximately)
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `service_id`) VALUES
	(1, 1, 1, 2, 450000.00, 1),
	(2, 1, 5, 1, 430000.00, 2),
	(3, 2, 3, 1, 390000.00, NULL);

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
  PRIMARY KEY (`id`),
  KEY `collection_id` (`collection_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.products: ~22 rows (approximately)
INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `collection_id`, `stock`, `status`, `created_at`) VALUES
	(1, 'Cheesecake', 'No-bake cheesecake with cream cheese & soy, salted egg yolk sauce, lemon sauce, gluten-free Mirliton bread.', 450000.00, 'opla-no-bake-cheesecake-234811.webp', 2, 10, 'in_stock', '2025-11-01 13:00:21'),
	(2, 'Raspberry Tiramisu', 'Ladyfinger biscuits infused with raspberries, organic Da Lat strawberries, light tiramisu cream, whipped cream, fresh fruit, rose and berry jelly.', 520000.00, 'RaspberryTiramisu15cm.webp', 4, 8, 'in_stock', '2025-11-01 13:00:21'),
	(3, 'Truffle Cream Cheese Roll', 'The bread is soft and fluffy, filled with truffle cream cheese, black truffle salsa, and black olives.', 390000.00, 'TRUFFLE_BUN_11.webp', 1, 12, 'in_stock', '2025-11-01 13:00:21'),
	(4, 'Fresh Buttercream Cake', 'Fresh avocado mousse, avocado sauce, soft roll cake, egg custard cream, caramelized cashews & roasted cocoa beans, chocolate cherries, passion fruit caramel.', 470000.00, 'BIRTHDAYREBIRTH9020_2.webp', 3, 6, 'in_stock', '2025-11-01 13:00:21'),
	(5, 'Mandarin Cake & Earl Grey Tea', 'The soft, moist tangerine butter cake base is combined with slow-cooked tangerine jam made from peeled and hand-separated tangerine segments, light Earl Grey tea cream, and a refreshing tangerine jelly topping. When you take a bite, you\'ll experience a distinct shift in flavor: soft – moist – smooth; fresh – refreshing – slightly bitter, then mellow.', 430000.00, 'Mandarincake15cm_01_11.webp', 4, 5, 'in_stock', '2025-11-01 13:00:21'),
	(6, 'Orchid Grace', 'Graceful orchids in a delicate arrangement.', 600000.00, 'cannele-203299.webp', 2, 7, 'in_stock', '2025-11-01 13:00:21'),
	(7, 'Peony Bloom', 'Fragrant peonies in full bloom.', 580000.00, 'cheese-tart-511220.webp', 2, 9, 'in_stock', '2025-11-01 13:00:21'),
	(8, 'Gerbera Fun Pack', 'Cheerful gerbera daisies in bold colors.', 360000.00, 'chocolate-cannele-994447.webp', 1, 10, 'in_stock', '2025-11-01 13:00:21'),
	(9, 'Lavender Peace', 'Soothing lavender bunch for peace and calm.', 490000.00, 'chocolate-hazelnut-kiss-468145.webp', 5, 6, 'in_stock', '2025-11-01 13:00:21'),
	(10, 'Daisy Freshness', 'Fresh and charming daisies.', 350000.00, 'CINNAMONCOFFEEBUN_11.webp', 1, 8, 'in_stock', '2025-11-01 13:00:21'),
	(11, 'Hydrangea Bloom Box', 'Pastel hydrangeas in a decorative box.', 620000.00, 'Copy_of_Bakes04021324.webp', 3, 4, 'in_stock', '2025-11-01 13:00:21'),
	(12, 'Romantic Mixed Roses', 'A mix of red, pink and white roses.', 560000.00, 'Copyof_AB_8696.webp', 2, 7, 'in_stock', '2025-11-01 13:00:21'),
	(13, 'Yellow Tulip Joy', 'Yellow tulips representing cheerfulness.', 410000.00, 'salted-duck-egg-curd-croissant-402524.webp', 1, 5, 'in_stock', '2025-11-01 13:00:21'),
	(14, 'Anthurium Exotic Charm', 'Exotic red anthuriums in a sleek arrangement.', 530000.00, 'tiramisu-lava-723383.webp', 5, 3, 'in_stock', '2025-11-01 13:00:21'),
	(15, 'Blue Rose Rarity', 'Rare and stunning blue roses for a unique touch.', 750000.00, 'MATCHA_TIRAMISU_12cm.webp', 3, 2, 'in_stock', '2025-11-01 13:00:21'),
	(16, 'Espresso Coffee', 'Two shots of iced Arabica coffee', 60000.00, 'ESPRESSO_COLD.webp', 6, 10, 'in_stock', '2026-03-14 16:12:38'),
	(17, 'Americano Coffee', 'Two shots of Arabica coffee (Ethiopian x Brazilian beans), water', 60000.00, 'AMERICANO_COLD_561e98f3-0f2c-4651-b84c-408f9b210646.webp', 6, 3, 'in_stock', '2026-03-14 16:15:33'),
	(18, 'Latte Coffee', 'Two shots of Arabica coffee (Ethiopian and Brazilian beans), fresh milk', 60000.00, 'LATTE_COLD.webp', 6, 5, 'in_stock', '2026-03-14 16:16:53'),
	(19, 'Cappuccino Coffee', 'Two shots of Arabica coffee (Ethiopian and Brazilian beans), fresh milk, and a layer of milk foam.', 60000.00, 'CAPUCCINO_ICED.webp', 6, 4, 'in_stock', '2026-03-14 16:18:55'),
	(20, 'Milk Coffee', 'Two shots of robusta, condensed milk, ice', 60000.00, 'CAFE_S_A_COLD.webp', 6, 7, 'in_stock', '2026-03-14 16:20:35'),
	(21, 'Brewing Coffee - Ethiopia Guji Shikaso', 'The bright citrus and lemon flavor, slightly resembling light tea, with a lingering tart and refreshing aftertaste.', 75000.00, 'BakesBrewingCoffee.webp', 6, 5, 'in_stock', '2026-03-14 16:21:33'),
	(22, 'Fresh Orange Americano', 'Espresso shot, fresh orange juice.', 83000.00, 'FRESH_ORANGE_AMERICANO.webp', 6, 2, 'in_stock', '2026-03-14 16:33:05');

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
  `role` enum('admin','customer') COLLATE utf8mb4_general_ci DEFAULT 'customer',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table ql_bakery.users: ~3 rows (approximately)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `role`) VALUES
	(1, 'Alice Nguyen', 'alice@example.com', 'hashed_pw1', '0901234567', '123 Hanoi Street', 'customer'),
	(2, 'Bob Tran', 'bob@example.com', 'hashed_pw2', '0912345678', '456 HCMC Street', 'customer'),
	(3, 'Admin Flower', 'admin@flower.com', 'hashed_pw3', '0987654321', 'Admin Office', 'admin');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
