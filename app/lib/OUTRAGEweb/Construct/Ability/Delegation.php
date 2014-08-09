<?php
/**
 *	Delegations trait for Phoenix - provides delegations that should be useful
 *	in the course of running this software.
 */


namespace OUTRAGEweb\Construct\Ability;

use \OUTRAGEweb\Configuration;
use \OUTRAGEweb\Database\Binding\MySQL as Database;


trait Delegation
{
	/**
	 *	Returns this object's class name.
	 */
	public function getter_class()
	{
		return (new \ReflectionObject($this))->name;
	}
	
	
	/**
	 *	Returns this object's class namespace.
	 */
	public function getter_namespace()
	{
		return (new \ReflectionObject($this))->getNamespaceName();
	}
	
	
	/**
	 *	Return the config object.
	 */
	public function getter_config()
	{
		return Configuration\Wallet::getInstance();
	}
	
	
	/**
	 *	Accessing the database...
	 */
	public function getter_db()
	{
		return Database\Connection::getInstance();
	}
}