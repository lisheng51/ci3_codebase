#
# TABLE STRUCTURE FOR: bc_send_mail
#

CREATE TABLE `bc_send_mail` (
  `mail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_email` varchar(90) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_email` varchar(90) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `send_date` datetime DEFAULT NULL,
  `is_send` tinyint(1) NOT NULL DEFAULT 0,
  `open_date` datetime DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `attach` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_value` int(10) DEFAULT NULL,
  `reply_to_json` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cc_json` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bcc_json` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`mail_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_send_mail AUTO_INCREMENT = 1;