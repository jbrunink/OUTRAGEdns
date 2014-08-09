<?php
/**
 *	Delegator trait for Phoenix - creating easier ways of complicating code
 *	beyond all belief.
 */


namespace OUTRAGEweb\Construct\Ability;


trait Delegator
{
	/**
	 *	Handler method for accessing virtual properties.
	 */
	public function &__get($property)
	{
		$return = null;
		$reflection = new \ReflectionObject($this);
		
		if($reflection->hasProperty("container"))
		{
			if(array_key_exists($property, $this->container))
				return $this->container[$property];
		}
		
		if(!$reflection->hasMethod("getter_".$property))
			return $return;
		
		# so, simple rule - if getter is defined with arity of 0, then
		# presume that whatever is returned is there to stay - it has
		# persistance. if getter has $persistance variable defined, it
		# shall use that to determine persistance. moreover, $persistance
		# can be optionally a reference, so in times of utmost confusion,
		# one can define whether a result needs to be persistant or not
		# on call time.
		$method = $reflection->getMethod("getter_".$property);
		
		if(!$method->getNumberOfParameters())
		{
			$this->{$property} = $method->invoke($this);
			return $this->{$property};
		}
		
		# and now the weird bit starts
		$parameter = $method->getParameters()[0];
		
		if(!$parameter->isOptional())
		{
			$this->{$property} = $method->invoke($this, true);
			return $this->{$property};
		}
		else
		{
			# and now we can decide wtf is going on!
			$persistance = $parameter->getDefaultValue();
			
			if($parameter->isPassedByReference())
				$return = &$method->invokeArgs($this, [ &$persistance ]);
			else
				$return = &$method->invoke($this, $persistance);
			
			if($persistance)
			{
				$this->{$property} = $return;
				return $this->{$property};
			}
		}
		
		return $return;
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
			return $this->{$property} = $value;
		
		$return = $reflection->getMethod("setter_".$property)->invoke($this, $value);
		
		if(isset($return))
			$this->{$property} = $return;
		
		return true;
	}
	
	
	/**
	 *	Handler method for checking if virtual property is set.
	 */
	public function __isset($property)
	{
		$reflection = new \ReflectionObject($this);
		
		if($reflection->hasProperty("container"))
		{
			if(array_key_exists($property, $this->container))
				return isset($this->container[$property]);
		}
		
		if(!$reflection->hasMethod("isset_".$property))
			return false;
		
		return $reflection->getMethod("isset_".$property)->invoke($this);
	}
	
	
	/**
	 *	Handler method for removing virtual properties.
	 */
	public function __unset($property)
	{
		$reflection = new \ReflectionObject($this);
		
		if($reflection->hasProperty("container"))
		{
			if(array_key_exists($property, $this->container))
			{
				unset($this->container[$property]);
				return true;
			}
		}
		
		if($reflection->hasMethod("unset_".$property))
			$reflection->getMethod("unset_".$property)->invoke($this);
		
		unset($this->{$property});
		return true;
	}
}