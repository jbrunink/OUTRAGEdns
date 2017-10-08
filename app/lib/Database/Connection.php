<?php


namespace OUTRAGEdns\Database;

use \OUTRAGEdns\Configuration\Configuration;
use \OUTRAGEdns\Entity\DelegatorTrait;
use \Zend\Db\Adapter\Adapter;
use \Zend\Db\Sql\Sql;


class Connection
{
	public static function getInstance()
	{
		static $instance = null;
		
		if(is_null($instance))
		{
			$config = Configuration::getInstance();
			
			$adapter = new Adapter([
				"driver" => "Pdo_Mysql",
				
				"hostname" => $config->database->production->host,
				"port" => $config->database->production->port,
				
				"database" => $config->database->production->database,
				"username" => $config->database->production->username,
				"password" => $config->database->production->password,
			]);
			
			$instance = new Sql($adapter);
		}
		
		return $instance;
	}
}