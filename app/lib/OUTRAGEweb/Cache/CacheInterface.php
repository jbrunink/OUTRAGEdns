<?php
/**
 *	Defining what methods we should have as our cache abstract.
 */


namespace OUTRAGEweb\Cache;


interface CacheInterface
{
	/**
	 *	Check to see if a cached object exists/is valid.
	 */
	public function test($key);
	
	
	/**
	 *	Load a cached object.
	 */
	public function load($key);
	
	
	/**
	 *	Save a cached object.
	 */
	public function save($key, $value, $expiry = 0);
	
	
	/**
	 *	Remove a cached object.
	 */
	public function remove($key);
}