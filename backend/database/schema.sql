-- Schema Definition for Chat App (CPK 2, 3, 4, 5, 6)
-- Engine: InnoDB, Charset: utf8mb4

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `device_tokens`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `users`;

-- 1. Users table (FR-20: Unpredictable ULID/UUID v4, no AUTO_INCREMENT)
CREATE TABLE `users` (
  `id` CHAR(26) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `photo_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Contacts table (FR-06, FR-07: owner contacts another registered user)
CREATE TABLE `contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` CHAR(26) NOT NULL,
  `contact_user_id` CHAR(26) NOT NULL,
  `alias_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contacts_owner_contact_unique` (`owner_id`, `contact_user_id`),
  KEY `idx_contacts_owner` (`owner_id`),
  KEY `idx_contacts_contact_user` (`contact_user_id`),
  CONSTRAINT `fk_contacts_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contacts_user` FOREIGN KEY (`contact_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Messages table (FR-08, FR-09, FR-10, FR-12)
CREATE TABLE `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` CHAR(26) NOT NULL,
  `receiver_id` CHAR(26) NOT NULL,
  `body` TEXT DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_messages_sender_receiver_created` (`sender_id`, `receiver_id`, `created_at`),
  KEY `idx_messages_receiver_created` (`receiver_id`, `created_at`),
  CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Device Tokens table for FCM Push Notifications (FR-29)
CREATE TABLE `device_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` CHAR(26) NOT NULL,
  `fcm_token` VARCHAR(500) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_device_tokens_user` (`user_id`),
  UNIQUE KEY `idx_device_token_unique` (`user_id`, `fcm_token`(191)),
  CONSTRAINT `fk_device_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
