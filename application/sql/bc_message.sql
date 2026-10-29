#
# TABLE STRUCTURE FOR: bc_message
#

CREATE TABLE `bc_message` (
  `message_id` int unsigned NOT NULL AUTO_INCREMENT,
  `from_user_id` int NOT NULL DEFAULT '0',
  `to_user_id` int unsigned NOT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_open` int NOT NULL DEFAULT '0',
  `open_at` datetime DEFAULT NULL,
  `is_del` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_message AUTO_INCREMENT = 1;