#
# TABLE STRUCTURE FOR: bc_fail_check
#

CREATE TABLE `bc_fail_check` (
  `fail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `browser` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `num` int(10) NOT NULL DEFAULT 1,
  `type_id` int(2) NOT NULL,
  PRIMARY KEY (`fail_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_fail_check AUTO_INCREMENT = 1;