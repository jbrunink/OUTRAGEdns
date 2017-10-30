-- OUTRAGEDNS BLOCK
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
	ADD `admin` TINYINT(1) NOT NULL DEFAULT '0';


-- you don't really need this bit, it's just an example
START TRANSACTION;
	DELETE FROM users;
	
	INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `active`, `admin`)
	VALUES
		(null, "admin", SHA1("ifacetherisk"), "Boring User", "outragedns@localhost", 1, 1);
COMMIT;


-- This section is for custom dynamic DNS interfaces
-- DW, 09/01
DROP TABLE IF EXISTS `dynamic_addresses`;
DROP TABLE IF EXISTS `dynamic_addresses_records`;

CREATE TABLE `dynamic_addresses` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`owner` int(11) DEFAULT NULL,
	`name` varchar(64) DEFAULT NULL,
	`token` varchar(128) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `dynamic_addresses_records` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`dynamic_address_id` int(11) NOT NULL,
	`name` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `parent` (`dynamic_address_id`)
) ENGINE=InnoDB;

ALTER TABLE `dynamic_addresses_records` 
	ADD COLUMN `domain_id` INT(11) NOT NULL AFTER `dynamic_address_id` DEFAULT '0';