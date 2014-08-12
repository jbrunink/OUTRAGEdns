<?php
/**
 *	Provides rudimentary file cache support.
 */


namespace OUTRAGEweb\Cache;

use \OUTRAGEweb\Construct;
use \OUTRAGEweb\Construct\Ability;


class File implements CacheInterface
{
	/**
	 *	We want to be able to be loaded only once, right?
	 */
	use Ability\Singleton;
	
	
	/**
	 *	Is this a valid key to load?
	 */
	public function test($key)
	{
		return file_exists($_SERVER["DOCUMENT_ROOT"]."/app/cache/blocks/".sha1($key).".object");
	}
	
	
	/**
	 *	Load the key.
	 */
	public function load($key)
	{
		if(!$this->test($key))
			return null;
		
		return unserialize(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/app/cache/blocks/".sha1($key).".object"));
	}
	
	
	/**
	 *	Save the value.
	 */
	public function save($key, $value, $expiry = 0)
	{
		return file_put_contents($_SERVER["DOCUMENT_ROOT"]."/app/cache/blocks/".sha1($key).".object", serialize($value));
	}
	
	
	/**
	 *	Remove the value.
	 */
	public function remove($key)
	{
		return unlink($_SERVER["DOCUMENT_ROOT"]."/app/cache/blocks/".sha1($key).".object");
	}
}