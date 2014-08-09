<?php
/**
 *	Configuration parser for OUTRAGEweb.
 */


namespace OUTRAGEweb\Configuration;

use \OUTRAGEweb\Construct;
use \OUTRAGEweb\Construct\Ability;


class Wallet extends Construct\ObjectContainer
{
	/**
	 *	We need to tell this that we only want this class
	 *	to be run once.
	 */
	use Ability\Singleton;
	
	
	/**
	 *	Load the configuration files for this path.
	 */
	public function load($pattern)
	{
		$items = [];
		$paths = glob($pattern);
		
		if(!$paths)
			throw new \Exception("Unable to find configuration files");
		
		foreach($paths as $path)
			$items[] = $this->compile($path);
		
		$this->populateContainerRecursively(call_user_func_array("array_replace_recursive", $items));
		
		return $this;
	}
	
	
	/**
	 *	Compiles a file into a nice little array.
	 */
	public function compile($target)
	{
		if(!file_exists($target))
			throw new \Exception("Problem with the configuration - can't find ".$target);
		
		if(!class_exists("\Services_JSON"))
			require $_SERVER["DOCUMENT_ROOT"]."/app/lib/PEAR/Services/JSON.php";
		
		$source = file($target);
		$handler = new \Services_JSON(\SERVICES_JSON_LOOSE_TYPE);
		
		# hurrah for stupidness!
		foreach($source as $line => $item)
		{
			$source[$line] = trim($item);
			
			if($source[$line] == "")
			{
				unset($source[$line]);
				continue;
			}
			
			$endchar = substr($source[$line], -1, 1);
			
			if(preg_match("/^[^\,\:\{\[]$/", $endchar))
				$source[$line] .= ",";
		}
		
		return $handler->decode("{ ".implode(" ", $source)." }");
	}
}