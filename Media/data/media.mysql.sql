--
-- Media Module MySQL Database for Phire CMS 2.0
--

-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------

--
-- Table structure for table `media_libraries`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]media_libraries` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `allowed_types` text,
  `disallowed_types` text,
  `max_filesize` int(16),
  `actions` text,
  `adapter` varchar(255),
  `order` int(16),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30001 ;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE IF NOT EXISTS `[{prefix}]media` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `library_id` int(16) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `media_library_id` (`library_id`),
  CONSTRAINT `fk_media_library` FOREIGN KEY (`library_id`) REFERENCES `[{prefix}]media_libraries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31001;

-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;
