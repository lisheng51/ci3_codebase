#
# TABLE STRUCTURE FOR: bc_permission
#

CREATE TABLE `bc_permission` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `object` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `link_title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_dir` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `has_link` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `order_num` int(10) unsigned NOT NULL DEFAULT 0,
  `parent_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `key` (`method`,`object`,`module_id`,`link_dir`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=648 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_permission AUTO_INCREMENT = 1;