#
# TABLE STRUCTURE FOR: bc_permission_group
#

CREATE TABLE `bc_permission_group` (
  `permission_group_id` int unsigned NOT NULL AUTO_INCREMENT,
  `permission_group_type_id` tinyint unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `sort_list_group` int unsigned NOT NULL DEFAULT '0',
  `is_lock` tinyint unsigned NOT NULL DEFAULT '0',
  `permission_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`permission_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_permission_group AUTO_INCREMENT = 1;