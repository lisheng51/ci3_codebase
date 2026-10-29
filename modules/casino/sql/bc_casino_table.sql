#
# TABLE STRUCTURE FOR: bc_casino_table
# A shared game table (dice or blackjack). state_json holds the running round, see CasinoDiceModel / CasinoBlackjackModel.
#

CREATE TABLE `bc_casino_table` (
  `casino_table_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game` varchar(20) NOT NULL DEFAULT 'dice',
  `name` varchar(100) NOT NULL,
  `min_bet` int(10) unsigned NOT NULL DEFAULT 10,
  `max_bet` int(10) unsigned NOT NULL DEFAULT 1000,
  `max_seats` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `solo` tinyint(1) NOT NULL DEFAULT 0,
  `round_no` int(10) unsigned NOT NULL DEFAULT 0,
  `state_json` mediumtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `order_num` int(10) unsigned NOT NULL DEFAULT 0,
  `createdby` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `modifiedby` int(10) unsigned NOT NULL DEFAULT 0,
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`casino_table_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_casino_table AUTO_INCREMENT = 1;
