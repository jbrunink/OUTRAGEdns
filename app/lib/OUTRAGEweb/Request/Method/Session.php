<?php
/**
 *	OUTRAGEweb framework
 *
 *	Session object - deals with parsing and manipulating sessions and
 *	stuff like that.
 */


namespace OUTRAGEweb\Request\Method;


class Session extends MethodAbstract
{
	/**
	 *	We can use this constant to determine whether setter calls populate the
	 *	object directly or should just be stuck in any container, if one exists.
	 */
	const DELEGATOR_SET_UNKNOWN_INTO_CONTAINER = true;
	
	
	/**
	 *	Called whenever the method is to be initialised.
	 */
	public function __construct(&$container = null)
	{
		if(isset($container))
			$this->populateContainerFromReference($container);
	}
	
	
	/**
	 *	Handler method for setting virtual properties.
	 */
	public function __set($property, $value)
	{
		$reflection = new \ReflectionObject($this);
		
		if($reflection->hasProperty("container"))
		{
			if(array_key_exists($property, $this->container))
			{
				$this->container[$property] = $value;
				return true;
			}
		}
		
		if(!$reflection->hasMethod("setter_".$property))
			return $this->container[$property] = $value;
		
		$return = $reflection->getMethod("setter_".$property)->invoke($this, $value);
		
		if(isset($return))
			$this->{$property} = $return;
		
		return true;
	}
	
	
	/**
	 *	Reset the state of the session.
	 */
	public function reset()
	{
		$previous_state = $this->toArray();
		
		$_SESSION = [];
		$this->container = [];
		
		session_destroy();
		session_start();
		
		return $previous_state;
	}
}