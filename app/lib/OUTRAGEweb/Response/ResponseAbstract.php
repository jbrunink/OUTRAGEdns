<?php
/**
 *	The response abstract - sharing common functionality between
 *	all of the response writers.
 */


namespace OUTRAGEweb\Response;

use \OUTRAGEweb\Construct;
use \OUTRAGEweb\Construct\Ability;
use \OUTRAGEweb\Request;


abstract class ResponseAbstract extends Construct\ObjectContainer
{
	/**
	 *	We can use this constant to determine whether setter calls populate the
	 *	object directly or should just be stuck in any container, if one exists.
	 */
	const DELEGATOR_SET_UNKNOWN_INTO_CONTAINER = true;
	
	
	/**
	 *	Make this handler a Singleton.
	 */
	use Ability\Singleton;
	
	
	/**
	 *	A reference to the request, might be useful somehow.
	 */
	protected $request = null;
	
	
	/**
	 *	Set the request environment that this response uses.
	 */
	public function setEnvironment(Request\Environment $environment)
	{
		$this->request = $environment;
	}
	
	
	/**
	 *	Called to output a template to the screen.
	 */
	abstract public function display($template = null, array $arguments = []);
	
	
	/**
	 *	Called to render a template as a string.
	 */
	abstract public function render($template = null, array $arguments = []);
}