#
# TABLE STRUCTURE FOR: bc_mail_temp
#

CREATE TABLE `bc_mail_temp` (
  `mail_temp_id` int unsigned NOT NULL AUTO_INCREMENT,
  `trigger_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `createdby` int unsigned NOT NULL DEFAULT '0',
  `modifiedby` int unsigned DEFAULT '0',
  `language_id` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`mail_temp_id`),
  KEY `trigger_name` (`trigger_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_mail_temp AUTO_INCREMENT = 1;