#
# TABLE STRUCTURE FOR: bc_visitor
#

CREATE TABLE `bc_visitor` (
  `visitor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `browser` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`visitor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_visitor AUTO_INCREMENT = 1;