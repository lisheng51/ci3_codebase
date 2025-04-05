#
# TABLE STRUCTURE FOR: bc_config
#

CREATE TABLE `bc_config` (
  `c_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `c_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`c_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_config AUTO_INCREMENT = 1;