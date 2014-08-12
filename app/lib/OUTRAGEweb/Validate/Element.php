<?php
/**
 *	Element for array input validation for OUTRAGEweb.
 */


namespace OUTRAGEweb\Validate;


class Element extends Component
{
	/**
	 *	Stores a list of all conditions that this element depends on for a
	 *	successful validation.
	 */
	protected $conditions = [];
	
	
	/**
	 *	Please extend and return this - you'll probably be needing this to
	 *	create your definitions.
	 */
	public function __construct($component = null)
	{
		parent::__construct($component);
		
		$this->required(true);
	}
	
	
	/**
	 *	Perform a validation on this element based on the condition.
	 */
	public function validate($input, $context = null)
	{
		$result = $input;
		
		foreach($this->conditions as $condition)
		{
			if($condition->clean()->validate($result))
				$context->error($this, $condition->error());
			
			if($condition instanceof Transformer)
				$result = $condition->transform($result);
		}
		
		return $result;
	}
	
	
	/**
	 *	So, since we're at this point, we can presume that we're going to either create
	 *	or modify a validator - so we'll do that stuff here!
	 */
	public function __call($condition, $arguments)
	{
		$matches = [];
		
		if(preg_match("/^(has|remove)([A-Za-z])$/", $condition, $matches))
		{
			$condition = $matches[2];
			$class = "\\OUTRAGEweb\\Validate\\Conditions\\".ucfirst($condition);
			
			if(!class_exists($class))
				throw new \Exception("Invalid validator condition: '".$condition."'");
			
			$found = false;
			
			foreach($this->conditions as $index => $condition)
			{
				if($condition instanceof $class)
				{
					$found = $index;
					break;
				}
			}
			
			switch($matches[1])
			{
				case "has":
					return $found !== false;
				
				case "remove":
				{
					if($found)
						unset($this->conditions[$index]);
					
					return $this;
				}
			}
		}
		else
		{
			# adding a new validator
			$class = "\\OUTRAGEweb\\Validate\\Conditions\\".ucfirst($condition);
			
			if(!class_exists($class))
				throw new \Exception("Invalid validator condition: '".$condition."'");
			
			$target = null;
			
			foreach($this->conditions as $condition)
			{
				if($condition instanceof $class)
				{
					$target = $condition;
					break;
				}
			}
			
			if(!$target)
			{
				$target = new $class();
				$this->conditions[] = $target;
			}
			
			if(method_exists($target, "arguments"))
				call_user_func_array([ $target, "arguments" ], $arguments);
			
			return $this;
		}
	}
}