ALTER TABLE `users`
  MODIFY `role` enum('student','admin') COLLATE utf8mb4_general_ci DEFAULT 'student';

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
