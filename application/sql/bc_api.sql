#
# TABLE STRUCTURE FOR: bc_api
#

CREATE TABLE `bc_api` (
  `api_id` int unsigned NOT NULL AUTO_INCREMENT,
  `secret` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `max` smallint NOT NULL DEFAULT '0',
  `token_min` smallint unsigned NOT NULL DEFAULT '0',
  `createdby` int NOT NULL DEFAULT '0',
  `modifiedby` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `permission_group_ids` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`api_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_api AUTO_INCREMENT = 1;