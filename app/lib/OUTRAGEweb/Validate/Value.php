<?php
/**
 *	The Value class allows us to pin point exactly what errors can
 *	be associated to what field, and make it easier to iterate
 *	through results or something.
 */


namespace OUTRAGEweb\Validate;


class Value implements Error\MessageInterface
{
	/**
	 *	Where does this pair sit on the family tree?
	 */
	public $tree = null;
	
	
	/**
	 *	What are the value(s) of this field?
	 */
	public $value = null;
	
	
	/**
	 *	What is the element that represents this particular field?
	 */
	public $element = null;
	
	
	/**
	 *	Add an error to this component, and if a parent somewhere exists,
	 *	to the parent form as well.
	 */
	public function error(Component $context, $message = "")
	{
		$error = new Error\Message();
		
		$error->name = $this->tree ? $this->compileTree($this->tree) : $context->name;
		$error->context = $context;
		$error->message = $message;
		
		$this->errors[] = $error;
		
		if(!empty($context->parent))
		{
			for($parent = $context->parent; $parent->parent != null; $parent = $parent->parent);
			
			if($parent instanceof Template)
				$parent->errors[] = $error;
		}
		
		return $this;
	}
	
	
	/**
	 *	Compiles a property tree structure into a string structure.
	 */
	protected function compileTree(array $tree)
	{
		$name = "";
		
		while(!$name)
			$name = (string) array_shift($tree);
		
		if(count($tree))
			$name .= "[".implode("][", $tree)."]";
		
		return $name;
	}
	
	
	/**
	 *	Flattens an array of Values into a nested array.
	 */
	public static function flatten(array $pairs = [], &$context = [])
	{
		foreach($pairs as $pair)
		{
			$pointer = &$context;
			
			if($count = count($pair->tree))
			{
				for($i = 0; $i < $count; ++$i)
				{
					$key = (string) $pair->tree[$i];
					
					if(!isset($pointer[$key]))
						$pointer[$key] = [];
					
					$pointer = &$pointer[$key];
				}
				
				if(is_array($pair->value))
				{
					if(isset($pair->element) && $pair->element->is_array)
					{
						foreach($pair->value as $item)
							self::flatten($item, $context);
					}
					else
					{
						self::flatten($pair->value, $context);
					}
				}
				else
				{
					$pointer = $pair->value;
				}
			}
			
			unset($pair);
			unset($pointer);
		}
		
		return $context;
	}
}