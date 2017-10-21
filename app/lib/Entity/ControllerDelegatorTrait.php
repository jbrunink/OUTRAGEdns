<?php


namespace OUTRAGEdns\Entity;


trait ControllerDelegatorTrait
{
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
}