<?php
/**
 *	The beginning of the end for all OUTRAGEweb requests.
 */


if(!class_exists("\OUTRAGEweb\Construct\Autoloader", false))
	require $_SERVER["DOCUMENT_ROOT"]."/app/lib/OUTRAGEweb/Construct/Autoloader.php";


# bootstrap the autoloader and load the config - crucial for pretty much
# everything in the system
session_start();

\OUTRAGEweb\Construct\Autoloader::register();

$configuration = \OUTRAGEweb\Configuration\Wallet::getInstance();

if(!$configuration)
	exit;

$configuration->load($_SERVER["DOCUMENT_ROOT"]."/app/etc/config/*.json");
$configuration->load($_SERVER["DOCUMENT_ROOT"]."/app/etc/config/entities/*.json");


# it's also a good idea to register the Twig autoloader, and other settings
# related to Twig, almost the world's best template engine
if(!class_exists("\Twig_Environment", false))
	require $_SERVER["DOCUMENT_ROOT"]."app/lib/Twig/Autoloader.php";


# perhaps it's a good idea to init our request environment, we don't need to
# do anything else here as default functionality is handled by the getters
$environment = new \OUTRAGEweb\Request\Environment();
$environment->session->current_users_id = 1;


# and now, what we need to do is find out what path we need to go down.
$router = new \OUTRAGEweb\Request\Router();

foreach($configuration->entities as $entity)
{
	if(!$entity->actions)
		continue;
	
	foreach($entity->actions as $action => $settings)
	{
		$route = "/".$entity->type."s/".$action."/";
		
		if($settings->id)
			$route .= ":id/";
		
		$class = "\\".str_replace(".", "\\", $entity->namespace)."\\Controller";
		
		if(!class_exists($class))
			continue;
		
		$router->register($route, [ new $class(), $action ]);
	}
}

$router->invoke($environment);
exit;