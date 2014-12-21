<?php
/**
 *	Validation condition for OUTRAGEweb: Checks if a string is numeric.
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class Prefix extends Validate\Condition
{
	/**
	 *	Are we going to check this or not then?
	 */
	protected $prefix = [];
	
	
	/**
	 *	Called whenever arguments are passed to the condition.
	 */
	public function arguments($prefix = [])
	{
		$this->prefix = is_array($prefix) ? $prefix : array($prefix);
	}
	
	
	/**
	 *	Called to make sure that this value is a numerical value - /^[0-9]*$/
	 */
	public function validate($input)
	{
		if(!$this->prefix)
			return false;
		
		foreach($this->prefix as $prefix)
		{
			if(preg_match("/^".preg_quote($prefix)."/", $input))
				return false;
		}
		
		return $this->error = "Value does not begin with '".implode("', '", $this->prefix)."'.";
	}
}