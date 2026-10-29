#
# TABLE STRUCTURE FOR: bc_spam_word
#

CREATE TABLE `bc_spam_word` (
  `word_id` int unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`word_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE bc_spam_word AUTO_INCREMENT = 1;