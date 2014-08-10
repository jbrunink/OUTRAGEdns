<?php
/**
 *	The request environment for OUTRAGEweb.
 */


namespace OUTRAGEweb\Request;

use \OUTRAGEweb\Construct;
use \OUTRAGEweb\Construct\Ability;


class Environment
{
	/**
	 *	So, we need a delegator!
	 */
	use Ability\Delegator;
	
	
	/**
	 *	Do stuff with GET requests.
	 */
	public function getter_get()
	{
		return new Method\Get($_GET);
	}
	
	
	/**
	 *	Do stuff with POST requests.
	 */
	public function getter_post()
	{
		return new Method\Post($_POST);
	}
	
	
	/**
	 *	Do stuff with SERVER variables.
	 */
	public function getter_server()
	{
		return new Method\Server($_SERVER);
	}
	
	
	/**
	 *	Do stuff with SESSION variables.
	 */
	public function getter_session()
	{
		return new Method\Session($_SESSION);
	}
	
	
	/**
	 *	Do stuff with request headers.
	 */
	public function getter_headers()
	{
		$headers = [];
		
		if(function_exists("apache_request_headers"))
		{
			$headers = apache_request_headers();
		}
		else
		{
			foreach($_SERVER as $property => $value)
			{
				if(substr($property, 0, 5) == "HTTP_")
				{
					# this is just ugly.
					$property = str_replace("- ", "-", ucwords(str_replace("_", "- ", strtolower(substr($property, 5)))));
					$headers[$property] = $value;
				}
			}
		}
		
		return new Method\Header($headers);
	}
	
	
	/**
	 *	Does things with the URI passed to the website.
	 */
	public function getter_url()
	{
		$parsed_url = parse_url($_SERVER["REQUEST_URI"]);
		$elements = explode('/', $parsed_url["path"]);
		
		$last = end($elements);
		
		if(!strlen($last))
			array_pop($elements);
		
		$scheme = ($_SERVER["SERVER_PORT"] == 443 ? "https" : "http");
		$elements[0] = $scheme."://".$_SERVER['HTTP_HOST'];
		
		if(isset($elements[1]))
			$elements[1] = trim($elements[1]);
		else
			$elements[1] = "";
		
		return new Method\URL($elements);
	}
}