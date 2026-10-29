#
# TABLE STRUCTURE FOR: bc_article_status
#

CREATE TABLE `bc_article_status` (
  `status_id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `style_class` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bc_article_status` (`status_id`, `label`, `color`, `style_class`) VALUES (1, 'Nieuw', '#337ab7', 'primary');
INSERT INTO `bc_article_status` (`status_id`, `label`, `color`, `style_class`) VALUES (2, 'Afwachten', '#f0ad4e', 'warning');
INSERT INTO `bc_article_status` (`status_id`, `label`, `color`, `style_class`) VALUES (3, 'Gesloten', '#d9534f', 'danger');
INSERT INTO `bc_article_status` (`status_id`, `label`, `color`, `style_class`) VALUES (4, 'Openbaar', '#5cb85c', 'success');


