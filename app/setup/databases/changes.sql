-- POWERADMIN BLOCK --
-- this block represents tables that currently exist within poweradmin, so you
-- should be able to upgrade to OUTRAGEdns if you just omit adding this stuff in

CREATE TABLE IF NOT EXISTS `users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`username` varchar(64) NOT NULL,
	`password` varchar(128) NOT NULL,
	`fullname` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`perm_templ` tinyint(4) NOT NULL, -- not really used, kept for consistency
	`active` tinyint(4) NOT NULL DEFAULT '1',
	`use_ldap` tinyint(4) NOT NULL DEFAULT '0',
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


-- OUTRAGEDNS BLOCK --
-- you will need to install these tables though, this is custom functionality that does not
-- really exist within poweradmin
CREATE TABLE `logs` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`content_type` varchar(64) NOT NULL,
	`content_id` int(11) NOT NULL,
	`action` varchar(32) NOT NULL,
	`state` longblob NOT NULL,
	`the_date` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `content` (`content_type`,`content_id`),
	KEY `action` (`content_type`,`content_id`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- you'll need to have this bit too - kept separately so that users can just "upgrade"
-- from the various versions (or perhaps if you're that way inclined, run them in parallel?
ALTER TABLE `users`
	ADD `is_admin` TINYINT(1) NOT NULL DEFAULT '0';


-- you don't really need this bit, it's just an example
START TRANSACTION;
	INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `active`, `is_admin`)
	VALUES
		(null, "admin", SHA1("ifacetherisk"), "Boring User", "outragedns@localhost", 1, 1);
COMMIT;
