<?php
/**
 *	Validation condition for OUTRAGEweb: Checks if a string is in a date format.
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;


class Date extends Validate\Condition
{
	/**
	 *	We'll want to save some key data here...
	 */
	protected $pattern = null;
	protected $result = null;
	
	
	/**
	 *	Called to set the arguments.
	 */
	public function arguments($pattern)
	{
		$this->pattern = (array) $pattern;
	}
	
	
	/**
	 *	We need to check that this is a valid date constraint.
	 */
	public function validate($input)
	{
		if($this->pattern)
		{
			$timezone = new \DateTimeZone("Europe/London");
			$date = new \DateTime("now", $timezone);
			
			foreach($this->pattern as $item)
			{
				if($this->result = $date->createFromFormat($item, $input, $timezone))
					return false;
			}
			
			if($input)
				return $this->error = "Value not a supported date format.";
		}
		
		return false;
	}
}