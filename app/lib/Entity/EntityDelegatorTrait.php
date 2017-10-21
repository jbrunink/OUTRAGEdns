<?php


namespace OUTRAGEdns\Entity;

use \OUTRAGEdns\Configuration\Configuration;
use \OUTRAGEdns\Database\Connection;
use \ReflectionObject;


trait EntityDelegatorTrait
{
	/**
	 *	Returns this object's class name.
	 */
	public function getter_class()
	{
		return $this->class = (new ReflectionObject($this))->name;
	}
	
	
	/**
	 *	Returns this object's class namespace.
	 */
	public function getter_namespace()
	{
		return $this->namespace = (new ReflectionObject($this))->getNamespaceName();
	}
	
	
	/**
	 *	Return the config object.
	 */
	public function getter_config()
	{
		return Configuration::getInstance();
	}
	
	
	/**
	 *	Accessing the database...
	 */
	public function getter_db()
	{
		return Connection::getInstance();
	}
}