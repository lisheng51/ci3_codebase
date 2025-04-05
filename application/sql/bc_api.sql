#
# TABLE STRUCTURE FOR: bc_api
#

CREATE TABLE `bc_api` (
  `api_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `secret` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max` smallint(5) NOT NULL DEFAULT 0,
  `token_min` smallint(4) unsigned NOT NULL DEFAULT 0,
  `createdby` int(10) NOT NULL DEFAULT 0,
  `modifiedby` int(10) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `is_del` tinyint(1) NOT NULL DEFAULT 0,
  `permission_group_ids` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`api_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_api AUTO_INCREMENT = 1;