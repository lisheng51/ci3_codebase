#
# TABLE STRUCTURE FOR: bc_user
#

CREATE TABLE `bc_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `display_info` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emailaddress` varchar(90) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `createdby` int(10) unsigned NOT NULL,
  `modifiedby` int(10) unsigned DEFAULT NULL,
  `permission_group_ids` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_del` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `nav_bookmark` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `emailaddress` (`emailaddress`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_user AUTO_INCREMENT = 1;