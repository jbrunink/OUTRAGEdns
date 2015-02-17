<?php
/**
 *	The beginning of the end for all OUTRAGEweb requests.
 */


# let's show all errors
ini_set("display_errors", "On");

if(!ini_get("date.timezone"))
	date_default_timezone_set("UTC");


# what if Xerox wants to be secure?
define("WWW_DIR", getenv("WWW_DIR") ?: $_SERVER["DOCUMENT_ROOT"]);
define("APP_DIR", getenv("APP_DIR") ?: WWW_DIR."/app");


# let's now use composer because i'd potentially like to use my
# framework in other places
require APP_DIR."/ext/lib/autoload.php";


# and now load the config
use \OUTRAGEweb\Cache\File as Cache;
use \OUTRAGEweb\Configuration\Wallet as Wallet;
use \OUTRAGEweb\Configuration\Loader\WeakJSON as ConfigLoader;
use \OUTRAGEweb\Request\Environment as Environment;
use \OUTRAGEweb\Request\Router as Router;
use \OUTRAGEdns\User as User;

$cache = Cache::getInstance();
$configuration = Wallet::getInstance();

if($cache->test("__main_config"))
{
	$configuration->load($cache->load("__main_config"));
}
else
{
	$loader = new ConfigLoader();
	
	$loader->import(APP_DIR."/etc/config/*.json");
	$loader->import(APP_DIR."/etc/config/entities/*.json");
	
	$configuration->load($loader);
}

session_start();


# perhaps it's a good idea to init our request environment, we don't need to
# do anything else here as default functionality is handled by the getters
$environment = new Environment();


# and now, what we need to do is find out what path we need to go down.
# should I make this cleaner or should I just stick to doing things the
# new fashioned way?
$user = new User\Controller();
$router = new Router();

if($environment->session->current_users_id)
{
	foreach($configuration->entities as $entity)
	{
		if(!$entity->actions)
			continue;
		
		$class = "\\".str_replace(".", "\\", $entity->namespace)."\\Controller";
		
		if(!class_exists($class))
			continue;
		
		$controller = new $class();
		$endpoint = $entity->route ?: $entity->type."s";
		
		foreach($entity->actions as $action => $settings)
		{
			$route = $settings->global ? ("/".$action."/") : ("/".$endpoint."/".$action."/");
			
			if($settings->id)
				$route .= ":id/";
			
			if(!class_exists($class))
				continue;
			
			$router->register($route, [ $controller, $action ])->before([ $controller, "init" ]);
			
			if($settings->default)
				$router->register("/".$endpoint."/", $route)->before([ $controller, "init" ]);
		}
	}
	
	$router->register("/logout/", [ $user, "logout" ])->before([ $user, "init" ]);
	
	$router->register("/admin/:mode/", function($mode) use ($environment)
	{
		$object = new User\Content();
		$object->load($environment->session->current_users_id);
		
		switch($mode)
		{
			case "on":
				if($object->admin)
				{
					$environment->session->_global_admin_mode = 1;
					break;
				}
			
			case "off":
				$environment->session->_global_admin_mode = 0;
			break;
		}
		
		header("Location: ".(!empty($environment->headers->Referer) ? $environment->headers->Referer : "/dashboard/"));
		exit;
	});
	
	$router->failure(function()
	{
		header("Location: /dashboard/");
		exit;
	});
}
else
{
	$router->register("/login/", [ $user, "login" ])->before([ $user, "init" ]);
	
	$router->failure(function()
	{
		header("Location: /login/");
		exit;
	});
}


# run our router!
$router->invoke($environment);
exit;
