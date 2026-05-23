SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('student','admin') COLLATE utf8mb4_general_ci DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `users`
  MODIFY `role` enum('student','admin') COLLATE utf8mb4_general_ci DEFAULT 'student';

CREATE TABLE IF NOT EXISTS `categories` (
  `cat_id` int NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `seller_id` int NOT NULL,
  `cat_id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `image_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('available','sold') COLLATE utf8mb4_general_ci DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item_id`),
  KEY `seller_id` (`seller_id`),
  KEY `cat_id` (`cat_id`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `items_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `inquiries` (
  `msg_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`msg_id`),
  KEY `item_id` (`item_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `inquiries_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `inquiries_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `buyer_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `item_id` (`item_id`),
  KEY `buyer_id` (`buyer_id`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `categories` (`cat_id`, `cat_name`) VALUES
  (1, 'Books'),
  (2, 'Electronics'),
  (3, 'Stationery'),
  (4, 'Engineering Tools'),
  (5, 'Furniture'),
  (6, 'Lab Gear'),
  (7, 'Calculators'),
  (8, 'Laptops'),
  (9, 'Sports'),
  (10, 'Bicycles');

-- Demo password for both seeded accounts: password
INSERT INTO `users` (`user_id`, `name`, `email`, `student_id`, `password_hash`, `role`, `created_at`) VALUES
  (11, 'YIC Admin', 'admin@yic.edu.sa', 'ADMIN001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC6OYxub4H.0AB6K6Y6G', 'admin', '2026-05-01 18:33:18'),
  (13, 'Demo Student', 'student@yic.edu.sa', '441000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC6OYxub4H.0AB6K6Y6G', 'student', '2026-05-16 13:32:16')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `student_id` = VALUES(`student_id`),
  `password_hash` = VALUES(`password_hash`),
  `role` = VALUES(`role`);

INSERT IGNORE INTO `items` (`item_id`, `seller_id`, `cat_id`, `title`, `description`, `price`, `quantity`, `image_path`, `status`, `created_at`) VALUES
  (12, 13, 1, 'Engineering Textbook', 'Used textbook in good condition.', 75.00, 1, NULL, 'available', '2026-05-15 20:02:08'),
  (15, 13, 2, 'Scientific Calculator', 'Calculator suitable for YIC engineering courses.', 60.00, 2, NULL, 'available', '2026-05-16 14:26:33'),
  (16, 13, 3, 'Stationery Set', 'Notebook, pens, and drawing tools.', 35.00, 5, NULL, 'available', '2026-05-16 15:32:47');

SET FOREIGN_KEY_CHECKS = 1;
