#
# TABLE STRUCTURE FOR: bc_casino_user
# Players of the casino site pages, they log in with Google. Chips are virtual.
#

CREATE TABLE `bc_casino_user` (
  `casino_user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `google_sub` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `picture` varchar(500) NOT NULL DEFAULT '',
  `balance` bigint(20) NOT NULL DEFAULT 0,
  `total_bet` bigint(20) unsigned NOT NULL DEFAULT 0,
  `total_win` bigint(20) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `bonus_at` datetime DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`casino_user_id`),
  UNIQUE KEY `google_sub` (`google_sub`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_casino_user AUTO_INCREMENT = 1;
