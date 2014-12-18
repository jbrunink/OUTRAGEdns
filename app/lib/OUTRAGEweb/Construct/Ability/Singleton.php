<?php
/**
 *	Singleton trait for Phoenix - provides a simple way
 *	for classes to be loaded only once.
 */


namespace OUTRAGEweb\Construct\Ability;


trait Singleton
{
	/**
	 *	Retrieve this instance.
	 */
	public static function getInstance()
	{
		$target = get_called_class();
		
		static $instance = null;
		return $instance ?: $instance = new $target();
	}
	
	
	/**
	 *	Prevent cloning of this object.
	 */
	public function __clone()
	{
		trigger_error("Cloning ".get_called_class()." is not allowed.", E_USER_ERROR);
		return false;
	}
	
	
	/**
	 *	Prevent de-serialisation of this object, which could bring about another
	 *	cloning scenario.
	 */
	public function __wakeup()
	{
		trigger_error("Unserializing ".get_called_class()." is not allowed.", E_USER_ERROR);
		return false;
	}
}