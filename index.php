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
define("TEMPLATE_DIR", getenv("TEMPLATE_DIR") ?: APP_DIR."/templates");


# let's now use composer because i'd potentially like to use my
# framework in other places
require WWW_DIR."/vendor/autoload.php";


# get some namespaces set up
use \OUTRAGEdns\Configuration\Configuration;
use \OUTRAGEdns\DynamicAddress\Controller as DynamicAddressController;
use \OUTRAGEdns\Request\Container as RequestContainer;
use \OUTRAGEdns\User\Content as UserContent;
use \OUTRAGEdns\User\Controller as UserController;
use \OUTRAGElib\Structure\ObjectList;
use \Silex\Application;
use \Silex\Provider\TwigServiceProvider;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Whoops\Handler\PrettyPageHandler;
use \WhoopsSilex\WhoopsServiceProvider;


# boot strap the config
$configuration = Configuration::getInstance();


# start the session
$session = new Session();
$session->start();


# let's mess about with silex now
$app = new Application();
$app->register(new TwigServiceProvider(), [ "twig.path" => TEMPLATE_DIR ]);

$app["twig"]->addExtension(new Twig_Extensions_Extension_Text());

#$app["debug"] = true;


# error handling?
if(true || !empty($app["debug"]))
{
	$whoops = new \Whoops\Run();
	$whoops->pushHandler(new PrettyPageHandler());
	$whoops->register();

	$app->register(new WhoopsServiceProvider());
}


# we might want to set some things up first
$app->before(function(Request $request, Application $app) use ($session)
{
	# set session
	$request->setSession($session);
	
	# if the user is not accessing the login page, and they're not logged in,
	# we'll just go ahead and re-direct them to the login page!
	if(!$session->get("authenticated_users_id"))
	{
		if(!preg_match("@^/login/@", $request->server->get("REQUEST_URI")))
		{
			header("Location: /login/");
			exit;
		}
	}
	
	# twig doesn't have a nice and lovely way to store variables in a global
	# context so we might as well use this as a sort of umbrella variable we
	# can then pass to twig
	$app["outragedns.context"] = new RequestContainer();
}, Application::EARLY_EVENT);

if($session->get("authenticated_users_id"))
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
			if($settings->default && !$settings->id)
				$app->match("/".$endpoint."/", [ $controller, $action ])->before([ $controller, "init" ]);
			
			$route = $settings->global ? ("/".$action."/") : ("/".$endpoint."/".$action."/");
			
			if($settings->id)
				$route .= "{id}/";
			
			$app->match($route, [ $controller, $action ])->before([ $controller, "init" ]);
		}
	}
	
	$app->match("/admin/on/", function(Request $request)
	{
		$session = $request->getSession();
		
		$user = new UserContent();
		$user->load($session->get("authenticated_users_id"));
		
		if($user->admin)
			$session->set("_global_admin_mode", 1);
		
		header("Location: /domains/grid/");
		exit;
	});
	
	$app->match("/admin/off/", function(Request $request)
	{
		$request->getSession()->remove("_global_admin_mode");
		
		header("Location: /domains/grid/");
		exit;
	});
	
	$app->match("/", function()
	{
		header("Location: /domains/grid/");
		exit;
	});
}
else
{
	$app->match("/", function()
	{
		header("Location: /login/");
		exit;
	});
}

# authentication
$controller = new UserController();

if($session->get("authenticated_users_id"))
	$app->match("/logout/", [ $controller, "logout" ])->before([ $controller, "init" ]);
else
	$app->match("/login/", [ $controller, "login" ])->before([ $controller, "init" ]);

# router is also required for the snazzy dynamic DNS feature
$app->match("/dynamic-dns/{token}/", [ new DynamicAddressController(), "updateDynamicAddresses" ]);

# run, run!!
$app->run();