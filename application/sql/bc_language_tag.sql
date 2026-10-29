#
# TABLE STRUCTURE FOR: bc_language_tag
#

CREATE TABLE `bc_language_tag` (
  `language_tag_id` int unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdby` int unsigned NOT NULL DEFAULT '0',
  `modifiedby` int unsigned DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `language_id` tinyint unsigned NOT NULL DEFAULT '0',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`language_tag_id`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_language_tag AUTO_INCREMENT = 1;