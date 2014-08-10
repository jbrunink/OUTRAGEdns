<?php
/**
 *	The base of all input validation classes - the component.
 */


namespace OUTRAGEweb\Validate;

use \OUTRAGEweb\Construct;
use \OUTRAGEweb\Construct\Ability;


abstract class Component
{
	/**
	 *	We'd like to use some delegators to make our life ever so easier.
	 */
	use Ability\Delegator;
	use Ability\Delegation;
	
	
	/**
	 *	Store all of our family trees here.
	 */
	public $parent = null;
	public $children = [];
	
	
	/**
	 *	Chances are that this component will have a name.
	 */
	public $component = null;
	
	
	/**
	 *	This component might even have a label to boot.
	 */
	public $label = null;
	
	
	/**
	 *	Is this an array?
	 */
	public $is_array = false;
	
	
	/**
	 *	We'll store all errors here as well.
	 */
	protected $errors = [];
	
	
	/**
	 *	What index is this component currently in in some sort
	 *	of pseudo-stack? Useful for validation or rule grabbing.
	 *
	 *	Be aware to clean up after using though!!
	 */
	public $key = null;
	
	
	/**
	 *	Please extend and return this - you'll probably be needing this to
	 *	create your definitions.
	 */
	public function __construct($component = null)
	{
		$this->component = $component;
	}
	
	
	/**
	 *	Returns a list of all accessable parent properties in this scope.
	 */
	public function getter_property_tree($persistant = false)
	{
		$target = $this;
		$tree = [];
		
		while(($target = $target->parent) != null)
		{
			if($target->is_array)
				array_unshift($tree, isset($this->key) ? $this->key : 0);
			
			if($target->component)
				array_unshift($tree, $target->component);
		}
		
		$tree[] = $this->component;
		
		return $tree;
	}
	
	
	/**
	 *	Get the name of this particular component.
	 *
	 *	Rather than cache it, I'll just generate its resolved name every time.
	 *	Shouldn't cause too many problems, right?
	 */
	public function getter_name($persistant = false)
	{
		$return = "";
		
		foreach($this->property_tree as $index => $node)
			$return .= $index ? "[".$node."]" : $node;
		
		return $return;
	}
	
	
	/**
	 *	Set the name (key?) of this component.
	 */
	public function name($name = null)
	{
		if($name === null)
			return $this->component;
		
		$this->component = $name;
		return $this;
	}
	
	
	/**
	 *	Set the label of this component.
	 */
	public function label($label = null)
	{
		if($label === null)
			return $this->label;
		
		$this->label = $label;
		return $this;
	}
	
	
	/**
	 *	Toggle the flag to determine whether this is an array element
	 *	or not.
	 */
	public function isArray($toggle = null)
	{
		if($toggle === null)
			return $this->is_array;
		
		$this->is_array = (boolean) $toggle;
		return $this;
	}
	
	
	/**
	 *	Appends this element to a input template.
	 */
	public function appendTo(Template $element)
	{
		$element->append($this);
		return $this;
	}
	
	
	/**
	 *	Add an error to this component.
	 */
	public function error(Component $context, $message = "")
	{
		$this->errors[] = (object) array
		(
			"component" => $context,
			"message" => $message,
		);
		
		return $this;
	}
	
	
	/**
	 *	Retrieve errors against this element.
	 */
	public function errors($named = true)
	{
		if($named)
		{
			$set = [];
			
			foreach($this->errors as $error)
			{
				if(!isset($set[$error->component->name]))
					$set[$error->component->name] = [];
				
				$set[$error->component->name][] = $error->message;
			}
			
			return $set;
		}
		
		return $this->errors;
	}
}