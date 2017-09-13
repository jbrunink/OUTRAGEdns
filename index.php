<?php
/**
 *	The beginning of the end for all OUTRAGEweb requests.
 */


# let's show all errors
if(!ini_get("date.timezone"))
	date_default_timezone_set("UTC");


# what if Xerox wants to be secure?
define("WWW_DIR", getenv("WWW_DIR") ?: $_SERVER["DOCUMENT_ROOT"]);
define("APP_DIR", getenv("APP_DIR") ?: WWW_DIR."/app");


# let's now use composer because i'd potentially like to use my
# framework in other places
require WWW_DIR."/vendor/autoload.php";

$whoops = new \Whoops\Run();
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
$whoops->register();


# and now load the config
use \OUTRAGEweb\Cache\File as Cache;
use \OUTRAGEweb\Configuration\Wallet as Wallet;
use \OUTRAGEweb\Request\Environment as Environment;
use \OUTRAGEweb\Request\Router as Router;
use \OUTRAGEdns\User as User;

use Symfony\Component\Yaml\Yaml;

$array = [];

foreach(glob(APP_DIR."/etc/config/*.yaml") as $file)
	$array = array_merge_recursive($array, Yaml::parse(file_get_contents($file)));

foreach(glob(APP_DIR."/etc/config/entities/*.yaml") as $file)
	$array = array_merge_recursive($array, Yaml::parse(file_get_contents($file)));

$configuration = Wallet::getInstance();
$configuration->load($array);

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
		
		header("Location: ".(!empty($environment->headers->Referer) ? $environment->headers->Referer : "/domains/grid/"));
		exit;
	});
	
	$router->failure(function()
	{
		header("Location: /domains/grid/");
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

# router is also required for the snazzy dynamic DNS feature
$router->register("/dynamic-dns/:token/", [ new \OUTRAGEdns\DynamicAddress\Controller(), "updateDynamicAddresses" ]);


# run our router!
$router->invoke($environment);
exit;
