CREATE TABLE IF NOT EXISTS `reservation` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `unique_reservation` (`user_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;