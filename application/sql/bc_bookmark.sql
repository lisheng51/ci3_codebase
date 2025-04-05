#
# TABLE STRUCTURE FOR: bc_bookmark
#

CREATE TABLE `bc_bookmark` (
  `bookmark_id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_bookmark_id` int(10) unsigned NOT NULL DEFAULT 0,
  `is_sort` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `open_new` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `is_extern` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `createdby` int(10) NOT NULL,
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `modifiedby` int(10) NOT NULL DEFAULT 0,
  `is_del` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_list` int(10) unsigned NOT NULL DEFAULT 1,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`bookmark_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_bookmark AUTO_INCREMENT = 1;