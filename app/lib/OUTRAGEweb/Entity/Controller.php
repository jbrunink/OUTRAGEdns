<?php
/**
 *	Controller entity for OUTRAGEweb
 */


namespace OUTRAGEweb\Entity;

use \OUTRAGEweb\Construct\Ability;
use \OUTRAGEweb\Request;
use \OUTRAGEweb\Response;


abstract class Controller
{
	/**
	 *	It'd be a nice idea to include delegators here
	 */
	use Ability\Delegator;
	use Ability\Delegation;
	
	
	/**
	 *	What environment are we using for this request?
	 */
	protected $request = null;
	
	
	/**
	 *	Set the request environment that this controller is to use.
	 */
	public function setEnvironment(Request\Environment $environment)
	{
		$this->request = $environment;
	}
	
	
	/**
	 *	What object are we modifying in this request? This one!
	 */
	public function getter_content()
	{
		$class = $this->namespace."\\Content";
		
		if(!class_exists($class))
			throw new \Exception("Unable to find content/model");
		
		return new $class();
	}
	
	
	/**
	 *	What forms can we use!
	 */
	public function getter_form()
	{
		$class = $this->namespace."\\Form";
		
		if(!class_exists($class))
			return null;
		
		$form = new $class();
		
		if($this->content)
			$form->content = $this->content;
		
		return $form;
	}
	
	
	/**
	 *	What is the response going to be? By default, it is the amazing
	 *	Twig engine.
	 */
	public function getter_response()
	{
		$response = Response\Twig::getInstance();
		$response->setEnvironment($this->request);
		
		return $response;
	}
}