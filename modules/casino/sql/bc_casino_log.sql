#
# TABLE STRUCTURE FOR: bc_casino_log
# Every chip movement and game event: login, bonus, bet, win, lose, push, admin changes.
#

CREATE TABLE `bc_casino_log` (
  `casino_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `casino_user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `casino_table_id` int(10) unsigned NOT NULL DEFAULT 0,
  `game` varchar(20) NOT NULL DEFAULT '',
  `round_no` int(10) unsigned NOT NULL DEFAULT 0,
  `action` varchar(30) NOT NULL DEFAULT '',
  `amount` bigint(20) NOT NULL DEFAULT 0,
  `balance_after` bigint(20) NOT NULL DEFAULT 0,
  `detail` varchar(1000) NOT NULL DEFAULT '',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `createdby` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`casino_log_id`),
  KEY `casino_user_id` (`casino_user_id`),
  KEY `casino_table_id` (`casino_table_id`, `round_no`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_casino_log AUTO_INCREMENT = 1;
