#
# TABLE STRUCTURE FOR: bc_send_mail_file
#

CREATE TABLE `bc_send_mail_file` (
  `mail_file_id` int unsigned NOT NULL AUTO_INCREMENT,
  `createdby` int unsigned NOT NULL,
  `modifiedby` int unsigned DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mail_id` int unsigned NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `base64` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`mail_file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_send_mail_file AUTO_INCREMENT = 1;