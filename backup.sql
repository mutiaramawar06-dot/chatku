-- MariaDB / MySQL Database Dump for Simple Chat App (CPK 2, 3, 4, 5, 6)
-- Generated: 2026-11-17 09:59:50
-- Host: 127.0.0.1   Database: chat-app
-- ------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7ME6HD33PK9GBD5J41JEX8P', 'Alice Test', '081283892076', '$2y$12$UrbWCf1DZgEIQpl8tsFRneTmHqY/hhxvnJ2np.Xuv1zSXPY1lnnWy', NULL, '2026-11-17 16:42:51', '2026-11-17 16:42:51');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7ME6HPTSZ8Q4J079A44P2Z3', 'Bob Test', '081371598177', '$2y$12$V7BB73jDXo0Unn6tww4IfeY0wXWQ5.Ca96JCAUR4mlSROwsldW5TO', NULL, '2026-11-17 16:42:51', '2026-11-17 16:42:51');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MEMVA7F4V3N1S554RECRRX', 'Alice Test', '081210657982', '$2y$12$yPfBhaEBjcSJu1QCq48ZIeWQWzRN9.Ejuys6f1S1b89P/UUJmIO/u', NULL, '2026-11-17 16:50:40', '2026-11-17 16:50:40');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MEMVM01JVHP351X92J75TY', 'Bob Test', '081382505321', '$2y$12$fHMkvmZAJtlyecpM614R9efI9HN83dVs3Gg4OW6bdEFa/VO5/16pS', NULL, '2026-11-17 16:50:40', '2026-11-17 16:50:40');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MEQFMB64P4484HWW52AZC2', 'Concurrent Racer', '087732830601', '$2y$12$avP867e9lFp0EwhB7kUshObS96M91zR.f1NI/lenKB8AgcR/jpWwe', NULL, '2026-11-17 16:52:06', '2026-11-17 16:52:06');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDV4XHW8KWE7SXA0FDQ', 'Benchmark Partner A', '08999990001', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark Main User', '08999990000', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDWA57656AAPZZX696N', 'Benchmark Partner B', '08999990002', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDWT99G4DDA66KX0MX7', 'Benchmark Partner C', '08999990003', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDX6RAY6R5BFMY67TXN', 'Benchmark Partner E', '08999990005', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDXZ3X6ND5N4EAW6TW9', 'Benchmark Partner D', '08999990004', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDY2AHZVAQSGRKMJ8ZW', 'Benchmark Partner F', '08999990006', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDY6ZC0GTEYGY4QFEJE', 'Benchmark Partner G', '08999990007', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDZF9RJ9F5VBNN5Z21P', 'Benchmark Partner H', '08999990008', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHDZSHG3NX1T56W28N2Y', 'Benchmark Partner I', '08999990009', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE08QWWYNX805KV3MAM', 'Benchmark Partner J', '08999990010', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE1E26J3TNYNHNPT58E', 'Benchmark Partner K', '08999990011', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE1VAG6A04PZ3N2FYZ8', 'Benchmark Partner L', '08999990012', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE26CJA3PCJ5D48JKFY', 'Benchmark Partner N', '08999990014', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE2GB8KS2C4BB8ARJGX', 'Benchmark Partner O', '08999990015', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE2Z3RC2XCD27FAH7XH', 'Benchmark Partner M', '08999990013', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE3GZK30Q2Q4CV3F5FG', 'Benchmark Partner Q', '08999990017', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE3SYTTTSPCMRA3RQ62', 'Benchmark Partner P', '08999990016', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE4B5DHK8A7FJSVWMST', 'Benchmark Partner S', '08999990019', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE4Q6BE69MHN0NC1P94', 'Benchmark Partner T', '08999990020', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `users` (`id`, `name`, `phone`, `password_hash`, `photo_path`, `created_at`, `updated_at`) VALUES ('01M7MESHE4QQ50QH09MH3JVGNB', 'Benchmark Partner R', '08999990018', 'hash', NULL, '2026-11-17 16:53:13', '2026-11-17 16:53:13');
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` char(26) NOT NULL,
  `contact_user_id` char(26) NOT NULL,
  `alias_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `contacts_owner_contact_unique` (`owner_id`,`contact_user_id`),
  KEY `idx_contacts_owner` (`owner_id`),
  KEY `idx_contacts_contact_user` (`contact_user_id`),
  CONSTRAINT `fk_contacts_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contacts_user` FOREIGN KEY (`contact_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('3', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDV4XHW8KWE7SXA0FDQ', 'Alias Benchmark Partner A', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('4', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDWA57656AAPZZX696N', 'Alias Benchmark Partner B', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('5', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDWT99G4DDA66KX0MX7', 'Alias Benchmark Partner C', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('6', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDXZ3X6ND5N4EAW6TW9', 'Alias Benchmark Partner D', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('7', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDX6RAY6R5BFMY67TXN', 'Alias Benchmark Partner E', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('8', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDY2AHZVAQSGRKMJ8ZW', 'Alias Benchmark Partner F', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('9', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDY6ZC0GTEYGY4QFEJE', 'Alias Benchmark Partner G', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('10', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDZF9RJ9F5VBNN5Z21P', 'Alias Benchmark Partner H', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('11', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDZSHG3NX1T56W28N2Y', 'Alias Benchmark Partner I', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('12', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE08QWWYNX805KV3MAM', 'Alias Benchmark Partner J', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('13', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE1E26J3TNYNHNPT58E', 'Alias Benchmark Partner K', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('14', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE1VAG6A04PZ3N2FYZ8', 'Alias Benchmark Partner L', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('15', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE2Z3RC2XCD27FAH7XH', 'Alias Benchmark Partner M', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('16', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE26CJA3PCJ5D48JKFY', 'Alias Benchmark Partner N', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('17', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE2GB8KS2C4BB8ARJGX', 'Alias Benchmark Partner O', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('18', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE3SYTTTSPCMRA3RQ62', 'Alias Benchmark Partner P', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('19', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE3GZK30Q2Q4CV3F5FG', 'Alias Benchmark Partner Q', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('20', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE4QQ50QH09MH3JVGNB', 'Alias Benchmark Partner R', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('21', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE4B5DHK8A7FJSVWMST', 'Alias Benchmark Partner S', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
INSERT INTO `contacts` (`id`, `owner_id`, `contact_user_id`, `alias_name`, `created_at`, `updated_at`) VALUES ('22', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE4Q6BE69MHN0NC1P94', 'Alias Benchmark Partner T', '2026-11-17 16:53:13', '2026-11-17 16:53:13');
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` char(26) NOT NULL,
  `receiver_id` char(26) NOT NULL,
  `body` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_messages_sender_receiver_created` (`sender_id`,`receiver_id`,`created_at`),
  KEY `idx_messages_receiver_created` (`receiver_id`,`created_at`),
  CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10005 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('1', '01M7ME6HD33PK9GBD5J41JEX8P', '01M7ME6HPTSZ8Q4J079A44P2Z3', 'Hello Bob! How are you doing?', NULL, '2026-11-17 16:42:52', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('2', '01M7ME6HPTSZ8Q4J079A44P2Z3', '01M7ME6HD33PK9GBD5J41JEX8P', 'Hi Alice! Doing great, thanks!', NULL, '2026-11-17 16:42:53', '2026-11-17 16:42:53');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('3', '01M7MEMVA7F4V3N1S554RECRRX', '01M7MEMVM01JVHP351X92J75TY', 'Hello Bob! How are you doing?', NULL, '2026-11-17 16:50:41', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('4', '01M7MEMVM01JVHP351X92J75TY', '01M7MEMVA7F4V3N1S554RECRRX', 'Hi Alice! Doing great, thanks!', NULL, '2026-11-17 16:50:41', '2026-11-17 16:50:41');
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('5', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDV4XHW8KWE7SXA0FDQ', 'Benchmark message payload #0 with some sample conversational content.', NULL, '2026-11-16 06:06:33', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('6', '01M7MESHDWA57656AAPZZX696N', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #1 with some sample conversational content.', NULL, '2026-11-16 06:06:43', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('7', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDWT99G4DDA66KX0MX7', 'Benchmark message payload #2 with some sample conversational content.', NULL, '2026-11-16 06:06:53', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('8', '01M7MESHDXZ3X6ND5N4EAW6TW9', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #3 with some sample conversational content.', NULL, '2026-11-16 06:07:03', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('9', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDX6RAY6R5BFMY67TXN', 'Benchmark message payload #4 with some sample conversational content.', NULL, '2026-11-16 06:07:13', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('10', '01M7MESHDY2AHZVAQSGRKMJ8ZW', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #5 with some sample conversational content.', NULL, '2026-11-16 06:07:23', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('11', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDY6ZC0GTEYGY4QFEJE', 'Benchmark message payload #6 with some sample conversational content.', NULL, '2026-11-16 06:07:33', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('12', '01M7MESHDZF9RJ9F5VBNN5Z21P', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #7 with some sample conversational content.', NULL, '2026-11-16 06:07:43', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('13', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDZSHG3NX1T56W28N2Y', 'Benchmark message payload #8 with some sample conversational content.', NULL, '2026-11-16 06:07:53', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('14', '01M7MESHE08QWWYNX805KV3MAM', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #9 with some sample conversational content.', NULL, '2026-11-16 06:08:03', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('15', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE1E26J3TNYNHNPT58E', 'Benchmark message payload #10 with some sample conversational content.', NULL, '2026-11-16 06:08:13', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('16', '01M7MESHE1VAG6A04PZ3N2FYZ8', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #11 with some sample conversational content.', NULL, '2026-11-16 06:08:23', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('17', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE2Z3RC2XCD27FAH7XH', 'Benchmark message payload #12 with some sample conversational content.', NULL, '2026-11-16 06:08:33', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('18', '01M7MESHE26CJA3PCJ5D48JKFY', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #13 with some sample conversational content.', NULL, '2026-11-16 06:08:43', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('19', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE2GB8KS2C4BB8ARJGX', 'Benchmark message payload #14 with some sample conversational content.', NULL, '2026-11-16 06:08:53', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('20', '01M7MESHE3SYTTTSPCMRA3RQ62', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #15 with some sample conversational content.', NULL, '2026-11-16 06:09:03', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('21', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE3GZK30Q2Q4CV3F5FG', 'Benchmark message payload #16 with some sample conversational content.', NULL, '2026-11-16 06:09:13', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('22', '01M7MESHE4QQ50QH09MH3JVGNB', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #17 with some sample conversational content.', NULL, '2026-11-16 06:09:23', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('23', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE4B5DHK8A7FJSVWMST', 'Benchmark message payload #18 with some sample conversational content.', NULL, '2026-11-16 06:09:33', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('24', '01M7MESHE4Q6BE69MHN0NC1P94', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #19 with some sample conversational content.', NULL, '2026-11-16 06:09:43', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('25', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDV4XHW8KWE7SXA0FDQ', 'Benchmark message payload #20 with some sample conversational content.', NULL, '2026-11-16 06:09:53', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('26', '01M7MESHDWA57656AAPZZX696N', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #21 with some sample conversational content.', NULL, '2026-11-16 06:10:03', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('27', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDWT99G4DDA66KX0MX7', 'Benchmark message payload #22 with some sample conversational content.', NULL, '2026-11-16 06:10:13', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('28', '01M7MESHDXZ3X6ND5N4EAW6TW9', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #23 with some sample conversational content.', NULL, '2026-11-16 06:10:23', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('29', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDX6RAY6R5BFMY67TXN', 'Benchmark message payload #24 with some sample conversational content.', NULL, '2026-11-16 06:10:33', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('30', '01M7MESHDY2AHZVAQSGRKMJ8ZW', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #25 with some sample conversational content.', NULL, '2026-11-16 06:10:43', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('31', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDY6ZC0GTEYGY4QFEJE', 'Benchmark message payload #26 with some sample conversational content.', NULL, '2026-11-16 06:10:53', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('32', '01M7MESHDZF9RJ9F5VBNN5Z21P', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #27 with some sample conversational content.', NULL, '2026-11-16 06:11:03', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('33', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDZSHG3NX1T56W28N2Y', 'Benchmark message payload #28 with some sample conversational content.', NULL, '2026-11-16 06:11:13', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('34', '01M7MESHE08QWWYNX805KV3MAM', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #29 with some sample conversational content.', NULL, '2026-11-16 06:11:23', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('35', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE1E26J3TNYNHNPT58E', 'Benchmark message payload #30 with some sample conversational content.', NULL, '2026-11-16 06:11:33', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('36', '01M7MESHE1VAG6A04PZ3N2FYZ8', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #31 with some sample conversational content.', NULL, '2026-11-16 06:11:43', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('37', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE2Z3RC2XCD27FAH7XH', 'Benchmark message payload #32 with some sample conversational content.', NULL, '2026-11-16 06:11:53', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('38', '01M7MESHE26CJA3PCJ5D48JKFY', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #33 with some sample conversational content.', NULL, '2026-11-16 06:12:03', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('39', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE2GB8KS2C4BB8ARJGX', 'Benchmark message payload #34 with some sample conversational content.', NULL, '2026-11-16 06:12:13', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('40', '01M7MESHE3SYTTTSPCMRA3RQ62', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #35 with some sample conversational content.', NULL, '2026-11-16 06:12:23', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('41', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE3GZK30Q2Q4CV3F5FG', 'Benchmark message payload #36 with some sample conversational content.', NULL, '2026-11-16 06:12:33', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('42', '01M7MESHE4QQ50QH09MH3JVGNB', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #37 with some sample conversational content.', NULL, '2026-11-16 06:12:43', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('43', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHE4B5DHK8A7FJSVWMST', 'Benchmark message payload #38 with some sample conversational content.', NULL, '2026-11-16 06:12:53', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('44', '01M7MESHE4Q6BE69MHN0NC1P94', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #39 with some sample conversational content.', NULL, '2026-11-16 06:13:03', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('45', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDV4XHW8KWE7SXA0FDQ', 'Benchmark message payload #40 with some sample conversational content.', NULL, '2026-11-16 06:13:13', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('46', '01M7MESHDWA57656AAPZZX696N', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #41 with some sample conversational content.', NULL, '2026-11-16 06:13:23', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('47', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDWT99G4DDA66KX0MX7', 'Benchmark message payload #42 with some sample conversational content.', NULL, '2026-11-16 06:13:33', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('48', '01M7MESHDXZ3X6ND5N4EAW6TW9', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #43 with some sample conversational content.', NULL, '2026-11-16 06:13:43', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('49', '01M7MESHDVGJPDGYBB4ZHX0MF8', '01M7MESHDX6RAY6R5BFMY67TXN', 'Benchmark message payload #44 with some sample conversational content.', NULL, '2026-11-16 06:13:53', NULL);
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `image_path`, `created_at`, `read_at`) VALUES ('50', '01M7MESHDY2AHZVAQSGRKMJ8ZW', '01M7MESHDVGJPDGYBB4ZHX0MF8', 'Benchmark message payload #45 with some sample conversational content.', NULL, '2026-11-16 06:14:03', NULL);
UNLOCK TABLES;

--
-- Table structure for table `device_tokens`
--

DROP TABLE IF EXISTS `device_tokens`;
CREATE TABLE `device_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(26) NOT NULL,
  `fcm_token` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_device_token_unique` (`user_id`,`fcm_token`(191)),
  KEY `idx_device_tokens_user` (`user_id`),
  CONSTRAINT `fk_device_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
