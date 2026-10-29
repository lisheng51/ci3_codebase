#
# TABLE STRUCTURE FOR: bc_fail_check
#

CREATE TABLE `bc_fail_check` (
  `fail_id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `browser` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `num` int NOT NULL DEFAULT '1',
  `type_id` int NOT NULL,
  PRIMARY KEY (`fail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_fail_check AUTO_INCREMENT = 1;