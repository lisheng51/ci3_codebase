#
# TABLE STRUCTURE FOR: bc_language
#

CREATE TABLE `bc_language` (
  `language_id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_nl` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_list` tinyint NOT NULL DEFAULT '0',
  `folder` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_del` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bc_language` (`language_id`, `name`, `name_nl`, `icon`, `order_list`, `folder`, `code`, `is_del`) VALUES (1, 'Dutch', 'Nederlands', NULL, 1, 'dutch', 'nl', 0);
INSERT INTO `bc_language` (`language_id`, `name`, `name_nl`, `icon`, `order_list`, `folder`, `code`, `is_del`) VALUES (2, 'English', 'Engels', NULL, 2, 'english', 'en', 0);
INSERT INTO `bc_language` (`language_id`, `name`, `name_nl`, `icon`, `order_list`, `folder`, `code`, `is_del`) VALUES (3, 'Spanish', 'Spaans', NULL, 3, 'spanish', 'es', 1);
INSERT INTO `bc_language` (`language_id`, `name`, `name_nl`, `icon`, `order_list`, `folder`, `code`, `is_del`) VALUES (4, 'German', 'Duits', NULL, 4, 'german', 'de', 0);
INSERT INTO `bc_language` (`language_id`, `name`, `name_nl`, `icon`, `order_list`, `folder`, `code`, `is_del`) VALUES (5, 'French', 'Frans', NULL, 5, 'french', 'fr', 1);
INSERT INTO `bc_language` (`language_id`, `name`, `name_nl`, `icon`, `order_list`, `folder`, `code`, `is_del`) VALUES (6, 'Chinese', 'Chinese', NULL, 6, 'chinese', 'cn', 0);


