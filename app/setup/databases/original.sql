-- SQL structure based on PowerDNS specification
-- thanks, PowerDNS docs!

CREATE TABLE IF NOT EXISTS `comments` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`domain_id` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	`type` varchar(10) NOT NULL,
	`modified_at` int(11) NOT NULL,
	`account` varchar(40) NOT NULL,
	`comment` varchar(64000) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `comments_domain_id_idx` (`domain_id`),
	KEY `comments_name_type_idx` (`name`,`type`),
	KEY `comments_order_idx` (`domain_id`,`modified_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `cryptokeys` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`domain_id` int(11) NOT NULL,
	`flags` int(11) NOT NULL,
	`active` tinyint(1) DEFAULT NULL,
	`content` text,
	PRIMARY KEY (`id`),
	KEY `domainidindex` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `domainmetadata` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`domain_id` int(11) NOT NULL,
	`kind` varchar(16) DEFAULT NULL,
	`content` text,
	PRIMARY KEY (`id`),
	KEY `domainmetaidindex` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `domains` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`master` varchar(128) DEFAULT NULL,
	`last_check` int(11) DEFAULT NULL,
	`type` varchar(6) NOT NULL,
	`notified_serial` int(11) DEFAULT NULL,
	`account` varchar(40) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `records` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`domain_id` int(11) DEFAULT NULL,
	`name` varchar(255) DEFAULT NULL,
	`type` varchar(10) DEFAULT NULL,
	`content` varchar(64000) DEFAULT NULL,
	`ttl` int(11) DEFAULT NULL,
	`prio` int(11) DEFAULT NULL,
	`change_date` int(11) DEFAULT NULL,
	`disabled` tinyint(1) DEFAULT '0',
	`ordername` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
	`auth` tinyint(1) DEFAULT '1',
	PRIMARY KEY (`id`),
	KEY `nametype_index` (`name`,`type`),
	KEY `domain_id` (`domain_id`),
	KEY `recordorder` (`domain_id`,`ordername`),
	CONSTRAINT `records_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `supermasters` (
	`ip` varchar(64) NOT NULL,
	`nameserver` varchar(255) NOT NULL,
	`account` varchar(40) DEFAULT NULL,
	PRIMARY KEY (`ip`,`nameserver`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `tsigkeys` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) DEFAULT NULL,
	`algorithm` varchar(50) DEFAULT NULL,
	`secret` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `namealgoindex` (`name`,`algorithm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;