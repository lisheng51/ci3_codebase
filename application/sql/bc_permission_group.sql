#
# TABLE STRUCTURE FOR: bc_permission_group
#

CREATE TABLE `bc_permission_group` (
  `permission_group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permission_group_type_id` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT 0,
  `sort_list_group` int(10) unsigned NOT NULL DEFAULT 0,
  `is_lock` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `permission_ids` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`permission_group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_permission_group AUTO_INCREMENT = 1;