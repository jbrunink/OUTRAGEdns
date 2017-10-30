ALTER TABLE `users` 
	CHANGE COLUMN `active` `active` TINYINT(4) NOT NULL DEFAULT 0,
	CHANGE COLUMN `description` `description` TEXT NOT NULL DEFAULT "",
	CHANGE COLUMN `perm_templ` `perm_templ` TEXT NOT NULL DEFAULT "",
	CHANGE COLUMN `use_ldap` `use_ldap` TINYINT(4) NOT NULL DEFAULT 0;