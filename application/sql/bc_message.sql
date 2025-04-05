#
# TABLE STRUCTURE FOR: bc_message
#

CREATE TABLE `bc_message` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_user_id` int(10) NOT NULL DEFAULT 0,
  `to_user_id` int(10) unsigned NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `is_open` int(1) NOT NULL DEFAULT 0,
  `open_at` datetime DEFAULT NULL,
  `is_del` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_message AUTO_INCREMENT = 1;