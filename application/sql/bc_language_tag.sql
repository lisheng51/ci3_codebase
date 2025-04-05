#
# TABLE STRUCTURE FOR: bc_language_tag
#

CREATE TABLE `bc_language_tag` (
  `language_tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdby` int(10) unsigned NOT NULL DEFAULT 0,
  `modifiedby` int(10) unsigned DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `language_id` tinyint(2) unsigned NOT NULL DEFAULT 0,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`language_tag_id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_language_tag AUTO_INCREMENT = 1;