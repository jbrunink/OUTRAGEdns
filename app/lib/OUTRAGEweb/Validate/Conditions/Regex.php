<?php
/**
 *	Validation condition for OUTRAGEweb: Checks if a pattern matches.
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class Regex extends Validate\Condition
{
	/**
	 *	Are we going to check this or not then?
	 */
	protected $pattern = null;
	
	
	/**
	 *	Called whenever arguments are passed to the condition.
	 */
	public function arguments($pattern)
	{
		$this->pattern = (array) $pattern;
	}
	
	
	/**
	 *	Called to make sure that this value is a numerical value - /^[0-9]*$/
	 */
	public function validate($input)
	{
		if($this->pattern)
		{
			foreach($this->pattern as $item)
			{
				if(preg_match($item, $input))
					return false;
			}
			
			return $this->error = "Value does not match pattern.";
		}
		
		return false;
	}
}