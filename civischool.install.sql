CREATE TABLE `school_extended_care_source` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `term` varchar(16) COLLATE utf8_unicode_ci DEFAULT 'Fall 2009',
  `min_grade` int(10) unsigned DEFAULT '1',
  `max_grade` int(10) unsigned DEFAULT '8',
  `day_of_week` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session` varchar(32) COLLATE utf8_unicode_ci DEFAULT 'First',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instructor` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fee_block` double DEFAULT '1',
  `total_fee_block` double NOT NULL DEFAULT '0' COMMENT 'Total fee blocks for this class in this session',
  `max_participants` int(10) unsigned DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this activity active?',
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_rows` int(11) NOT NULL DEFAULT '0',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0',
  `is_free_class` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `INDEX_sfschool_extended_care_source` (`term`,`day_of_week`,`session`,`is_active`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=236 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

