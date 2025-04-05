#
# TABLE STRUCTURE FOR: bc_module
#

CREATE TABLE `bc_module` (
  `module_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path_description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_list` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `use_path` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`module_id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_module AUTO_INCREMENT = 1;