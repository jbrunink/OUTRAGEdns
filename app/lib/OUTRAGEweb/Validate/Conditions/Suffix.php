<?php
/**
 *	Validation condition for OUTRAGEweb: Checks if a string is numeric.
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class Suffix extends Validate\Condition
{
	/**
	 *	Are we going to check this or not then?
	 */
	protected $suffix = [];
	
	
	/**
	 *	Called whenever arguments are passed to the condition.
	 */
	public function arguments($suffix = [])
	{
		$this->suffix = is_array($suffix) ? $suffix : array($suffix);
	}
	
	
	/**
	 *	Called to make sure that this value is a numerical value - /^[0-9]*$/
	 */
	public function validate($input)
	{
		if(!$this->suffix)
			return false;
		
		foreach($this->suffix as $suffix)
		{
			if(preg_match("/.*".preg_quote($suffix)."$/", $input))
				return false;
		}
		
		return $this->error = "Value does not end with '".implode("', '", $this->suffix)."'.";
	}
}