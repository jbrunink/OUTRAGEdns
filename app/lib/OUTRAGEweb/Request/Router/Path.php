<?php
/**
 *	Path class allows 
 */


namespace OUTRAGEweb\Request\Router;

use OUTRAGEweb\Construct\Ability;


class Path
{
	/**
	 *	Here lies the unadjusted route.
	 */
	public $route = null;
	
	
	/**
	 *	Here lies the pattern that is to be checked when testing
	 *	the patterns.
	 */
	public $pattern = null;
	
	
	/**
	 *	Some metadata about this path lies here.
	 */
	public $args = [];
	
	
	/**
	 *	Called whenever the path is to be constructed.
	 */
	public function __construct($route, $callback)
	{
		# say what the original route is
		$this->route = $route;
		
		# generate the callback closure
		if($callback instanceof \Closure)
		{
			$this->callback = $callback;
		}
		elseif(is_string($callback))
		{
			$this->callback = (new ReflectionFunction($callback))->getClosure();
		}
		elseif(is_array($callback))
		{
			$reflection = new \ReflectionObject($handler[0]);
			
			if($reflection->hasMethod($handler[1]))
				$this->callback = $reflection->getMethod($handler[1])->getClosure($handler[0]);
		}
		
		if(!$this->callback)
			throw new \Exception("Callback not found.");
		
		# compile the path pattern, also compile metadata
		$path = explode("/", $route);
		$path = array_filter($path);
		$path = array_values($path);
		
		$this->pattern = "";
		
		foreach($path as $item)
		{
			$this->pattern .= "\/";
			
			if(substr($item, 0, 1) == ":")
			{
				$this->pattern .= "(.*?)";
				$this->args[] = substr($item, 1);
			}
			else
			{
				$this->pattern .= preg_quote($item);
			}
		}
		
		$this->pattern .= "\/?";
	}
	
	
	/**
	 *	Called to test this route, based on a URL passed to it.
	 */
	public function test($uri)
	{
		return (boolean) preg_match("/^".$this->pattern."$/", $uri);
	}
	
	
	/**
	 *	Run the closure associated with this path.
	 */
	public function invoke($uri)
	{
		$reflection = new \ReflectionFunction($this->callback);
		
		if($reflection->getNumberOfParameters() != count($this->args))
			throw new \Exception("Arity of callback does not match that supplied in the path.");
		
		$arguments = [];
		
		if(!preg_match("/^".$this->pattern."$/", $uri, $arguments))
			throw new \Exception("Pattern does not match URI supplied.");
		
		array_shift($arguments);
		
		return call_user_func_array($this->callback, $arguments);
	}
}