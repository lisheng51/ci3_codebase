#
# TABLE STRUCTURE FOR: bc_send_mail_file
#

CREATE TABLE `bc_send_mail_file` (
  `mail_file_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `createdby` int(10) unsigned NOT NULL,
  `modifiedby` int(10) unsigned DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `mail_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base64` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`mail_file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_send_mail_file AUTO_INCREMENT = 1;