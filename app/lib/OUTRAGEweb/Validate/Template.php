<?php
/**
 *	Template for array input validation for OUTRAGEweb.
 */


namespace OUTRAGEweb\Validate;


class Template extends Component
{
	/**
	 *	We shall use this to store values generate from validated input methods.
	 */
	protected $values = [];
	
	
	/**
	 *	Please extend and return this - you'll probably be needing this to
	 *	create your definitions.
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
		
		$this->rules();
	}
	
	
	/**
	 *	Template function to generate rules.
	 */
	public function rules()
	{
		return true;
	}
	
	
	/**
	 *	Appends a child element to this element.
	 */
	public function append(Component $element)
	{
		if($element->parent)
			$element->parent->remove($element);
		
		$element->parent = $this;
		
		$this->children[] = $element;
		
		return $this;
	}
	
	
	/**
	 *	Removes a child element from this element.
	 */
	public function remove(Component $element)
	{
		$element->parent = null;
		
		foreach($this->children as $index => $child)
		{
			if($element === $child)
				unset($this->children[$index]);
		}
		
		$this->children = array_values($this->children);
		
		return $this;
	}
	
	
	/**
	 *	Create a new element, do not append to any validator.
	 */
	public function element($component)
	{
		if($component instanceof Element)
			return $component->appendTo($this);
		
		if(is_array($component))
		{
			$set = [];
			
			foreach($component as $item)
				$set[] = new Element($item);
			
			return $set;
		}
		
		return new Element($component);
	}
	
	
	/**
	 *	Creates a new template, ready for elements to be applied to it.
	 */
	public function template($component)
	{
		if($component instanceof Template)
			return $component->appendTo($this);
		
		if(is_array($component))
		{
			$set = [];
			
			foreach($component as $item)
				$set[] = new Template($item);
			
			return $set;
		}
		
		return new Template($component);
	}
	
	
	/**
	 *	Retrieves an child on this template level.
	 */
	public function getElement($component)
	{
		if($this->children)
		{
			foreach($this->children as $child)
			{
				if($child->component == $component)
					return $child;
			}
		}
		
		return null;
	}
	
	
	/**
	 *	Checks if this template already has an element with the same name
	 *	already on this template level.
	 */
	public function hasElement($component)
	{
		if($this->children)
		{
			foreach($this->children as $child)
			{
				if($child->component == $component)
					return true;
			}
		}
		
		return false;
	}
	
	
	/**
	 *	Iterate through the templates and invokes the callback with that input
	 *	passed as an argument.
	 *
	 *	The callback is passed two arguments: child component; value - if null,
	 *	then the value doesn't exist.
	 */
	public function iterate($input, $callback)
	{
		if($input instanceof \OUTRAGEweb\Construct\ObjectContainer)
			$input = $input->toArray();
		elseif($input instanceof \Traversable)
			$input = iterator_to_array($input);
		
		if(is_array($input))
		{
			foreach($this->children as $child)
			{
				if(empty($child->component))
				{
					if($child instanceof Template)
						$child->iterate($input, $callback);
				}
				elseif(isset($input[$child->component]))
				{
					if($child instanceof Template)
						$child->iterate($input[$child->component], $callback);
					else
						$callback($child, $input[$child->component]);
				}
				else
				{
					if($child instanceof Template)
						$child->iterate([], $callback);
					else
						$callback($child, null);
				}
			}	
		}
		
		return $this;
	}
	
	
	/**
	 *	We'll use this to validate elements.
	 */
	public function validate($input)
	{
		$this->errors = [];
		$this->values = [];
		
		$this->iterate($input, function($element, $value)
		{
			$result = $element->validate($value, $this);
			
			$tree = $element->property_tree;
			$target = &$this->values;
			
			for($i = 0; $i < $count = count($tree); ++$i)
			{
				$node = $tree[$i];
				
				if(($i + 1) == $count)
				{
					$target[$node] = $result;
					break;
				}
				
				if(!isset($target[$node]))
					$target[$node] = [];
				
				$target = &$target[$node];
			}
		});
		
		if(!empty($input[":validate"]))
			return $this->handleAJAX();
		
		return count($this->errors) == 0;
	}
	
	
	/**
	 *	Retrieve values from the last validation attempt. Will return values regardless
	 *	of the validity of the last request.
	 */
	public function values()
	{
		return $this->values;
	}
	
	
	/**
	 *	This will only get called if AJAX validation has been requested.
	 */
	public function handleAJAX()
	{
		$errors = $this->errors();
		
		$result = array
		(
			"errors" => $errors ?: false,
		);
		
		echo json_encode($result, JSON_PRETTY_PRINT);
		exit;
	}
}