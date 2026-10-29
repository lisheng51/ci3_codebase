#
# TABLE STRUCTURE FOR: bc_send_mail
#

CREATE TABLE `bc_send_mail` (
  `mail_id` int unsigned NOT NULL AUTO_INCREMENT,
  `from_email` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_email` varchar(90) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `send_date` datetime DEFAULT NULL,
  `is_send` tinyint(1) NOT NULL DEFAULT '0',
  `open_date` datetime DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attach` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `event_value` int DEFAULT NULL,
  `reply_to_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cc_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bcc_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_send_mail AUTO_INCREMENT = 1;