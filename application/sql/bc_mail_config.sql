#
# TABLE STRUCTURE FOR: bc_mail_config
#

CREATE TABLE `bc_mail_config` (
  `mail_config_id` int(10) NOT NULL AUTO_INCREMENT,
  `host` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pass` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `crypto` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `createdby` int(10) unsigned NOT NULL,
  `modifiedby` int(10) unsigned NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `refresh_token` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`mail_config_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_mail_config AUTO_INCREMENT = 1;