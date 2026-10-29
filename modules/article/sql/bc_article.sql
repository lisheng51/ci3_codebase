#
# TABLE STRUCTURE FOR: bc_article
#

CREATE TABLE `bc_article` (
  `article_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_title` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cate_id` smallint(5) unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdby` int(10) NOT NULL,
  `modifiedby` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `status_id` tinyint(2) unsigned NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT 0,
  `hits` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_article AUTO_INCREMENT = 1;