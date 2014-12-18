<?php
/**
 *	Validation condition for OUTRAGEweb: Required values.
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class Required extends Validate\Condition
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
	 *	Called to make sure that this value does indeed exist.
	 */
	public function validate($input)
	{
		if($this->perform)
		{
			if($input === null || $input === "")
				return $this->error = "Value not supplied.";
		}
		
		return false;
	}
}