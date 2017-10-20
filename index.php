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
define("TEMPLATE_DIR", getenv("TEMPLATE_DIR") ?: WWW_DIR."/templates");


# let's now use composer because i'd potentially like to use my
# framework in other places
require WWW_DIR."/vendor/autoload.php";


# get some namespaces set up
use \OUTRAGEdns\Configuration\Configuration;
use \OUTRAGEdns\Request\Container as RequestContainer;
use \OUTRAGEdns\User as User;
use \OUTRAGElib\Structure\ObjectList;
use \Silex\Application;
use \Silex\Provider\TwigServiceProvider;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Session\Session;
use \Whoops\Handler\PrettyPageHandler;
use \WhoopsSilex\WhoopsServiceProvider;


# error handling?
$whoops = new \Whoops\Run();
$whoops->pushHandler(new PrettyPageHandler());
$whoops->register();


# boot strap the config
$configuration = Configuration::getInstance();


# start the session
$session = new Session();
$session->start();


# let's mess about with silex now
$app = new Application();

$app->register(new WhoopsServiceProvider());
$app->register(new TwigServiceProvider(), [ "twig.path" => TEMPLATE_DIR ]);

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

if(true)
{
	$app->match("/login/", function(Request $request, Application $app)
	{
		$controller = new User\Controller();
		
		$controller->init($request, $app);
		$controller->login();
		
		return $app["twig"]->render("index.twig", $app["outragedns.context"]->toArray());
	});
}

$app->run();