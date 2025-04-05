#
# TABLE STRUCTURE FOR: bc_spam_word
#

CREATE TABLE `bc_spam_word` (
  `word_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`word_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_spam_word AUTO_INCREMENT = 1;