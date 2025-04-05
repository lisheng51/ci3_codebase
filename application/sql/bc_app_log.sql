#
# TABLE STRUCTURE FOR: bc_app_log
#

CREATE TABLE `bc_app_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM AUTO_INCREMENT=804 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_app_log AUTO_INCREMENT = 1;