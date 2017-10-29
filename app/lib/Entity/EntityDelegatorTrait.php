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
	protected function getter_class()
	{
		return $this->class = (new ReflectionObject($this))->name;
	}
	
	
	/**
	 *	Returns this object's class namespace.
	 */
	protected function getter_namespace()
	{
		return $this->namespace = (new ReflectionObject($this))->getNamespaceName();
	}
	
	
	/**
	 *	Return the config object.
	 */
	protected function getter_config()
	{
		return Configuration::getInstance();
	}
	
	
	/**
	 *	Accessing the database...
	 */
	protected function getter_db()
	{
		return Connection::getInstance();
	}
}