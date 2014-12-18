<?php
/**
 *	Validation condition for OUTRAGEweb: Checks if a string is numeric.
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class Numeric extends Validate\Condition
{
	/**
	 *	Are we going to check this or not then?
	 */
	protected $perform = false;
	
	
	/**
	 *	Called whenever arguments are passed to the condition.
	 */
	public function arguments($perform)
	{
		$this->perform = (boolean) $perform;
	}
	
	
	/**
	 *	Called to make sure that this value is a numerical value - /^[0-9]*$/
	 */
	public function validate($input)
	{
		if($this->perform)
		{
			if(!ctype_digit($input))
				return $this->error = "Value not a numerical value.";
		}
		
		return false;
	}
}