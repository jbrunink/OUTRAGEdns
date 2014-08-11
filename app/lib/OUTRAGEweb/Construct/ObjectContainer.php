<?php
/**
 *	ObjectContainer class for OUTRAGEweb - simple way to change the functionality
 *	of magic functions and make it easier to add getters and setters without
 *	having to resort to chaining in different classes.
 *	
 *	It is also default functionality to access newly created properties as a part of
 *	the stored pairs - to cancel this functionality just create your own set of methods
 *	such as these: [__get, __set, __isset, __unset] - the mere existance of one of
 *	these will cause the ObjectContainer functionality of that particular method to be
 *	cancelled/ignored.
 *	
 *	There is no way to cancel the array accessing and iteration of this class, because
 *	that is the main point of this class.
 */


namespace OUTRAGEweb\Construct;


class ObjectContainer implements \ArrayAccess, \Countable, \Iterator, \Serializable
{
	/**
	 *	Include our hidden abilities - this will provide getter/setter support
	 *	across all the scopes.
	 */
	use Ability\Delegator;
	use Ability\Delegation;
	use Ability\ArrayMap;
	use Ability\Conditional;
	
	
	/**
	 *	Return the array keys of the container.
	 */
	public function toArrayKeys()
	{
		return array_keys($this->container);
	}
	
	
	/**
	 *	I'd like to return an array representation of this set.
	 */
	public final function toArray($recursive = true, $fields = null)
	{
		if(!$recursive)
		{
			if($fields)
				return array_intersect_key($this->container, array_flip($fields));
			
			return $this->container;
		}
		
		$array = [];
		
		foreach($this->container as $property => $item)
		{
			if(!$fields || in_array($property, $fields))
			{
				if($item instanceof ObjectContainer)
					$array[$property] = $item->toArray();
				else
					$array[$property] = $item;
			}
		}
		
		return $array;
	}
	
	
	/**
	 *	I'd like to also return an object representation of this set.
	 */
	public final function toObject()
	{
		return (object) $this->container;
	}
	
	
	/**
	 *	Clones an instance of this object container.
	 */
	public final function cloneContainer()
	{
		$container = new self();
		$container->populateContainerRecursively($this->toArray());
		
		return $container;
	}
	
	
	/**
	 *	This is only to be used in very, very carefully considered situations.
	 */
	protected final function &getContainerReference()
	{
		return $this->container;
	}
	
	
	/**
	 *	Populate the container from an array or object.
	 */
	public final function populateContainer($container)
	{
		if(is_array($container))
			$this->container = $container;
		elseif(is_object($container))
			$this->container = $container instanceof Traversable ? iterator_to_array($container) : get_object_vars($container);
		
		return true;
	}
	
	
	/**
	 *	Use an array reference as the container.
	 */
	public final function populateContainerFromReference(array &$container)
	{
		$this->container = &$container;
		return true;
	}
	
	
	/**
	 *	Populates the container from an array or an object, changing the
	 *	type of an object to this class recursively.
	 */
	public final function populateContainerRecursively($container)
	{
		$class = get_class($this);
		
		foreach($container as $property => $item)
		{
			if(is_array($item))
			{
				$this[$property] = new $class();
				$this[$property]->populateContainerRecursively($item);
			}
			elseif(is_object($item))
			{
				$item = $item instanceof Traversable ? iterator_to_array($item) : get_object_vars($item);
				
				$this[$property] = new $class();
				$this[$property]->populateContainerRecursively($item);
			}
			else
			{
				$this[$property] = $item;
			}
		}
	}
	
	
	/**
	 *	Resets/totally empties this object.
	 */
	public final function resetContainer()
	{
		$this->container = [];
		return true;
	}
	
	
	/**
	 *	Checks if the internal container contains a specific key.
	 */
	public final function hasContainerProperty($property)
	{
		return array_key_exists($property, $this->container);
	}
	
	
	/**
	 *	ArrayAccess interface: Checks if an offset exists.
	 */
	public final function offsetExists($property)
	{
		return isset($this->container[$property]);
	}
	
	
	/**
	 *	ArrayAccess interface: Retrieves an offset.
	 */
	public final function &offsetGet($property)
	{
		if(isset($this->container[$property]))
			return $this->container[$property];
		
		$null = null; return $null;
	}
	
	
	/**
	 *	ArrayAccess interface: Gives an offset a value.
	 */
	public final function offsetSet($property, $value)
	{
		if(!isset($property))
			return $this->container[] = $value;
		
		return $this->container[$property] = $value;
	}
	
	
	/**
	 *	ArrayAccess interface: Removes an offset from the array.
	 */
	public final function offsetUnset($property)
	{
		unset($this->container[$property]);
		return true;
	}
	
	
	/**
	 *	Countable interface: Counts the amount of accessable properties.
	 */
	public final function count()
	{
		return count($this->container);
	}
	
	
	/**
	 *	Iterator interface: Returns the current accessed property.
	 */
	public final function current()
	{
		return current($this->container);
	}
	
	
	/**
	 *	Iterator interface: Returns the current accessed key.
	 */
	public final function key()
	{
		return key($this->container);
	}
	
	
	/**
	 *	Iterator interface: Returns the next property.
	 */
	public final function next()
	{
		return next($this->container);
	}
	
	
	/**
	 *	Iterator interface: Returns the previous property.
	 */
	public final function rewind()
	{
		return reset($this->container);
	}
	
	
	/**
	 *	Iterator interface: Checks if the internal array is valid.
	 */
	public final function valid()
	{
		return current($this->container);
	}
	
	
	/**
	 *	Serializable interface: Returns a serialised representation of
	 *	the the current accessable pairs.
	 */
	public final function serialize()
	{
		return serialize($this->container);
	}
	
	
	/**
	 *	Serializable interface: Unserialised the string into the 
	 *	local accessable cache.
	 */
	public final function unserialize($container)
	{
		$this->container = unserialize($container);
		return true;
	}
}