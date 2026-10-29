#
# TABLE STRUCTURE FOR: bc_api_log
#

CREATE TABLE `bc_api_log` (
  `log_id` int unsigned NOT NULL AUTO_INCREMENT,
  `msg` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_id` int unsigned NOT NULL DEFAULT '0',
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `path` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `browser` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `get_value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `header_value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `out_value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_api_log AUTO_INCREMENT = 1;