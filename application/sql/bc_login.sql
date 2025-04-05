#
# TABLE STRUCTURE FOR: bc_login
#

CREATE TABLE `bc_login` (
  `user_id` int(10) unsigned NOT NULL,
  `username` varchar(90) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_date` datetime DEFAULT NULL,
  `with_access_code` tinyint(1) NOT NULL DEFAULT 0,
  `access_code` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_code_date` datetime DEFAULT NULL,
  `password_reset_date` datetime DEFAULT NULL,
  `redirect_url` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `with_2fa` tinyint(1) NOT NULL DEFAULT 0,
  `2fa_secret` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_login AUTO_INCREMENT = 1;