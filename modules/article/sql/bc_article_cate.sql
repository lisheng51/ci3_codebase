#
# TABLE STRUCTURE FOR: bc_article_cate
#

CREATE TABLE `bc_article_cate` (
  `cate_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_list` smallint(5) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `createdby` int(10) NOT NULL,
  `modified_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `modifiedby` int(10) unsigned NOT NULL DEFAULT 0,
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`cate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bc_article_cate` (`cate_id`, `name`, `description`, `sort_list`, `created_at`, `createdby`, `modified_at`, `modifiedby`, `is_del`) VALUES (5, 'Documentatie', 'documentatiedddd', 0, '2019-10-04 14:25:15', 1, '2021-08-24 14:28:35', 1, 0);
INSERT INTO `bc_article_cate` (`cate_id`, `name`, `description`, `sort_list`, `created_at`, `createdby`, `modified_at`, `modifiedby`, `is_del`) VALUES (6, 'wat is mijn test', 'asdfasdfasdfasdf', 0, '2021-08-24 14:26:53', 1, '2021-08-24 14:28:29', 1, 0);
INSERT INTO `bc_article_cate` (`cate_id`, `name`, `description`, `sort_list`, `created_at`, `createdby`, `modified_at`, `modifiedby`, `is_del`) VALUES (7, 'dffd', 'vbvbv', 0, '2021-08-24 14:50:29', 1, '2023-08-18 08:58:22', 1, 0);


