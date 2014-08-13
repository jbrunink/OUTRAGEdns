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
	 *	Where do we go on failure?
	 */
	protected $failure = null;
	
	
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
	 *	Where do we want to go on failure?
	 */
	public function failure($callback)
	{
		return $this->failure = new Router\Path("@error-404", $callback);
	}
	
	
	/**
	 *	Locate and invoke the correct path, based on a URI provided to the
	 *	router.
	 */
	public function invoke(Environment $environment)
	{
		foreach($this->routes as $route)
		{
			if($route->test($environment))
				return $route->invoke($environment);
		}
		
		if($this->failure)
			$this->failure->invoke($environment, false);
		
		return false;
	}
}