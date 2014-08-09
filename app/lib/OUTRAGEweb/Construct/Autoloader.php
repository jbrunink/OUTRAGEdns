<?php
/**
 *	Autoloader register method - we want this to be called
 *	to load all OUTRAGEweb libraries/classes.
 *
 *	Previous comment, saved for no reason, apart from prosperity for
 *	a BBC Radio 4 programme:
 *
 *		Yes, I'm scared, I'm afraid, I'm afeared. Which I know, for a Lion is
 *		rather weird. I'm like a rose that's been deflowered or a bath that's
 *		been deshowered or a horse that's underpowered. I am so chicken hearted
 *		that I jump when someone one... shouted. I'm afraid, that's my role, in
 *		a bagel I'm the hole, I'm Sir Noël without the Noël, I'm a coward.
 *
 *	Happy? I know I am...
 */


namespace OUTRAGEweb\Construct;


class Autoloader
{
	/**
	 *	Register the autoloader.
	 */
	public static function register()
	{
        spl_autoload_register(array(new self, "autoload"));
	}
	
	
	/**
	 *	Our autoloader method. Not PSR-0 as of such, but there are some things
	 *	best left out of PSR-0.
	 */
	public static function autoload($class)
	{
		if($path = self::getAutoloadPath($class))
		{
			require $path;
			return true;
		}
		
		return false;
	}
	
	
	/**
	 *	Our autoloader helper function - now we've separated it we can do awful things
	 *	like caching class paths and such.
	 */
	public static function getAutoloadPath($class)
	{
		$spec = explode("\\", $class);
		$spec = array_filter($spec);
		$spec = array_values($spec);
		
		$location = $_SERVER["DOCUMENT_ROOT"]."/app/lib/".implode("/", $spec).".php";
		
		if(file_exists($location))
			return $location;
		
		return null;
	}
}