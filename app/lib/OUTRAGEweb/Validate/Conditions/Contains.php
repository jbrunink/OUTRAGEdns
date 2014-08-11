<?php
/**
 *	Validation condition for OUTRAGEweb: Checks to see if a condition exists.
 */


namespace OUTRAGEweb\Validate\Conditions;

use \OUTRAGEweb\Validate;
use \OUTRAGEweb\Construct;


class Contains extends Validate\Condition
{
	/**
	 *	Are we going to check this or not then?
	 */
	protected $dictionary = null;
	
	
	/**
	 *	Called whenever arguments are passed to the condition.
	 */
	public function arguments($dictionary)
	{
		if(!is_array($dictionary))
		{
			if($dictionary instanceof Construct\ObjectContainer)
				$dictionary = $dictionary->toArray();
			elseif($dictionary instanceof \Traversable)
				$dictionary = iterator_to_array($dictionary);
		}
		
		$this->dictionary = array_values($dictionary);
	}
	
	
	/**
	 *	Called to make sure that this value is a numerical value - /^[0-9]*$/
	 */
	public function validate($input)
	{
		if(!$this->dictionary)
			return false;
		
		if(!in_array($input, $this->dictionary))
			return $this->error = "Value is not valid.";
		
		return false;
	}
}