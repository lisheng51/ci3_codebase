#
# TABLE STRUCTURE FOR: bc_api_log
#

CREATE TABLE `bc_api_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_id` int(10) unsigned NOT NULL DEFAULT 0,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `browser` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_value` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `get_value` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_value` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `out_value` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_api_log AUTO_INCREMENT = 1;