#
# TABLE STRUCTURE FOR: bc_mail_temp
#

CREATE TABLE `bc_mail_temp` (
  `mail_temp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trigger_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `createdby` int(10) unsigned NOT NULL DEFAULT 0,
  `modifiedby` int(10) unsigned DEFAULT 0,
  `language_id` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`mail_temp_id`),
  KEY `trigger_name` (`trigger_name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_mail_temp AUTO_INCREMENT = 1;