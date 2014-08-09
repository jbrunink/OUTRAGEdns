# these changes should match up to poweradmin - i'm hoping to get something like
# 100% consistency with poweradmin - permissions are an exception, the ACL will
# be different by definition.

CREATE TABLE IF NOT EXISTS `users`
(
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(64) NOT NULL DEFAULT '0',
	`password` varchar(128) NOT NULL DEFAULT '0',
	`fullname` varchar(255) NOT NULL DEFAULT '0',
	`email` varchar(255) NOT NULL DEFAULT '0',
	`description` varchar(1024) NOT NULL DEFAULT '0',
	`active` tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `zones`
(
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`domain_id` int(11) NOT NULL DEFAULT '0',
	`owner` int(11) NOT NULL DEFAULT '0',
	`comment` varchar(1024) DEFAULT '0',
	`zone_templ_id` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `zone_templ`
(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(128) NOT NULL DEFAULT '0',
	`descr` varchar(1024) NOT NULL DEFAULT '0',
	`owner` bigint(20) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `zone_templ_records`
(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`zone_templ_id` bigint(20) NOT NULL DEFAULT '0',
	`name` varchar(255) NOT NULL DEFAULT '0',
	`type` varchar(6) NOT NULL DEFAULT '0',
	`content` varchar(255) NOT NULL DEFAULT '0',
	`ttl` bigint(20) NOT NULL DEFAULT '0',
	`prio` bigint(20) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;