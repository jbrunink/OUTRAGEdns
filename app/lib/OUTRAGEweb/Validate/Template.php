<?php
/**
 *	Template for array input validation for OUTRAGEweb.
 */


namespace OUTRAGEweb\Validate;


class Template extends Component
{
	/**
	 *	We shall use this to store values generated from validated input methods.
	 */
	public $values = [];
	
	
	/**
	 *	We shall use this to store errors generated from validated input methods.
	 */
	public $errors = [];
	
	
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
	 *	Validate this template based on fields passed.
	 */
	public function validate($input)
	{
		$this->performValidationIteration($input);
		
		if(!empty($input[":validate"]))
			return $this->handleAJAX();
		
		return count($this->errors) == 0;
	}
	
	
	/**
	 *	Wrapper to perform validation and return some values, if needed.
	 */
	public function performValidationIteration($input, $tree = [])
	{
		if(!is_array($input))
		{
			if($input instanceof \OUTRAGEweb\Construct\ObjectContainer)
				$input = $input->toArray();
			elseif($input instanceof \Traversable)
				$input = iterator_to_array($input);
		}
		
		if(!is_array($input))
			return $this->values = array();
		
		if(method_exists($this, "prevalidate"))
		{
			if($this->prevalidate($input) === false)
				return $this->values = array();
		}
		
		return $this->values = $this->iterate($input, $tree);
	}
	
	
	/**
	 *	Iterate through a set of values and do the validation.
	 */
	protected function iterate($input, $tree = [])
	{
		# now for the fun bit of iterating through this mess and doing our validation
		$offset = count($tree);
		$pairs = [];
				
		# iterate through our defined elements
		foreach($this->children as $element)
		{
			$tree[] = $element;
			
			# it's probably a good idea to locate the actual value we want to
			# manipulate here
			$pointer = $input;
			
			if($count = count($tree))
			{
				for($i = $offset; $i < $count; ++$i)
				{
					$name = (string) $tree[$i];
					
					if(!$name)
						continue;
					
					if($i == $count)
						break;
					
					if(isset($pointer[$name]))
					{
						$pointer = &$pointer[$name];
						continue;
					}
					
					$pointer = null;
					break;
				}
			}
			
			# do different things depending on whether this is a template - or not
			$pair = new Value();
			
			$pair->tree = $tree;
			$pair->element = $element;
			
			if($element instanceof Template)
			{
				if($element->is_array)
				{
					$pair->value = [];
					
					if(is_array($pointer))
					{
						foreach($pointer as $key => $value)
						{
							$tree[] = $key;
							$pair->value[$key] = $element->duplicate()->performValidationIteration($value, $tree);
							
							array_pop($tree);
						}
					}
				}
				else
				{
					$pair->value = $element->duplicate()->performValidationIteration($value, $tree);
				}
			}
			else
			{
				if($element->is_array)
				{
					$pair->value = [];
					
					if(is_array($pointer))
					{
						foreach($pointer as $key => $value)
							$pair->value[$key] = $element->validate($pointer, $pair);
					}
				}
				else
				{
					$pair->value = $element->validate($pointer, $pair);
				}
			}
			
			$pairs[] = $pair;
			
			unset($pair);
			unset($pointer);
			
			array_pop($tree);
		}
		
		return $pairs;
	}
	
	
	/**
	 *	Duplicate this template, making sure that changes to this will *not* affect conditions
	 *	of its siblings.
	 */
	public function duplicate()
	{
		$template = clone $this;
		
		foreach($template->children as $key => $child)
			$template->children[$key] = $child->duplicate();
		
		return $template;
	}
	
	
	/**
	 *	Retrieve values from the last validation attempt. Will return values regardless
	 *	of the validity of the last request.
	 */
	public function values()
	{
		return Value::flatten($this->values);
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