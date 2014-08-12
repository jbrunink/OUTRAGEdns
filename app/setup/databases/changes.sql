-- these changes should match up to poweradmin - i'm hoping to get something like
-- 100% consistency with poweradmin in terms of records and templates. i don't care
-- about permissions... thankfully.

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(128) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `perm_templ` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `use_ldap` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `owner` int(11) NOT NULL,
  `comment` text,
  `zone_templ_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_id` (`domain_id`),
  KEY `owner` (`owner`),
  CONSTRAINT `domain` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `zone_templ` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `descr` text NOT NULL,
  `owner` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `zone_templ_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_templ_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(6) NOT NULL,
  `content` varchar(255) NOT NULL,
  `ttl` int(11) NOT NULL,
  `prio` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `template` (`zone_templ_id`),
  CONSTRAINT `template` FOREIGN KEY (`zone_templ_id`) REFERENCES `zone_templ` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;