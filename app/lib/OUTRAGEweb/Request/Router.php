<?php
/**
 *	Simple router for OUTRAGEweb.
 */


namespace OUTRAGEweb\Request;

use OUTRAGEweb\Construct\Ability;


class Router
{
	/**
	 *	It's probably beneficial to declare ourselves as a singleton object,
	 *	stop things spiraling out of control.
	 */
	use Ability\Singleton;
	
	
	/**
	 *	Store a list of valid routes.
	 */
	protected $routes = [];
	
	
	/**
	 *	Register a route.
	 */
	public function register($route, $callback)
	{
		if($route instanceof Router\Path)
		{
			if(isset($this->routes[$route->route]))
				throw new \Exception("Route is already defined.");
			
			return $this->routes[$route->route] = $route;
		}
		else
		{
			if(isset($this->routes[$route]))
				throw new \Exception("Route is already defined.");
			
			return $this->routes[$route] = new Router\Path($route, $callback);
		}
		
		return null;
	}
	
	
	/**
	 *	Locate and invoke the correct path, based on a URI provided to the
	 *	router.
	 */
	public function invoke($uri)
	{
		foreach($this->routes as $route)
		{
			if($route->test($uri))
				return $route->invoke($uri);
		}
		
		return false;
	}
}