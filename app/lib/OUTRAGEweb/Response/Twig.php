<?php
/**
 *	Handler for the Twig template engine - simplifies loading of all the
 *	tags and other custom functions.
 */


namespace OUTRAGEweb\Response;


class Twig extends ResponseAbstract
{
	/**
	 *	Reference to the actual template handler.
	 */
	protected $twig = null;
	
	
	/**
	 *	A set of loaders that may be useful in the course of
	 *	running the engine.
	 */
	protected $loaders = array();
	
	
	/**
	 *	Initiate this handler and the template engine.
	 */
	public function __construct()
	{
		if(!class_exists("Twig_Loader_Filesystem"))
			\Twig_Autoloader::register();
		
		$this->loaders["fs"] = new \Twig_Loader_Filesystem(WWW_DIR."/templates/");
		$this->loaders["string"] = new \Twig_Loader_String();
		
		$config = array
		(
			"debug" => true,
			"cache" => APP_DIR."/cache/templates/",
		);
		
		$this->twig = new \Twig_Environment($this->loaders["fs"], $config);
		
		if($config["debug"])
			$this->twig->addExtension(new \Twig_Extension_Debug());
		
		return true;
	}
	
	
	/**
	 *	Display the template that we're needing.
	 */
	public function display($template = null, array $arguments = [])
	{
		if(!$template)
			return null;
		
		$context = array
		(
			"request" => $this->request,
		);
		
		return $this->twig->display($template, array_merge($context, $this->toArray(false), $arguments));
	}
	
	
	/**
	 *	Renders the template that we're needing.
	 */
	public function render($template = null, array $arguments = [])
	{
		if(!$template)
			return null;
		
		$context = array
		(
			"request" => $this->request,
		);
		
		return $this->twig->display($template, array_merge($context, $this->toArray(false), $arguments));
	}
}
